<?php

namespace App\Providers;

use App\API\RapidAPI;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class APIServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(RapidAPI::class, function () {
            return new RapidAPI(new Client([
                'base_uri' => config('rapidapi.url'),
            ]), [
                'key' => config('rapidapi.key'),
                'host' => config('rapidapi.host'),
            ]);
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
