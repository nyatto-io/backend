<?php

namespace App\Console;

use App\Console\Commands\ImportScoutModels;
use App\Console\Commands\RefreshAnimeCache;
use App\Console\Commands\RefreshMangaCache;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        RefreshAnimeCache::class,
        RefreshMangaCache::class,
        ImportScoutModels::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('anime:cache')->daily();
        $schedule->command('manga:cache')->daily();
        $schedule->command('models:import')->twiceDaily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
