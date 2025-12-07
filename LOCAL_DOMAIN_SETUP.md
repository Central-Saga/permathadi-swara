# Setup Domain Local untuk Development

Dokumen ini menjelaskan cara setup domain lokal `permathadi-swara.test` untuk development.

## Prerequisites

- macOS (script ini dirancang untuk macOS)
- Nginx terinstall (via Homebrew: `brew install nginx`)
- Laravel Sail sudah running

## Quick Setup

Jalankan script setup otomatis:

```bash
./setup-local-domain.sh
```

Script ini akan:
1. Menambahkan entry ke `/etc/hosts`
2. Copy nginx config ke directory nginx
3. Test dan reload nginx

## Manual Setup

### 1. Setup Hosts File

Tambahkan entry berikut ke `/etc/hosts`:

```bash
sudo nano /etc/hosts
```

Tambahkan baris:
```
127.0.0.1 permathadi-swara.test www.permathadi-swara.test
```

### 2. Setup Nginx Reverse Proxy

Copy nginx config ke directory nginx:

```bash
# Untuk Homebrew nginx
sudo cp nginx/permathadi-swara.test.conf /opt/homebrew/etc/nginx/servers/

# Atau untuk nginx biasa
sudo cp nginx/permathadi-swara.test.conf /usr/local/etc/nginx/servers/
```

Test dan reload nginx:

```bash
sudo nginx -t
sudo nginx -s reload
```

### 3. Update Environment

Update file `.env`:

```env
APP_URL=http://permathadi-swara.test
```

### 4. Start Laravel Sail

Pastikan Sail sudah running:

```bash
./vendor/bin/sail up -d
```

## Akses Aplikasi

Setelah setup selesai, aplikasi dapat diakses di:

- http://permathadi-swara.test
- http://www.permathadi-swara.test

Atau tetap bisa diakses di:
- http://localhost (default Sail port)

## Troubleshooting

### Domain tidak resolve

1. Pastikan entry sudah ada di `/etc/hosts`
2. Flush DNS cache:
   ```bash
   sudo dscacheutil -flushcache; sudo killall -HUP mDNSResponder
   ```

### Nginx tidak start

1. Cek apakah nginx sudah terinstall:
   ```bash
   brew list nginx
   ```
2. Cek error nginx:
   ```bash
   sudo nginx -t
   ```
3. Cek apakah port 80 sudah digunakan:
   ```bash
   lsof -i :80
   ```

### Port 80 sudah digunakan

Jika port 80 sudah digunakan oleh Sail, ubah nginx config untuk menggunakan port lain (misalnya 8080) dan update `proxy_pass` di config.

### Sail tidak accessible

Pastikan Sail container sudah running:
```bash
./vendor/bin/sail ps
```

## Catatan

- Nginx reverse proxy hanya diperlukan jika ingin menggunakan domain custom
- Jika tidak menggunakan nginx, tetap bisa akses langsung via `http://localhost`
- Domain `.test` otomatis di-resolve ke localhost di macOS

