<?php

namespace App\Jobs;

use App\Interfaces\MangaDriver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CacheManga implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Interfaces\MangaDriver
     */
    public $driver;

    /**
     * @var int
     */
    public $pages;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60 * 60 * 60;

    /**
     * Create a new job instance.
     *
     * @param \App\Interfaces\MangaDriver $driver
     * @param int $pages
     * @return void
     */
    public function __construct(MangaDriver $driver, $pages)
    {
        $this->driver = $driver;
        $this->pages = $pages;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->driver->refreshCache($this->pages);
    }
}
