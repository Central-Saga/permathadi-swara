# Setup Caddy untuk Permathadi Swara

## Install Caddy

### macOS (Homebrew)
```bash
brew install caddy
```

### Atau download langsung
```bash
# Download dari https://caddyserver.com/download
# Atau untuk macOS:
brew install caddy
```

## Setup

1. **Stop Nginx (jika running)**
   ```bash
   sudo nginx -s stop
   # Atau melalui ServBay interface
   ```

2. **Start Caddy dengan Caddyfile**
   ```bash
   # Dari directory project
   sudo caddy run --config Caddyfile
   
   # Atau untuk background:
   sudo caddy start --config Caddyfile
   ```

3. **Cek status**
   ```bash
   sudo caddy status
   ```

4. **Reload config (jika sudah running)**
   ```bash
   sudo caddy reload --config Caddyfile
   ```

## Keuntungan Caddy

- ✅ Auto SSL (menggunakan `tls internal` untuk local development)
- ✅ Konfigurasi lebih sederhana
- ✅ Built-in reverse proxy yang powerful
- ✅ WebSocket support otomatis
- ✅ Tidak perlu setup SSL certificate manual

## Troubleshooting

### Cek log
```bash
tail -f /tmp/caddy-permathadi-swara.log
```

### Stop Caddy
```bash
sudo caddy stop
```

### Test config
```bash
sudo caddy validate --config Caddyfile
```

## Catatan

- Caddy akan auto-generate SSL certificate untuk `permathadi-swara.test`
- Pastikan Vite dev server running: `npm run dev`
- Pastikan Laravel Sail running: `./vendor/bin/sail up -d`

