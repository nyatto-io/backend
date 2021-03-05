<?php

namespace App\Interfaces;

/**
 * @property \App\Models\Favorite[] $favorites
 */
interface Favorable
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function favorites();
}
