<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $primaryKey = 'plan_id';

    protected $fillable = [
        'name',
        'price_monthly',
        'price_yearly',
        'max_screens',
        'video_quality',
        'description',
        'features',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'features' => 'array',
    ];

    public function userSubscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'plan_id', 'plan_id');
    }

    public function activeSubscriptions()
    {
        return $this->userSubscriptions()->where('status', 'active');
    }

    public function getPriceForBillingCycle($cycle)
    {
        return $cycle === 'yearly' ? $this->price_yearly : $this->price_monthly;
    }

    public function getYearlySavings()
    {
        $monthlyTotal = $this->price_monthly * 12;
        return $monthlyTotal - $this->price_yearly;
    }
}