# VoucherNet - Sistem Pencatatan & Rekapitulasi Voucher RT/RW Net (Redbox)

Aplikasi web modern berbasis **Laravel + Tailwind CSS + SQLite** dalam lingkungan **Docker** untuk pencatatan distribusi voucher hotspot RT/RW Net yang dicetak melalui Redbox, pembukuan kas pemasukan & pengeluaran operasional, serta rekonsiliasi bagi hasil reseller.

---

## 🚀 Cara Menjalankan dengan Docker

Pastikan Docker Desktop sudah terpasang dan berjalan di laptop/komputer Anda.

### 1. Jalankan Container
Cukup jalankan satu perintah berikut di terminal direktori proyek:
```bash
docker compose up -d --build
```

Container Docker akan secara otomatis:
- Menyiapkan environment PHP 8.3 dengan ekstensi SQLite & GD
- Menginstal dependensi Composer
- Menyiapkan database SQLite (`database/database.sqlite`)
- Menjalankan migrasi database & seeder data default
- Membuat tautan storage publik (`storage:link`)
- Menjalankan server web pada port `8000`

### 2. Buka Aplikasi di Browser
Buka browser dan akses:
👉 **`http://localhost:8000`**

### 3. Menghentikan Aplikasi
```bash
docker compose down
```

---

## 🔑 Akun Default untuk Login (Demo Ready)

Di halaman login telah tersedia tombol jalan pintas (*1-click demo*) atau Anda dapat mengetik manual:

| Peran (Role) | Username | Password | Deskripsi Tugas |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `superadmin` | `password` | Owner RT/RW Net. Kontrol penuh, kelola paket voucher, persetujuan hapus bayar, buku kas (omzet kotor vs laba bersih), reset password. |
| **Admin** | `admin` | `password` | Operator lapangan. Input pesanan masuk & offline, konfirmasi bayar (cash/transfer), upload PDF Redbox, ajukan hapus bayar. *(Menu keuangan disembunyikan)*. |
| **Tenant 1** | `warung_siti` | `password` | Warung Bu Siti. Katalog tiket voucher, stepper quantity, tombol instan kirim pesanan ke WhatsApp Admin, download PDF voucher. |
| **Tenant 2** | `kios_budi` | `password` | Kios Pulsa Barokah Budi. Akun reseller kedua untuk simulasi multi-warung. |

---

## 📋 Alur Operasional Sistem di Lapangan

```
1. PEMESANAN (TENANT / RESELLER)
   - Tenant membuka web di HP (tampilan mobile-first ala tiket voucher).
   - Memilih paket (cth: Paket 3 Jam - Rp 3.000, setor Rp 2.500, untung Rp 500).
   - Menentukan jumlah lembar (cth: 50 lembar).
   - Menekan tombol "Kirim Pesanan ke WhatsApp Admin" (membuka WhatsApp Web / App).

2. VERIFIKASI & BAYAR (ADMIN)
   - Admin membalas pesan WhatsApp & konfirmasi pembayaran.
   - Tenant membayar via Transfer (kirim bukti transfer) atau Cash di tempat.
   - Admin membuka aplikasi -> Menu Pesanan Masuk:
     - Klik "Konfirmasi Bayar" -> Pilih Cash atau Transfer (+ upload foto bukti transfer).
   - Admin mencetak voucher di software Redbox.
   - Admin mengirimkan file PDF / lembaran fisik ke Tenant.
   - Admin meng-upload file PDF hasil cetak Redbox ke pesanan tersebut sebagai arsip rekap.

3. KOREKSI & PENCEGAHAN SALAH CATAT
   - Jika ada salah input atau salah approve bayar:
     - Admin TIDAK BISA langsung menghapus.
     - Admin menekan "Ajukan Hapus Bayar" + mengisi alasan kesalahan.
     - Super Admin meninjau data -> Memilih "Setujui Pembatalan" atau "Tolak".

4. KEUANGAN & LABA KOTOR VS BERSIH (SUPER ADMIN)
   - Super Admin memantau Buku Kas:
     * Omzet Nilai Voucher: Qty x Harga di Voucher (Rp 3.000)
     * Kas Masuk Penjualan: Qty x Harga Setor Owner (Rp 2.500)
     * Bagi Hasil Reseller: Qty x Untung Reseller (Rp 500)
     * Beban Operasional: Tagihan ISP, Listrik tiang, kabel FO, alat.
     * Laba Bersih Kas: Kas Masuk - Pengeluaran Operasional.
```

---

## ⚙️ Fitur-Fitur Kunci

1. **Anti AI-Slop & Desain Teduh**: Menggunakan palet Slate-50 yang adem di mata, kontras tinggi, tipografi Inter tajam, angka keuangan berformat monospace (`font-mono`), dan bebas dari gradien neon menyilaukan.
2. **Mobile-First App Shell**: Memiliki bottom navigation bar ala aplikasi native iOS/Android untuk warung/tenant.
3. **Pemesanan Offline / Walk-in**: Menu khusus bagi Admin untuk mencatat warung yang datang membeli fisik secara langsung ke basecamp.
4. **Hierarki Password & Hak Akses**:
   - Super Admin dapat me-reset password semua akun jika mereka lupa kata sandi.
   - Semua pengguna dapat mengganti kata sandi akun mereka sendiri di menu profil.
5. **Persistent Data**: File database SQLite (`database/database.sqlite`) dan berkas unggahan bukti/PDF (`storage/`) tersimpan secara persisten di folder lokal host.
