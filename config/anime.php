<?php

return [
    'default' => env('ANIME_DRIVER', 'gogoanime'),
    'urls' => [
        'gogoanime' => env('GOGOANIME_URL', 'https://gogoanime.so'),
    ],
    'drivers' => [
        'gogoanime' => App\Drivers\Anime\Gogoanime::class,
    ],
];
