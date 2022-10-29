<?php

namespace Monet\Stock;

use Barryvdh\Snappy\ServiceProvider as SnappyServiceProvider;
use Filament\PluginServiceProvider;
use Monet\Framework\Monet;
use Monet\Stock\Filament\Resources\ProductResource;
use Monet\Stock\Filament\Resources\StockResource;
use Spatie\LaravelPackageTools\Package;

class StockServiceProvider extends PluginServiceProvider
{
    public static string $name = 'stock';

    protected array $resources = [
        ProductResource::class,
        StockResource::class
    ];

    public function packageConfigured(Package $package): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Monet::group('Stock');

        $this->app->register(SnappyServiceProvider::class);

        $this->updateSnappyConfig();

        $this->publishes([
            __DIR__ . '/../dist' => public_path('monet/stock'),
        ], 'assets');
    }

    protected function updateSnappyConfig(): void
    {
        $binary = windows_os()
            ? 'wkhtmltopdf64.exe.bat'
            : 'wkhtmltopdf-amd64';

        config()->set(
            'snappy.pdf.binary',
            module_path('monet-php/stock', 'vendor/bin/' . $binary)
        );
    }

    public function publishAssets(): array
    {
        return [
            'assets'
        ];
    }
}
