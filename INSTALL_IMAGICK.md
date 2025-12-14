# 📦 Install Imagick Extension - Quick Guide

Script `install-imagick.sh` **HARUS** dijalankan di dalam Laravel Sail container, bukan di host machine!

## ✅ Cara yang Benar

### Opsi 1: Masuk ke Container Dulu (Recommended)

```bash
# 1. Masuk ke container
./vendor/bin/sail shell

# 2. Di dalam container, jalankan script
bash /var/www/html/install-imagick.sh

# 3. Keluar dari container
exit
```

### Opsi 2: Pipe Script ke Container

```bash
# Jalankan dari host, script akan di-pipe ke container
cat install-imagick.sh | ./vendor/bin/sail shell
```

### Opsi 3: Exec Command

```bash
# Jalankan langsung dari host
./vendor/bin/sail exec laravel.test bash /var/www/html/install-imagick.sh
```

### Opsi 4: Manual Install (Jika Script Gagal)

```bash
# Masuk ke container
./vendor/bin/sail shell

# Install dependencies
apt-get update
apt-get install -y libmagickwand-dev imagemagick pkg-config

# Install Imagick extension
pecl install imagick

# (Tekan Enter jika diminta konfirmasi)

# Enable extension
docker-php-ext-enable imagick

# Verify
php -m | grep imagick
php -r "echo (extension_loaded('imagick') && in_array('AVIF', Imagick::queryFormats())) ? 'AVIF supported!' : 'AVIF not supported';"

# Keluar
exit
```

## ❌ Yang SALAH

**JANGAN** jalankan script langsung di host:

```bash
# ❌ SALAH - Ini akan error!
./install-imagick.sh

# ❌ SALAH - Ini juga akan error!
bash install-imagick.sh
```

## 🔍 Troubleshooting

### Error: "pecl: command not found"

-   Pastikan Anda di dalam container (`./vendor/bin/sail shell`)
-   PECL biasanya sudah terinstall di Laravel Sail container

### Error: "docker-php-ext-enable: command not found"

-   Pastikan Anda di dalam container
-   Command ini hanya tersedia di Docker PHP container

### Error: "php: command not found"

-   Pastikan Anda di dalam container
-   Di host machine, gunakan `./vendor/bin/sail artisan` atau `./vendor/bin/sail php`

### Installation Gagal

Jika `pecl install imagick` gagal, coba:

```bash
# Di dalam container
apt-get install -y php8.4-dev php-pear
pecl install imagick
```

### AVIF Tidak Terdeteksi

Setelah install, cek:

```bash
# Di dalam container
php -r "var_dump(Imagick::queryFormats());" | grep -i avif
```

Jika tidak ada, mungkin ImageMagick di-compile tanpa AVIF support. Cek versi:

```bash
convert -version
```

## ✅ Verifikasi Setelah Install

```bash
# Masuk ke container
./vendor/bin/sail shell

# Cek Imagick extension
php -m | grep imagick

# Cek AVIF support
php -r "echo (extension_loaded('imagick') && in_array('AVIF', Imagick::queryFormats())) ? 'AVIF supported!' : 'AVIF not supported';"

# Keluar
exit
```

## 🔄 Setelah Install

1. **Update `.env`:**

    ```env
    IMAGE_DRIVER=imagick
    ```

2. **Clear cache:**

    ```bash
    ./vendor/bin/sail artisan config:clear
    ./vendor/bin/sail artisan cache:clear
    ```

3. **Clear failed jobs:**

    ```bash
    ./vendor/bin/sail artisan queue:flush
    ```

4. **Restart queue worker:**
    ```bash
    ./vendor/bin/sail artisan queue:work
    ```

## ⚠️ Catatan Penting

-   **Extension akan hilang** jika container di-rebuild
-   Untuk permanen, buat custom Dockerfile (lihat `FIX_AVIF_GD_ERROR.md`)
-   Setelah install, pastikan `IMAGE_DRIVER=imagick` di `.env`

---

**Selamat! Imagick sudah terinstall dan AVIF conversion akan berfungsi! 🎉**
