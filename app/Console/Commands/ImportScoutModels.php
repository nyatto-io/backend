<?php

namespace App\Console\Commands;

use App\Models\Anime;
use App\Models\Favorite;
use App\Models\Manga;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ImportScoutModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'models:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports unsynchronized models to the scout driver.';

    protected $models = [
        Anime::class,
        Favorite::class,
        Manga::class,
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $bar = $this->output->createProgressBar(count($this->models));

        foreach ($this->models as $model) {
            $this->newLine();
            $this->info(sprintf('Queueing %s',  $model));
            $this->newLine();
            $bar->advance();
            $this->newLine();
            Artisan::queue('tntsearch:import', [
                'model' => $model,
            ]);
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done");
        $this->newLine();
        return 0;
    }
}
