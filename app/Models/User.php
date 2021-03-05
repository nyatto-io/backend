<?php

namespace App\Models;

use App\Casts\Password;
use App\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property Setting[] $settings
 * @property Favorite[] $favorites
 * @method static static create($attributes)
 * @method static static findOrFail($id)
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'facebook',
        'twitter',
        'youtube',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => Password::class,
    ];

    protected $with = ['picture'];

    protected static function booted()
    {
        static::created(function (self $user) {
            $user->settings()->createMany([
                [
                    'key' => 'manga-driver',
                    'value' => config('manga.default'),
                ],
                [
                    'key' => 'anime-driver',
                    'value' => config('anime.default'),
                ],
                [
                    'key' => 'notify-favorites-update',
                    'value' => 'no',
                ],
            ]);
        });

        static::deleting(function (self $user) {
            $user->favorites()->delete();
            $user->requests()->delete();
        });

        static::deleted(function (self $user) {
            $user->picture()->delete();
        });
    }

    public function setSetting($key, $value)
    {
        $setting = $this->getSetting($key);
        if ($setting === null) {
            $setting = $this->settings()->make(['key' => $key]);
        }
        $setting->value = $value;
        $setting->save();

        return $this;
    }

    public function getSetting($key, $default = null)
    {
        $setting = $this->settings()->where('key', $key)->first();
        if ($setting === null) {
            return $default;
        }
        return $setting->value;
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function picture()
    {
        return $this->belongsTo(File::class, 'profile_picture_id');
    }

    public function requests()
    {
        return $this->hasMany(ChangeEmailRequest::class);
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail($this));
    }
}
