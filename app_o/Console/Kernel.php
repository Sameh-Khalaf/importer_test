<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        Commands\CreateDatabaseAndMigrate::class,
        Commands\Flight\PrepareFiles::class,
        Commands\Flight\QueueProcess::class,
        Commands\Flight\QueueHandle::class,
        Commands\Flight\AutoImport::class,
        Commands\Flight\FixDuplicatesInvRemarks::class,
        Commands\Flight\Fix::class,
        Commands\Flight\FixUserSign::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }
}
