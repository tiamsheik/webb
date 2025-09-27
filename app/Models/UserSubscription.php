<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory;

    protected $primaryKey = 'subscription_id';

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'billing_cycle',
        'start_date',
        'end_date',
        'auto_renew',
        'stripe_subscription_id',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id', 'plan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('end_date', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'active')->where('end_date', '<=', now());
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->end_date->isFuture();
    }

    public function daysUntilExpiration()
    {
        return now()->diffInDays($this->end_date);
    }

    public function renew()
    {
        $this->update([
            'end_date' => $this->billing_cycle === 'yearly' 
                ? $this->end_date->addYear()
                : $this->end_date->addMonth(),
        ]);
    }
}