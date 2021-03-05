<?php

namespace App\Providers;

use App\Drivers\Anime\Gogoanime;
use Illuminate\Support\ServiceProvider;

class AnimeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Gogoanime::class, function () {
            return new Gogoanime(config('anime.urls.gogoanime'));
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
