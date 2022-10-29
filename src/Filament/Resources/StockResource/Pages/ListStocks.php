<?php

namespace Monet\Stock\Filament\Resources\StockResource\Pages;

use Arr;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Carbon\Carbon;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\Rule;
use Monet\Stock\Filament\Resources\StockResource;
use Monet\Stock\Job\StockDocumentScanJob;
use Monet\Stock\Models\Product;
use Monet\Stock\Models\ProductCategory;
use Monet\Stock\StockManager;

class ListStocks extends ListRecords
{
    protected static string $resource = StockResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('import')
                ->label('Import')
                ->action('import')
                ->color('success')
                ->form([
                    DatePicker::make('date')
                        ->label('Date')
                        ->required()
                        ->default(now()->startOfWeek()),
                    Grid::make()
                        ->columns([
                            'sm' => 2
                        ])
                        ->schema([
                            Select::make('category')
                                ->label('Category')
                                ->options(ProductCategory::all()->pluck('name', 'id')),
                            Checkbox::make('auto_detect')
                                ->label('Auto detect')
                                ->helperText('Auto detect the category from the document. If disabled, you will need to provide a category.')
                                ->default(true)
                                ->rule('accepted_if:category,null')
                        ]),

                    MarkdownEditor::make('notes')
                        ->maxLength(65_535)
                        ->columnSpan(2),

                    FileUpload::make('images')
                        ->label('Documents')
                        ->required()
                        ->multiple()
                        ->directory('scan-tmp')
                        ->acceptedFileTypes([
                            'image/png',
                            'image/jpeg'
                        ])
                ]),
            Action::make('export')
                ->label('Export')
                ->action('export')
                ->color('secondary')
                ->form([
                    Select::make('category')
                        ->label('Category')
                        ->options(ProductCategory::all()->pluck('name', 'id'))
                ]),
            ...parent::getActions()
        ];
    }

    public function import(StockManager $manager, ComponentContainer $form, array $data)
    {
        $component = Arr::last($form->getFlatComponents());

        $storage = $component->getDisk();

        $files = collect($data['images'])
            ->map(fn($file): string => $storage->path($file))
            ->all();

        $manager->queue($files, $data);

        Notification::make()
            ->success()
            ->title('Import queued')
            ->body('You will be notified once it has been completed')
            ->send();
    }

    public function export(array $data)
    {
        ['category' => $categoryId] = $data;

        $category = ProductCategory::query()
            ->find($categoryId);

        if ($category === null) {
            Notification::make()
                ->danger()
                ->title('Invalid category')
                ->body('Product category could not be found')
                ->send();

            return;
        }

        $products = Product::query()
            ->where('category_id', '=', $categoryId)
            ->lazy();

        return response()->streamDownload(function () use ($category, $products) {
            echo SnappyPdf::loadView('stock::report', [
                'category' => $category,
                'products' => $products
            ])->output();
        }, 'The Queens Head Stock - ' . $category->name . '.pdf');
    }
}
