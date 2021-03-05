<?php

return [
    'default' => env('MANGA_DRIVER', 'mangakakalot'),
    'urls' => [
        // 'webtoon' => env('WEBTOON_URL', 'https://webtoon.xyz'),
        'mangakakalot' => env('MANGAKAKALOT_URL', 'https://mangakakalot.com'),
    ],
    'drivers' => [
        // 'webtoon' => App\Drivers\Manga\Webtoon::class,
        'mangakakalot' => App\Drivers\Manga\Mangakakalot::class,
    ],
];
