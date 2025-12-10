# Cara Menjalankan Aplikasi dengan SSL & Reverse Proxy (Caddy)

## Prasyarat

1. Docker Desktop harus running
2. Caddy harus terinstall (lihat [CADDY_SETUP.md](./CADDY_SETUP.md))
3. File Caddyfile sudah ada di root project

## Quick Start

Gunakan script `dev.sh` untuk start semua sekaligus:

```bash
./dev.sh
```

Script ini akan:

-   Check dan setup Caddy
-   Setup /etc/hosts (jika belum ada)
-   Start Caddy dengan Caddyfile
-   Start Docker containers (Laravel Sail)
-   Start Vite dev server

## Langkah-langkah Manual

### 1. Install Caddy (jika belum)

**macOS (Homebrew):**

```bash
brew install caddy
```

**Atau download langsung:**

-   Download dari: https://caddyserver.com/download

### 2. Setup /etc/hosts

Tambahkan entry berikut ke `/etc/hosts`:

```bash
sudo nano /etc/hosts
```

Tambahkan baris:

```
127.0.0.1 permathadi-swara.test www.permathadi-swara.test
```

### 3. Setup Environment

Pastikan `APP_PORT=8080` di file `.env`:

```env
APP_PORT=8080
APP_URL=https://permathadi-swara.test
VITE_DEV_SERVER_URL=https://permathadi-swara.test
```

**Catatan:** Caddy akan menggunakan port 80 dan 443, jadi Laravel Sail harus menggunakan port lain (8080).

### 4. Start Laravel Sail (Docker Container)

```bash
./vendor/bin/sail up -d
```

Cek status:

```bash
./vendor/bin/sail ps
```

### 5. Start Caddy

Validasi Caddyfile terlebih dahulu:

```bash
sudo caddy validate --config Caddyfile
```

Start Caddy:

```bash
sudo caddy start --config Caddyfile
```

Cek status:

```bash
sudo caddy status
```

### 6. Start Vite Dev Server

Jalankan di terminal terpisah (atau background):

```bash
npm run dev
```

Atau jika ingin di background:

```bash
npm run dev > /dev/null 2>&1 &
```

Cek apakah Vite running:

```bash
curl http://127.0.0.1:5173/@vite/client
```

### 7. Clear Laravel Cache

```bash
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan view:clear
./vendor/bin/sail artisan cache:clear
```

### 8. Akses Aplikasi

Buka browser dan akses:

```
https://permathadi-swara.test
```

Caddy akan otomatis generate SSL certificate untuk domain local.

## Troubleshooting

### Jika Vite assets masih 404:

1. Pastikan Vite dev server running: `ps aux | grep vite`
2. Test akses langsung: `curl http://127.0.0.1:5173/@vite/client`
3. Cek Caddy logs: `sudo caddy logs`

### Jika masih ada mixed content error:

1. Pastikan `APP_URL=https://permathadi-swara.test` di `.env`
2. Pastikan `VITE_DEV_SERVER_URL=https://permathadi-swara.test` di `.env`
3. Clear cache lagi: `./vendor/bin/sail artisan config:clear`

### Jika port 5173 sudah digunakan:

```bash
# Cari process yang menggunakan port 5173
lsof -i :5173

# Kill process tersebut
kill -9 <PID>
```

### Jika port 80 sudah digunakan:

```bash
# Cari process yang menggunakan port 80
lsof -i :80

# Jika Caddy sudah running, cek status:
sudo caddy status

# Jika ada service lain, stop terlebih dahulu
```

### Jika Caddy tidak start:

1. Cek apakah Caddyfile valid: `sudo caddy validate --config Caddyfile`
2. Cek apakah port 80 dan 443 tersedia
3. Cek log: `sudo caddy logs`
4. Coba stop Caddy dulu: `sudo caddy stop`, lalu start lagi

### Domain tidak resolve:

1. Pastikan entry sudah ada di `/etc/hosts`
2. Flush DNS cache:
    ```bash
    sudo dscacheutil -flushcache; sudo killall -HUP mDNSResponder
    ```

### Sail tidak accessible:

Pastikan Sail container sudah running:

```bash
./vendor/bin/sail ps
```

Jika tidak running, start ulang:

```bash
./vendor/bin/sail up -d
```

## Caddy Commands

-   **Start Caddy**: `sudo caddy start --config Caddyfile`
-   **Stop Caddy**: `sudo caddy stop`
-   **Reload config**: `sudo caddy reload --config Caddyfile`
-   **Check status**: `sudo caddy status`
-   **View logs**: `sudo caddy logs`
-   **Validate config**: `sudo caddy validate --config Caddyfile`

## Keuntungan Menggunakan Caddy

-   ✅ Auto SSL (menggunakan `tls internal` untuk local development)
-   ✅ Konfigurasi lebih sederhana daripada Nginx
-   ✅ Built-in reverse proxy yang powerful
-   ✅ WebSocket support otomatis
-   ✅ Tidak perlu setup SSL certificate manual
-   ✅ Tidak bergantung pada ServBay atau software tambahan
