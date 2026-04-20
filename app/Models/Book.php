<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'source_id',
        'isbn',
        'title',
        'author',
        'publisher',
        'published_year',
        'description',
        'cover_url',
        'normalized_title',
        'normalized_author',
    ];

    public function userBooks(): HasMany
    {
        return $this->hasMany(UserBook::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
}
