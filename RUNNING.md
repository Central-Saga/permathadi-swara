# Cara Menjalankan Aplikasi dengan SSL & Reverse Proxy

## Prasyarat
1. Docker Desktop harus running
2. Nginx (ServBay) harus running
3. File nginx config sudah di-copy ke `/Applications/ServBay/package/etc/nginx/vhosts/`

## Langkah-langkah

### 1. Start Laravel Sail (Docker Container)
```bash
./vendor/bin/sail up -d
```

Cek status:
```bash
./vendor/bin/sail ps
```

### 2. Start Vite Dev Server
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

### 3. Reload Nginx Config
Pastikan nginx config sudah ter-copy dan reload:
```bash
# Copy config ke ServBay
sudo cp nginx/permathadi-swara.test.conf /Applications/ServBay/package/etc/nginx/vhosts/

# Test config
sudo nginx -t

# Reload nginx
sudo nginx -s reload
```

### 4. Clear Laravel Cache
```bash
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan view:clear
./vendor/bin/sail artisan cache:clear
```

### 5. Akses Aplikasi
Buka browser dan akses:
```
https://permathadi-swara.test
```

## Troubleshooting

### Jika Vite assets masih 404:
1. Pastikan Vite dev server running: `ps aux | grep vite`
2. Test akses langsung: `curl http://127.0.0.1:5173/@vite/client`
3. Cek nginx logs: `tail -f /Applications/ServBay/logs/nginx/permathadi-swara.test.error.log`

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

## Quick Start Script

Atau gunakan script ini untuk start semua sekaligus:

```bash
#!/bin/bash
# Start Laravel Sail
./vendor/bin/sail up -d

# Wait for containers
sleep 5

# Start Vite dev server
npm run dev > /dev/null 2>&1 &

# Clear cache
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan view:clear

echo "✅ Aplikasi siap diakses di: https://permathadi-swara.test"
```

