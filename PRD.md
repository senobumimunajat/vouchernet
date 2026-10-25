# PRODUCT REQUIREMENT DOCUMENT (PRD)
## SISTEM PENCATATAN & REKAPITULASI VOUCHER RT/RW NET (REDBOX INTEGRATED)

---

## 1. Ringkasan Eksekutif (Executive Summary)

### 1.1 Latar Belakang
Pada operasional jaringan RT/RW Net, pencetakan voucher hotspot umumnya menggunakan software eksternal seperti **Redbox** yang menghasilkan voucher fisik/PDF. Banyak pengelola RT/RW Net tidak memerlukan sistem yang terhubung langsung ke server RADIUS/MikroTik untuk validasi login, melainkan **sistem pencatatan transaksi, distribusi ke warung/tenant (reseller), rekapitulasi audit voucher, serta pembukuan keuangan (pemasukan & pengeluaran operasional)**.

Sering terjadi kebocoran atau selisih antara jumlah lembar voucher yang dicetak di Redbox dengan uang yang diterima dari reseller. Selain itu, pembagian keuntungan antara Owner RT/RW Net dan Reseller/Warung sering kali belum tercatat rapi, dan admin operasional rentan salah catat tanpa adanya mekanisme kontrol dari Super Admin (Owner).

### 1.2 Tujuan Produk
Membangun aplikasi web berbasis **Laravel + Tailwind CSS + SQLite** dalam lingkungan **Docker** yang berfungsi sebagai:
1. **Pencatatan Pesanan Voucher:** Tenant/reseller dapat memesan voucher dengan integrasi pesan WhatsApp Web instan ke Admin.
2. **Rekapitulasi Cetak Redbox & Arsip PDF:** Admin mengonfirmasi pembayaran (cash/transfer) dan mengunggah arsip PDF voucher Redbox yang diserahkan ke tenant.
3. **Mekanisme Koreksi Aman (Approval Hapus):** Admin tidak dapat menghapus transaksi secara sepihak, melainkan harus mengajukan permohonan hapus yang wajib disetujui oleh Super Admin.
4. **Pembukuan Keuangan Tertutup (Role-Based Privacy):** Super Admin memiliki kontrol penuh atas pemasukan, pengeluaran operasional, dan analisis laba (kotor vs bersih vs bagi hasil reseller), sementara Admin operasional dibatasi dari data keuangan.

---

## 2. Struktur Pengguna & Hak Akses (User Roles & Matrix)

Sistem memiliki 3 (tiga) peran pengguna:

| Modul / Kemampuan | Super Admin (Owner) | Admin (Operator) | Tenant (Reseller / Warung) |
| :--- | :---: | :---: | :---: |
| **Akses Dashboard** | Dashboard Keuangan & Eksekutif | Dashboard Operasional & Pesanan | Dashboard Katalog & Riwayat Order |
| **Kelola Paket Voucher** | Buat, Ubah, Hapus, Set Harga | Lihat & Aktifkan Paket | Hanya Lihat Katalog & Margin |
| **Pemesanan Voucher** | Monitoring Seluruh Order | Terima & Proses Order | Buat Order & Kirim Format WA |
| **Konfirmasi / Approve Bayar** | Override / Monitoring | **Konfirmasi Bayar** (Upload bukti / Cash) | Menunggu Verifikasi |
| **Upload PDF Voucher Redbox** | Akses Penuh Arsip | **Upload File PDF** per Order | Download/Akses PDF Voucher Miliknya |
| **Pengajuan Hapus Bayar** | **Review, Approve, atau Tolak** | **Mengajukan Hapus** (+ Alasan Wajib) | Tidak Ada Akses |
| **Menu Pemasukan (Cash In)** | Input & Monitoring Penuh | ❌ **Dilarang / Disembunyikan** | ❌ **Dilarang** |
| **Menu Pengeluaran (Cash Out)** | Input & Monitoring Biaya Ops | ❌ **Dilarang / Disembunyikan** | ❌ **Dilarang** |
| **Laporan & Analisis Laba** | Laporan Laba Kotor & Bersih | ❌ **Dilarang / Disembunyikan** | ❌ **Dilarang** |
| **Tambah & Kelola Tenant (Warung)**| **Bisa Tambah, Edit, Reset Password** | **Bisa Tambah & Edit Tenant** | Edit Profil Pribadi |
| **Tambah & Kelola Admin** | **Bisa Tambah, Edit, Reset Password** | ❌ **Tidak Bisa Tambah Admin** | ❌ **Tidak Ada Akses** |
| **Reset Password Pengguna Lain** | **Bisa Reset Password Semua User** | ❌ **Tidak Bisa Reset User Lain**| ❌ **Tidak Bisa** |
| **Ubah Password Sendiri** | **Bisa Ubah Password Sendiri** | **Bisa Ubah Password Sendiri** | **Bisa Ubah Password Sendiri** |
| **Pengaturan Nomor WA & Bank** | Atur Nomor WA & Data Rekening | Atur Nomor WA & Data Rekening | Hanya Melihat untuk Transfer |

