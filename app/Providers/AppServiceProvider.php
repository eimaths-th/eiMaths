<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FileUploadService;
use App\Services\MoneyConvertService;
use App\Interfaces\MoneyConvertServiceInterface;
use App\Models\Contact;
use Illuminate\Database\Console\Migrations\StatusCommand;

use Illuminate\Pagination\Paginator;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

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
        // Blade::component('backend.status', Status::class);
        Paginator::useBootstrap();
        Blade::component('frontend.infixlmstheme.pages.ebook-plan-checkout-page-section', 'ebook-plan-checkout-page-section');
        view()->composer('*', function ($view) {
            // Check if a student is authenticated
            if (Auth::guard('student')->check()) {
                $student = Contact::find(Auth::guard('student')->user()->id);
                $view->with('student', $student);
            }
            // Check if a regular user is authenticated
            elseif (Auth::check()) {
                $user = Auth::user();
                $view->with('user', $user);
            }
        });
    }
}
