<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Galeri extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\GaleriFactory> */
    use HasFactory, InteractsWithMedia;

    protected $table = 'galeri';

    protected $fillable = [
        'title',
        'description',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('galeri_images');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        // Thumbnail conversion untuk tabel
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(400)
            ->sharpen(10)
            ->performOnCollections('galeri_images');

        // Responsive images untuk landing page
        $this->addMediaConversion('responsive')
            ->withResponsiveImages()
            ->performOnCollections('galeri_images');

        // WebP conversion untuk modern browsers
        $this->addMediaConversion('webp')
            ->format('webp')
            ->performOnCollections('galeri_images');

        // AVIF conversion untuk browsers terbaru
        $this->addMediaConversion('avif')
            ->format('avif')
            ->performOnCollections('galeri_images');
    }

    /**
     * Scope untuk hanya mengambil galeri yang published
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at');
    }
}
