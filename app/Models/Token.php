<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken;

/**
 * @method static static create($attributes)
 * @method static static findOrFail($id)
 */
class Token extends PersonalAccessToken
{
    protected $table = 'personal_access_tokens';

    protected $dates = [
        'expiry',
    ];
}
