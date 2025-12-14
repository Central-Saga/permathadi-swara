# 🧹 Clear Failed Jobs - Quick Guide

Setelah fix Media Library, ikuti langkah ini untuk clear failed jobs:

## 📋 Langkah-langkah

### 1. Clear Failed Jobs

```bash
./vendor/bin/sail artisan queue:flush
```

Atau jika ingin retry dulu:

```bash
# Retry semua failed jobs
./vendor/bin/sail artisan queue:retry all

# Atau retry specific job ID
./vendor/bin/sail artisan queue:retry {job-id}
```

### 2. Clear Config Cache

```bash
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
```

### 3. Restart Queue Worker

Stop queue worker yang sedang berjalan (Ctrl+C), lalu jalankan lagi:

```bash
./vendor/bin/sail artisan queue:work
```

### 4. Verifikasi

Setelah restart, queue worker seharusnya tidak lagi menampilkan error `FAIL` untuk `PerformConversionsJob`.

## ✅ Checklist

-   [ ] AVIF conversion sudah di-disable di `Layanan.php` dan `Galeri.php`
-   [ ] Image optimizers sudah di-disable di `config/media-library.php`
-   [ ] Config cache sudah di-clear
-   [ ] Failed jobs sudah di-flush
-   [ ] Queue worker sudah di-restart
-   [ ] Tidak ada error `FAIL` lagi di queue worker

## 🎯 Test

Upload gambar baru di admin panel dan pastikan:

1. Upload berhasil
2. Queue worker memproses tanpa error
3. Gambar terkonversi dengan benar (thumb, webp, responsive)

---

**Catatan:** Untuk production, install tools yang diperlukan dan enable kembali AVIF dan optimizers. Lihat `FIX_MEDIA_LIBRARY_QUEUE.md` untuk detail.
