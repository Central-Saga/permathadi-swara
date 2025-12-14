<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Layanan extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\LayananFactory> */
    use HasFactory, InteractsWithMedia, LogsActivity;

    protected $table = 'layanan';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'duration',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('layanan_cover')
            ->singleFile();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        // Thumbnail conversion
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(400)
            ->sharpen(10)
            ->performOnCollections('layanan_cover');

        // Responsive images untuk original image
        // Spatie akan otomatis generate responsive images saat upload
        // jika withResponsiveImages() dipanggil pada conversion apapun
        $this->addMediaConversion('responsive')
            ->withResponsiveImages()
            ->performOnCollections('layanan_cover');

        // WebP conversion untuk modern browsers
        $this->addMediaConversion('webp')
            ->format('webp')
            ->performOnCollections('layanan_cover');

        // AVIF conversion untuk browsers terbaru (optional, lebih kecil dari WebP)
        // Memerlukan libavif-bin terinstall di container
        $this->addMediaConversion('avif')
            ->format('avif')
            ->performOnCollections('layanan_cover');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'description', 'price', 'duration', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Layanan {$eventName}");
    }
}
