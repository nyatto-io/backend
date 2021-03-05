<?php

namespace App\Console\Commands;

use App\Interfaces\MangaDriver;
use App\Jobs\CacheManga;
use Illuminate\Console\Command;

class RefreshMangaCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manga:cache {--pages= : Amount of pages to cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs the latest mangas on the site and saves it on cache or the database';

    /**
     * @var \App\Interfaces\MangaDriver[]
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
        $this->drivers = $this->drivers = collect(config('manga.drivers'))->map(function ($driver) {
            return app($driver);
        });;
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
            dispatch(new CacheManga($driver, $pages));
            $bar->advance();
        }

        $bar->finish();

        $this->newLine();
        $this->info("Done");

        return 0;
    }
}
