<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    use HasFactory;

    protected $primaryKey = 'genre_id';

    protected $fillable = [
        'name',
        'description',
    ];

    public function contents()
    {
        return $this->belongsToMany(Content::class, 'content_genre', 'genre_id', 'content_id');
    }

    public function getContentCountAttribute()
    {
        return $this->contents()->count();
    }
}