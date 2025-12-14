# 🔧 Fix Media Library Queue Failures

Job `PerformConversionsJob` dari Spatie Media Library terus gagal. Berikut solusinya:

## 🔍 Diagnosa Masalah

### 1. Cek Detail Error

Jalankan command berikut untuk melihat error detail:

```bash
./vendor/bin/sail artisan queue:failed
```

Atau untuk melihat detail lengkap:

```bash
./vendor/bin/sail artisan queue:failed-table
./vendor/bin/sail artisan queue:failed
```

### 2. Cek Log

```bash
./vendor/bin/sail artisan tinker
```

```php
// Cek failed jobs
DB::table('failed_jobs')->latest()->first();
```

## 🛠️ Solusi

### Solusi 1: Disable AVIF Conversion (Recommended untuk Development)

AVIF conversion memerlukan tool `avifenc` yang mungkin tidak terinstall. Disable sementara:

**Edit file `app/Models/Layanan.php`:**

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

    // AVIF conversion - DISABLE untuk sementara
    // $this->addMediaConversion('avif')
    //     ->format('avif')
    //     ->performOnCollections('layanan_cover');
}
```

**Edit file `app/Models/Galeri.php` dengan cara yang sama.**

### Solusi 2: Disable Image Optimizers

Image optimizers memerlukan binary tools yang mungkin tidak terinstall. Disable sementara:

**Edit file `config/media-library.php`:**

```php
'image_optimizers' => [
    // Comment semua optimizers untuk sementara
    // Spatie\ImageOptimizer\Optimizers\Jpegoptim::class => [
    //     '-m85',
    //     '--force',
    //     '--strip-all',
    //     '--all-progressive',
    // ],
    // ... comment semua optimizers lainnya
],
```

Atau set ke array kosong:

```php
'image_optimizers' => [],
```

### Solusi 3: Disable Queue untuk Conversions (Quick Fix)

Jika tidak ingin setup tools sekarang, disable queue untuk conversions:

**Edit file `.env`:**

```env
QUEUE_CONVERSIONS_BY_DEFAULT=false
```

Atau edit `config/media-library.php`:

```php
'queue_conversions_by_default' => false,
```

**Catatan:** Ini akan membuat conversions berjalan secara synchronous, yang bisa memperlambat upload.

### Solusi 4: Install Required Tools (Production Ready)

Jika ingin menggunakan semua fitur, install tools berikut di container:

```bash
# Masuk ke container
./vendor/bin/sail shell

# Install tools
apt-get update
apt-get install -y \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    webp \
    libavif-bin

# Verify installations
jpegoptim --version
optipng --version
pngquant --version
gifsicle --version
cwebp -version
avifenc --version
```

### Solusi 5: Clear Failed Jobs dan Retry

Setelah fix, clear failed jobs:

```bash
# Clear semua failed jobs
./vendor/bin/sail artisan queue:flush

# Atau retry specific job
./vendor/bin/sail artisan queue:retry all
```

## 🎯 Recommended Quick Fix untuk Development

Untuk development, saya sarankan:

1. **Disable AVIF conversion** (Solusi 1)
2. **Disable image optimizers** (Solusi 2)
3. **Clear failed jobs** (Solusi 5)

Ini akan membuat conversions berjalan tanpa tools eksternal, menggunakan hanya PHP GD/Imagick.

## 📝 Langkah-langkah Implementasi

### Step 1: Disable AVIF dan Optimizers

1. Edit `app/Models/Layanan.php` - comment AVIF conversion
2. Edit `app/Models/Galeri.php` - comment AVIF conversion
3. Edit `config/media-library.php` - set `image_optimizers` ke `[]`

### Step 2: Clear Cache

```bash
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
```

### Step 3: Clear Failed Jobs

```bash
./vendor/bin/sail artisan queue:flush
```

### Step 4: Test Upload

1. Upload gambar baru di admin panel
2. Cek queue worker - seharusnya tidak ada error lagi
3. Verifikasi gambar terkonversi dengan benar

## 🔍 Verifikasi

Setelah fix, test dengan:

```bash
# Cek queue worker
./vendor/bin/sail artisan queue:work

# Upload gambar baru dan lihat apakah conversions berhasil
```

## ⚠️ Catatan

-   **Development:** Disable AVIF dan optimizers untuk kemudahan
-   **Production:** Install semua tools untuk performa dan ukuran file optimal
-   **Queue Worker:** Tetap harus berjalan untuk memproses conversions

## 🚀 Untuk Production

Jika ingin menggunakan semua fitur di production:

1. Install semua tools (Solusi 4)
2. Enable kembali AVIF conversion
3. Enable kembali image optimizers
4. Monitor queue untuk memastikan tidak ada error
