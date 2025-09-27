<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $primaryKey = 'device_id';

    protected $fillable = [
        'user_id',
        'device_type',
        'device_name',
        'last_active',
        'is_active',
    ];

    protected $casts = [
        'last_active' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('device_type', $type);
    }

    public function activate()
    {
        $this->update([
            'is_active' => true,
            'last_active' => now(),
        ]);
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    public function updateLastActive()
    {
        $this->update(['last_active' => now()]);
    }

    public function markAsActive()
    {
        $this->update([
            'is_active' => true,
            'last_active' => now(),
        ]);
    }

    public function markAsInactive()
    {
        $this->update(['is_active' => false]);
    }
}