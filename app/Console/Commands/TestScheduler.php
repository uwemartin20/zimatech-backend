<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:scheduler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Scheduler is running at ' . now());

        // Also write to a file you can easily inspect
        file_put_contents(
            storage_path('logs/scheduler_test.log'),
            'Scheduler ran at ' . now() . PHP_EOL,
            FILE_APPEND
        );

        return self::SUCCESS;
    }
}
