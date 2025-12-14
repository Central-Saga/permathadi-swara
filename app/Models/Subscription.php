<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Subscription extends Model
{
    /** @use HasFactory<\Database\Factories\SubscriptionFactory> */
    use HasFactory, LogsActivity;

    protected $fillable = [
        'anggota_id',
        'layanan_id',
        'status',
        'start_date',
        'end_date',
        'notes',
        'renewed_from_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => 'string',
        ];
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function renewedFrom(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'renewed_from_id');
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(Subscription::class, 'renewed_from_id');
    }

    /**
     * Check if subscription can be renewed
     */
    public function canBeRenewed(): bool
    {
        return in_array($this->status, ['active', 'expired']);
    }

    /**
     * Check if subscription is expiring soon (within 30 days)
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->end_date || $this->status !== 'active') {
            return false;
        }

        // Check if end_date is in the future and within 30 days
        $daysUntilExpiry = now()->diffInDays($this->end_date, false);
        return $daysUntilExpiry >= 0 && $daysUntilExpiry <= 30;
    }

    /**
     * Get days until expiry (positive if future, negative if past)
     */
    public function getDaysUntilExpiryAttribute(): int
    {
        if (!$this->end_date) {
            return 0;
        }

        return now()->diffInDays($this->end_date, false);
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-green-50 text-green-700 ring-green-700/10 dark:bg-green-900/50 dark:text-green-200',
            'pending' => 'bg-orange-50 text-orange-700 ring-orange-700/10 dark:bg-orange-900/50 dark:text-orange-200',
            'expired' => 'bg-gray-50 text-gray-700 ring-gray-700/10 dark:bg-gray-900/50 dark:text-gray-200',
            'canceled' => 'bg-red-50 text-red-700 ring-red-700/10 dark:bg-red-900/50 dark:text-red-200',
            default => 'bg-gray-50 text-gray-700 ring-gray-700/10 dark:bg-gray-900/50 dark:text-gray-200',
        };
    }

    protected static function boot()
    {
        parent::boot();

        // Auto-expire subscription jika end_date sudah lewat saat save
        static::saving(function ($subscription) {
            if ($subscription->end_date && $subscription->status === 'active') {
                if ($subscription->end_date->isPast()) {
                    $subscription->status = 'expired';
                }
            }
        });
    }

    /**
     * Scope untuk mendapatkan subscription yang perlu di-expire
     */
    public function scopeShouldExpire($query)
    {
        return $query->where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<', now());
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['anggota_id', 'layanan_id', 'status', 'start_date', 'end_date', 'notes', 'renewed_from_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Subscription {$eventName}");
    }
}
