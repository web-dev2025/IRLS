<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    protected $fillable = ['page_id', 'phrase', 'translation', 'comment', 'x', 'y', 'width', 'height', 'is_learned'];

    protected $casts = [
        'x'          => 'float',
        'y'          => 'float',
        'width'      => 'float',
        'height'     => 'float',
        'is_learned' => 'boolean',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
