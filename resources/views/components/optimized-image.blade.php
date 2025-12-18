@props([
    'model',
    'collection' => 'default',
    'conversion' => null,
    'alt' => '',
    'width' => null,
    'height' => null,
    'sizes' => '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw',
    'loading' => 'lazy',
    'class' => '',
    'placeholder' => true,
    'responsive' => true,
])

@php
    // Defaults biar gak pernah "Undefined variable"
    $placeholderUrl = null;
    $placeholderStyle = '';
    $srcset = '';
    $baseUrl = '';
    $webpUrl = null;
    $avifUrl = null;

    // Get media instance
    $media = $model->getFirstMedia($collection);

    if (!$media) {
        return;
    }

    // Responsive?
    $useResponsive = $responsive
        && method_exists($media, 'hasResponsiveImages')
        && $media->hasResponsiveImages();

    // Base URL
    $baseUrl = $conversion ? $media->getUrl($conversion) : $media->getUrl();

    // Srcset (built-in)
    if ($useResponsive && method_exists($media, 'getSrcset')) {
        try {
            $srcset = $media->getSrcset($conversion ?: '');
        } catch (\Exception $e) {
            $srcset = '';
        }
    }

    // Placeholder (tiny) kalau ada
    if ($placeholder) {
        try {
            if (method_exists($media, 'getTinyUrl')) {
                $placeholderUrl = $media->getTinyUrl();
            }
        } catch (\Exception $e) {
            $placeholderUrl = null;
        }
    }

    // WebP/AVIF conversions
    try {
        if ($media->hasGeneratedConversion('webp')) {
            $webpUrl = $media->getUrl('webp');
        }
    } catch (\Exception $e) {}

    try {
        if ($media->hasGeneratedConversion('avif')) {
            $avifUrl = $media->getUrl('avif');
        }
    } catch (\Exception $e) {}

    // Alt
    $altText = $alt ?: ($model->name ?? 'Image');

    // Placeholder style
    $placeholderStyle = $placeholderUrl
        ? "background-image: url('{$placeholderUrl}'); background-size: cover; background-position: center; filter: blur(20px); transform: scale(1.1);"
        : '';
@endphp


<div 
    class="optimized-image-wrapper {{ $class }}"
    style="position: relative; overflow: hidden; @if($placeholderUrl) {{ $placeholderStyle }} @endif"
    data-optimized-image="true">
    
    <picture>
        @if($avifUrl)
            <source 
                type="image/avif" 
                srcset="{{ $avifUrl }}"
                @if($sizes) sizes="{{ $sizes }}" @endif />
        @endif
        
        @if($webpUrl)
            <source 
                type="image/webp" 
                srcset="{{ $webpUrl }}"
                @if($sizes) sizes="{{ $sizes }}" @endif />
        @endif
        
        <img 
            src="{{ $baseUrl }}"
            @if($srcset) srcset="{{ $srcset }}" @endif
            @if($sizes && $srcset) sizes="{{ $sizes }}" @endif
            alt="{{ $altText }}"
            @if($width) width="{{ $width }}" @endif
            @if($height) height="{{ $height }}" @endif
            loading="{{ $loading }}"
            decoding="async"
            class="optimized-image {{ $class }}"
            style="transform: translateZ(0); backface-visibility: hidden; will-change: transform; width: 100%; height: 100%; object-fit: cover; display: block; @if($placeholderUrl) opacity: 0; transition: opacity 0.3s ease-in-out; @else opacity: 1; @endif"
            @if($placeholderUrl) 
                data-placeholder="{{ $placeholderUrl }}"
                onload="this.style.opacity = 1; this.parentElement.parentElement.style.backgroundImage = 'none';"
                onerror="this.style.opacity = 1; this.parentElement.parentElement.style.backgroundImage = 'none';"
            @endif
        />
    </picture>
</div>

@once
@push('styles')
<style>
    .optimized-image-wrapper {
        position: relative;
        overflow: hidden;
        display: block;
    }
    
    .optimized-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    
    .optimized-image[data-placeholder] {
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
    
    /* Fix for dark theme visibility */
    .dark .optimized-image-wrapper {
        background-color: transparent;
    }
    
    .dark .optimized-image {
        opacity: 1 !important;
        filter: none;
    }
    
    .dark .optimized-image-wrapper[style*="background-image"] {
        background-color: rgba(0, 0, 0, 0.1);
    }
</style>
@endpush
@endonce

