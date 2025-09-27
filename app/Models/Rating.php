<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $primaryKey = 'rating_id';

    protected $fillable = [
        'user_id',
        'content_id',
        'rating',
        'review',
        'is_approved',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id', 'content_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeWithReview($query)
    {
        return $query->whereNotNull('review')->where('review', '!=', '');
    }

    public function approve()
    {
        $this->update(['is_approved' => true]);
    }

    public function reject()
    {
        $this->update(['is_approved' => false]);
    }
}