<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimeEpisode extends Model
{
    use HasFactory;

    protected $fillable = ['url', 'title'];

    public function anime()
    {
        return $this->belongsTo(Anime::class);
    }

    public function scopeTitle($query, $title)
    {
        return $query->where('title', $title);
    }
}
