# Aplikasi Doorprize (Laravel)

Aplikasi undian doorprize dengan layar untuk proyektor dan dashboard panitia:
pengaturan latar, input peserta (nama + NIP) lengkap dengan impor/ekspor CSV & Excel,
input hadiah beserta jumlah pemenang, daftar pemenang, dan pemilihan hadiah yang akan diundi.

## Cara memasang

Butuh PHP 8.2+, Composer, dan MySQL/MariaDB (atau SQLite untuk uji coba).

```bash
composer create-project laravel/laravel doorprize
cd doorprize
```

Salin isi folder ini ke dalam proyek Laravel tadi (timpa `routes/web.php`):

```
app/Http/Controllers/...      app/Models/...        app/Services/...
app/Http/Middleware/AdminAuth.php
config/doorprize.php          database/migrations/...
database/seeders/DoorprizeSeeder.php
resources/views/...           routes/web.php
```

Daftarkan middleware di `bootstrap/app.php` (Laravel 11/12):

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias(['admin' => \App\Http\Middleware\AdminAuth::class]);
})
```

Untuk Laravel 10 ke bawah, tambahkan di `app/Http/Kernel.php`:

```php
protected $middlewareAliases = [
    // ...
    'admin' => \App\Http\Middleware\AdminAuth::class,
];
```

Buat database kosong lebih dulu:

```sql
CREATE DATABASE doorprizehadiah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Isi `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=doorprizehadiah
DB_USERNAME=root
DB_PASSWORD=

DOORPRIZE_USER=panitia
DOORPRIZE_PASSWORD=gantiSandiIni
```

Lalu jalankan:

```bash
php artisan migrate
php artisan storage:link          # wajib, supaya foto hadiah & latar tampil
php artisan db:seed --class=DoorprizeSeeder   # opsional: contoh data
php artisan serve
```

Untuk impor file `.xlsx`/`.xls` (bukan CSV), tambahkan satu paket:

```bash
composer require phpoffice/phpspreadsheet
```

Tanpa paket ini aplikasi tetap jalan penuh dengan format CSV, dan file Excel
cukup disimpan ulang sebagai CSV sebelum diunggah.

## Alamat penting

| Halaman | URL |
|---|---|
| Layar undian (proyektor) | `/` |
| Layar daftar pemenang | `/pemenang` |
| Masuk panitia | `/masuk` |
| Ringkasan | `/panel` |
| Peserta | `/panel/peserta` |
| Grup pengecualian | `/panel/grup` |
| Hadiah | `/panel/hadiah` |
| Pemenang | `/panel/pemenang` |
| Tampilan layar | `/panel/tampilan` |

## Alur pemakaian saat acara

1. **Tampilan layar** — unggah gambar latar, logo kiri/kanan, judul acara, lalu pilih
   aturan undian: tampilan papan dan apakah putaran berhenti otomatis.
2. **Peserta** — impor CSV/Excel berisi kolom `nip`, `nama`, `unit`. Unduh template
   dulu kalau ragu formatnya. NIP jadi kunci unik, jadi impor ulang tidak menggandakan data.
3. **Hadiah** — isi nama hadiah, foto, **jumlah pemenang**, dan **pemenang per putaran**.
4. **Grup pengecualian** (opsional) — buat grup seperti "Panitia" atau "Direksi", isi
   anggotanya lewat pencarian langsung, satu unit kerja sekaligus, atau dengan
   menempel daftar NIP dari Excel, lalu centang hadiah mana
   yang mengecualikan grup itu. Anggota grup tidak muncul di layar undian untuk hadiah
   tersebut, tetapi tetap ikut undian hadiah lainnya. Penyaringan ini tidak ditulis
   di layar undian; penandanya hanya muncul di halaman Hadiah untuk panitia.
5. **Ringkasan atau Hadiah** — tekan "Undi ini" pada hadiah yang mau diundi sekarang.
6. Buka `/` di proyektor, tekan **Start** lalu **Stop**.
   Spasi juga berfungsi sebagai Start/Stop supaya pembawa acara tidak perlu mencari kursor.
   Menu hamburger di pojok kiri bawah dipakai untuk berpindah undian ke hadiah berikutnya
   tanpa meninggalkan layar, sekaligus mengatur layar penuh.
7. **Layar pemenang** (`/pemenang`) menampilkan foto hadiah di kiri dan daftar nama
   pemenang di kanan. Cocok dipasang di layar kedua: isinya menyegarkan diri tiap
   20 detik sehingga nama baru muncul sendiri sementara undian berjalan di layar utama.
   Hadiah yang ditampilkan bisa diganti lewat menu di pojok kiri bawah.
8. **Pemenang** — tandai hadiah yang sudah diambil, batalkan pemenang yang tidak hadir
   (namanya otomatis kembali ke undian), atau ekspor seluruh daftar ke CSV.

## Catatan teknis

- Pengundian dilakukan di server memakai `random_int()`, bukan di browser, sehingga
  hasilnya tidak bisa diatur dari sisi klien. Animasi di layar murni tampilan.