### 2.1 Hierarki Manajemen Pengguna & Kata Sandi (Password Governance)
1. **Pembuatan Akun Warung / Tenant:**
   - **Admin** dan **Super Admin** dapat mendaftarkan akun Tenant baru (Nama Pemilik, Nama Warung, Nomor WhatsApp, Username, dan Password awal).
2. **Pembuatan Akun Admin:**
   - **Hanya Super Admin** yang berhak membuat dan mengelola akun Admin. Admin tidak dapat membuat sesama Admin.
3. **Mekanisme Reset Password:**
   - **Super Admin** memiliki otoritas tertinggi untuk me-reset password semua akun pengguna (Admin maupun Tenant) jika mereka lupa password.
4. **Ubah Password Mandiri:**
   - **Semua peran (Super Admin, Admin, dan Tenant)** memiliki menu *Profil / Ganti Password* untuk mengubah password akun mereka sendiri kapan saja dengan memvalidasi password lama.

---

## 3. Skema Perhitungan Harga & Keuangan

Setiap Paket Voucher memiliki 3 parameter harga:
1. **Harga Voucher Tertera (Harga End-User / Konsumen):**
   - Harga yang dicetak pada fisik voucher Redbox dan dijual oleh warung/reseller ke warga.
   - *Contoh:* **Rp 3.000 / voucher**.
2. **Harga Owner / RT RW Net (Harga Setoran Reseller):**
   - Harga yang wajib disetor oleh Tenant/Reseller kepada kas RT/RW Net.
   - *Contoh:* **Rp 2.500 / voucher**.
3. **Keuntungan Reseller (Margin Warung):**
   - Selisih keuntungan yang dinikmati oleh pemilik warung:
     $$\text{Margin Reseller} = \text{Harga Tertera} - \text{Harga Owner} = \text{Rp } 3.000 - \text{Rp } 2.500 = \text{Rp } 500$$

### Parameter Analisis Laba di Super Admin:
- **Omzet Nilai Voucher (Hasil Perputaran Kotor):**  
  $$\text{Omzet Kotor} = \sum (\text{Qty Terjual} \times \text{Harga Tertera})$$
- **Total Uang Masuk Kas RT RW Net (Penerimaan Bersih Penjualan):**  
  $$\text{Kas Masuk Penjualan} = \sum (\text{Qty Terjual} \times \text{Harga Owner})$$
- **Total Margin Bagi Hasil Reseller:**  
  $$\text{Total Margin Reseller} = \sum (\text{Qty Terjual} \times \text{Margin Reseller})$$
- **Laba Bersih Operasional RT RW Net:**  
  $$\text{Laba Bersih Kas} = \text{Kas Masuk Penjualan} + \text{Pemasukan Lain} - \text{Total Pengeluaran Operasional}$$

---

## 4. Alur Kerja Sistem (End-to-End User Journeys)

```
[ Tenant ]                           [ Admin ]                         [ Super Admin ]
    │                                    │                                    │
    ├─ 1. Pilih Paket & Qty              │                                    │
    ├─ 2. Klik "Kirim ke WA" ───────────►│ (Menerima Pesan WA Order)          │
    │                                    ├─ 3. Balas Chat (Nomor Rek/Cash)    │
    │◄─ 4. Bayar & Kirim Bukti di WA ────┤                                    │
    │                                    ├─ 5. Buka Web -> "Konfirmasi Bayar" │
    │                                    │    - Jika Transfer: Upload bukti   │
    │                                    │    - Jika Cash: Pilih Cash         │
    │                                    ├─ 6. Cetak Voucher di Redbox        │
    │                                    ├─ 7. Kirim PDF ke Tenant via WA     │
    │                                    ├─ 8. Upload File PDF ke Web         │
    │                                    │                                    │
    │ (Opsional: Jika ada salah input)   │                                    │
    │                                    ├─ 9. Klik "Ajukan Hapus Bayar" ────►│
    │                                    │     (+ Tulis Alasan Salah Catat)   ├─ 10. Review Pengajuan
    │                                    │                                    ├─ 11. Approve/Reject
    │                                    │                                    │
    │                                    │                                    ├─ 12. Rekap Kas & Laba
    │                                    │                                    │     (Pemasukan, Beban
    │                                    │                                    │      ISP, Listrik, dll)
```

