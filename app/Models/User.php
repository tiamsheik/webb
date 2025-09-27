<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
        'role',
        'avatar_url',
        'email_verified_at',
        'last_login_at',
        'subscription_status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'user_id', 'user_id');
    }

    public function activeSubscription()
    {
        return $this->hasOne(UserSubscription::class, 'user_id', 'user_id')
                    ->where('status', 'active')
                    ->where('end_date', '>', now());
    }

    public function watchHistory()
    {
        return $this->hasMany(WatchHistory::class, 'user_id', 'user_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'user_id', 'user_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id', 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }

    public function devices()
    {
        return $this->hasMany(Device::class, 'user_id', 'user_id');
    }

    public function createdContent()
    {
        return $this->hasMany(Content::class, 'created_by', 'user_id');
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isContentManager()
    {
        return $this->role === 'content_manager' || $this->isAdmin();
    }

    public function hasActiveSubscription()
    {
        return $this->activeSubscription()->exists();
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}