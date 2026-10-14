# Manual Aplikasi Doorprize

Panduan penggunaan untuk panitia acara.

---

## Daftar Isi

1. [Mengenal aplikasi](#1-mengenal-aplikasi)
2. [Masuk ke dashboard](#2-masuk-ke-dashboard)
3. [Persiapan sebelum acara](#3-persiapan-sebelum-acara)
4. [Menjalankan undian](#4-menjalankan-undian)
5. [Layar daftar pemenang](#5-layar-daftar-pemenang)
6. [Penjelasan semua pengaturan](#6-penjelasan-semua-pengaturan)
7. [Grup pengecualian peserta](#7-grup-pengecualian-peserta)
8. [Mengelola pemenang](#8-mengelola-pemenang)
9. [Daftar periksa hari-H](#9-daftar-periksa-hari-h)
10. [Mengatasi masalah](#10-mengatasi-masalah)
11. [Pertanyaan yang sering muncul](#11-pertanyaan-yang-sering-muncul)

---

## 1. Mengenal aplikasi

Aplikasi ini punya tiga halaman utama dengan peran berbeda.

| Halaman | Alamat | Untuk siapa |
|---|---|---|
| Layar undian | `/` | Ditampilkan ke penonton lewat proyektor |
| Layar pemenang | `/pemenang` | Ditampilkan di layar kedua atau setelah acara |
| Dashboard panitia | `/panel` | Hanya panitia, perlu login |

Dua halaman pertama sengaja dibuat tanpa login agar bisa langsung dibuka di komputer
proyektor. Dashboard butuh nama pengguna dan sandi.

### Bagaimana undian ditentukan

Pemenang diundi oleh server memakai fungsi acak kriptografis, bukan oleh browser.
Artinya hasilnya tidak bisa diintip maupun diubah dari komputer yang menampilkan layar.
Animasi nama berputar di layar murni tampilan; berhenti di detik ke berapa pun tidak
memengaruhi siapa yang menang.

Setiap pemenang dicatat ke database dengan waktu undinya, dan satu orang tidak bisa
memenangkan hadiah yang sama dua kali.

---

## 2. Masuk ke dashboard

Buka alamat `/masuk`, lalu isi nama pengguna dan sandi yang diberikan administrator.

Setelah masuk, menu di sisi kiri berisi:

- **Ringkasan** — angka penting dan pemilihan hadiah yang akan diundi
- **Peserta** — daftar nama dan NIP
- **Hadiah** — daftar hadiah dan jumlah pemenangnya
- **Grup pengecualian** — kumpulan peserta yang tidak ikut hadiah tertentu
- **Pemenang** — hasil undian
- **Tampilan layar** — pengaturan tampilan dan aturan undian

Untuk mengubah sandi, hubungi administrator yang mengelola server. Sandi disimpan di
berkas konfigurasi server, bukan di dashboard.

---

## 3. Persiapan sebelum acara

### 3.1 Memasukkan peserta

Buka menu **Peserta**. Ada tiga cara:

**Satu per satu.** Isi NIP, nama, dan unit kerja di formulir kiri atas. Cocok untuk
menambahkan satu dua orang yang terlewat.

**Impor dari CSV atau Excel.** Cara ini yang dipakai untuk data banyak. Klik
**Unduh template** dulu untuk melihat format yang benar, lalu isi dengan data Anda.

Baris pertama berkas harus berisi judul kolom:

| nip | nama | unit |
|---|---|---|
| 198701012010011001 | Ditha Paramitha | Produksi |
| 198903152012012002 | Yunus Anggoro Muchlis | Maintenance |

Kolom `unit` boleh dikosongkan. Urutan kolom bebas. Beberapa penulisan lain juga
diterima, misalnya `name`, `nama_lengkap`, `nik`, `departemen`, atau `divisi`.

NIP dipakai sebagai penanda unik. Kalau Anda mengimpor ulang berkas yang sama, data
lama diperbarui, bukan digandakan. Jadi memperbaiki salah ketik nama cukup dengan
membetulkan berkas lalu mengimpor ulang.

Centang **"Hapus semua peserta lama dulu"** hanya kalau Anda memang ingin mengganti
seluruh daftar. Tindakan ini juga menghapus data pemenang yang terkait.

**Ekspor.** Tombol **Ekspor CSV** mengunduh seluruh peserta beserta statusnya. Berguna
untuk arsip atau untuk mengoreksi data di Excel lalu mengimpornya kembali.

### 3.2 Menonaktifkan peserta

Setiap baris peserta punya centang **"Ikut undian"**. Matikan centang itu untuk peserta
yang tidak hadir. Namanya langsung hilang dari layar undian.

Untuk pengecualian yang hanya berlaku di hadiah tertentu, jangan pakai cara ini —
pakai grup pengecualian (bagian 7), karena centang ini berlaku untuk semua hadiah dan
mudah terlupa dinyalakan kembali.

### 3.3 Memasukkan hadiah

Buka menu **Hadiah**. Untuk setiap hadiah isi:

- **Nama hadiah** — tampil di layar undian, tulis lengkap seperti "Mini Blender Kris 0.5L - Putih"
- **Sponsor** — opsional
- **Jumlah pemenang** — berapa unit hadiah ini tersedia
- **Pemenang per putaran** — berapa nama yang keluar sekali tekan Stop
- **Foto hadiah** — tampil besar di layar, gunakan gambar dengan latar polos
- **Urutan** — menentukan susunan di daftar

Contoh: hadiah voucher sebanyak 10 buah bisa diatur jumlah pemenang 10 dan pemenang
per putaran 2, sehingga selesai dalam 5 kali putaran, masing-masing memunculkan 2 nama.

### 3.4 Mengunci posisi pemenang tertentu

Kalau ada nama yang sudah dipastikan menang di posisi tertentu, buka halaman Hadiah lalu
klik **Posisi pemenang** pada hadiah yang bersangkutan.

Hadiah tetap diundi acak. Yang dikunci hanyalah posisi tertentu: kalau posisi 7 dikunci
atas nama Budi, pemenang ketujuh pasti Budi, sedangkan posisi lainnya tetap hasil undian.
Hadiah berkuota 50 dengan satu posisi terkunci berarti 1 nama sudah pasti dan 49 sisanya
diundi seperti biasa.

Posisi dihitung dari urutan keluarnya pemenang hadiah itu, bukan urutan putaran. Kalau
sekali tekan Stop memanggil 2 nama, putaran pertama mengisi posisi 1 dan 2.

Nama yang dikunci di suatu hadiah ditahan dari undian hadiah lain sampai posisinya
benar-benar dipanggil. Jadi kalau seseorang dikunci di hadiah utama yang diundi terakhir,
namanya tidak akan keluar lebih dulu di hadiah-hadiah sebelumnya.

Nama bisa dikunci lewat pencarian dengan mengisi nomor posisinya, atau ditempel sekaligus
dengan format `posisi,nip` satu baris satu nama. Baris yang hanya berisi NIP ditaruh di
posisi kosong berikutnya. Grup pengecualian tetap berlaku: nama yang tersaring ditandai
**Dilewati** dan posisinya diisi undian acak.

### 3.5 Mengatur tampilan layar

Buka menu **Tampilan layar**. Minimal atur:

- **Judul acara** dan anak judulnya
- **Gambar latar** ukuran 1920 × 1080 piksel, atau cukup pilih warna latar
- **Logo kiri dan kanan** untuk logo perusahaan dan penyelenggara

Penjelasan lengkap semua pilihan ada di bagian 6.

---

## 4. Menjalankan undian

### 4.1 Memilih hadiah yang diundi

Di menu **Ringkasan** atau **Hadiah**, tekan tombol **"Undi ini"** pada hadiah yang
akan diundi. Layar undian langsung menampilkan hadiah tersebut.

Perpindahan juga bisa dilakukan tanpa membuka dashboard, lewat menu di pojok kiri bawah
layar undian.

### 4.2 Membuka layar undian

Buka `/` di komputer yang tersambung proyektor, lalu tekan **F11** agar layar penuh.
Bisa juga lewat menu pojok kiri bawah, pilih **Layar penuh**.

### 4.3 Mengundi

1. Pastikan angka **"Undi N orang"** sudah sesuai
2. Tekan **Start** — nama berputar cepat dengan iringan suara menegangkan
3. Tekan **Stop** — nama pemenang muncul disertai bunyi lonceng

Tombol **spasi** berfungsi sebagai Start dan Stop, sehingga pembawa acara tidak perlu
mencari kursor. Klik Start sekali di awal acara agar browser mengizinkan suara berbunyi.

Nama pemenang otomatis hilang dari papan supaya tidak terundi dua kali. Ulangi sampai
sisa kuota hadiah habis, lalu pindah ke hadiah berikutnya.

### 4.4 Menu pojok kiri bawah

Tombol transparan di pojok kiri bawah berisi:

- **Pindah undian** — daftar semua hadiah beserta sisa kuotanya, sekali klik langsung berpindah
- **Lihat daftar pemenang** — membuka layar pemenang
- **Layar penuh** — menyalakan atau mematikan mode layar penuh
- **Muat ulang daftar peserta** — menyegarkan halaman setelah ada perubahan di dashboard
- **Suara** — membisukan sementara, misalnya saat pembawa acara sedang bicara

Menu ini bisa dikunci agar hanya panitia yang sudah login dapat mengganti hadiah.
Pengaturannya ada di **Tampilan layar**.

---

## 5. Layar daftar pemenang

Buka `/pemenang`. Foto hadiah tampil di kiri, daftar nama pemenang di kanan, lengkap
dengan nomor urut.

Halaman ini menyegarkan dirinya secara berkala (bawaan 20 detik), jadi bisa dipasang di
layar kedua dan nama pemenang baru akan muncul sendiri sementara undian berjalan di layar
utama. Atur jedanya, atau isi 0 untuk mematikannya, lewat **Tampilan layar → Penyegaran
layar pemenang**. Kalau dimatikan, penyegaran hanya dilakukan lewat menu di pojok kiri
bawah.

Pemenang yang sudah ditandai mengambil hadiahnya diberi garis tepi hijau, sehingga
panitia bisa memantau dari kejauhan siapa yang belum maju ke panggung.

Menu pojok kiri bawah berisi daftar semua hadiah untuk berpindah menampilkan pemenang
hadiah lain.

---

## 6. Penjelasan semua pengaturan

Semua ada di menu **Tampilan layar**. Setelah menyimpan, layar undian yang sedang
terbuka perlu dimuat ulang agar perubahan terlihat.

### Latar belakang

| Pengaturan | Penjelasan |
|---|---|
| Gambar latar | Ukuran ideal 1920 × 1080 piksel, maksimal 6 MB |
| Warna latar | Dipakai kalau tidak ada gambar latar, format seperti `#8B5CF6` |

Tanpa gambar latar, layar memakai warna pilihan Anda dengan pola titik-titik bawaan.

### Judul dan logo

Judul acara, anak judul, serta logo kiri dan kanan. Logo sebaiknya berformat PNG
dengan latar transparan.

Papan judul di tengah atas layar bisa dimatikan lewat centang **Tampilkan papan judul**.
Berguna kalau judul acara sudah tercetak di gambar latar sehingga tidak tertulis dua kali;
logo kiri dan kanan tetap tampil.

### Aturan undian

**Tata letak layar**

| Pilihan | Kapan dipakai |
|---|---|
| Otomatis | Bawaan. Tegak untuk layar biasa, samping untuk layar sangat lebar |
| Tegak | Hadiah di atas, nama di bawah. Cocok untuk 1920 × 1080 |
| Samping | Hadiah di kiri, nama di kanan. Cocok untuk videotron seperti 3840 × 997 |

Mode otomatis membaca rasio jendela browser, bukan resolusi layar. Tekan F11 sebelum
acara, atau kunci ke salah satu pilihan kalau jendelanya tidak selalu penuh.

**Waktu pengundian dijalankan**

| Pilihan | Penjelasan |
|---|---|
| Saat Start | Bawaan. Hasil dipesan ke server sejak Start, nama keluar seketika saat Stop |
| Saat Stop | Ada jeda singkat menunggu jawaban server |

Server tetap yang mengundi pada kedua pilihan. Pada pilihan "Saat Start", kalau putaran
dibatalkan sebelum Stop ditekan, pemenangnya sudah terlanjur tercatat dan perlu
dibatalkan lewat menu Pemenang.

**Cara berhenti saat Stop ditekan**

| Pilihan | Penjelasan |
|---|---|
| Langsung | Nama pemenang muncul seketika |
| Melambat | Putaran mengendur sekitar 1,3 detik lalu berhenti di pemenang |

Pada kedua pilihan, teks nama dikaburkan selama putaran masih cepat agar penonton tidak
sempat mengunci satu nama lalu merasa pemenangnya berbeda.

**Tampilan papan saat mengundi**

| Pilihan | Kapan dipakai |
|---|---|
| Daftar | Semua nama peserta tampil, satu nama disorot bergantian |
| Papan kosong | Hanya kolom pemenang. Kosong sebelum Start, terisi saat Stop |

Papan kosong lebih cocok kalau pesertanya ratusan sehingga tidak muat di layar.

**Kalau nama tidak muat satu layar**

| Pilihan | Penjelasan |
|---|---|
| Kecilkan huruf | Huruf terus mengecil sampai semua nama muat |
| Beri scroll | Huruf berhenti di ukuran minimal, sisanya digulir panitia |
| Scroll otomatis | Sama, tetapi papan bergulir sendiri pelan naik-turun |

Nama pemenang otomatis dibawa ke tengah layar saat undian berhenti.

**Pengaturan lain**

| Pengaturan | Penjelasan |
|---|---|
| Ukuran huruf minimal | Batas bawah pengecilan huruf pada mode scroll |
| Jumlah kolom nama | Otomatis, atau kunci di angka 1 sampai 10 |
| Durasi berhenti otomatis | Detik sebelum undian berhenti sendiri |
| Berhenti otomatis | Matikan agar putaran berlanjut sampai Stop ditekan |
| Satu orang hanya boleh menang sekali | Pemenang lama tidak ikut undian hadiah berikutnya |
| Keterangan di bawah nama | Tidak ada, NIP, unit kerja, atau keduanya |
| Penulisan nama peserta | Apa adanya, KAPITAL SEMUA, atau Awal Kata Besar |
| Kunci menu pindah undian | Ganti hadiah hanya bisa dilakukan panitia yang sudah login |

### Suara

Tanpa mengunggah berkas apa pun, layar sudah membunyikan dengung menegangkan saat Start
dan bunyi lonceng saat pemenang muncul. Unggah berkas MP3, WAV, OGG, atau M4A kalau
ingin memakai musik sendiri.

---

## 7. Grup pengecualian peserta

Fitur ini untuk kasus seperti "panitia tidak boleh menang hadiah utama" atau "direksi
tidak ikut undian sesi pertama".

### Membuat grup

1. Buka menu **Grup pengecualian**
2. Isi nama grup, misalnya "Panitia", dan keterangan singkat
3. Klik **Simpan grup**

### Mengisi anggota

Ada tiga cara, pilih yang paling cepat sesuai data Anda:

**Pencarian langsung.** Ketik nama atau NIP, hasilnya muncul seketika, klik Tambah.
Cocok untuk beberapa orang saja.

**Satu unit sekaligus.** Pilih unit kerja dari dropdown, seluruh anggotanya masuk.

**Tempel daftar NIP.** Salin kolom NIP dari Excel lalu tempel di kotak yang tersedia.
Pemisah boleh baris baru, koma, titik koma, atau tab. Ini cara tercepat untuk puluhan
sampai ratusan orang.

Nama juga diterima, tetapi hanya kalau nama itu dimiliki satu orang saja. Kalau ada dua
peserta bernama sama, baris tersebut dilewati dan Anda diberi tahu agar memakai NIP.
Pengaman ini mencegah kesalahan paling merugikan di aplikasi undian: mengecualikan
orang yang salah tanpa ada yang sadar.

### Memasang grup ke hadiah

Di halaman grup, centang hadiah mana saja yang mengecualikan grup ini, lalu simpan.
Anggota grup tidak akan muncul di layar undian untuk hadiah yang dicentang, dan tetap
ikut undian hadiah lainnya.

Di menu **Hadiah**, setiap hadiah menampilkan lencana kuning seperti "Tanpa Panitia"
supaya penyaringan yang aktif mudah dipastikan. Keterangan ini sengaja tidak ditampilkan
di layar undian.

Satu peserta boleh masuk beberapa grup. Perubahan grup berlaku untuk putaran
berikutnya; pemenang yang sudah terlanjur terundi tidak ikut terhapus.

---

## 8. Mengelola pemenang

Menu **Pemenang** berisi seluruh hasil undian, bisa disaring per hadiah.

**Menandai hadiah sudah diambil.** Klik tombol di kolom Pengambilan setiap kali
pemenang maju mengambil hadiahnya. Statusnya juga terlihat di layar pemenang.

**Membatalkan pemenang.** Kalau orangnya tidak hadir, klik **Batalkan**. Namanya
otomatis kembali masuk undian dan kuota hadiah bertambah lagi, sehingga bisa langsung
diundi ulang.

**Mengulang undian satu hadiah.** Pilih hadiahnya di penyaring, lalu klik **Ulangi
undian hadiah ini**. Seluruh pemenang hadiah tersebut dihapus dan undian dimulai dari
nol. Berguna saat gladi bersih.

**Ekspor CSV.** Mengunduh seluruh pemenang lengkap dengan hadiah, NIP, unit, waktu
undi, dan status pengambilan. Simpan berkas ini sebagai laporan resmi.

**Hapus semua pemenang.** Di bagian bawah halaman ada kotak untuk menghapus seluruh
pemenang dari semua hadiah sekaligus, dipakai membersihkan sisa uji coba sebelum acara.
Panitia harus mengetik kata HAPUS untuk melanjutkan. Peserta, hadiah, grup pengecualian,
dan posisi yang dikunci tidak ikut terhapus, dan setiap hadiah kembali ke kuota penuh.

---

## 9. Daftar periksa hari-H

**Sehari sebelum acara**

- [ ] Data peserta sudah diimpor dan jumlahnya dicek
- [ ] Semua hadiah sudah diisi lengkap dengan foto dan jumlah pemenang
- [ ] Grup pengecualian sudah dibuat dan dipasang ke hadiah yang tepat
- [ ] Gambar latar, logo, dan judul acara sudah diatur
- [ ] Gladi bersih: undi beberapa kali untuk memastikan alurnya lancar
- [ ] Bersihkan hasil gladi bersih lewat "Hapus semua pemenang" di menu Pemenang
- [ ] Pastikan daftar pemenang benar-benar kosong sebelum acara dimulai

**Sebelum acara dimulai**

- [ ] Buka layar undian di komputer proyektor, tekan F11
- [ ] Klik Start sekali agar browser mengizinkan suara
- [ ] Cek volume speaker
- [ ] Pastikan hadiah pertama sudah dipilih
- [ ] Nyalakan "Kunci menu pindah undian" kalau komputer bisa disentuh umum
- [ ] Siapkan dashboard di laptop terpisah untuk memantau

**Selama acara**

- [ ] Tandai setiap pemenang yang sudah mengambil hadiahnya
- [ ] Batalkan pemenang yang tidak hadir, lalu undi ulang

**Setelah acara**

- [ ] Ekspor CSV daftar pemenang
- [ ] Minta administrator menyimpan cadangan database

---

## 10. Mengatasi masalah

**Perubahan pengaturan tidak terlihat di layar undian.**
Halaman undian tidak memantau perubahan secara langsung. Muat ulang lewat menu pojok
kiri bawah.

**Foto hadiah tidak muncul, hanya kotak kosong.**
Berkas gambarnya belum tersalin ke server, atau tautan penyimpanan belum dibuat.
Hubungi administrator untuk menjalankan `php artisan storage:link`.

**Suara tidak berbunyi.**
Browser melarang audio sebelum ada interaksi pengguna. Klik tombol Start dengan mouse
sekali, setelah itu spasi bebas dipakai. Cek juga saklar Suara di menu pojok kiri bawah.

**Muncul pesan "Tidak ada peserta yang memenuhi syarat untuk diundi."**
Semua peserta sudah pernah menang, atau tersaring grup pengecualian. Periksa pengaturan
"Satu orang hanya boleh menang sekali" dan grup yang terpasang di hadiah tersebut.

**Nama peserta terpotong di layar.**
Kurangi jumlah kolom, atau ubah ke tampilan papan kosong kalau pesertanya sangat banyak.

**Undian gagal, muncul pesan koneksi terputus.**
Jaringan ke server terputus. Periksa koneksi lalu ulangi putaran. Tidak ada pemenang
yang tercatat kalau permintaannya gagal.

**Lupa sandi panitia.**
Sandi disimpan di berkas konfigurasi server. Hubungi administrator untuk mengubahnya.

---

## 11. Pertanyaan yang sering muncul

**Apakah hasil undian bisa diatur?**
Tidak. Pemenang ditentukan server dengan fungsi acak kriptografis, dan browser hanya
menampilkan hasilnya. Waktu penekanan tombol Stop tidak memengaruhi siapa yang menang.

**Kenapa nama terakhir yang terlihat berbeda dengan pemenang?**
Selama berputar cepat, teks sengaja dikaburkan agar tidak ada nama yang bisa terbaca.
Kalau Anda merasa masih terbaca, ganti cara berhenti ke "Melambat" supaya perpindahan
ke nama pemenang terlihat menyatu.

**Bisakah satu orang menang lebih dari satu hadiah?**
Bisa, kalau pengaturan "Satu orang hanya boleh menang sekali" dimatikan. Yang tidak
pernah bisa terjadi adalah satu orang menang hadiah yang sama dua kali.

**Bagaimana kalau pemenang tidak hadir?**
Batalkan lewat menu Pemenang. Namanya kembali masuk undian dan kuotanya bertambah lagi,
lalu tekan Start untuk mengundi penggantinya.

**Apakah layar undian bisa dibuka orang lain?**
Bisa, karena sengaja tanpa login agar praktis di komputer proyektor. Mereka hanya bisa
melihat dan mengganti hadiah yang ditampilkan, tidak bisa mengubah data. Nyalakan
"Kunci menu pindah undian" untuk menutup celah itu.

**Berapa banyak peserta yang bisa ditampung?**
Tidak ada batas teknis. Untuk tampilan, sekitar 70 nama masih nyaman dibaca di layar
1920 × 1080. Di atas itu gunakan mode scroll atau tampilan papan kosong.