### 4.1 Alur Pemesanan oleh Tenant
1. Tenant login dan diarahkan ke antarmuka pemesanan ramah seluler (*mobile-first*).
2. Tenant memilih salah satu paket voucher aktif.
3. Tenant memasukkan jumlah lembar (*quantity*).
4. Sistem menampilkan ringkasan:
   - Nama Paket
   - Harga Voucher (Harga eceran yang akan dijual ke warga)
   - Harga Setor (Total yang harus dibayar ke RT/RW Net)
   - Potensi Keuntungan Tenant
   - Pilihan Rekening Pembayaran (BCA, BRI, Dana, Cash, dll).
5. Tenant menekan tombol **"Kirim Pesanan ke WhatsApp"**:
   - Sistem membuka URL `https://wa.me/{nomor_admin}?text={pesan_terformat}`.
   - Pesanan tersimpan di sistem dengan status **`menunggu_pembayaran`**.

### 4.1.B Alur Pembelian Offline / Walk-in (Warung Datang Langsung ke Basecamp)
1. Pemilik warung/tenant datang langsung ke basecamp atau menghubungi via telepon/langsung tanpa memesan sendiri di web.
2. **Admin** membuka menu **"Tambah Pesanan (Input Offline)"**:
   - Memilih nama Tenant/Warung yang membeli (atau memilih/membuat akun tenant baru di tempat).
   - Memilih Paket Voucher & memasukkan jumlah lembar (quantity).
   - Memilih metode pembayaran langsung: **Cash (Tunai)** atau **Transfer**.
3. Admin langsung mengonfirmasi pembayaran (jika transfer upload bukti, jika cash langsung selesai).
4. Admin mencetak voucher di Redbox, menyerahkan fisik voucher langsung ke tenant (atau kirim file via WA jika diminta).
5. Admin mengunggah file PDF voucher ke pesanan tersebut sebagai arsip rekapitulasi.
6. Catatan keuangan dan rekap omzet otomatis tercatat sama persis seperti pemesanan online.

### 4.2 Alur Verifikasi & Pengunggahan oleh Admin
1. Admin menerima pesan WhatsApp dari tenant, lalu membalas secara manual untuk verifikasi.
2. Tenant mengirimkan bukti transfer via WhatsApp (atau janji temu cash).
3. Admin masuk ke menu **Pesanan Masuk** di aplikasi:
   - Klik aksi **"Konfirmasi Bayar"**.
   - Memilih metode:
     - **Transfer:** Mengunggah file gambar/screenshot bukti transfer yang dikirim tenant.
     - **Cash:** Menandai sebagai tunai (tanpa kewajiban upload bukti).
   - Status pesanan berubah menjadi **`lunas`**.
4. Admin mencetak lembaran voucher dari software **Redbox**.
5. Admin mengirimkan file PDF / lembaran voucher ke Tenant secara manual via WhatsApp.
6. Admin mengunggah file PDF cetak Redbox tersebut ke dalam rincian pesanan di aplikasi sebagai rekap & arsip valid.

### 4.3 Alur Pengajuan Hapus Bayar (Fraud & Error Prevention)
1. Jika terjadi kesalahan input (salah nominal, salah paket, atau pembatalan):
   - Admin **tidak diberikan tombol Hapus langsung**.
   - Admin menekan tombol **"Ajukan Hapus Bayar"**.
   - Muncul modal/formulir wajib yang meminta **Alasan Pengajuan Pembatalan**.
   - Status pesanan berubah menjadi **`menunggu_approval_hapus`**.
2. Super Admin menerima notifikasi pada dashboard-nya:
   - Super Admin meninjau data pesanan dan alasan yang ditulis Admin.
   - **Tindakan Super Admin:**
     - **Approve (Setujui):** Transaksi dibatalkan/dihapus, catatan kas dikoreksi.
     - **Reject (Tolak):** Transaksi dikembalikan ke status `lunas` semula.

