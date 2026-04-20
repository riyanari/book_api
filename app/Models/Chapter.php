<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_book_id',
        'chapter_number',
        'title',
        'summary',
    ];

    public function userBook(): BelongsTo
    {
        return $this->belongsTo(UserBook::class);
    }

    public function points(): HasMany
    {
        return $this->hasMany(ChapterPoint::class);
    }
}