- Pemenang disimpan dalam transaksi database dengan indeks unik `(prize_id, participant_id)`,
  jadi satu orang tidak bisa menang hadiah yang sama dua kali.
- Opsi "satu orang hanya boleh menang sekali" di halaman Tampilan layar mengatur apakah
  pemenang lama ikut lagi pada hadiah berikutnya.
- Nama pemenang otomatis hilang dari papan setelah diumumkan.
- Halaman undian tidak butuh login supaya bisa dibuka di komputer proyektor,
  tetapi endpoint pengundian tetap dilindungi token CSRF.
- Tampilan papan punya dua pilihan. **Daftar** menampilkan seluruh nama peserta dan
  menyorot satu nama bergantian. **Papan kosong** hanya menampilkan kolom pemenang:
  kosong sebelum Start, nama berganti sangat cepat selama berputar, lalu berhenti di
  nama pemenang saat Stop ditekan — seluruh kolom terbuka serentak, tanpa jeda bertahap.
  Jumlah kolom mengikuti pengaturan, tetapi tidak pernah melebihi jumlah pemenang
  yang sedang diundi, dan ukuran huruf menyesuaikan sendiri. Tiap kolom punya lebar
  ideal sekitar 620 px, jadi undian satu orang tampil sebagai satu kartu di tengah
  layar dan bukan kotak memanjang. Kalau barisnya melebihi tinggi layar, kolom
  pemenang ikut bisa digulir.
- Waktu pengundian bisa dipilih. Bawaannya hasil sudah dipesan ke server sejak Start,
  sehingga nama pemenang keluar seketika saat Stop tanpa menunggu jaringan. Server tetap
  yang mengundi; momen penekanan Stop tidak pernah memengaruhi hasil. Kalau putaran
  dibatalkan sebelum Stop, pemenang sudah terlanjur tercatat dan perlu dibatalkan lewat
  halaman Pemenang.
- Cara berhenti bisa dipilih: **langsung** (nama pemenang muncul seketika, bawaan) atau
  **melambat** (putaran mengendur sekitar 1,3 detik lalu mendarat di pemenang).
- Pada kedua pilihan, teks nama dikaburkan selama putaran masih cepat. Ini bagian yang
  penting: tanpa kabur, penonton sempat membaca nama terakhir sebelum Stop dan merasa
  pemenang yang diumumkan berbeda, seolah undian sudah diatur.
- Layar juga terus berputar selama menunggu jawaban server, supaya tidak ada jeda beku
  antara Stop dan pengumuman.
- Ukuran huruf nama dihitung ulang oleh JavaScript sampai seluruh daftar muat dalam
  tinggi layar, dan dihitung lagi setiap kali jendela diubah ukurannya atau ada
  pemenang yang keluar dari papan.
- Tata letak menyesuaikan rasio layar. Pada 16:9 seperti 1920 × 1080 dipakai susunan
  tegak; begitu layar dua kali lebih lebar daripada tingginya — misalnya videotron
  3840 × 997 — blok hadiah pindah ke kolom kiri dan daftar nama mengisi sisa lebar
  setinggi penuh, menaikkan ruang vertikal nama dari sekitar 500 px ke hampir 900 px.
  Bisa dikunci ke Tegak atau Samping lewat Tampilan layar.
- Jumlah kolom bisa diatur Otomatis: kolom hanya bertambah kalau dibutuhkan, dihitung
  dari berapa baris yang muat dalam tinggi papan dibagi jumlah nama. Tiap kolom dijatah
  sekitar 300 px dan papan dipusatkan, jadi peserta sedikit tidak membuat papan melebar
  sia-sia.
- Papan daftar dibatasi selebar 820 px agar nama tetap besar dan mudah dibaca;
  kelebihan barisnya ditangani dengan scroll vertikal, bukan dengan melebarkan papan.
- Kalau baris nama melebihi tinggi layar, ada tiga pilihan di Tampilan layar
  (bawaannya sekarang scroll manual):
  terus mengecilkan huruf sampai muat, berhenti di ukuran minimal lalu memberi scroll
  yang digulir panitia sendiri, atau scroll yang bergulir otomatis pelan naik-turun.
  Pada kedua mode scroll, nama pemenang dibawa ke tengah layar saat undian berhenti.
- Layar membunyikan dengung menegangkan saat Start dan bunyi lonceng saat pemenang
  muncul, dibangkitkan langsung oleh Web Audio sehingga tidak butuh berkas tambahan
  maupun koneksi internet. Panitia bisa mengunggah musik sendiri di halaman Tampilan layar,
  dan pembawa acara bisa membisukan lewat menu di pojok kiri bawah.
- Berhenti otomatis bisa dimatikan. Kalau mati, nama terus berputar sampai Stop ditekan
  tanpa batas waktu, sehingga tempo sepenuhnya di tangan pembawa acara.
- Menu pindah undian di layar terbuka tanpa login. Kalau komputer proyektor bisa
  disentuh siapa saja, aktifkan "Kunci menu pindah undian di layar" di halaman
  Tampilan layar; pemindahan hadiah lalu hanya bisa dilakukan panitia yang sudah masuk.
