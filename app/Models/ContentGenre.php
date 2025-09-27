<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ContentGenre extends Pivot
{
    protected $table = 'content_genre';

    public $incrementing = true;
    
    protected $fillable = [
        'content_id',
        'genre_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}