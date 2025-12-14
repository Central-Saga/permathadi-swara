# 🔧 Fix AVIF Error: "AVIF image support has been disabled"

Error ini terjadi karena **PHP GD extension tidak memiliki dukungan AVIF**. GD di-compile tanpa flag `--with-avif`.

## 🔍 Masalah

-   PHP GD tidak support AVIF format
-   Error: `imageavif(): AVIF image support has been disabled`
-   AVIF conversion memerlukan Imagick driver

## ✅ Solusi: Gunakan Imagick Driver

Imagick memiliki dukungan AVIF yang lebih baik daripada GD.

### Step 1: Install Imagick Extension

Jalankan di dalam container:

```bash
# Masuk ke container
./vendor/bin/sail shell

# Install ImageMagick library
apt-get update
apt-get install -y libmagickwand-dev imagemagick

# Install PHP Imagick extension
pecl install imagick

# Enable extension
docker-php-ext-enable imagick

# Verify
php -m | grep imagick
php -r "echo (extension_loaded('imagick') && in_array('AVIF', Imagick::queryFormats())) ? 'AVIF supported!' : 'AVIF not supported';"
```

Atau gunakan script:

```bash
cat install-imagick.sh | ./vendor/bin/sail shell
```

### Step 2: Update Konfigurasi

Edit file `.env`:

```env
IMAGE_DRIVER=imagick
```

Atau edit `config/media-library.php`:

```php
'image_driver' => env('IMAGE_DRIVER', 'imagick'), // Ubah default dari 'gd' ke 'imagick'
```

### Step 3: Clear Cache

```bash
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
```

### Step 4: Clear Failed Jobs

```bash
./vendor/bin/sail artisan queue:flush
```

### Step 5: Restart Queue Worker

```bash
./vendor/bin/sail artisan queue:work
```

## 🧪 Testing

### Test 1: Verify Imagick Support

```bash
./vendor/bin/sail artisan tinker
```

```php
// Cek apakah Imagick terinstall
extension_loaded('imagick');

// Cek format yang didukung
Imagick::queryFormats();

// Cek AVIF support
in_array('AVIF', Imagick::queryFormats());
```

### Test 2: Upload Gambar

1. Upload gambar baru di admin panel
2. Cek queue worker - seharusnya tidak ada error
3. Verifikasi AVIF conversion berhasil

## ⚠️ Catatan Penting

### Imagick vs GD

-   **GD**: Lebih cepat, lebih sedikit dependencies, tapi **TIDAK support AVIF**
-   **Imagick**: Lebih lambat sedikit, lebih banyak dependencies, tapi **SUPPORT AVIF dan format lainnya**

### Extension Akan Hilang Setelah Rebuild

**Imagick extension akan hilang jika container di-rebuild!**

Untuk membuat installasi permanen, buat custom Dockerfile:

```dockerfile
FROM laravelsail/php84-composer:latest

# Install ImageMagick
RUN apt-get update && apt-get install -y \
    libmagickwand-dev \
    imagemagick \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && rm -rf /var/lib/apt/lists/*
```

## 🔄 Alternatif: Disable AVIF (Jika Tidak Bisa Install Imagick)

Jika tidak bisa install Imagick, disable AVIF conversion:

### Edit `app/Models/Layanan.php`:

```php
// Comment AVIF conversion
// $this->addMediaConversion('avif')
//     ->format('avif')
//     ->performOnCollections('layanan_cover');
```

### Edit `app/Models/Galeri.php` dengan cara yang sama.

## 📊 Perbandingan

| Feature        | GD         | Imagick      |
| -------------- | ---------- | ------------ |
| AVIF Support   | ❌ No      | ✅ Yes       |
| WebP Support   | ✅ Yes     | ✅ Yes       |
| Performance    | ⚡ Faster  | 🐢 Slower    |
| Dependencies   | 📦 Less    | 📦 More      |
| Format Support | ⚠️ Limited | ✅ Extensive |

## ✅ Checklist

-   [ ] Imagick extension sudah terinstall
-   [ ] AVIF support sudah terverifikasi
-   [ ] `IMAGE_DRIVER=imagick` sudah di-set di `.env`
-   [ ] Config cache sudah di-clear
-   [ ] Failed jobs sudah di-flush
-   [ ] Queue worker sudah di-restart
-   [ ] Test upload gambar baru berhasil
-   [ ] AVIF conversion berhasil tanpa error

---

**Setelah install Imagick, AVIF conversion akan berfungsi dengan baik! 🎉**
