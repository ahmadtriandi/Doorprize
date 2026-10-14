@extends('layouts.admin')
@section('judul','Tampilan layar')

@section('isi')
<div class="kepala-halaman">
  <div>
    <h1>Tampilan layar undian</h1>
    <p>Latar, logo, dan judul yang muncul di proyektor.</p>
  </div>
  <a class="tombol garis" href="{{ route('draw') }}" target="_blank">Lihat hasilnya</a>
</div>

<form method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
  @csrf @method('PUT')

  <div class="kotak">
    <h2>Latar belakang</h2>
    <div class="baris">
      <div class="medan">
        <label for="background">Gambar latar</label>
        <input id="background" type="file" name="background" accept="image/*">
        <div class="bantuan">Ukuran ideal 1920 × 1080 piksel, maksimal 6 MB.</div>
      </div>
      <div class="medan">
        <label for="background_color">Warna latar</label>
        <input id="background_color" name="background_color" value="{{ $settings['background_color'] ?: '#8B5CF6' }}">
        <div class="bantuan">Dipakai kalau tidak ada gambar latar. Contoh: #8B5CF6</div>
      </div>
    </div>

    @if($settings['background_path'])
      <img src="{{ asset('storage/'.$settings['background_path']) }}" alt="Pratinjau latar"
           style="width:280px;border-radius:10px;border:1px solid var(--garis)">
      <div style="margin-top:8px">
        <button class="tombol kecil bahaya" form="hapus-background">Hapus gambar latar</button>
      </div>
    @else
      <p class="bantuan">Belum ada gambar latar. Layar memakai warna dan pola bawaan.</p>
    @endif
  </div>

  <div class="kotak">
    <h2>Judul dan logo</h2>
    <div class="baris">
      <div class="medan">
        <label for="event_title">Judul acara</label>
        <input id="event_title" name="event_title" value="{{ $settings['event_title'] }}" placeholder="Excellent Together">
      </div>
      <div class="medan">
        <label for="event_subtitle">Anak judul</label>
        <input id="event_subtitle" name="event_subtitle" value="{{ $settings['event_subtitle'] }}" placeholder="Doorprize Gathering">
      </div>
    </div>
    <div class="baris">
      <div class="medan">
        <label for="logo_left">Logo kiri</label>
        <input id="logo_left" type="file" name="logo_left" accept="image/*">
        @if($settings['logo_left_path'])
          <img src="{{ asset('storage/'.$settings['logo_left_path']) }}" alt="" style="height:40px;margin-top:8px">
          <button class="tombol kecil bahaya" form="hapus-logo_left">Hapus</button>
        @endif
      </div>
      <div class="medan">
        <label for="logo_right">Logo kanan</label>
        <input id="logo_right" type="file" name="logo_right" accept="image/*">
        @if($settings['logo_right_path'])
          <img src="{{ asset('storage/'.$settings['logo_right_path']) }}" alt="" style="height:40px;margin-top:8px">
          <button class="tombol kecil bahaya" form="hapus-logo_right">Hapus</button>
        @endif
      </div>
    </div>
  </div>

  <div class="kotak">
    <h2>Aturan undian</h2>

    <div class="medan">
      <label for="layout_mode">Tata letak layar</label>
      <select id="layout_mode" name="layout_mode" required>
        <option value="otomatis" @selected(($settings['layout_mode'] ?: 'otomatis') === 'otomatis')>
          Otomatis mengikuti rasio layar
        </option>
        <option value="tegak" @selected($settings['layout_mode'] === 'tegak')>
          Tegak — hadiah di atas, nama di bawah
        </option>
        <option value="samping" @selected($settings['layout_mode'] === 'samping')>
          Samping — hadiah di kiri, nama di kanan
        </option>
      </select>
      <div class="bantuan">
        Otomatis memakai tata letak tegak pada layar biasa seperti 1920 × 1080, dan
        beralih ke samping begitu layarnya dua kali lebih lebar daripada tingginya —
        misalnya videotron 3840 × 997. Tata letak samping memberi ruang vertikal
        hampir dua kali lipat untuk daftar nama.
        <br><br>
        Rasio dihitung dari jendela browser, bukan dari layarnya. Tekan F11 agar layar
        penuh sebelum acara, atau kunci ke salah satu pilihan kalau jendelanya tidak
        selalu penuh.
      </div>
    </div>

    <div class="medan">
      <label for="board_mode">Tampilan papan saat mengundi</label>
      <select id="board_mode" name="board_mode" required>
        <option value="daftar" @selected(($settings['board_mode'] ?: 'daftar') === 'daftar')>
          Tampilkan semua nama peserta, satu nama disorot bergantian
        </option>
        <option value="kosong" @selected($settings['board_mode'] === 'kosong')>
          Papan kosong, nama berganti cepat saat Start dan berhenti di pemenang
        </option>
      </select>
      <div class="bantuan">
        Mode papan kosong hanya menampilkan kolom pemenang. Sebelum Start kolomnya kosong,
        saat Start nama peserta berganti sangat cepat, dan begitu Stop ditekan nama pemenang
        yang berhenti di kolom itu. Cocok kalau pesertanya ratusan sehingga tidak muat di layar.
      </div>
    </div>

    <div class="medan">
      <label for="draw_moment">Waktu pengundian dijalankan</label>
      <select id="draw_moment" name="draw_moment" required>
        <option value="start" @selected(($settings['draw_moment'] ?: 'start') === 'start')>
          Saat Start — nama pemenang keluar seketika begitu Stop ditekan
        </option>
        <option value="stop" @selected($settings['draw_moment'] === 'stop')>
          Saat Stop — ada jeda singkat menunggu jawaban server
        </option>
      </select>
      <div class="bantuan">
        Server tetap yang mengundi pada kedua pilihan, hanya waktunya yang bergeser.
        Momen penekanan Stop memang tidak pernah memengaruhi hasil, jadi memesan hasil
        sejak Start tidak mengurangi keadilan dan menghilangkan jeda jaringan yang
        membuat layar seakan berpikir dulu sebelum mengumumkan.
        <br><br>
        Konsekuensinya, kalau putaran dibatalkan di tengah jalan — halaman ditutup atau
        dimuat ulang sebelum Stop — pemenang sudah terlanjur tercatat. Batalkan lewat
        halaman Pemenang agar namanya kembali masuk undian. Pilih "Saat Stop" kalau
        jaringan Anda stabil dan Anda ingin menghindari hal ini sepenuhnya.
      </div>
    </div>

    <div class="medan">
      <label for="stop_style">Cara berhenti saat Stop ditekan</label>
      <select id="stop_style" name="stop_style" required>
        <option value="langsung" @selected(($settings['stop_style'] ?: 'langsung') === 'langsung')>
          Langsung — nama pemenang muncul seketika
        </option>
        <option value="melambat" @selected($settings['stop_style'] === 'melambat')>
          Melambat — putaran mengendur sekitar 1,3 detik lalu berhenti di pemenang
        </option>
      </select>
      <div class="bantuan">
        Keduanya sama-sama jujur karena pemenang tetap diundi server setelah Stop ditekan.
        Yang membedakan hanya rasa: <em>langsung</em> terasa tegas dan cepat, <em>melambat</em>
        memberi jeda dramatis. Selama nama masih berputar, teksnya sengaja dikaburkan pada
        kedua pilihan sehingga penonton tidak sempat mengunci satu nama lalu merasa
        pemenangnya berbeda.
      </div>
    </div>

    <div class="baris">
      <div class="medan">
        <label for="overflow_mode">Kalau nama tidak muat satu layar</label>
        <select id="overflow_mode" name="overflow_mode" required>
          <option value="kecilkan" @selected($settings['overflow_mode'] === 'kecilkan')>
            Kecilkan huruf sampai semuanya muat
          </option>
          <option value="gulir_manual" @selected(($settings['overflow_mode'] ?: 'gulir_manual') === 'gulir_manual')>
            Beri scroll, huruf tetap terbaca
          </option>
          <option value="gulir" @selected($settings['overflow_mode'] === 'gulir')>
            Beri scroll dan gulirkan otomatis
          </option>
        </select>
        <div class="bantuan">
          Untuk peserta ratusan, mengecilkan huruf terus-menerus membuat nama tidak
          terbaca dari kursi belakang. Dua pilihan scroll berhenti mengecilkan huruf di
          ukuran minimal lalu menyisakan sisanya untuk digulir: yang pertama digulir
          panitia sendiri, yang kedua bergulir pelan naik-turun dengan sendirinya.
          Pada keduanya, nama pemenang otomatis dibawa ke tengah layar begitu undian
          berhenti.
        </div>
      </div>
      <div class="medan">
        <label for="min_font">Ukuran huruf minimal</label>
        <input id="min_font" type="number" name="min_font" min="10" max="34"
               value="{{ $settings['min_font'] ?: 16 }}" required>
        <div class="bantuan">Piksel. Dipakai pada kedua mode scroll.</div>
      </div>
    </div>

    <div class="baris">
      <div class="medan">
        <label for="name_columns">Jumlah kolom nama</label>
        @php $kolomTersimpan = (string) ($settings['name_columns'] ?: 'auto'); @endphp
        <select id="name_columns" name="name_columns" required>
          <option value="auto" @selected($kolomTersimpan === 'auto')>Otomatis</option>
          @for($i = 1; $i <= 10; $i++)
            <option value="{{ $i }}" @selected($kolomTersimpan === "$i")>{{ $i }} kolom</option>
          @endfor
        </select>
        <div class="bantuan">
          Nilai tersimpan di database saat ini:
          <strong>{{ $settings['name_columns'] ?: 'auto' }}</strong>.
          Kalau nilai ini sudah berubah tetapi layar belum, halaman undian hanya
          perlu dimuat ulang.
        </div>
        <div class="bantuan">
          <strong>Otomatis</strong> menambah kolom hanya kalau memang dibutuhkan:
          dua puluh nama cukup dua kolom dan papan tidak melebar sia-sia, sedangkan
          tiga ratus nama akan memakai kolom sebanyak yang muat. Tiap kolom dijatah
          sekitar 300 px dan papan dipusatkan, jadi peserta sedikit tidak membuat pil
          nama membentang dari ujung ke ujung.
          <br><br>
          Ukuran huruf tetap dihitung sendiri agar nama muat, dan pada papan kosong
          kolom tidak pernah lebih banyak dari jumlah pemenang yang sedang diundi.
        </div>
      </div>
      <div class="medan">
        <label for="spin_duration">Durasi berhenti otomatis</label>
        <input id="spin_duration" type="number" name="spin_duration" min="1" max="60"
               value="{{ $settings['spin_duration'] ?: 5 }}">
        <div class="bantuan">Detik. Hanya berlaku kalau berhenti otomatis dinyalakan.</div>
      </div>
    </div>

    <div class="centang">
      <input type="checkbox" id="auto_stop" name="auto_stop" value="1" @checked($settings['auto_stop'])>
      <label for="auto_stop">Berhenti otomatis setelah durasi di atas</label>
    </div>
    <div class="bantuan" style="margin-bottom:14px">
      Biarkan mati kalau putaran ingin dikendalikan penuh oleh pembawa acara: nama akan
      terus berputar sampai tombol Stop ditekan, tanpa batas waktu.
    </div>

    <div class="centang">
      <input type="checkbox" id="one_win_per_person" name="one_win_per_person" value="1" @checked($settings['one_win_per_person'])>
      <label for="one_win_per_person">Satu orang hanya boleh menang sekali</label>
    </div>
    @php
      // Nilai 0 harus tetap terbaca 0, bukan jatuh ke bawaan 20.
      $segar = $settings['winner_refresh'];
      $segar = ($segar === null || $segar === '') ? 20 : (int) $segar;
    @endphp
    <div class="medan">
      <label for="winner_refresh">Penyegaran layar pemenang</label>
      <input id="winner_refresh" type="number" name="winner_refresh" min="0" max="300"
             value="{{ $segar }}" required>
      <div class="bantuan">
        Detik. Layar <code>/pemenang</code> memuat ulang dirinya sendiri setiap sekian
        detik supaya nama baru muncul tanpa disentuh, berguna kalau dipasang di layar kedua.
        Isi <strong>0</strong> untuk mematikannya sama sekali; penyegaran lalu hanya
        dilakukan lewat menu di pojok kiri bawah.
      </div>
    </div>

    @php $ketTersimpan = $settings['name_caption'] ?: ($settings['show_nip'] ? 'nip' : 'tidak'); @endphp
    <div class="medan">
      <label for="name_caption">Keterangan di bawah nama</label>
      <select id="name_caption" name="name_caption" required>
        <option value="tidak" @selected($ketTersimpan === 'tidak')>Tidak ada, hanya nama</option>
        <option value="nip" @selected($ketTersimpan === 'nip')>NIP</option>
        <option value="unit" @selected($ketTersimpan === 'unit')>Unit kerja</option>
        <option value="keduanya" @selected($ketTersimpan === 'keduanya')>NIP dan unit kerja</option>
      </select>
      <div class="bantuan">
        Berlaku di papan daftar, kolom pemenang, dan layar daftar pemenang. Untuk layar
        proyektor, satu baris keterangan biasanya sudah cukup — dua baris di bawah nama
        besar cenderung ramai dan mengurangi dampak momen pengumuman.
      </div>
    </div>
    <div class="centang">
      <input type="checkbox" id="lock_screen_menu" name="lock_screen_menu" value="1" @checked($settings['lock_screen_menu'])>
      <label for="lock_screen_menu">Kunci menu pindah undian di layar</label>
    </div>
    <div class="bantuan">
      Menu hamburger di pojok kiri bawah layar undian dipakai untuk berpindah hadiah.
      Kunci opsi ini kalau komputer proyektor bisa disentuh siapa saja — pemindahan
      hadiah lalu hanya bisa dilakukan setelah panitia masuk.
    </div>
  </div>

  <div class="kotak">
    <h2>Suara</h2>

    <div class="centang">
      <input type="checkbox" id="sound_enabled" name="sound_enabled" value="1" @checked($settings['sound_enabled'])>
      <label for="sound_enabled">Nyalakan suara di layar undian</label>
    </div>
    <div class="bantuan" style="margin-bottom:14px">
      Tanpa mengunggah berkas apa pun, layar sudah membunyikan dengung menegangkan
      saat Start dan bunyi lonceng saat pemenang muncul. Unggah berkas di bawah kalau
      ingin memakai musik sendiri. Pembawa acara juga bisa membisukan sementara lewat
      menu di pojok kiri bawah layar.
    </div>

    <div class="baris">
      <div class="medan">
        <label for="spin_sound">Musik saat berputar</label>
        <input id="spin_sound" type="file" name="spin_sound" accept="audio/*">
        <div class="bantuan">MP3, WAV, OGG, atau M4A maksimal 5 MB. Diputar berulang sampai Stop ditekan.</div>
        @if($settings['spin_sound_path'])
          <audio controls src="{{ asset('storage/'.$settings['spin_sound_path']) }}" style="width:100%;margin-top:8px"></audio>
          <button class="tombol kecil bahaya" form="hapus-spin_sound" style="margin-top:6px">Hapus musik</button>
        @endif
      </div>
      <div class="medan">
        <label for="win_sound">Suara saat pemenang muncul</label>
        <input id="win_sound" type="file" name="win_sound" accept="audio/*">
        <div class="bantuan">Bunyi pendek, misalnya lonceng atau tepuk tangan.</div>
        @if($settings['win_sound_path'])
          <audio controls src="{{ asset('storage/'.$settings['win_sound_path']) }}" style="width:100%;margin-top:8px"></audio>
          <button class="tombol kecil bahaya" form="hapus-win_sound" style="margin-top:6px">Hapus suara</button>
        @endif
      </div>
    </div>
  </div>

  <button class="tombol">Simpan tampilan</button>
  <span class="bantuan" style="display:inline-block;margin-left:12px">
    Layar undian yang sudah terbuka perlu dimuat ulang agar perubahan terlihat.
  </span>
</form>

@php
  $berkasHapus = [
    'background_path' => 'background',
    'logo_left_path'  => 'logo_left',
    'logo_right_path' => 'logo_right',
    'spin_sound_path' => 'spin_sound',
    'win_sound_path'  => 'win_sound',
  ];
@endphp

@foreach($berkasHapus as $key => $nama)
  @if($settings[$key])
    <form id="hapus-{{ $nama }}" method="post" action="{{ route('admin.settings.image.remove', $key) }}">
      @csrf @method('DELETE')
    </form>
  @endif
@endforeach
@endsection
