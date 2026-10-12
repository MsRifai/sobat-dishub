# 🚦 Sobat Dishub — Sistem Presensi Magang

Aplikasi web presensi mandiri dan manajemen kehadiran peserta magang berbasis **Geofencing GPS** untuk **Dinas Perhubungan**. Dibangun menggunakan **Laravel 12**, dirancang dengan pendekatan *mobile-first*, serta dilengkapi panel administrasi dan ekspor laporan kehadiran.

---

## 🌟 Fitur Utama

### 📱 Sisi Peserta Magang
- **Presensi Berbasis Geofencing (GPS):** Check-in dan Check-out hanya dapat dilakukan jika berada di dalam radius kantor yang ditentukan (didukung Leaflet.js & GPS Geolocation).
- **Deteksi Keterlambatan Otomatis:** Perhitungan otomatis status kedatangan (*Tepat Waktu* atau *Terlambat* beserta jumlah menit keterlambatan).
- **Penyesuaian Jam Operasional:** Mendukung perbedaan batas jam pulang untuk hari biasa dan hari Jumat.
- **Pengajuan Izin / Sakit / Cuti:** Pengajuan izin digital disertai unggah bukti/surat keterangan dan pemantauan status persetujuan (*Pending*, *Approved*, *Rejected*).
- **Manajemen Profil & Akun:** Pengaturan informasi profil mandiri dan ubah kata sandi.

### 🛡️ Sisi Admin (Pengelola Magang)
- **Dashboard Ringkasan Kehadiran:** Statistik harian kehadiran, keterlambatan, dan pengajuan izin yang membutuhkan persetujuan.
- **Monitoring & Log Presensi:** Tabel kehadiran lengkap dengan filter tanggal, pencarian nama, dan detail koordinat presensi.
- **Ekspor Laporan (PDF & CSV):** 
  - Rekap absensi seluruh peserta ke format CSV dan PDF.
  - Laporan individual per mahasiswa/peserta magang ke format PDF resmi.
- **Kelola Pengajuan Izin/Sakit:** Peninjauan berkas bukti serta aksi *Approve* atau *Reject*.
- **Kelola Titik Kantor (Geofence):** Pengaturan koordinat latitude, longitude, nama kantor, dan radius jangkauan presensi (meter).
- **Manajemen Akun Peserta:** Tambah peserta, edit data kampus/instansi, reset password peserta, dan pengalihan status aktif/nonaktif akun.

---

## 🛠️ Tech Stack & Dependensi

- **Backend:** [PHP 8.2+](https://php.net) & [Laravel 12](https://laravel.com)
- **Frontend:** Blade Templating, [Tailwind CSS](https://tailwindcss.com), [Vite](https://vitejs.dev)
- **Peta & Geolocation:** Leaflet.js & Browser Geolocation API
- **Ekspor Dokumen:** [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf)
- **Database:** PostgreSQL (Supabase) / MySQL / SQLite
- **Deployment:** [Vercel](https://vercel.com) (PHP Serverless Runtime)

---

## 🚀 Panduan Instalasi & Menjalankan di Lokal

### 1. Prasyarat Sistem
- PHP >= 8.2 (ekstensi `pdo`, `mbstring`, `openssl`, `gd`, `fileinfo` aktif)
- Composer
- Node.js & NPM
- Database (PostgreSQL / MySQL)

### 2. Kloning Repositori
```bash
git clone https://github.com/MsRifai/sobat-dishub.git
cd sobat-dishub
```

### 3. Pasang Dependensi
```bash
composer install
npm install
```

### 4. Konfigurasi Lingkungan (.env)
Salin berkas `.env.example` ke `.env`:
```bash
cp .env.example .env
```
Generate App Key:
```bash
php artisan key:generate
```
Sesuaikan konfigurasi database pada berkas `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sobat_dishub
DB_USERNAME=postgres
DB_PASSWORD=secret
```

### 5. Jalankan Migrasi dan Seeder
Jalankan migrasi beserta data sampel awal (kantor, akun admin, dan akun magang):
```bash
php artisan migrate --seed
```

### 6. Hubungkan Storage Link
```bash
php artisan storage:link
```

### 7. Jalankan Server Pengembangan
Jalankan backend Laravel dan Vite frontend secara bersamaan:
```bash
# Terminal 1 - Backend Server
php artisan serve

# Terminal 2 - Frontend Assets
npm run dev
```
Aplikasi dapat diakses melalui browser di: `http://127.0.0.1:8000`

---

## 🔑 Akun Default (Hasil Seeder)

Untuk keperluan pengujian lokal, gunakan kredensial bawaan berikut:

| Peran | Username | Email | Password Default | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| **Admin** | `admin` | `admin@dishub.go.id` | `password123` | Akses penuh dashboard admin |
| **Magang 1** | `magang01` | `budi@student.ac.id` | `password123` | Sudah presensi (Tepat Waktu) |
| **Magang 2** | `magang02` | `siti@student.ac.id` | `password123` | Sudah presensi (Terlambat) |
| **Magang 3** | `magang03` | `ahmad@student.ac.id` | `password123` | Belum presensi (Siap uji check-in) |

> **Catatan Uji Lokasi Presensi:** Koordinat default kantor disetel pada **Kantor Induk Dishub DIY** (`-7.795600, 110.369500`) dengan radius 100 meter. Anda dapat mengubah titik lokasi kantor via menu **Admin > Lokasi Kantor** atau menggunakan fake GPS / browser sensor override saat menguji fitur absensi.

---

## 📂 Struktur Proyek Singkat

```text
├── app/
│   ├── Http/Controllers/
│   │   ├── AdminController.php         # Kelola dashboard, rekap, export PDF/CSV, kantor, & user
│   │   ├── AttendanceController.php    # Logika presensi check-in/out & validasi geofence
│   │   ├── AuthController.php          # Otentikasi login, register, reset password
│   │   ├── LeaveRequestController.php  # Pengajuan & persetujuan izin/sakit/cuti
│   │   └── ProfileController.php       # Profil pengguna
│   ├── Models/                         # User, Attendance, OfficeLocation, LeaveRequest
│   └── Services/GeoService.php         # Perhitungan jarak koordinat (Haversine Formula)
├── database/
│   ├── migrations/                     # Skema tabel database
│   └── seeders/DatabaseSeeder.php      # Data dummy & user pengujian
├── resources/views/                    # Tampilan UI Blade (Tailwind CSS)
├── routes/web.php                      # Rute web aplikasi
├── vercel.json                         # Konfigurasi deployment serverless Vercel
└── README.md                           # Dokumentasi proyek
```

---

## 📄 Lisensi
Proyek ini dikembangkan untuk kebutuhan internal pengelolaan magang Dinas Perhubungan. Hak cipta dilindungi undang-undang.

