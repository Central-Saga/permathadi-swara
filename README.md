# Permathadi Swara

Sistem manajemen sanggar seni untuk melestarikan dan mengembangkan seni tari dan tabuh tradisional Bali. Aplikasi web ini menyediakan platform lengkap untuk mengelola anggota, program pembelajaran, subscription, pembayaran, galeri, dan komunikasi dengan pengunjung.

## 📋 Deskripsi Project

Permathadi Swara adalah aplikasi web berbasis Laravel yang dirancang khusus untuk sanggar seni tradisional Bali. Aplikasi ini memungkinkan pengelola sanggar untuk:

-   Mengelola data anggota dan registrasi
-   Menyediakan informasi program/layanan pembelajaran
-   Mengelola sistem subscription dan pembayaran
-   Menampilkan galeri foto kegiatan
-   Menerima dan mengelola pesan kontak dari pengunjung
-   Mengelola konten website melalui admin panel

Aplikasi ini terdiri dari dua bagian utama:

1. **Landing Page** - Halaman publik untuk menampilkan informasi sanggar, program, galeri, dan kontak
2. **Admin Panel (Godmode)** - Panel administrasi untuk mengelola semua aspek aplikasi dengan role-based access control

## ✨ Fitur-fitur

### Landing Page (Public)

-   **Home** - Halaman utama dengan hero section, statistik, program unggulan, dan testimoni
-   **Tentang** - Informasi tentang sanggar, profil, visi misi, dan keunggulan
-   **Program** - Daftar program/layanan yang tersedia dengan detail lengkap
-   **Galeri** - Galeri foto kegiatan sanggar
-   **Kontak** - Form kontak untuk pengunjung mengirim pesan
-   **History** - Riwayat subscription dan pembayaran untuk anggota yang sudah login
-   **Subscribe** - Halaman untuk anggota mendaftar ke program/layanan
-   **Renew** - Halaman untuk memperpanjang subscription

### Admin Panel (Godmode)

-   **Dashboard** - Overview sistem dan statistik
-   **User Management** - Kelola pengguna dan hak akses
-   **Role & Permission** - Manajemen role dan permission menggunakan Spatie Permission
-   **Anggota Management** - Kelola data anggota sanggar
-   **Layanan Management** - Kelola program/layanan pembelajaran
-   **Subscription Management** - Kelola subscription anggota
-   **Payment Management** - Kelola pembayaran dan verifikasi bukti pembayaran
-   **Contact Messages** - Kelola pesan dari form kontak
-   **Galeri Management** - Kelola galeri foto dengan upload multiple images

### Fitur Teknis

-   **Authentication** - Sistem autentikasi menggunakan Laravel Fortify
-   **Authorization** - Role-based access control dengan Spatie Permission
-   **Media Management** - Upload dan manajemen media menggunakan Spatie Media Library dengan support responsive images, WebP, dan AVIF
-   **Queue System** - Background job processing dengan Laravel Horizon
-   **Email Notifications** - Notifikasi email untuk konfirmasi kontak dan pembayaran
-   **Export/Import** - Export data menggunakan Maatwebsite Excel
-   **PDF Generation** - Generate PDF menggunakan DomPDF
-   **Document Generation** - Generate dokumen Word menggunakan PHPWord

## 🛠️ Tech Stack

### Backend

-   **Laravel 12** - PHP Framework
-   **Livewire 3** - Full-stack framework untuk Laravel
-   **Livewire Volt** - Single-file components untuk Livewire
-   **Laravel Fortify** - Authentication services
-   **Laravel Horizon** - Queue monitoring dan management
-   **Spatie Permission** - Role & Permission management
-   **Spatie Media Library** - Media management dengan image optimization
-   **Maatwebsite Excel** - Excel import/export
-   **Barryvdh DomPDF** - PDF generation
-   **PHPOffice PHPWord** - Word document generation

### Frontend

-   **Flux UI 2** - UI component library untuk Livewire
-   **Flux Pro 2.6** - Premium components dari Flux
-   **Tailwind CSS 4** - Utility-first CSS framework
-   **Vite 7** - Build tool dan dev server
-   **GSAP** - Animation library untuk landing page
-   **Axios** - HTTP client

### Infrastructure

-   **Docker** - Containerization
-   **Laravel Sail** - Docker development environment
-   **Caddy** - Reverse proxy dengan auto SSL untuk development
-   **MySQL 8.0** - Database
-   **Redis** - Cache dan queue driver

