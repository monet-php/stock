<?php

namespace Monet\Stock\Job;

use Carbon\Carbon;
use DateTimeInterface;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Monet\Framework\Auth\Models\User;
use Monet\Stock\Filament\Resources\StockResource;
use Monet\Stock\Models\Product;
use Monet\Stock\Models\ProductCategory;
use Monet\Stock\Models\ProductStock;
use Monet\Stock\Models\Stock;
use Monet\Stock\StockManager;

class StockDocumentScanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;

    protected DateTimeInterface $date;

    protected bool $autoDetect;

    protected array $files;

    protected ?int $categoryId;

    protected ?string $notes;

    public function __construct(
        int $userId,
        DateTimeInterface $date,
        bool $autoDetect,
        array $files,
        ?int $categoryId = null,
        ?string $notes = null
    )
    {
        $this->userId = $userId;
        $this->date = $date;
        $this->autoDetect = $autoDetect;
        $this->files = $files;
        $this->categoryId = $categoryId;
        $this->notes = $notes;
    }

    public function handle(StockManager $manager): void
    {
        $products = $this->getProducts($manager);

        if (empty($products)) {
            return;
        }

        $categoryId = $this->categoryId;
        if ($this->autoDetect) {
            $index = 0;
            while ($categoryId === null) {
                $categoryId = Product::query()
                    ->find($products[$index]['id'])
                    ?->category_id;
            }
        }

        $user = User::query()
            ->find($this->userId);

        if (!$category = ProductCategory::query()->find($categoryId)) {
            $this->notifyUser(
                $user,
                Notification::make()
                    ->danger()
                    ->title('Stock import failed')
                    ->body('Failed to detect the category')
                    ->sendToDatabase($user)
            );

            return;
        }

        $stock = Stock::query()
            ->create([
                'date' => $this->date,
                'category_id' => $categoryId,
                'notes' => $this->notes
            ]);

        ProductStock::query()
            ->insert(
                collect($products)
                    ->map(fn(array $product): array => [
                        'product_id' => $product['id'],
                        'stock_id' => $stock->id,
                        'amount' => $product['amount']
                    ])
                    ->all()
            );

        $this->notifyUser(
            $user,
            Notification::make()
                ->success()
                ->title($category->name . ' stock import successfully')
                ->actions([
                    Action::make('view')
                        ->label('View')
                        ->button()
                        ->url(StockResource::getUrl('edit', [$stock->id]), true)
                ])
        );
    }

    protected function getProducts(StockManager $manager): array
    {
        $products = [];
        foreach ($this->files as $file) {
            $products[] = $manager->scan($file) ?? [];

            File::delete($file);
        }

        return array_merge(...$products);
    }

    protected function notifyUser(?User $user, Notification $notification): void
    {
        if ($user === null) {
            return;
        }

        $notification->sendToDatabase($user);
    }
}
