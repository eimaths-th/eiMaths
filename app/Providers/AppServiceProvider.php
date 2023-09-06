<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FileUploadService;
use App\Services\MoneyConvertService;
use App\Interfaces\MoneyConvertServiceInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('file-upload-service', function () {
            return new FileUploadService();
        });
        $this->app->bind(MoneyConvertServiceInterface::class, MoneyConvertService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
