# 📧 Panduan Setup Email di Windows

Dokumen ini menjelaskan cara setup dan menjalankan email setelah clone project ke device Windows.

## 🎯 Opsi Konfigurasi Email

Ada beberapa cara untuk setup email di Windows, pilih sesuai kebutuhan:

### 1. **Mailpit (Recommended untuk Development/Testing)**

Mailpit adalah tool untuk testing email yang menangkap semua email yang dikirim dan menampilkannya di web interface.

#### Installasi Mailpit di Windows:

1. **Download Mailpit untuk Windows:**

    - Kunjungi: https://github.com/axllent/mailpit/releases
    - Download file `mailpit-windows-amd64.exe` atau `mailpit-windows-arm64.exe` sesuai arsitektur Windows Anda

2. **Jalankan Mailpit:**

    ```powershell
    # Simpan file mailpit.exe di folder yang mudah diakses, misalnya C:\mailpit\
    # Jalankan dari Command Prompt atau PowerShell:
    C:\mailpit\mailpit.exe
    ```

    Atau buat file batch `start-mailpit.bat`:

    ```batch
    @echo off
    cd C:\mailpit
    mailpit.exe
    ```

3. **Akses Web Interface:**
    - Buka browser dan kunjungi: http://localhost:8025
    - Di sini Anda bisa melihat semua email yang dikirim oleh aplikasi

#### Konfigurasi `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@permathadi-swara.test"
MAIL_FROM_NAME="${APP_NAME}"

# Email untuk menerima notifikasi kontak
CONTACT_EMAIL="your-email@gmail.com"
```

---

### 2. **Gmail SMTP (Untuk Production/Testing dengan Email Nyata)**

#### Setup Gmail SMTP:

1. **Aktifkan 2-Step Verification** di akun Google Anda
2. **Buat App Password:**
    - Kunjungi: https://myaccount.google.com/apppasswords
    - Pilih "Mail" dan "Other (Custom name)"
    - Masukkan nama: "Laravel App"
    - Copy password yang dihasilkan (16 karakter)

#### Konfigurasi `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME="your-email@gmail.com"
MAIL_PASSWORD="your-16-char-app-password"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"

CONTACT_EMAIL="your-email@gmail.com"
```

---

### 3. **Outlook/Hotmail SMTP**

#### Konfigurasi `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-mail.outlook.com
MAIL_PORT=587
MAIL_USERNAME="your-email@outlook.com"
MAIL_PASSWORD="your-password"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@outlook.com"
MAIL_FROM_NAME="${APP_NAME}"

CONTACT_EMAIL="your-email@outlook.com"
```

---

### 4. **Log Driver (Testing tanpa Mengirim Email)**

Jika Anda hanya ingin testing tanpa benar-benar mengirim email, gunakan log driver:

#### Konfigurasi `.env`:

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@permathadi-swara.test"
MAIL_FROM_NAME="${APP_NAME}"

CONTACT_EMAIL="your-email@gmail.com"
```

Email akan disimpan di `storage/logs/laravel.log` sebagai log entry.

---

## ⚙️ Setup Setelah Clone

### Langkah-langkah:

1. **Copy file `.env.example` ke `.env`** (jika belum ada):

    ```powershell
    copy .env.example .env
    ```

2. **Edit file `.env`** dan tambahkan konfigurasi email sesuai pilihan di atas

3. **Clear config cache** (jika sudah pernah menjalankan aplikasi):

    ```powershell
    php artisan config:clear
    ```

4. **Test konfigurasi email:**

    ```powershell
    php artisan tinker
    ```

    Kemudian di tinker:

    ```php
    Mail::raw('Test email', function ($message) {
        $message->to('test@example.com')
                 ->subject('Test Email');
    });
    ```

---

## 🔄 Queue Worker (Penting!)

Aplikasi ini menggunakan **queue** untuk mengirim email secara background. Pastikan queue worker berjalan:

### Untuk Development (Windows):

```powershell
php artisan queue:work
```

Atau untuk auto-restart saat ada perubahan:

```powershell
php artisan queue:work --watch
```

### Untuk Production:

Gunakan **Supervisor** atau **Windows Task Scheduler** untuk menjalankan queue worker secara otomatis.

#### Setup dengan Windows Task Scheduler:

1. Buka **Task Scheduler** (taskschd.msc)
2. Create Basic Task
3. Trigger: "When the computer starts"
4. Action: "Start a program"
5. Program: `php`
6. Arguments: `C:\path\to\your\project\artisan queue:work --sleep=3 --tries=3`
7. Start in: `C:\path\to\your\project`

---

## 🧪 Testing Email

### Test Contact Form:

1. Buka halaman kontak: `http://localhost/kontak`
2. Isi form dan submit
3. Cek:
    - Jika menggunakan **Mailpit**: Buka http://localhost:8025
    - Jika menggunakan **Log driver**: Cek `storage/logs/laravel.log`
    - Jika menggunakan **SMTP**: Cek inbox email Anda

### Test via Tinker:

```powershell
php artisan tinker
```

```php
use App\Mail\ContactConfirmation;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Mail;

// Buat dummy contact message
$message = ContactMessage::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'phone' => '081234567890',
    'subject' => 'Test Subject',
    'message' => 'Test Message'
]);

// Kirim email
Mail::to('test@example.com')->send(new ContactConfirmation($message));
```

---

## 🐛 Troubleshooting

### Email tidak terkirim:

1. **Cek konfigurasi `.env`:**

    ```powershell
    php artisan config:show mail
    ```

2. **Pastikan queue worker berjalan:**

    ```powershell
    php artisan queue:work
    ```

3. **Cek log:**

    ```powershell
    # Windows PowerShell
    Get-Content storage\logs\laravel.log -Tail 50
    ```

4. **Test koneksi SMTP:**
    ```powershell
    php artisan tinker
    ```
    ```php
    config('mail.mailers.smtp')
    ```

### Error "Connection refused" (Mailpit):

-   Pastikan Mailpit sudah berjalan di port 1025
-   Cek firewall Windows tidak memblokir port 1025

### Error "Authentication failed" (Gmail):

-   Pastikan menggunakan **App Password**, bukan password biasa
-   Pastikan 2-Step Verification sudah aktif
-   Cek username dan password di `.env` sudah benar

### Queue tidak diproses:

-   Pastikan queue worker berjalan: `php artisan queue:work`
-   Cek driver queue di `.env`: `QUEUE_CONNECTION=database` atau `sync`
-   Jika menggunakan database queue, jalankan migration:
    ```powershell
    php artisan queue:table
    php artisan migrate
    ```

---

## 📝 Catatan Penting

1. **Jangan commit file `.env`** ke repository
2. **Gunakan Mailpit atau Log driver** untuk development
3. **Gunakan SMTP nyata** hanya untuk production atau testing dengan email real
4. **Queue worker harus berjalan** untuk email yang menggunakan queue
5. **Clear config cache** setelah mengubah `.env`:
    ```powershell
    php artisan config:clear
    ```

---

## 🔗 Referensi

-   [Laravel Mail Documentation](https://laravel.com/docs/mail)
-   [Mailpit GitHub](https://github.com/axllent/mailpit)
-   [Gmail App Passwords](https://support.google.com/accounts/answer/185833)
