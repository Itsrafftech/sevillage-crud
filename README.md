# Sevillage Puncak - Aplikasi Reservasi & Penjualan Tiket (Fase 4)

Prototype/MVP untuk tugas kuliah KOM220F Basis Data - Proyek Fase 4 (aplikasi
CRUD). Dibangun dengan **Laravel 12 (PHP 8.2+) + PostgreSQL**, memakai database
`sevillage` dari Fase 3 apa adanya (tidak ada migration/ALTER, hanya `DB::table`
Query Builder karena ada FK komposit dan primary key berupa teks seperti `R-001`).

## Proses Bisnis

1. **Reservasi Penginapan** - pemesanan unit penginapan (glamping/tenda), mulai
   dari pemesanan publik, penugasan unit otomatis, hingga pembayaran dan check-in.
2. **Penjualan Tiket & Paket Wisata** - pembelian tiket masuk dan paket wisata
   (pelajar/gathering/fun games/edukasi anak), mulai dari transaksi hingga
   penerbitan tiket per pengunjung.

## Fitur CRUD

| Modul | Lihat | Tambah | Ubah | Hapus |
|---|---|---|---|---|
| Reservasi Penginapan (`/internal/reservasi`) | fo, fin, mgr | fo | fo | fo |
| Transaksi Tiket & Paket (`/internal/transaksi`) | tix, fin, mgr | tix | tix | tix |

Ditambah alur publik tanpa login: beranda (katalog + tarif), pesan penginapan
(unit dipilih otomatis oleh sistem), beli tiket/paket, serta cek & batalkan
reservasi dengan kode + nomor telepon.

Dashboard tersedia untuk setiap peran (fo, tix, fin, mgr) dengan statistik yang
dihitung langsung dari database, bukan angka tetap.

## Akun Demo

Semua password demo: **`demo1234`**. Bisa juga diisi otomatis lewat tombol di
halaman login.

| ID Karyawan | Nama | Departemen | Peran |
|---|---|---|---|
| K-008 | Hadi Nurdin | Front Office | `fo` |
| K-007 | Ezha Vitriyani | Ticketing Admin | `tix` |
| K-009 | Riffan Arriandi | Finance | `fin` |
| K-002 | Eli Hernawati | Direksi | `mgr` (read-only) |

## Cara Menjalankan

Prasyarat: PHP 8.2+ dengan ekstensi `pdo_pgsql` aktif, Composer, dan PostgreSQL
(ekstensi `pgsql`/`pdo_pgsql` di `php.ini` harus di-uncomment bila belum aktif).

```powershell
# 1. Buat database dan impor skema Fase 3 (sekali saja / bila database belum ada)
createdb -U postgres sevillage
psql -U postgres -d sevillage -f db/01_struktur_dan_data.sql

# 2. Siapkan environment
copy .env.example .env
# lalu sesuaikan DB_DATABASE=sevillage, DB_USERNAME, DB_PASSWORD di .env

# 3. Install dependency PHP (tanpa npm/Vite - aplikasi ini memakai CSS biasa)
composer install

# 4. Key aplikasi
php artisan key:generate

# 5. Jalankan
php artisan serve
```

Lalu buka `http://127.0.0.1:8000`.

**Jangan jalankan `php artisan migrate`** - skema database sepenuhnya berasal
dari `db/01_struktur_dan_data.sql` dan tidak boleh diubah.

## Keterbatasan & Asumsi

- **Autentikasi disederhanakan.** Skema Fase 3 tidak memuat tabel/kolom
  kredensial untuk karyawan, sehingga akun login (ID + password hash) disimpan
  di `config/akun.php`, sementara nama dan departemen tetap diambil dari tabel
  `karyawan`. Pada sistem produksi sesungguhnya, ini akan ditambah tabel akun
  tersendiri dengan relasi ke `karyawan`.
- **Pembeli tiket/paket baru via web** (yang belum pernah tercatat dengan nomor
  telepon tersebut) dibuat sebagai pelanggan `is_ots = true`, karena skema
  tidak memiliki peran pelanggan khusus untuk pembelian online.
- Petugas untuk transaksi/reservasi yang dibuat lewat halaman publik
  menggunakan ID karyawan tetap (`K-008` untuk reservasi, `K-007` untuk
  tiket/paket) sesuai ketentuan tugas.
- Form internal "Transaksi Baru" hanya mendukung satu item tiket/paket per
  transaksi (sesuai alur yang diminta); untuk mengubah item, transaksi perlu
  dihapus dan dibuat ulang.
