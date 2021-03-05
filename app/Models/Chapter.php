<?php

namespace App\Models;

use App\Casts\FloatingPoint;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = ['manga_id', 'title', 'url'];

    protected $casts = [
        'title' => FloatingPoint::class,
    ];

    protected static function booted()
    {
        static::deleted(function (self $chapter) {
            $chapter->images()->delete();
        });
    }

    public function manga()
    {
        return $this->belongsTo(Manga::class);
    }

    public function images()
    {
        return $this->hasMany(ChapterImage::class);
    }

    public function scopeTitle($query, $title)
    {
        return $query->where('title', $title);
    }
}
