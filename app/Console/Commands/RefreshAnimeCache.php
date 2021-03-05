<?php

namespace App\Console\Commands;

use App\Interfaces\AnimeDriver;
use App\Jobs\CacheAnime;
use Illuminate\Console\Command;

class RefreshAnimeCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'anime:cache {--pages= : Amount of pages to cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs the latest anime on the site and saves it on cache or the database.';

    /**
     * @var \App\Interfaces\AnimeDriver[]
     */
    protected $drivers;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->drivers = collect(config('anime.drivers'))->map(function ($driver) {
            return app($driver);
        });
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $bar = $this->output->createProgressBar(count($this->drivers));

        $bar->start();

        foreach ($this->drivers as $driver) {
            $class = get_class($driver);
            $this->newLine();
            $this->info("Caching {$class}");
            $this->newLine();
            $pages = (int)$this->option('pages') ?: 1;
            dispatch(new CacheAnime($driver, $pages));
            $bar->advance();
        }

        $bar->finish();

        $this->newLine();
        $this->info("Done");

        return 0;
    }
}