### 4.4 Alur Keuangan & Analisis Laba (Khusus Super Admin)
1. **Pemasukan Otomatis:** Setiap transaksi berstatus `lunas` otomatis dihitung sebagai penerimaan kas dari voucher.
2. **Pemasukan Manual:** Super Admin dapat mencatat kas masuk di luar voucher (misal: iuran bulanan pelanggan rumahan, biaya pasang baru, sewa port).
3. **Pengeluaran Operasional:** Super Admin mencatat pos-pos beban kas:
   - Tagihan Bandwidth ISP Utama
   - Biaya Listrik & Token Tiang/AP
   - Pembelian Alat (Kabel FO, HTB, Patch Cord, Router, Tang Crimping)
   - Kas Operasional & Konsumsi
4. **Filter Analisis Laba:**
   - Filter berdasarkan rentang tanggal (Hari Ini, Bulan Ini, Kustom).
   - Filter per Tenant / Warung.
   - Filter per Paket Voucher.
   - Output metrik: Omzet Voucher, Penerimaan Owner, Bagi Hasil Reseller, Biaya Operasional, dan Laba Bersih.

---

## 5. Spesifikasi Database (Database Schema)

Aplikasi menggunakan **SQLite** (`database/database.sqlite`) dengan tabel-tabel terstruktur:

### 5.1 Tabel `users`
- `id` (INTEGER, Primary Key)
- `name` (VARCHAR)
- `username` (VARCHAR, Unique)
- `email` (VARCHAR, Nullable, Unique)
- `phone` (VARCHAR, Nullable) - Nomor WhatsApp
- `password` (VARCHAR)
- `role` (ENUM: `'super_admin'`, `'admin'`, `'tenant'`)
- `shop_name` (VARCHAR, Nullable) - Nama Warung/Toko untuk Tenant
- `address` (TEXT, Nullable)
- `is_active` (BOOLEAN, Default true)
- `timestamps`

### 5.2 Tabel `voucher_packages`
- `id` (INTEGER, Primary Key)
- `name` (VARCHAR) - Misal: "Paket 3 Jam", "Paket 24 Jam", "Paket 7 Hari"
- `duration_info` (VARCHAR) - Info durasi/kuota untuk display
- `printed_price` (DECIMAL 12,2) - Harga yang tertera di voucher (Rp 3.000)
- `owner_price` (DECIMAL 12,2) - Harga yang disetor ke RT RW Net (Rp 2.500)
- `reseller_profit` (DECIMAL 12,2) - Keuntungan warung (Rp 500, auto-calculated)
- `is_active` (BOOLEAN, Default true)
- `description` (TEXT, Nullable)
- `timestamps`

### 5.3 Tabel `orders`
- `id` (INTEGER, Primary Key)
- `order_number` (VARCHAR, Unique) - Format: `ORD-YYYYMMDD-XXXX`
- `tenant_id` (FOREIGN KEY -> `users.id`)
- `package_id` (FOREIGN KEY -> `voucher_packages.id`)
- `quantity` (INTEGER) - Jumlah lembar voucher
- `printed_price_each` (DECIMAL 12,2)
- `owner_price_each` (DECIMAL 12,2)
- `total_printed_amount` (DECIMAL 12,2) - `quantity * printed_price_each`
- `total_owner_amount` (DECIMAL 12,2) - `quantity * owner_price_each` (Nilai Tagihan)
- `total_reseller_profit` (DECIMAL 12,2) - `quantity * (printed - owner)`
- `payment_method` (ENUM: `'unspecified'`, `'transfer'`, `'cash'`)
- `payment_proof_file` (VARCHAR, Nullable) - Path upload bukti transfer
- `voucher_pdf_file` (VARCHAR, Nullable) - Path upload file PDF cetak Redbox
- `status` (ENUM: `'menunggu_pembayaran'`, `'lunas'`, `'menunggu_approval_hapus'`, `'dibatalkan'`)
- `confirmed_by` (FOREIGN KEY -> `users.id`, Nullable) - Admin yang mengonfirmasi
- `confirmed_at` (DATETIME, Nullable)
- `delete_requested_by` (FOREIGN KEY -> `users.id`, Nullable)
- `delete_reason` (TEXT, Nullable)
- `delete_requested_at` (DATETIME, Nullable)
- `delete_approved_by` (FOREIGN KEY -> `users.id`, Nullable)
- `delete_approved_at` (DATETIME, Nullable)
- `timestamps`

