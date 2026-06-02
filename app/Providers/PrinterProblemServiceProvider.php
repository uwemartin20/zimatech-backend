<?php

namespace App\Providers;

use App\Interfaces\PrinterProblemRepositoryInterface;
use App\Repositories\EloquentPrinterProblemRepository;
use Illuminate\Support\ServiceProvider;

class PrinterProblemServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            PrinterProblemRepositoryInterface::class,
            EloquentPrinterProblemRepository::class,
        );
    }

    public function boot(): void
    {
        //
    }
}