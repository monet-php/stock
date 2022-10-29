<?php

namespace Monet\Stock;

use Aws\Textract\TextractClient;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Monet\Stock\Job\StockDocumentScanJob;

class StockManager
{
    protected TextractClient $client;

    public function __construct()
    {
        $this->client = new TextractClient([
            'region' => config('stock.aws.region'),
            'version' => 'latest',
            'credentials' => config('stock.aws.credentials')
        ]);
    }

    public function queue(string|array $files, array $data): void
    {
        $files = is_array($files) ? $files : [$files];

        dispatch(new StockDocumentScanJob(
            auth()->id(),
            Carbon::parse($data['date']),
            $data['auto_detect'],
            $files,
            $data['category'],
            $data['notes']
        ));
    }

    public function scan(string $file): ?array
    {
        return rescue(function () use ($file) {
            $document = $this->analyseDocument($file);
            $cells = $this->getCells($document);

            $count = count($cells);
            if ($count % 3 !== 0) {
                return null;
            }

            $products = [];
            for ($index = 0; $index < $count;) {
                $idCell = $cells[$index++];
                $productCell = $cells[$index++];
                $amountCell = $cells[$index++];

                $id = $this->getCellText($document, $idCell);
                $product = $this->getCellText($document, $productCell);

                $amount = $this->getCellText($document, $amountCell);
                if (empty($amount)) {
                    $amount = 0;
                }

                $products[] = [
                    'id' => $id,
                    'product' => $product,
                    'amount' => (float)$amount
                ];
            }

            return $products;
        });
    }

    protected function analyseDocument(string $file): Collection
    {
        $result = $this->client->analyzeDocument([
            'Document' => [
                'Bytes' => File::get($file)
            ],
            'FeatureTypes' => ['TABLES']
        ])->toArray()['Blocks'];

        return collect($result)
            ->mapWithKeys(function ($i) {
                return [
                    $i['Id'] => $i
                ];
            });
    }

    protected function getCells(Collection $document)
    {
        return $document
            ->filter(function ($i) {
                if ($i['BlockType'] !== 'CELL') {
                    return false;
                }

                if (!empty($i['EntityTypes']) && $i['EntityTypes'][0] === 'COLUMN_HEADER') {
                    return false;
                }

                return true;
            })
            ->values();
    }

    protected function getCellText(Collection $data, array $cell): string
    {
        if (empty($cell['Relationships'])) {
            return '';
        }

        return collect($cell['Relationships'])
            ->filter(fn($i) => $i['Type'] === 'CHILD')
            ->map(fn($i) => $i['Ids'])
            ->flatten()
            ->map(fn($i) => $data->get($i)['Text'])
            ->join(' ');
    }
}
