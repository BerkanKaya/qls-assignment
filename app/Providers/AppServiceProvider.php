<?php

namespace App\Providers;

use App\Factories\DTOFactory;
use App\Factories\ShipmentRequestFactory;
use App\Services\Document\ShippingDocumentService;
use App\Services\PDF\LabelService;
use App\Services\PDF\PackingSlipService;
use App\Services\PDF\PDFMergerService;
use App\Services\QLS\ProductService;
use App\Services\QLS\QLSApiService;
use App\Services\QLS\ShipmentService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->bindQls();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    private function bindQls(): void
    {
        $this->app->singleton(QLSApiService::class, fn() => QLSApiService::fromConfig());
        $this->app->singleton(
            ProductService::class,
            fn($app) =>
            new ProductService($app->make(QLSApiService::class), config('qls.company_id'))
        );
        $this->app->singleton(
            ShipmentService::class,
            fn($app) =>
            new ShipmentService($app->make(QLSApiService::class), config('qls.company_id'))
        );
        $this->app->singleton(DTOFactory::class);
        $this->app->singleton(ShipmentRequestFactory::class);
        $this->app->singleton(PackingSlipService::class);
        $this->app->singleton(LabelService::class, function () {
            $qls = config('qls');

            return new LabelService(
                username: $qls['username'],
                password: $qls['password'],
                timeout: $qls['timeout']
            );
        });
        $this->app->singleton(PDFMergerService::class);
        $this->app->singleton(ShippingDocumentService::class);
    }
}
