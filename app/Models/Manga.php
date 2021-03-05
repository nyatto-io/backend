<?php

namespace App\Models;

use App\Interfaces\Favorable;
use App\Traits\IsFavorable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;

class Manga extends Model implements Favorable
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
        'chapters',
        'description',
    ];

    protected $appends = ['is_favorite'];

    protected static function booted()
    {
        static::deleted(function (self $manga) {
            $manga->image->delete();
        });

        static::deleting(function (self $manga) {
            $manga->chapters()->delete();
        });
    }

    public function toSearchableArray()
    {
        $data = $this->toArray();
        return Arr::only($data, ['id', 'group', 'genres', 'title']);
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    public function image()
    {
        return $this->belongsTo(File::class, 'image_id');
    }

    public function getIsFavoriteAttribute()
    {
        /**
         * @var User
         */
        $user = auth()->user();
        return $user && $user->favorites()
            ->manga()
            ->group($this->group)
            ->where('favorable_id', $this->getKey())
            ->count() !== 0;
    }

    public function scopeWebtoon($query)
    {
        return $query->where('group', 'webtoon');
    }

    public function scopeMangakakalot($query)
    {
        return $query->where('group', 'mangakakalot');
    }

    public function scopeTitle($query, $title)
    {
        return $query->where('title', $title);
    }
}
