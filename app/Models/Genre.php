<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'title',
        'url',
        'type',
    ];

    protected static function booted()
    {
        static::saving(function (self $genre) {
            $genre->title = trim($genre->title);
        });
    }

    public function scopeGogoanime($query)
    {
        return $this->scopeGroup($query, 'gogoanime');
    }

    public function scopeWebtoon($query)
    {
        return $this->scopeGroup($query, 'webtoon');
    }

    public function scopeMangakakalot($query)
    {
        return $this->scopeGroup($query, 'mangakakalot');
    }

    public function scopeGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    public function scopeTitle($query, $title)
    {
        return $query->where('title', $title);
    }

    public function scopeSearch($query, $title)
    {
        return $query->where('title', 'LIKE', sprintf('%%%s%%', $title));
    }

    public function scopeAnime($query)
    {
        return $query->where('type', 'anime');
    }

    public function scopeManga($query)
    {
        return $query->where('type', 'manga');
    }
}
