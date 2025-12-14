# 🚀 Enable All Media Library Features

Panduan lengkap untuk mengaktifkan semua fitur Media Library termasuk AVIF conversion dan image optimizers.

## 📋 Prerequisites

-   Laravel Sail sudah berjalan
-   Container `laravel.test` sudah aktif

## 🛠️ Langkah-langkah

### Step 1: Install Image Optimization Tools

Ada 2 cara untuk install tools:

#### Opsi A: Install via Script (Recommended)

Jalankan script install di dalam container:

```bash
# Masuk ke container
./vendor/bin/sail shell

# Di dalam container, jalankan:
apt-get update
apt-get install -y jpegoptim optipng pngquant gifsicle webp libavif-bin
npm install -g svgo

# Verify installations
jpegoptim --version
optipng --version
pngquant --version
gifsicle --version
cwebp -version
avifenc --version
svgo --version
```

#### Opsi B: Install via Script File

```bash
# Copy script ke container dan jalankan
cat install-image-tools.sh | ./vendor/bin/sail shell

# Atau jalankan langsung
./vendor/bin/sail exec laravel.test bash -c "$(cat install-image-tools.sh)"
```

### Step 2: Enable AVIF Conversion

Edit file `app/Models/Layanan.php`:

```php
public function registerMediaConversions(Media $media = null): void
{
    // Thumbnail conversion
    $this->addMediaConversion('thumb')
        ->width(400)
        ->height(400)
        ->sharpen(10)
        ->performOnCollections('layanan_cover');

    // Responsive images
    $this->addMediaConversion('responsive')
        ->withResponsiveImages()
        ->performOnCollections('layanan_cover');

    // WebP conversion
    $this->addMediaConversion('webp')
        ->format('webp')
        ->performOnCollections('layanan_cover');

    // AVIF conversion - ENABLE KEMBALI
    $this->addMediaConversion('avif')
        ->format('avif')
        ->performOnCollections('layanan_cover');
}
```

Edit file `app/Models/Galeri.php` dengan cara yang sama.

### Step 3: Enable Image Optimizers

Edit file `config/media-library.php`:

```php
'image_optimizers' => [
    Spatie\ImageOptimizer\Optimizers\Jpegoptim::class => [
        '-m85',
        '--force',
        '--strip-all',
        '--all-progressive',
    ],
    Spatie\ImageOptimizer\Optimizers\Pngquant::class => [
        '--force',
    ],
    Spatie\ImageOptimizer\Optimizers\Optipng::class => [
        '-i0',
        '-o2',
        '-quiet',
    ],
    Spatie\ImageOptimizer\Optimizers\Svgo::class => [
        '--disable=cleanupIDs',
    ],
    Spatie\ImageOptimizer\Optimizers\Gifsicle::class => [
        '-b',
        '-O3',
    ],
    Spatie\ImageOptimizer\Optimizers\Cwebp::class => [
        '-m 6',
        '-pass 10',
        '-mt',
        '-q 90',
    ],
    Spatie\ImageOptimizer\Optimizers\Avifenc::class => [
        '-a cq-level=23',
        '-j all',
        '--min 0',
        '--max 63',
        '--minalpha 0',
        '--maxalpha 63',
        '-a end-usage=q',
        '-a tune=ssim',
    ],
],
```

### Step 4: Clear Cache

```bash
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
```

### Step 5: Clear Failed Jobs

```bash
./vendor/bin/sail artisan queue:flush
```

### Step 6: Restart Queue Worker

Stop queue worker yang sedang berjalan (Ctrl+C), lalu jalankan lagi:

```bash
./vendor/bin/sail artisan queue:work
```

## 🧪 Testing

### Test 1: Upload Gambar Baru

1. Buka admin panel
2. Upload gambar baru (Layanan atau Galeri)
3. Cek queue worker - seharusnya tidak ada error
4. Verifikasi gambar terkonversi:
    - Thumbnail (400x400)
    - WebP format
    - AVIF format (jika browser support)
    - Responsive images

### Test 2: Verify Conversions

```bash
./vendor/bin/sail artisan tinker
```

```php
use App\Models\Layanan;

$layanan = Layanan::first();
$media = $layanan->getFirstMedia('layanan_cover');

// Cek semua conversions
$media->getGeneratedConversions();

// Test URL conversions
$media->getUrl('thumb');
$media->getUrl('webp');
$media->getUrl('avif');
```

## ⚠️ Catatan Penting

### Tools Akan Hilang Setelah Rebuild

**Tools yang diinstall akan hilang jika container di-rebuild!**

Untuk membuat installasi permanen, ada beberapa opsi:

#### Opsi 1: Custom Dockerfile (Recommended untuk Production)

Buat file `Dockerfile` di root project:

```dockerfile
FROM ubuntu:22.04

# Install system dependencies
RUN apt-get update && apt-get install -y \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    webp \
    libavif-bin \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js untuk SVGO
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g svgo

# Copy dari Sail base image
COPY --from=laravelsail/php84-composer:latest / /
```

Kemudian update `compose.yaml` untuk menggunakan custom Dockerfile.

#### Opsi 2: Install Script di Entrypoint

Buat script yang dijalankan saat container start, tapi ini lebih kompleks.

#### Opsi 3: Install Manual Setiap Rebuild

Install tools setiap kali container di-rebuild (cukup untuk development).

## 🔍 Troubleshooting

### Error: "avifenc: command not found"

-   Pastikan `libavif-bin` sudah terinstall
-   Verify: `avifenc --version`

### Error: "jpegoptim: command not found"

-   Install: `apt-get install -y jpegoptim`
-   Verify: `jpegoptim --version`

### Error: "svgo: command not found"

-   Install via npm: `npm install -g svgo`
-   Verify: `svgo --version`

### Conversions masih gagal

1. Cek apakah tools terinstall:

    ```bash
    ./vendor/bin/sail exec laravel.test which jpegoptim optipng pngquant gifsicle cwebp avifenc svgo
    ```

2. Cek log untuk detail error:

    ```bash
    ./vendor/bin/sail artisan queue:failed
    ```

3. Test conversion manual:
    ```bash
    ./vendor/bin/sail artisan tinker
    ```
    ```php
    use App\Models\Layanan;
    $layanan = Layanan::first();
    $media = $layanan->getFirstMedia('layanan_cover');
    $media->performConversions();
    ```

## 📊 Performance Impact

-   **AVIF conversion**: Lebih lambat tapi menghasilkan file lebih kecil (~50% dari WebP)
-   **Image optimizers**: Menambah waktu processing tapi mengurangi ukuran file
-   **Queue processing**: Semua conversions berjalan di background, tidak blocking upload

## ✅ Checklist

-   [ ] Tools sudah terinstall (jpegoptim, optipng, pngquant, gifsicle, webp, libavif-bin, svgo)
-   [ ] AVIF conversion sudah di-enable di `Layanan.php` dan `Galeri.php`
-   [ ] Image optimizers sudah di-enable di `config/media-library.php`
-   [ ] Config cache sudah di-clear
-   [ ] Failed jobs sudah di-flush
-   [ ] Queue worker sudah di-restart
-   [ ] Test upload gambar baru berhasil
-   [ ] Semua conversions (thumb, webp, avif) ter-generate dengan benar

---

**Selamat! Semua fitur Media Library sudah aktif! 🎉**
