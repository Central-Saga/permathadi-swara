# Setup Domain Local untuk Development dengan Caddy

Dokumen ini menjelaskan cara setup domain lokal `permathadi-swara.test` untuk development menggunakan Caddy sebagai reverse proxy.

## Prerequisites

-   macOS (script ini dirancang untuk macOS, tapi bisa diadaptasi untuk Linux)
-   Caddy terinstall (via Homebrew: `brew install caddy`)
-   Docker Desktop terinstall
-   Laravel Sail sudah dikonfigurasi

## Quick Setup

Gunakan script `dev.sh` untuk setup otomatis:

```bash
./dev.sh
```

Script ini akan:

1. Check dan install Caddy (jika belum ada)
2. Menambahkan entry ke `/etc/hosts`
3. Setup dan start Caddy dengan Caddyfile
4. Start Docker containers
5. Start Vite dev server

## Manual Setup

### 1. Install Caddy

**macOS (Homebrew):**

```bash
brew install caddy
```

**Atau download langsung:**

-   Download dari: https://caddyserver.com/download

### 2. Setup Hosts File

Tambahkan entry berikut ke `/etc/hosts`:

```bash
sudo nano /etc/hosts
```

Tambahkan baris:

```
127.0.0.1 permathadi-swara.test www.permathadi-swara.test
```

**Catatan:** Domain `.test` otomatis di-resolve ke localhost di macOS, tapi tetap perlu entry di `/etc/hosts` untuk konsistensi.

### 3. Setup Environment

Update file `.env`:

```env
APP_PORT=8080
APP_URL=https://permathadi-swara.test
VITE_DEV_SERVER_URL=https://permathadi-swara.test
```

**Penting:**

-   `APP_PORT` harus 8080 (bukan 80) karena Caddy akan menggunakan port 80 dan 443
-   `APP_URL` dan `VITE_DEV_SERVER_URL` harus menggunakan HTTPS

### 4. Setup Caddy Reverse Proxy

Caddyfile sudah tersedia di root project. Validasi terlebih dahulu:

```bash
sudo caddy validate --config Caddyfile
```

Start Caddy:

```bash
# Dari directory project
sudo caddy start --config Caddyfile
```

Cek status:

```bash
sudo caddy status
```

### 5. Start Laravel Sail

Pastikan Sail sudah running:

```bash
./vendor/bin/sail up -d
```

Cek status:

```bash
./vendor/bin/sail ps
```

### 6. Start Vite Dev Server

Jalankan Vite dev server:

```bash
npm run dev
```

## Akses Aplikasi

Setelah setup selesai, aplikasi dapat diakses di:

-   **HTTPS**: https://permathadi-swara.test (dengan SSL auto-generated)
-   **HTTP**: http://permathadi-swara.test (akan redirect ke HTTPS)

Atau tetap bisa diakses langsung di:

-   **Direct**: http://localhost:8080 (tanpa SSL, langsung ke Laravel Sail)

## Troubleshooting

### Domain tidak resolve

1. Pastikan entry sudah ada di `/etc/hosts`
2. Flush DNS cache:
    ```bash
    sudo dscacheutil -flushcache; sudo killall -HUP mDNSResponder
    ```
3. Cek dengan ping:
    ```bash
    ping permathadi-swara.test
    ```

### Caddy tidak start

1. Cek apakah Caddy sudah terinstall:
    ```bash
    which caddy
    caddy version
    ```
2. Cek error Caddy:
    ```bash
    sudo caddy validate --config Caddyfile
    sudo caddy logs
    ```
3. Cek apakah port 80 sudah digunakan:
    ```bash
    lsof -i :80
    ```
4. Jika port 80 digunakan oleh service lain, stop terlebih dahulu

### Port 80 sudah digunakan

Jika port 80 sudah digunakan oleh Sail atau service lain:

1. Pastikan `APP_PORT=8080` di `.env`
2. Restart Sail containers:
    ```bash
    ./vendor/bin/sail down
    ./vendor/bin/sail up -d
    ```
3. Cek apakah ada service lain yang menggunakan port 80:
    ```bash
    lsof -i :80
    ```

### SSL Certificate Error

Caddy menggunakan `tls internal` untuk auto-generate certificate. Jika ada error:

1. Cek Caddy logs: `sudo caddy logs`
2. Reload Caddy: `sudo caddy reload --config Caddyfile`
3. Clear browser cache dan cookies untuk domain tersebut

### Sail tidak accessible

Pastikan Sail container sudah running:

```bash
./vendor/bin/sail ps
```

Jika tidak running, start ulang:

```bash
./vendor/bin/sail up -d
```

Tunggu beberapa detik untuk container siap, lalu cek lagi.

### Vite HMR tidak bekerja

1. Pastikan Vite dev server running: `ps aux | grep vite`
2. Test akses langsung: `curl http://127.0.0.1:5173/@vite/client`
3. Pastikan `VITE_DEV_SERVER_URL=https://permathadi-swara.test` di `.env`
4. Clear cache: `./vendor/bin/sail artisan config:clear`

## Caddy Commands Reference

-   **Start**: `sudo caddy start --config Caddyfile`
-   **Stop**: `sudo caddy stop`
-   **Reload**: `sudo caddy reload --config Caddyfile`
-   **Status**: `sudo caddy status`
-   **Logs**: `sudo caddy logs`
-   **Validate**: `sudo caddy validate --config Caddyfile`

## Keuntungan Menggunakan Caddy

-   ✅ Auto SSL (menggunakan `tls internal` untuk local development)
-   ✅ Konfigurasi lebih sederhana daripada Nginx
-   ✅ Built-in reverse proxy yang powerful
-   ✅ WebSocket support otomatis
-   ✅ Tidak perlu setup SSL certificate manual
-   ✅ Tidak bergantung pada ServBay atau software tambahan
-   ✅ Cross-platform (macOS, Linux, Windows)

## Catatan

-   Caddy akan auto-generate SSL certificate untuk `permathadi-swara.test`
-   Pastikan Vite dev server running: `npm run dev`
-   Pastikan Laravel Sail running: `./vendor/bin/sail up -d`
-   Caddy reverse proxy hanya diperlukan jika ingin menggunakan domain custom dengan SSL
-   Jika tidak menggunakan Caddy, tetap bisa akses langsung via `http://localhost:8080`
