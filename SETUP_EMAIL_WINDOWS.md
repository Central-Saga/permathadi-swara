# ✅ Setup Email Gmail di Windows - Quick Start

Konfigurasi email Gmail Anda sudah benar! Ikuti langkah-langkah berikut untuk memastikan semuanya berjalan:

## 📋 Checklist Konfigurasi

Konfigurasi Anda sudah benar:

-   ✅ `MAIL_MAILER=smtp`
-   ✅ `MAIL_HOST=smtp.gmail.com`
-   ✅ `MAIL_PORT=587`
-   ✅ `MAIL_USERNAME=mirayasmin34@gmail.com`
-   ✅ `MAIL_PASSWORD="ozbp oxff zduo mlxd"` (App Password)
-   ✅ `MAIL_ENCRYPTION=tls`
-   ✅ `MAIL_FROM_ADDRESS=mirayasmin34@gmail.com`
-   ✅ `CONTACT_EMAIL=mirayasmin34@gmail.com`

## 🚀 Langkah-langkah Setup

### 1. Clear Config Cache

Setelah mengubah `.env`, selalu clear config cache:

```powershell
php artisan config:clear
php artisan cache:clear
```

### 2. Verifikasi Konfigurasi

Cek apakah konfigurasi sudah terbaca dengan benar:

```powershell
php artisan config:show mail
```

Anda harus melihat konfigurasi Gmail SMTP yang sudah di-set.

### 3. Setup Redis (PENTING!)

Karena `QUEUE_CONNECTION=redis`, pastikan Redis berjalan:

#### Opsi A: Jika menggunakan Laravel Sail/Docker

```powershell
# Pastikan container Redis berjalan
docker ps
# Atau
sail ps
```

#### Opsi B: Jika menggunakan Redis lokal di Windows

1. **Download Redis untuk Windows:**

    - Kunjungi: https://github.com/microsoftarchive/redis/releases
    - Atau gunakan WSL2 dengan Redis
    - Atau install via Chocolatey: `choco install redis-64`

2. **Jalankan Redis:**
    ```powershell
    redis-server
    ```

#### Opsi C: Gunakan Database Queue (Alternatif)

Jika tidak ingin setup Redis, ubah di `.env`:

```env
QUEUE_CONNECTION=database
```

Kemudian jalankan migration:

```powershell
php artisan queue:table
php artisan migrate
```

### 4. Jalankan Queue Worker (WAJIB!)

Email di aplikasi ini menggunakan **queue**, jadi queue worker **HARUS** berjalan:

```powershell
php artisan queue:work
```

Atau untuk auto-restart saat ada perubahan:

```powershell
php artisan queue:work --watch
```

**Catatan:** Biarkan terminal ini tetap terbuka dan berjalan saat Anda menggunakan aplikasi.

### 5. Test Email

#### Test 1: Via Tinker (Quick Test)

```powershell
php artisan tinker
```

Kemudian di tinker:

```php
use Illuminate\Support\Facades\Mail;

Mail::raw('Test email dari Permathadi Swara', function ($message) {
    $message->to('mirayasmin34@gmail.com')
             ->subject('Test Email - Setup Berhasil');
});
```

Cek inbox email Anda!

#### Test 2: Via Contact Form (Real Test)

1. Buka aplikasi: `https://permathadi-swara.test/kontak`
2. Isi form kontak dengan data valid
3. Submit form
4. Cek:
    - **Inbox email Anda** (`mirayasmin34@gmail.com`) - akan menerima 2 email:
        - Email notifikasi (ContactMessageNotification)
        - Email konfirmasi ke email yang diisi di form (ContactConfirmation)

## 🔍 Troubleshooting

### Email tidak terkirim?

1. **Pastikan queue worker berjalan:**

    ```powershell
    php artisan queue:work
    ```

2. **Cek apakah ada job yang failed:**

    ```powershell
    php artisan queue:failed
    ```

3. **Cek log untuk error:**

    ```powershell
    # Windows PowerShell
    Get-Content storage\logs\laravel.log -Tail 100
    ```

4. **Test koneksi SMTP langsung:**

    ```powershell
    php artisan tinker
    ```

    ```php
    // Cek konfigurasi
    config('mail.mailers.smtp')

    // Test koneksi
    try {
        Mail::raw('Test', function($m) {
            $m->to('mirayasmin34@gmail.com')->subject('Test');
        });
        echo "Email berhasil dikirim!";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    ```

### Error "Connection refused" atau "Connection timeout"

-   Pastikan koneksi internet aktif
-   Cek firewall Windows tidak memblokir port 587
-   Coba gunakan port 465 dengan SSL:
    ```env
    MAIL_PORT=465
    MAIL_ENCRYPTION=ssl
    ```

### Error "Authentication failed"

-   Pastikan menggunakan **App Password**, bukan password Gmail biasa
-   Pastikan 2-Step Verification sudah aktif di akun Google
-   Cek apakah App Password masih valid (bisa buat ulang di https://myaccount.google.com/apppasswords)
-   Pastikan tidak ada spasi ekstra di `.env`:
    ```env
    MAIL_PASSWORD="ozbp oxff zduo mlxd"  # Pastikan ada tanda kutip
    ```

### Queue tidak diproses

-   **Pastikan Redis berjalan** (jika `QUEUE_CONNECTION=redis`)
-   Atau **ubah ke database queue**:
    ```env
    QUEUE_CONNECTION=database
    ```
    Lalu jalankan:
    ```powershell
    php artisan queue:table
    php artisan migrate
    php artisan queue:work
    ```

### Email masuk ke Spam

-   Ini normal untuk email dari aplikasi development
-   Cek folder Spam di Gmail
-   Untuk production, pertimbangkan menggunakan domain email sendiri atau service seperti Mailgun/SendGrid

## 📝 Catatan Penting

1. **Queue worker HARUS berjalan** - Tanpa ini, email tidak akan terkirim karena aplikasi menggunakan queue
2. **Redis harus berjalan** - Jika `QUEUE_CONNECTION=redis`, pastikan Redis service aktif
3. **Clear cache setelah ubah .env** - Selalu jalankan `php artisan config:clear`
4. **App Password, bukan password biasa** - Gmail memerlukan App Password untuk aplikasi eksternal
5. **Biarkan queue worker berjalan** - Jangan tutup terminal yang menjalankan `php artisan queue:work`

## 🎯 Quick Commands Summary

```powershell
# 1. Clear cache
php artisan config:clear
php artisan cache:clear

# 2. Verifikasi config
php artisan config:show mail

# 3. Jalankan queue worker (WAJIB!)
php artisan queue:work

# 4. Test email
php artisan tinker
# Lalu jalankan: Mail::raw('Test', fn($m) => $m->to('mirayasmin34@gmail.com')->subject('Test'));

# 5. Cek failed jobs
php artisan queue:failed

# 6. Cek log
Get-Content storage\logs\laravel.log -Tail 50
```

## ✅ Checklist Final

-   [ ] Config cache sudah di-clear
-   [ ] Redis berjalan (atau menggunakan database queue)
-   [ ] Queue worker berjalan (`php artisan queue:work`)
-   [ ] Test email via tinker berhasil
-   [ ] Test email via contact form berhasil
-   [ ] Email masuk ke inbox (cek juga folder Spam)

---

**Selamat! Email Anda sudah siap digunakan! 🎉**

Jika masih ada masalah, cek bagian Troubleshooting di atas atau file `EMAIL_SETUP_WINDOWS.md` untuk panduan lebih detail.
