<?php

namespace App\Models;

use App\Interfaces\Favorable;
use App\Traits\IsFavorable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;

class Anime extends Model implements Favorable
{
    use HasFactory,
        Searchable,
        IsFavorable;

    protected $fillable = [
        'group',
        'genres',
        'rating',
        'title',
        'status',
        'url',
        'image_id',
        'episodes',
        'description',
        'english_title',
        'synonyms',
        'japanese_title',
    ];

    protected $appends = ['is_favorite'];

    public function toSearchableArray()
    {
        $data = $this->toArray();
        return Arr::only($data, [
            'id',
            'group',
            'genres',
            'title',
            'english_title',
            'synonyms',
            'japanese_title'
        ]);
    }

    protected static function booted()
    {
        static::deleted(function (self $anime) {
            $anime->image->delete();
        });
    }

    public function episodes()
    {
        return $this->hasMany(AnimeEpisode::class);
    }

    public function image()
    {
        return $this->belongsTo(File::class, 'image_id');
    }

    public function getIsFavoriteAttribute()
    {
        /**
         * @var User|null
         */
        $user = auth()->user();
        return $user && $user->favorites()
            ->anime()
            ->group($this->group)
            ->where('favorable_id', $this->getKey())
            ->count() !== 0;
    }

    public function scopeGogoanime($query)
    {
        return $query->where('group', 'gogoanime');
    }
}
