<?php

namespace App\Traits;

use App\Models\Favorite;

trait IsFavorable
{
    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favorable');
    }
}
