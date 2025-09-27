<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WatchHistory extends Model
{
    use HasFactory;

    protected $primaryKey = 'watch_id';

    protected $fillable = [
        'user_id',
        'content_id',
        'progress_time',
        'total_duration',
        'completion_percentage',
        'last_watched_at',
    ];

    protected $casts = [
        'last_watched_at' => 'datetime',
        'completion_percentage' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id', 'content_id');
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('last_watched_at', '>=', now()->subDays($days));
    }

    public function scopeCompleted($query)
    {
        return $query->where('completion_percentage', '>=', 90);
    }

    public function markAsCompleted()
    {
        $this->update([
            'progress_time' => $this->total_duration,
            'completion_percentage' => 100,
            'last_watched_at' => now(),
        ]);
    }

    public function updateProgress($progressTime)
    {
        $percentage = min(100, ($progressTime / $this->total_duration) * 100);
        
        $this->update([
            'progress_time' => $progressTime,
            'completion_percentage' => $percentage,
            'last_watched_at' => now(),
        ]);
    }
}