### 5.4 Tabel `expenses` (Pengeluaran Operasional)
- `id` (INTEGER, Primary Key)
- `expense_number` (VARCHAR, Unique) - Format: `EXP-YYYYMMDD-XXXX`
- `title` (VARCHAR) - Misal: "Tagihan Internet ISP Indihome/Biznet", "Beli Kabel FO 1 Roll"
- `category` (VARCHAR) - ISP Bandwidth, Listrik, Perangkat, Perawatan, dll
- `amount` (DECIMAL 12,2)
- `expense_date` (DATE)
- `notes` (TEXT, Nullable)
- `receipt_file` (VARCHAR, Nullable) - Nota/Struk
- `created_by` (FOREIGN KEY -> `users.id`)
- `timestamps`

### 5.5 Tabel `incomes` (Pemasukan Kas Manual Selain Voucher)
- `id` (INTEGER, Primary Key)
- `income_number` (VARCHAR, Unique) - Format: `INC-YYYYMMDD-XXXX`
- `title` (VARCHAR) - Misal: "Iuran Bulanan Rumah Bpk. Joko", "Biaya Pasang Baru"
- `category` (VARCHAR) - Iuran Bulanan, Pasang Baru, Lain-lain
- `amount` (DECIMAL 12,2)
- `income_date` (DATE)
- `notes` (TEXT, Nullable)
- `created_by` (FOREIGN KEY -> `users.id`)
- `timestamps`

### 5.6 Tabel `settings`
- `key` (VARCHAR, Primary Key)
- `value` (TEXT, Nullable)
- Setting mencakup:
  - `admin_whatsapp_number`: Nomor tujuan kirim pesanan (contoh: `6281234567890`)
  - `bank_accounts`: Informasi daftar rekening transfer (BCA, Mandiri, Dana, dll)
  - `app_name`: Nama RT/RW Net (contoh: "VoucherNet RT 04")
  - `app_tagline`: Deskripsi / alamat basecamp

---

## 6. Rancangan Antarmuka & UX (UI/UX Design)

### 6.1 Filosofi Desain (Anti AI-Slop & Human-Centered)
- **Hindari Elemen "AI-Slop":**
  - Tidak menggunakan efek gradien neon menyilaukan (pink/ungu futuristik yang tidak natural).
  - Tidak menggunakan background gelap berbintik/bintang luar angkasa yang melelahkan mata.
  - Tidak ada animasi mengambang (floating) berlebihan yang memperlambat interaksi.
  - Tidak menggunakan teks abu-abu pudar berukuran kecil yang sulit dibaca di layar HP di bawah sinar matahari.
- **Prinsip Utama:**
  - **Tenang & Teduh di Mata:** Menggunakan perpaduan warna Slate/Netral yang lembut dan tidak melelahkan untuk pemakaian harian.
  - **Legibilitas Maksimal (High Contrast):** Kontras teks hitam/slate gelap di atas kanvas putih/terang sehingga sangat mudah dibaca oleh pemilik warung maupun admin.
  - **Tipografi Fungsional:** Angka uang dan nominal rupiah menggunakan font monospaced (`font-mono`) terstruktur dengan pemisah ribuan yang jelas (`Rp 150.000`).

### 6.2 Palet Warna (Calm & Practical Palette)
- **Kanvas Latar (Background):** Slate-50 (`#f8fafc`) — adem, bersih, tidak menyilaukan.
- **Permukaan Kartu (Cards/Surface):** Putih Murni (`#ffffff`) dengan garis tepi halus Slate-200 (`#e2e8f0`) dan bayangan lembut (`shadow-sm`).
- **Warna Teks Utama:** Slate-900 (`#0f172a`) untuk judul & nominal, Slate-600 (`#475569`) untuk label pendukung.
- **Aksen Aksi Utama (Primary Action):** Slate-800 / Deep Navy (`#1e293b`) memberikan kesan kokoh, profesional, dan tenang.
- **Aksen WhatsApp:** Hijau WhatsApp asli (`#16a34a`) yang familiar dan nyaman dilihat.
- **Sistem Status Badge (Soft Semantic):**
  - *Lunas / Disetujui:* Latar Emerald-50 (`#ecfdf5`), Teks Emerald-700 (`#047857`), Border Emerald-200.
  - *Menunggu Pembayaran:* Latar Amber-50 (`#fffbeb`), Teks Amber-700 (`#b45309`), Border Amber-200.
  - *Pengajuan Hapus:* Latar Rose-50 (`#fff1f2`), Teks Rose-700 (`#be123c`), Border Rose-200.
  - *Dibatalkan / Void:* Latar Slate-100 (`#f1f5f9`), Teks Slate-600 (`#475569`), Border Slate-300.

