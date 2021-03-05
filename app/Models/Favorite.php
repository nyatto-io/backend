<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

/**
 * @property User $user
 */
class Favorite extends Model
{
    use HasFactory, Searchable;

    protected $fillable = ['type', 'group'];

    public function favorable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAnime($query)
    {
        return $query->where('favorable_type', Anime::class);
    }

    public function scopeManga($query)
    {
        return $query->where('favorable_type', Manga::class);
    }

    public function scopeGroup($query, $group)
    {
        return $query->where('group', $group);
    }
}
