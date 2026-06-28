<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Chapter extends Model
{
    protected $fillable = ['category_id', 'title', 'source_url', 'source_html', 'image_urls', 'status', 'sort_order'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class)->orderBy('page_number');
    }

    public function notes(): HasManyThrough
    {
        return $this->hasManyThrough(Note::class, Page::class);
    }
}
