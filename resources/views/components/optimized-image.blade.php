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
    // Get media instance
    $media = $model->getFirstMedia($collection);
    
    if (!$media) {
        // Fallback jika tidak ada media
        return;
    }

    // Determine if we should use responsive images
    $useResponsive = $responsive && $media->hasResponsiveImages();
    
    // Get base URL
    $baseUrl = $conversion 
        ? $media->getUrl($conversion) 
        : $media->getUrl();
    
    // Get responsive images if available
    // Spatie Media Library menyimpan responsive images di custom_properties
    $responsiveImages = [];
    if ($useResponsive) {
        $responsiveImagesData = $media->getCustomProperty('responsive_images', []);
        if (!empty($responsiveImagesData)) {
            foreach ($responsiveImagesData as $width => $path) {
                $responsiveImages[$width] = $media->getUrl($conversion ?: '');
            }
        }
    }
    
    // Get placeholder (blur/tiny image)
    $placeholderUrl = null;
    if ($placeholder && $useResponsive) {
        try {
            $placeholderUrl = $media->getTinyUrl();
        } catch (\Exception $e) {
            // Fallback jika tiny placeholder tidak ada
            $placeholderUrl = null;
        }
    }
    
    // Generate srcset for responsive images
    $srcset = '';
    if ($useResponsive) {
        try {
            // Use media library's built-in method if available
            if (method_exists($media, 'getSrcset')) {
                $srcset = $media->getSrcset($conversion ?: '');
            } else {
                // Build srcset manually from responsive_images data
                $responsiveImagesData = $media->getCustomProperty('responsive_images', []);
                if (!empty($responsiveImagesData)) {
                    $srcsetParts = [];
                    foreach ($responsiveImagesData as $width => $path) {
                        // Build URL for responsive image
                        $responsiveUrl = str_replace(
                            $media->file_name,
                            "responsive-images/{$path}",
                            $baseUrl
                        );
                        $srcsetParts[] = "{$responsiveUrl} {$width}w";
                    }
                    $srcset = implode(', ', $srcsetParts);
                }
            }
        } catch (\Exception $e) {
            // If responsive images not available, use base URL
            $srcset = '';
        }
    }
    
    // Get WebP/AVIF versions if available
    $webpUrl = null;
    $avifUrl = null;
    
    try {
        if ($media->hasGeneratedConversion('webp')) {
            $webpUrl = $media->getUrl('webp');
        }
    } catch (\Exception $e) {
        // WebP not available
    }
    
    try {
        if ($media->hasGeneratedConversion('avif')) {
            $avifUrl = $media->getUrl('avif');
        }
    } catch (\Exception $e) {
        // AVIF not available
    }
    
    // Default alt text
    $altText = $alt ?: ($model->name ?? 'Image');
    
    // Style for placeholder blur effect
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

