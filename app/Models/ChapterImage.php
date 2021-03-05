<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChapterImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
    ];

    protected $with = ['file'];

    protected static function booted()
    {
        static::deleted(function (self $chapterImage) {
            $chapterImage->file->delete();
        });
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }
}
