<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as BaseModel;

/**
 * @method static static create($attributes)
 * @method static static findOrFail($id)
 */
abstract class Model extends BaseModel
{
    use HasFactory;

    public function scopeLike($query, $key, $value)
    {
        return $query->where($key, 'LIKE', sprintf('%%%s%%', $value));
    }
}