## 📦 Prerequisites

Sebelum memulai instalasi, pastikan Anda telah menginstall:

-   **Docker Desktop** - [Download Docker Desktop](https://www.docker.com/products/docker-desktop)
-   **Caddy** - Reverse proxy dengan auto SSL (install via Homebrew: `brew install caddy`)
-   **Git** - Untuk clone repository
-   **Composer** (opsional) - Jika ingin install dependencies di luar Docker
-   **Node.js & NPM** (opsional) - Jika ingin build assets di luar Docker

> **Catatan**:
>
> -   Jika menggunakan Laravel Sail, Anda tidak perlu menginstall PHP, MySQL, atau Redis secara lokal. Semua akan berjalan di dalam Docker container.
> -   Caddy digunakan untuk reverse proxy dengan SSL otomatis. Lihat [CADDY_SETUP.md](./CADDY_SETUP.md) untuk detail setup.

## 🚀 Instalasi dengan Docker

Project ini menggunakan [Laravel Sail](https://laravel.com/docs/sail) untuk development environment dengan Docker. Sail menyediakan Docker containers yang sudah dikonfigurasi untuk menjalankan aplikasi Laravel.

### 1. Clone Repository

```bash
git clone <repository-url>
cd permathadi-swara
```

### 2. Install Dependencies

Install PHP dependencies menggunakan Composer:

```bash
composer install
```

Install Node.js dependencies:

```bash
npm install
```

### 3. Setup Environment

Copy file `.env.example` ke `.env`:

```bash
cp .env.example .env
```

Edit file `.env` dan sesuaikan konfigurasi sesuai kebutuhan Anda, terutama:

-   `APP_NAME` - Nama aplikasi
-   `APP_URL` - URL aplikasi
-   Database configuration
-   Mail configuration (jika diperlukan)

Generate application key:

```bash
./vendor/bin/sail artisan key:generate
```

Atau jika Sail sudah di-aliased:

```bash
sail artisan key:generate
```

### 4. Start Docker Containers

Jalankan Sail untuk start semua containers:

```bash
./vendor/bin/sail up -d
```

Atau jika Sail sudah di-aliased:

```bash
sail up -d
```

Ini akan start tiga containers:

-   `laravel.test` - Aplikasi Laravel (PHP 8.4)
-   `mysql` - Database MySQL 8.0
-   `redis` - Redis untuk cache dan queue

### 5. Setup Database

Jalankan migrations untuk membuat tabel-tabel database:

```bash
sail artisan migrate
```

Jalankan seeders untuk mengisi data awal (opsional):

```bash
sail artisan db:seed
```

Atau jalankan migration dan seeder sekaligus:

```bash
sail artisan migrate --seed
```

### 6. Build Assets

Build frontend assets untuk production:

```bash
sail npm run build
```

Atau untuk development dengan hot reload:

```bash
sail npm run dev
```

### 7. Setup Storage Link

Buat symbolic link untuk storage:

```bash
sail artisan storage:link
```

### 8. Setup Queue Worker (Opsional)

Jika menggunakan queue, jalankan Horizon:

```bash
sail artisan horizon
```

Atau untuk development, gunakan queue:listen:

```bash
sail artisan queue:listen
```

### 9. Setup Caddy (Reverse Proxy dengan SSL)

Untuk menggunakan domain custom dengan SSL otomatis, setup Caddy:

```bash
# Install Caddy (jika belum)
brew install caddy

# Setup /etc/hosts (tambahkan entry untuk permathadi-swara.test)
sudo nano /etc/hosts
# Tambahkan: 127.0.0.1 permathadi-swara.test www.permathadi-swara.test

# Pastikan APP_PORT=8080 di .env (Caddy akan menggunakan port 80)
# Start Caddy
sudo caddy start --config Caddyfile
```

Atau gunakan script `dev.sh` yang akan setup semua secara otomatis:

```bash
./dev.sh
```

Lihat [CADDY_SETUP.md](./CADDY_SETUP.md) atau [RUNNING.md](./RUNNING.md) untuk detail lebih lengkap.

### 10. Akses Aplikasi

Setelah semua setup selesai, aplikasi dapat diakses di:

-   **Dengan Caddy (Recommended)**: https://permathadi-swara.test (dengan SSL)
-   **Tanpa Caddy**: http://localhost:8080 (langsung ke Laravel Sail)
-   **Horizon Dashboard**: https://permathadi-swara.test/horizon (dengan Caddy) atau http://localhost:8080/horizon (tanpa Caddy)

## 🔧 Konfigurasi

### Database Configuration

Project ini dikonfigurasi untuk menggunakan MySQL melalui Laravel Sail. Konfigurasi database ada di file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

### Port Configuration

**Penting:** Jika menggunakan Caddy sebagai reverse proxy, `APP_PORT` harus diset ke `8080` (bukan 80) karena Caddy akan menggunakan port 80 dan 443.

Jika port default sudah digunakan, Anda bisa mengubahnya di file `.env`:

```env
APP_PORT=8080  # Harus 8080 jika menggunakan Caddy
FORWARD_DB_PORT=3307
FORWARD_REDIS_PORT=6380
VITE_PORT=5173
```

**Port Configuration Summary:**

-   **Caddy**: Port 80 (HTTP) dan 443 (HTTPS) - untuk reverse proxy
-   **Laravel Sail**: Port 8080 (APP_PORT) - diakses oleh Caddy
-   **Vite Dev Server**: Port 5173 - diakses oleh Caddy untuk dev assets

Kemudian restart containers:

```bash
sail down
sail up -d
```

### Mail Configuration

Konfigurasi email di file `.env` untuk fitur notifikasi:

```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Media Library Configuration

Spatie Media Library sudah dikonfigurasi untuk menyimpan media di `storage/app/public`. Pastikan symbolic link sudah dibuat dengan:

```bash
sail artisan storage:link
```

## 📁 Struktur Project

### Model Utama

-   **User** - Model untuk pengguna sistem
-   **Anggota** - Model untuk anggota sanggar (berelasi dengan User)
-   **Layanan** - Model untuk program/layanan pembelajaran
-   **Subscription** - Model untuk subscription anggota ke layanan
-   **Payment** - Model untuk pembayaran subscription
-   **Galeri** - Model untuk galeri foto
-   **ContactMessage** - Model untuk pesan kontak

### Routing Structure

#### Public Routes (Landing Page)

-   `/` - Home page
-   `/tentang` - About page
-   `/program` - Program listing
-   `/program/{slug}` - Program detail
-   `/galeri` - Gallery page
-   `/kontak` - Contact page

#### Authenticated Routes

-   `/dashboard` - Dashboard (hanya untuk admin)
-   `/settings/profile` - Profile settings
-   `/settings/password` - Password settings
-   `/settings/appearance` - Appearance settings
-   `/history` - Subscription history
-   `/subscribe/{slug}` - Subscribe to program
-   `/renew/{subscription}` - Renew subscription

#### Admin Routes (Godmode)

Semua route admin menggunakan prefix `/godmode`:

-   `/godmode/users` - User management
-   `/godmode/roles` - Role & Permission management
-   `/godmode/anggota` - Anggota management
-   `/godmode/layanan` - Layanan management
-   `/godmode/subscriptions` - Subscription management
-   `/godmode/payments` - Payment management
-   `/godmode/contact-messages` - Contact messages management
-   `/godmode/galeri` - Galeri management

### Folder Structure

```
permathadi-swara/
├── app/
│   ├── Actions/          # Action classes
│   ├── Http/             # HTTP layer (Controllers, Middleware)
│   ├── Livewire/         # Livewire components
│   ├── Mail/             # Mail classes
│   ├── Models/           # Eloquent models
│   └── Providers/        # Service providers
├── database/
│   ├── factories/         # Model factories
│   ├── migrations/        # Database migrations
│   └── seeders/           # Database seeders
├── resources/
│   ├── css/              # CSS files
│   ├── js/               # JavaScript files
│   └── views/            # Blade templates
├── routes/
│   └── web.php           # Web routes
├── storage/              # Storage files (media, logs)
├── tests/                # Test files
├── compose.yaml          # Docker Compose configuration
└── README.md             # This file
```

## 💻 Development Guide

### Scripts yang Tersedia

Project ini menyediakan beberapa script untuk memudahkan development:

#### dev.sh (Recommended)

Script utama untuk menjalankan development environment dengan Caddy:

```bash
./dev.sh
```

Script ini akan:

-   Check dan setup Caddy (jika belum terinstall, beri instruksi)
-   Setup `/etc/hosts` untuk domain `permathadi-swara.test`
-   Validasi dan start Caddy dengan Caddyfile
-   Pastikan `APP_PORT=8080` di `.env`
-   Start Docker containers (Laravel Sail)
-   Start NPM dev server (Vite)

**Catatan:** Script ini akan setup semua yang diperlukan untuk development dengan SSL otomatis via Caddy.

#### setup.sh

Script untuk setup project dari awal (fresh clone):

```bash
./setup.sh
```

Script ini akan:

-   Membuat file `.env` dari `.env.example`
-   Install PHP dan NPM dependencies
-   Generate application key
-   Start Docker containers
-   Run migrations
-   Build frontend assets
-   Clear caches

#### start-dev.sh

Script untuk menjalankan semua development services sekaligus:

```bash
./start-dev.sh
```

Script ini akan:

-   Start Docker containers
-   Clear caches
-   Start Horizon (queue worker) di background
-   Start NPM dev server di background

#### stop-dev.sh

Script untuk menghentikan semua development services:

```bash
./stop-dev.sh
```

Script ini akan:

-   Stop background processes (Horizon, NPM dev server)
-   Terminate Horizon

### Development Workflow

1. **Start Development Environment (Recommended dengan Caddy)**

    ```bash
    ./dev.sh
    ```

    Script ini akan setup Caddy, Docker, dan Vite secara otomatis. Aplikasi akan accessible di `https://permathadi-swara.test`.

    Atau manual tanpa Caddy:

    ```bash
    sail up -d
    sail npm run dev
    sail artisan horizon
    ```

    Aplikasi akan accessible di `http://localhost:8080`.

2. **Development dengan Hot Reload**

    Untuk development dengan hot reload pada frontend assets:

    ```bash
    sail npm run dev
    ```

    Aplikasi akan otomatis reload saat ada perubahan di file CSS/JS.

3. **Queue Processing**

    Untuk development, jalankan queue worker:

    ```bash
    sail artisan queue:listen
    ```

    Atau gunakan Horizon untuk monitoring:

    ```bash
    sail artisan horizon
    ```

4. **Database Seeding**

    Untuk mengisi database dengan data dummy:

    ```bash
    sail artisan db:seed
    ```

    Atau seed specific seeder:

    ```bash
    sail artisan db:seed --class=UserSeeder
    ```

## 📝 Command Reference

### Sail Commands

-   `sail up` - Start containers
-   `sail up -d` - Start containers di background
-   `sail down` - Stop containers
-   `sail ps` - Lihat status containers
-   `sail logs` - Lihat logs containers
-   `sail logs -f` - Follow logs containers
-   `sail shell` - Masuk ke shell container
-   `sail exec laravel.test bash` - Masuk ke bash container

### Artisan Commands

-   `sail artisan migrate` - Run database migrations
-   `sail artisan migrate:fresh` - Fresh migration (drop semua tabel)
-   `sail artisan migrate:fresh --seed` - Fresh migration dengan seed
-   `sail artisan migrate:rollback` - Rollback migration terakhir
-   `sail artisan db:seed` - Run database seeders
-   `sail artisan tinker` - Laravel REPL
-   `sail artisan route:list` - List semua routes
-   `sail artisan make:model` - Buat model baru
-   `sail artisan make:migration` - Buat migration baru
-   `sail artisan make:livewire` - Buat Livewire component baru

### Composer & NPM

-   `sail composer install` - Install PHP dependencies
-   `sail composer update` - Update PHP dependencies
-   `sail composer require <package>` - Install package baru
-   `sail npm install` - Install Node dependencies
-   `sail npm run dev` - Development build dengan hot reload
-   `sail npm run build` - Production build
-   `sail npm run watch` - Watch mode untuk development

### Cache & Optimization

-   `sail artisan config:clear` - Clear config cache
-   `sail artisan cache:clear` - Clear application cache
-   `sail artisan route:clear` - Clear route cache
-   `sail artisan view:clear` - Clear view cache
-   `sail artisan optimize` - Optimize application (cache config, routes, views)
-   `sail artisan optimize:clear` - Clear semua cache

### Queue & Horizon

-   `sail artisan queue:work` - Start queue worker
-   `sail artisan queue:listen` - Listen queue dengan auto-restart
-   `sail artisan horizon` - Start Horizon dashboard
-   `sail artisan horizon:terminate` - Terminate Horizon
-   `sail artisan queue:failed` - List failed jobs
-   `sail artisan queue:retry all` - Retry semua failed jobs

### Caddy Commands

-   `sudo caddy start --config Caddyfile` - Start Caddy dengan Caddyfile
-   `sudo caddy stop` - Stop Caddy
-   `sudo caddy reload --config Caddyfile` - Reload Caddy config
-   `sudo caddy status` - Check Caddy status
-   `sudo caddy logs` - View Caddy logs
-   `sudo caddy validate --config Caddyfile` - Validate Caddyfile

Lihat [CADDY_SETUP.md](./CADDY_SETUP.md) atau [RUNNING.md](./RUNNING.md) untuk detail lebih lengkap tentang Caddy.

### Testing

-   `sail artisan test` - Run tests
-   `sail artisan test --parallel` - Run tests secara parallel
-   `sail artisan test --filter <test-name>` - Run specific test
-   `sail artisan test --coverage` - Run tests dengan coverage

### Storage & Media

-   `sail artisan storage:link` - Buat symbolic link untuk storage
-   `sail artisan media-library:clean` - Clean unused media files
-   `sail artisan media-library:regenerate` - Regenerate media conversions

## 🐛 Troubleshooting

### Port sudah digunakan

Jika port 80, 3306, atau 6379 sudah digunakan, ubah di file `.env`:

```env
APP_PORT=8080
FORWARD_DB_PORT=3307
FORWARD_REDIS_PORT=6380
```

Kemudian restart containers:

```bash
sail down
sail up -d
```

### Permission Issues

Jika ada masalah permission, jalankan:

```bash
sail artisan storage:link
sail artisan cache:clear
sail artisan config:clear
sail artisan route:clear
sail artisan view:clear
```

Atau jika masalah permission pada storage:

```bash
sail shell
chmod -R 775 storage bootstrap/cache
chown -R sail:sail storage bootstrap/cache
```

### Container tidak start

Jika container tidak start, cek logs:

```bash
sail logs
```

Atau cek status containers:

```bash
sail ps
```

Jika ada masalah, coba rebuild containers:

```bash
sail down
sail build --no-cache
sail up -d
```

### Database connection error

Pastikan MySQL container sudah running:

```bash
sail ps
```

Jika tidak running, start ulang:

```bash
sail up -d mysql
```

Tunggu beberapa detik untuk MySQL siap, lalu coba lagi.

### NPM build error

Jika ada error saat build, coba:

```bash
rm -rf node_modules package-lock.json
sail npm install
sail npm run build
```

### Composer memory limit

Jika ada error memory limit saat install dependencies:

```bash
COMPOSER_MEMORY_LIMIT=-1 sail composer install
```

### Media tidak muncul

Pastikan symbolic link sudah dibuat:

```bash
sail artisan storage:link
```

Dan pastikan permission folder storage benar:

```bash
sail shell
chmod -R 775 storage
```

### Caddy tidak start atau domain tidak accessible

1. **Cek apakah Caddy terinstall:**

    ```bash
    which caddy
    caddy version
    ```

    Jika belum terinstall: `brew install caddy`

2. **Cek apakah entry sudah ada di /etc/hosts:**

    ```bash
    grep permathadi-swara.test /etc/hosts
    ```

    Jika belum ada, tambahkan: `127.0.0.1 permathadi-swara.test www.permathadi-swara.test`

3. **Cek apakah port 80 sudah digunakan:**

    ```bash
    lsof -i :80
    ```

    Jika port 80 digunakan oleh service lain, stop terlebih dahulu.

4. **Validasi Caddyfile:**

    ```bash
    sudo caddy validate --config Caddyfile
    ```

5. **Cek Caddy status dan logs:**

    ```bash
    sudo caddy status
    sudo caddy logs
    ```

6. **Pastikan APP_PORT=8080 di .env:**
   Caddy akan menggunakan port 80, jadi Laravel Sail harus menggunakan port 8080.

Lihat [RUNNING.md](./RUNNING.md) untuk troubleshooting lebih lengkap tentang Caddy.

## 📚 Resources

-   [Laravel Documentation](https://laravel.com/docs)
-   [Livewire Documentation](https://livewire.laravel.com/docs)
-   [Flux UI Documentation](https://flux.laravel.com/docs)
-   [Laravel Sail Documentation](https://laravel.com/docs/sail)
-   [Spatie Permission Documentation](https://spatie.be/docs/laravel-permission)
-   [Spatie Media Library Documentation](https://spatie.be/docs/laravel-medialibrary)

## 📄 License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
