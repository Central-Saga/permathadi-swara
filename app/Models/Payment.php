<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Payment extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'subscription_id',
        'amount',
        'method',
        'status',
        'paid_at',
        'proof_url',
        'bank_name',
        'account_number',
        'account_holder',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'status' => 'string',
            'method' => 'string',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('payment_proof')
            ->singleFile();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(400)
            ->sharpen(10)
            ->performOnCollections('payment_proof');
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'paid' => 'bg-green-50 text-green-700 ring-green-700/10 dark:bg-green-900/50 dark:text-green-200',
            'pending' => 'bg-orange-50 text-orange-700 ring-orange-700/10 dark:bg-orange-900/50 dark:text-orange-200',
            'failed' => 'bg-red-50 text-red-700 ring-red-700/10 dark:bg-red-900/50 dark:text-red-200',
            default => 'bg-gray-50 text-gray-700 ring-gray-700/10 dark:bg-gray-900/50 dark:text-gray-200',
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($payment) {
            // Auto-set paid_at ketika status diubah ke 'paid'
            if ($payment->isDirty('status')) {
                if ($payment->status === 'paid' && !$payment->paid_at) {
                    $payment->paid_at = now();
                } elseif ($payment->status !== 'paid' && $payment->paid_at) {
                    $payment->paid_at = null;
                }
            }
        });

        static::creating(function ($payment) {
            // Auto-set paid_at ketika status = 'paid' saat create
            if ($payment->status === 'paid' && !$payment->paid_at) {
                $payment->paid_at = now();
            }
        });
    }
}
