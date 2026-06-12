<?php

namespace App\Providers;

use App\Models\Notification;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('files', function ($app) {
            return new Filesystem;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        // Make $notifications available in all admin views
        View::composer('admin.*', function ($view) {
            $view->with('notifications', Notification::latest()->take(10)->get());
        });
    }
}
