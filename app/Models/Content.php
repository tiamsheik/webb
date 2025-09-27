<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Content extends Model
{
    use HasFactory;

    protected $primaryKey = 'content_id';

    protected $fillable = [
        'title',
        'description',
        'category',
        'duration',
        'release_year',
        'rating',
        'thumbnail_url',
        'video_url',
        'file_size',
        'is_featured',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'release_year' => 'integer',
        'duration' => 'integer',
        'file_size' => 'integer',
    ];

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'content_genre', 'content_id', 'genre_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function watchHistory()
    {
        return $this->hasMany(WatchHistory::class, 'content_id', 'content_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'content_id', 'content_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'content_id', 'content_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeByGenre(Builder $query, $genreId): Builder
    {
        return $query->whereHas('genres', function ($q) use ($genreId) {
            $q->where('genres.genre_id', $genreId);
        });
    }

    public function scopeSearch(Builder $query, string $searchTerm): Builder
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('title', 'LIKE', "%{$searchTerm}%")
              ->orWhere('description', 'LIKE', "%{$searchTerm}%");
        });
    }

    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('rating') ?: 0;
    }

    public function getRatingCountAttribute()
    {
        return $this->ratings()->count();
    }

    public function getFavoriteCountAttribute()
    {
        return $this->favorites()->count();
    }

    public function getDurationFormattedAttribute()
    {
        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        
        return "{$minutes}m";
    }

    public function isRatedByUser($userId)
    {
        return $this->ratings()->where('user_id', $userId)->exists();
    }

    public function isFavoritedByUser($userId)
    {
        return $this->favorites()->where('user_id', $userId)->exists();
    }
}