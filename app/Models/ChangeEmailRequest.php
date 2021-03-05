<?php

namespace App\Models;

use App\Jobs\SendMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChangeEmailRequest extends Model
{
    use HasFactory;

    protected $fillable = ['email', 'approved'];

    protected static function booted()
    {
        static::created(function (self $request) {
            dispatch(new SendMail($request->email, $request->user, $request));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
