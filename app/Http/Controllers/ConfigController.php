<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function drivers()
    {
        return [
            'anime' => array_keys(config('anime.drivers')),
            'manga' => array_keys(config('manga.drivers')),
        ];
    }
}
