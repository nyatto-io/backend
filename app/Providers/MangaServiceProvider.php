<?php

namespace App\Providers;

use App\Drivers\Manga\Mangakakalot;
use App\Drivers\Manga\Webtoon;
use Illuminate\Support\ServiceProvider;

class MangaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Webtoon::class, function () {
            return new Webtoon(config('manga.urls.webtoon'));
        });
        $this->app->singleton(Mangakakalot::class, function () {
            return new Mangakakalot(config('manga.urls.mangakakalot'));
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

    public function provides()
    {
        return [Webtoon::class, Mangakakalot::class];
    }
}
