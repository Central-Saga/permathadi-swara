# Permathadi Swara

Laravel application dengan Livewire dan Flux UI.

## Requirements

- Docker Desktop
- PHP 8.2+ (jika menjalankan tanpa Sail)
- Composer
- Node.js & NPM

## Setup dengan Laravel Sail

Project ini menggunakan [Laravel Sail](https://laravel.com/docs/sail) untuk development environment dengan Docker.

### 1. Install Dependencies

```bash
composer install
npm install
```

### 2. Setup Environment

Copy file `.env.example` ke `.env`:

```bash
cp .env.example .env
```

Generate application key:

```bash
sail artisan key:generate
```

### 3. Start Docker Containers

Jalankan Sail untuk start semua containers:

```bash
sail up -d
```

### 4. Setup Database

Jalankan migrations:

```bash
sail artisan migrate
```

### 5. Build Assets

Build frontend assets:

```bash
sail npm run build
```

Atau untuk development dengan hot reload:

```bash
sail npm run dev
```

## Command-command Penting

### Sail Commands

- `sail up` - Start containers
- `sail up -d` - Start containers di background
- `sail down` - Stop containers
- `sail ps` - Lihat status containers
- `sail logs` - Lihat logs containers

### Artisan Commands

- `sail artisan migrate` - Run database migrations
- `sail artisan migrate:fresh` - Fresh migration dengan seed
- `sail artisan db:seed` - Run database seeders
- `sail artisan tinker` - Laravel REPL

### Composer & NPM

- `sail composer install` - Install PHP dependencies
- `sail composer update` - Update PHP dependencies
- `sail npm install` - Install Node dependencies
- `sail npm run dev` - Development build dengan hot reload
- `sail npm run build` - Production build

### Testing

- `sail artisan test` - Run tests
- `sail artisan test --parallel` - Run tests secara parallel

## Akses Application

Setelah containers running, aplikasi dapat diakses di:

- **Web**: http://localhost
- **MySQL**: localhost:3306
  - Database: `laravel`
  - Username: `sail`
  - Password: `password`

## Database Configuration

Project ini dikonfigurasi untuk menggunakan MySQL melalui Laravel Sail. Konfigurasi database ada di file `.env`:

```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

## Troubleshooting

### Port sudah digunakan

Jika port 80 atau 3306 sudah digunakan, Anda bisa mengubahnya di file `.env`:

```
APP_PORT=8080
FORWARD_DB_PORT=3307
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
```

## Development

Untuk development dengan hot reload, jalankan:

```bash
sail npm run dev
```

Aplikasi akan otomatis reload saat ada perubahan di file CSS/JS.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

