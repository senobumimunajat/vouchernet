# Panduan Deploy VoucherNet ke VPS Pribadi

Aplikasi **VoucherNet** sangat cocok dan mudah dijalankan di VPS pribadi Anda. Anda bisa memilih cara yang paling sesuai dengan konfigurasi VPS Anda.

---

## ⚡ CARA 1: Menggunakan Docker (Paling Direkomendasikan & Cepat)

Jika VPS Anda sudah terpasang Docker & Docker Compose, prosesnya hanya butuh **3 langkah**:

### Langkah 1: Masuk ke VPS & Clone Repository
Buka terminal dan masuk ke VPS Anda via SSH:
```bash
ssh root@IP_VPS_ANDA
```
Lalu unduh kode proyek dari GitHub:
```bash
git clone https://github.com/senobumimunajat/vouchernet.git
cd vouchernet
```

### Langkah 2: Siapkan File `.env`
Salin template konfigurasi:
```bash
cp .env.example .env
```
*(Opsional: edit `.env` menggunakan `nano .env` jika ingin mengubah `APP_URL` dengan domain/IP Anda).*

### Langkah 3: Jalankan Aplikasi
Jalankan satu perintah ini:
```bash
docker compose up -d --build
```
> **Selesai!** Aplikasi VoucherNet langsung aktif dan berjalan di background pada port **8000** (`http://IP_VPS_ANDA:8000`).

---

## 🌐 Menghubungkan Domain & SSL (HTTPS Gembok Hijau)

Agar bisa diakses via domain tanpa port `:8000` dan memiliki SSL HTTPS:

### Menggunakan Nginx Reverse Proxy (Biasa)
Jika di VPS sudah terpasang Nginx, buat konfigurasi:
```bash
sudo nano /etc/nginx/sites-available/vouchernet
```
Isi konfigurasi berikut:
```nginx
server {
    listen 80;
    server_name namadomainanda.com;

    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```
Aktifkan dan pasang SSL gratis dengan Certbot:
```bash
sudo ln -s /etc/nginx/sites-available/vouchernet /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo certbot --nginx -d namadomainanda.com
```

### Atau Menggunakan Nginx Proxy Manager / Caddy / Cloudflare Tunnel
- Cukup arahkan domain ke `http://127.0.0.1:8000` dengan SSL otomatis aktif.

---

## 🔄 Cara Melakukan Update Fitur di Masa Depan
Jika ada perbaikan atau penambahan fitur dari laptop:
1. Kita push kode terbaru ke GitHub dari laptop (`git push`).
2. Masuk ke VPS Anda via SSH, masuk ke folder proyek, dan jalankan:
   ```bash
   cd vouchernet
   git pull
   docker compose restart app
   ```
> Database transaksi dan data keuangan Anda **tetap 100% aman** karena volume SQLite tersimpan di host VPS dan diabaikan oleh Git.

---

## 💾 Pencadangan (Backup) Database di VPS
1. **Via Web:** Login sebagai Super Admin ➔ menu **Nomor WA & Bank** ➔ klik tombol hijau **Unduh Cadangan Database Sekarang (.sqlite)**.
2. **Via Server:** File database berada di `vouchernet/database/database.sqlite`. Anda bisa menyalinnya kapan saja atau mengatur cron job otomatis.