### 6.3 Rancangan Perangkat & Tata Letak
1. **Mode Tenant (Mobile First):**
   - Kartu katalog paket voucher yang bersih, tombol kuantiti besar dan nyaman ditekan jempol (*thumb-friendly*).
   - Rincian estimasi biaya & keuntungan yang transparan sebelum checkout.
   - Tombol hijau menonjol: **"Kirim Pesanan ke WhatsApp Admin"** yang langsung memicu WhatsApp.
   - Tab "Riwayat Pesanan Saya" yang memungkinkan tenant mendownload PDF voucher setelah di-approve admin.
2. **Mode Admin:**
   - Tabel pesanan real-time dengan status badge yang jelas.
   - Modal Konfirmasi Bayar yang cepat (opsi Cash atau upload gambar transfer).
   - Form Upload PDF Voucher Redbox yang praktis.
   - Modal Pengajuan Hapus Bayar dengan konfirmasi pencegahan ketidaksengajaan.
3. **Mode Super Admin:**
   - Kartu KPI Eksekutif: Total Omzet, Kas Masuk, Bagi Hasil Warung, Kas Keluar, Laba Bersih.
   - Filter interaktif (Pilihan Bulan & Tahun, Pilihan Tenant, Pilihan Paket).
   - Menu Approval Hapus dengan riwayat alasan yang transparan.
   - Halaman Buku Kas (Pemasukan & Pengeluaran) beserta tombol export/cetak laporan.

---

## 7. Arsitektur Docker & Deployment

Aplikasi dibungkus dalam kontainer Docker terisolasi:

```yaml
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: voucher_net_app
    restart: unless-stopped
    ports:
      - "8000:8000"
    volumes:
      - ./:/var/www/html
      - ./storage/app:/var/www/html/storage/app
    environment:
      - APP_NAME="Voucher RT RW Net"
      - APP_ENV=production
      - APP_DEBUG=false
      - DB_CONNECTION=sqlite
      - DB_DATABASE=/var/www/html/database/database.sqlite
```

### Karakteristik Docker:
- **Base Image:** PHP 8.2 / 8.3 Alpine (ringan, footprint < 100MB).
- **Extension:** `pdo_sqlite`, `sqlite3`, `mbstring`, `fileinfo`, `gd`, `zip`, `opcache`.
- **Persistent Data:** File database SQLite dan folder uploads (`storage/app/public`) dimount langsung ke host machine agar data tetap aman saat container di-restart.

---

## 8. Rencana Verifikasi & Uji Coba (Acceptance Criteria)

1. **Uji Role & Hak Akses:**
   - Pastikan URL `/finances`, `/expenses`, `/reports` menghasilkan HTTP 403 Forbidden ketika diakses oleh Admin atau Tenant.
2. **Uji Order Tenant & WhatsApp Link:**
   - Buat pesanan baru dari Tenant, klik tombol WA, pastikan URL link WA memuat nomor admin yang benar serta teks format pesanan yang rapi.
3. **Uji Konfirmasi Bayar & Upload PDF:**
   - Admin konfirmasi bayar dengan opsi Transfer (upload bukti) dan Cash.
   - Admin upload file PDF cetakan Redbox ke pesanan tersebut.
   - Tenant dapat mengunduh file PDF voucher tersebut dari dashboard miliknya.
4. **Uji Alur Pengajuan Hapus:**
   - Admin mengajukan hapus dengan alasan.
   - Super Admin meninjau data -> Klik Approve -> Transaksi status `dibatalkan` dan uang tidak dihitung dalam kas.
5. **Uji Laporan Laba Kotor vs Bersih:**
   - Pastikan perhitungan:
     - Harga Tertera (Rp 3.000) × Qty terhitung sebagai Nilai Omzet Voucher.
     - Harga Owner (Rp 2.500) × Qty terhitung sebagai Kas Masuk Penjualan.
     - Selisih (Rp 500) × Qty terhitung sebagai Bagi Hasil Reseller.
     - Nilai Laba Bersih = Kas Masuk - Pengeluaran Kas.
