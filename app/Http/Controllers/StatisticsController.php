<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Favorite;
use App\Models\Manga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function cachedAnimesAndMangas()
    {
        return [
            'animes' => Anime::whereYear('created_at', date('Y'))
                ->get()
                ->map(function (Anime $model) {
                    $data = $model->toArray();
                    $data['month'] = $model->created_at->monthName;

                    return $data;
                })
                ->groupBy('month'),

            'mangas' => Manga::whereYear('created_at', date('Y'))
                ->get()
                ->map(function (Manga $model) {
                    $data = $model->toArray();
                    $data['month'] = $model->created_at->monthName;

                    return $data;
                })
                ->groupBy('month'),
        ];
    }

    public function favorites(Request $request)
    {
        return $request->user()
            ->favorites()
            ->whereYear('created_at', date('Y'))
            ->orderBy('favorable_type', 'ASC')
            ->get()
            ->map(function (Favorite $model) {
                $data = $model->toArray();
                $data['month'] = $model->created_at->monthName;

                return $data;
            })
            ->groupBy('month');
    }
}
