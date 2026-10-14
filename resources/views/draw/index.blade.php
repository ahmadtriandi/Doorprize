@php
  /** Keterangan kecil di bawah nama, mengikuti pengaturan panitia. */
  $ket = function ($p) use ($settings) {
      return match ($settings['caption']) {
          'nip'      => $p->nip,
          'unit'     => $p->department,
          'keduanya' => trim($p->nip.($p->department ? ' · '.$p->department : '')),
          default    => null,
      };
  };

  // 'auto' atau angka 1-10. Nilai akhirnya diterapkan JavaScript.
  $kolom  = $settings['columns'] === 'auto' ? 'auto' : min(10, max(1, (int) $settings['columns']));
  $tataKelas = $settings['layout'] === 'tegak' ? 'kunci-tegak'
             : ($settings['layout'] === 'samping' ? 'kunci-samping' : '');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $settings['title'] }} — Undian</title>
<style>
  :root{
    --ungu:#8B5CF6; --ungu-tua:#5B21B6; --sian:#22D3EE; --pink:#F472B6;
    --kuning:#FBBF24; --krem:#FDF4FF; --jingga:#FB7185;
  }
  *{box-sizing:border-box;margin:0;padding:0}
  html,body{height:100%}
  body{
    font-family:'Trebuchet MS','Segoe UI',system-ui,sans-serif;
    background:{{ $settings['bg_color'] ?: '#8B5CF6' }};
    @if($settings['background'])
      background-image:url('{{ asset('storage/'.$settings['background']) }}');
      background-size:cover;background-position:center;background-attachment:fixed;
    @endif
    color:var(--ungu-tua);overflow:hidden;
  }
  @if(!$settings['background'])
  body::before{
    content:'';position:fixed;inset:0;pointer-events:none;opacity:.55;
    background-image:
      radial-gradient(circle at 12% 18%, var(--sian) 0 9px, transparent 10px),
      radial-gradient(circle at 88% 76%, var(--pink) 0 14px, transparent 15px),
      radial-gradient(circle at 78% 12%, var(--kuning) 0 7px, transparent 8px),
      radial-gradient(circle at 22% 86%, var(--kuning) 0 6px, transparent 7px);
    background-size:340px 340px,420px 420px,260px 260px,300px 300px;
  }
  @endif

  /* Panggung memakai grid bernama supaya satu markup bisa dipakai dua tata letak:
     tegak untuk layar 16:9, dan samping untuk layar sangat lebar seperti 3840x997. */
  .panggung{
    position:relative;z-index:2;height:100vh;padding:18px 26px 22px;
    display:grid;gap:0 30px;
    grid-template-columns:1fr;
    grid-template-rows:auto auto 1fr auto auto;
    grid-template-areas:"kepala" "hadiah" "isi" "petunjuk" "kendali";
  }
  .kepala{grid-area:kepala}
  .hadiah{grid-area:hadiah}
  .daftar,.papan{grid-area:isi}
  .petunjuk-gulir{grid-area:petunjuk}
  .kendali{grid-area:kendali}

  /* Tata letak samping: blok hadiah di kolom kiri, papan nama mengisi sisa lebar
     setinggi penuh. Di layar 4:1 ini menaikkan ruang vertikal nama dari sekitar
     500 px menjadi hampir 900 px. */
  .tata-samping .panggung{
    grid-template-columns:minmax(0,880px) minmax(0,1fr);
    grid-template-rows:auto 1fr auto;
    grid-template-areas:
      "kepala  kepala"
      "hadiah  isi"
      "kendali petunjuk";
  }
  .tata-samping .hadiah{justify-content:center;margin-top:0}
  .tata-samping .hadiah img,.tata-samping .hadiah .kosong{height:44vh;max-height:none}
  .tata-samping .pita{min-width:0;width:100%}
  .tata-samping .kendali{margin-top:0;align-self:center}
  .tata-samping .daftar{width:100%;margin-top:0}
  .tata-samping .papan{margin-top:0}

  /* --- kepala --- */
  .kepala{display:flex;align-items:flex-start;justify-content:space-between;gap:20px}
  .logo{max-height:64px;max-width:200px;object-fit:contain}
  .judul{
    text-align:center;background:#1E1B4B;border:4px solid var(--sian);border-radius:14px;
    padding:8px 30px;box-shadow:0 8px 0 rgba(0,0,0,.22);
  }
  .judul b{display:block;font-size:26px;line-height:1;color:var(--kuning);letter-spacing:.06em;text-transform:uppercase}
  .judul span{display:block;font-size:13px;color:var(--sian);letter-spacing:.28em;margin-top:4px;text-transform:uppercase}

  /* --- hadiah --- */
  .hadiah{display:flex;flex-direction:column;align-items:center;margin-top:10px}
  .hadiah img{
    height:18vh;max-height:180px;background:#fff;border-radius:10px;padding:8px;
    box-shadow:0 10px 0 rgba(0,0,0,.18);object-fit:contain;
  }
  .hadiah .kosong{height:18vh;max-height:180px;aspect-ratio:1;background:rgba(255,255,255,.5);border:3px dashed #fff;border-radius:10px;display:grid;place-items:center;color:#fff;font-size:13px;padding:12px;text-align:center}
  .pita{
    margin-top:14px;min-width:min(660px,86vw);text-align:center;
    background:linear-gradient(#EDE9FE,#DDD6FE);border:3px solid #fff;border-radius:8px;
    padding:9px 34px;font-size:24px;font-weight:800;letter-spacing:.04em;
    text-transform:uppercase;color:#4C1D95;box-shadow:0 6px 0 rgba(0,0,0,.18);
  }
  .sisa{margin-top:8px;font-size:14px;font-weight:700;color:#fff;background:rgba(0,0,0,.28);padding:4px 14px;border-radius:20px}

  /* --- papan mode daftar: semua nama peserta --- */
  /* Jumlah kolom ditulis sebagai style inline pada elemennya, bukan di sini,
     supaya tidak bisa ditimpa aturan lain maupun media query. */
  .daftar{
    flex:1;min-height:0;margin:14px auto 0;width:100%;max-width:min(820px,96vw);
    display:grid;grid-template-columns:repeat(3,1fr);
    align-content:start;overflow-x:hidden;overflow-y:auto;
    scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.55) transparent;scroll-behavior:auto;
    /* Ukuran huruf dihitung JavaScript sampai semua nama muat tanpa scroll.
       Jarak dan bantalan memakai em supaya ikut mengecil bersamanya. */
    font-size:15px;gap:.34em 1.3em;
  }
  .nama{
    background:#fff;border:2px solid var(--kuning);border-radius:999px;
    padding:.32em .8em;text-align:center;font-size:1em;font-weight:600;color:#3B0764;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.25;
  }
  .nama.sorot{background:var(--kuning);border-color:#fff;transform:scale(1.06)}
  .nama small{display:block;font-weight:400;font-size:.72em;opacity:.65;line-height:1.2}
  .daftar::-webkit-scrollbar,.papan::-webkit-scrollbar{width:8px}
  .daftar::-webkit-scrollbar-thumb,.papan::-webkit-scrollbar-thumb{background:rgba(255,255,255,.55);border-radius:8px}
  .daftar::-webkit-scrollbar-track,.papan::-webkit-scrollbar-track{background:transparent}
  .petunjuk-gulir{
    text-align:center;color:#fff;font-size:12px;font-weight:600;letter-spacing:.06em;
    background:rgba(0,0,0,.24);border-radius:20px;padding:3px 14px;margin:6px auto 0;
  }
  .daftar-kosong{grid-column:1/-1;text-align:center;color:#fff;font-size:18px;padding:40px;background:rgba(0,0,0,.2);border-radius:12px}

  /* --- papan mode kosong: hanya kolom pemenang --- */
  /* Jumlah kolom diatur lewat style inline dari JavaScript, mengikuti
     pengaturan panitia dan jumlah pemenang yang sedang diundi. */
  .papan{
    flex:1;min-height:0;margin:18px auto 0;width:min(1920px,98vw);
    display:grid;gap:16px;align-content:center;justify-content:stretch;
    overflow-x:hidden;overflow-y:auto;
    scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.55) transparent;
    --ukuran-nama:clamp(22px,3.4vw,42px);
  }
  .slot{
    min-width:0;min-height:96px;
    background:rgba(255,255,255,.95);border:4px solid var(--kuning);border-radius:16px;
    display:grid;place-content:center;padding:14px 22px;text-align:center;
    box-shadow:0 8px 0 rgba(0,0,0,.2);
  }
  .slot .isi{
    font-size:var(--ukuran-nama);font-weight:800;color:#4C1D95;line-height:1.15;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;
  }
  .slot .unit{font-size:14px;font-weight:500;color:#7C3AED;margin-top:4px;min-height:18px}
  .slot .tunggu{font-size:15px;font-weight:600;color:#A78BFA;letter-spacing:.18em;text-transform:uppercase}
  .slot{transition:transform .2s ease-out,border-color .2s}
  .slot.berputar{border-color:var(--sian);background:#fff}
  .slot.berputar .isi{color:#7C3AED;opacity:.85}
  .slot.menang{border-color:var(--pink);background:var(--krem);transform:scale(1.03)}

  /* --- kendali --- */
  .kendali{display:flex;justify-content:center;align-items:center;gap:14px;margin-top:14px}
  .tombol{
    font-family:inherit;font-size:20px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;
    padding:10px 40px;border:3px solid #92400E;border-radius:6px;cursor:pointer;
    background:var(--kuning);color:#7C2D12;box-shadow:0 6px 0 #92400E;transition:transform .08s,box-shadow .08s;
  }
  .tombol:active{transform:translateY(4px);box-shadow:0 2px 0 #92400E}
  .tombol[disabled]{opacity:.45;cursor:not-allowed;box-shadow:0 6px 0 #92400E}
  .tombol.stop{background:var(--jingga);color:#fff;border-color:#9F1239;box-shadow:0 6px 0 #9F1239}
  .tombol.stop:active{box-shadow:0 2px 0 #9F1239}
  .jumlah{background:rgba(255,255,255,.9);border:3px solid #fff;border-radius:6px;padding:8px 10px;font:inherit;font-size:15px;font-weight:700;color:#4C1D95}

  /* --- pengumuman pemenang (mode daftar) --- */
  .tirai{
    position:fixed;inset:0;z-index:20;display:none;place-items:center;
    background:rgba(30,27,75,.9);backdrop-filter:blur(3px);padding:24px;
  }
  .tirai.tampil{display:grid}
  .kartu{
    background:var(--krem);border:6px solid var(--kuning);border-radius:20px;
    padding:30px 52px;text-align:center;max-width:min(900px,92vw);max-height:88vh;overflow:auto;
    box-shadow:0 16px 0 rgba(0,0,0,.3);
  }
  .kartu h2{font-size:16px;letter-spacing:.3em;text-transform:uppercase;color:var(--pink)}
  .kartu .menang{font-size:clamp(28px,5vw,52px);font-weight:800;color:#4C1D95;margin:10px 0;line-height:1.15}
  .kartu .menang small{display:block;font-size:15px;font-weight:500;color:#7C3AED;margin-top:2px}
  .kartu p{color:#6D28D9;font-size:15px;margin-top:6px}
  .kartu .tombol{margin-top:22px}
  .galat{background:#FEE2E2;border:3px solid #DC2626;color:#7F1D1D;border-radius:10px;padding:14px 18px;font-weight:600}

  .kabar-layar{
    position:fixed;left:78px;bottom:26px;z-index:30;max-width:340px;
    background:rgba(220,38,38,.92);color:#fff;padding:10px 16px;border-radius:10px;
    font-size:14px;font-weight:600;box-shadow:0 8px 20px rgba(0,0,0,.3);
  }

  /* --- menu pindah undian, pojok kiri bawah --- */
  .menu-pojok{position:fixed;left:18px;bottom:18px;z-index:30}
  .menu-tombol{
    width:46px;height:46px;display:grid;place-content:center;gap:5px;
    background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.4);
    border-radius:12px;cursor:pointer;backdrop-filter:blur(6px);
    transition:background .15s,opacity .15s;opacity:.5;
  }
  .menu-tombol:hover,.menu-tombol:focus-visible,.menu-pojok.buka .menu-tombol{background:rgba(255,255,255,.34);opacity:1}
  .menu-tombol i{display:block;width:22px;height:2.5px;border-radius:2px;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.35)}
  .menu-pojok.buka .menu-tombol i:nth-child(2){opacity:0}

  .menu-isi{
    position:absolute;left:0;bottom:58px;width:300px;display:none;
    background:rgba(30,27,75,.86);backdrop-filter:blur(10px);
    border:1px solid rgba(255,255,255,.28);border-radius:14px;padding:12px;
    box-shadow:0 12px 30px rgba(0,0,0,.35);color:#fff;
    max-height:min(70vh,560px);overflow:auto;
  }
  .menu-pojok.buka .menu-isi{display:block}
  .menu-isi h3{font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#C4B5FD;margin:0 4px 8px}
  .menu-isi a,.menu-isi > button{
    display:block;width:100%;text-align:left;background:none;border:0;color:#fff;
    font:inherit;font-size:14px;padding:8px 10px;border-radius:8px;cursor:pointer;text-decoration:none;
  }
  .menu-isi a:hover,.menu-isi > button:hover,.menu-isi a:focus-visible,.menu-isi > button:focus-visible{background:rgba(255,255,255,.16)}
  .menu-isi .pisah{height:1px;background:rgba(255,255,255,.18);margin:10px 4px}
  .menu-isi small{display:block;color:#C4B5FD;font-size:12px;padding:2px 10px 6px}

  .undian{
    display:block;width:100%;text-align:left;background:none;border:0;color:#fff;font:inherit;
    padding:8px 10px;border-radius:8px;cursor:pointer;margin-bottom:2px;
    border-left:3px solid transparent;
  }
  .undian:hover,.undian:focus-visible{background:rgba(255,255,255,.16)}
  .undian .nama-undian{display:block;font-size:14px;font-weight:600;line-height:1.25}
  .undian .sisa-undian{display:block;font-size:12px;color:#C4B5FD;margin-top:1px}
  .undian.kini{background:rgba(251,191,36,.2);border-left-color:var(--kuning);cursor:default}
  .undian.kini .sisa-undian{color:var(--kuning)}
  .undian.habis{opacity:.4;cursor:not-allowed}
  .undian.habis:hover{background:none}

  /* Ambang 2:1 memisahkan layar 16:9 (rasio 1,78 - tetap tegak) dari layar
     ultra lebar seperti 3840x997 (rasio 3,85 - beralih ke samping). */
  @media (min-aspect-ratio:2/1){
    body.tata-otomatis .panggung{
      grid-template-columns:minmax(0,880px) minmax(0,1fr);
      grid-template-rows:auto 1fr auto;
      grid-template-areas:
        "kepala  kepala"
        "hadiah  isi"
        "kendali petunjuk";
    }
    body.tata-otomatis .hadiah{justify-content:center;margin-top:0}
    body.tata-otomatis .hadiah img,body.tata-otomatis .hadiah .kosong{height:44vh;max-height:none}
    body.tata-otomatis .pita{min-width:0;width:100%}
    body.tata-otomatis .kendali{margin-top:0;align-self:center}
    body.tata-otomatis .daftar{width:100%;margin-top:0}
    body.tata-otomatis .papan{margin-top:0}
  }

  @media (prefers-reduced-motion:reduce){.nama.sorot,.slot.menang{transform:none}}
  @media (max-width:820px){
    .pita{font-size:17px}
    .judul b{font-size:18px}
  }
</style>
</head>
<body class="{{ $tataKelas ?: 'tata-otomatis' }} {{ $settings['layout'] === 'samping' ? 'tata-samping' : '' }}">
<div class="panggung">

  <header class="kepala">
    <div>
      @if($settings['logo_left'])
        <img class="logo" src="{{ asset('storage/'.$settings['logo_left']) }}" alt="">
      @endif
    </div>

    <div class="judul">
      <b>{{ $settings['title'] }}</b>
      <span>{{ $settings['subtitle'] }}</span>
    </div>

    <div>
      @if($settings['logo_right'])
        <img class="logo" src="{{ asset('storage/'.$settings['logo_right']) }}" alt="">
      @endif
    </div>
  </header>

  @if($prize)
    <section class="hadiah">
      @if($prize->image_url)
        <img src="{{ $prize->image_url }}" alt="{{ $prize->name }}">
      @else
        <div class="kosong">Belum ada foto hadiah</div>
      @endif

      <div class="pita">{{ $prize->name }}</div>
      <div class="sisa" id="sisa">Sisa hadiah: {{ $prize->remaining_slot }} dari {{ $prize->quota }}</div>

    </section>

    @if($settings['board'] === 'kosong')
      {{-- Papan kosong: kolom pemenang baru terisi setelah Stop. --}}
      <main class="papan" id="papan"></main>
    @else
      {{-- Papan daftar: semua nama peserta tampil, satu nama disorot bergantian. --}}
      <main class="daftar" id="daftar">
        @forelse($participants as $p)
          <div class="nama" data-id="{{ $p->id }}">
            {{ $p->name }}
            @if($baris = $ket($p))<small>{{ $baris }}</small>@endif
          </div>
        @empty
          <div class="daftar-kosong">Belum ada peserta yang bisa diundi. Tambahkan peserta lewat dashboard panitia.</div>
        @endforelse
      </main>
    @endif

    <div class="petunjuk-gulir" id="petunjukGulir" hidden></div>

    <div class="kendali">
      <label class="jumlah">
        Undi
        <input type="number" id="jumlah" min="1" max="{{ max(1,$prize->remaining_slot) }}"
               value="{{ min($prize->per_draw, max(1,$prize->remaining_slot)) }}"
               style="width:56px;border:0;background:transparent;font:inherit">
        orang
      </label>
      <button class="tombol" id="mulai">Start</button>
      <button class="tombol stop" id="berhenti" disabled>Stop</button>
    </div>
  @else
    <main class="daftar"><div class="daftar-kosong">Belum ada hadiah. Buat hadiah dulu di dashboard panitia, lalu pilih hadiah yang akan diundi.</div></main>
  @endif
</div>

<div class="tirai" id="tirai">
  <div class="kartu" id="kartu"></div>
</div>

@if(session('error'))
  <div class="kabar-layar">{{ session('error') }}</div>
@endif

<div class="menu-pojok" id="menuPojok">
  <button class="menu-tombol" id="menuTombol" aria-expanded="false" aria-controls="menuIsi" aria-label="Buka menu layar">
    <i></i><i></i><i></i>
  </button>

  <div class="menu-isi" id="menuIsi" role="menu">
    <h3>Pindah undian</h3>

    @forelse($prizes as $p)
      @php
        $sisa     = $p->quota - $p->winners_count;
        $sekarang = $p->id === optional($prize)->id;
      @endphp

      @if($sekarang)
        <div class="undian kini" role="menuitem" aria-current="true">
          <span class="nama-undian">{{ $p->name }}</span>
          <span class="sisa-undian">Sedang diundi · sisa {{ $sisa }}</span>
        </div>
      @else
        <form method="post" action="{{ route('draw.select', $p) }}">
          @csrf
          <button type="submit" class="undian {{ $sisa < 1 ? 'habis' : '' }}" role="menuitem" @disabled($sisa < 1)>
            <span class="nama-undian">{{ $p->name }}</span>
            <span class="sisa-undian">{{ $sisa < 1 ? 'Sudah habis' : 'Sisa '.$sisa.' dari '.$p->quota }}</span>
          </button>
        </form>
      @endif
    @empty
      <small>Belum ada hadiah. Tambahkan lewat dashboard panitia.</small>
    @endforelse

    <div class="pisah"></div>

    <a href="{{ route('draw.winners') }}" role="menuitem">Lihat daftar pemenang</a>
    <button type="button" id="layarPenuh" role="menuitem">Layar penuh</button>
    @if($settings['sound'])
      <button type="button" id="saklarSuara" role="menuitem">Suara</button>
    @endif
    <button type="button" onclick="location.reload()" role="menuitem">Muat ulang daftar peserta</button>
    @if(session('doorprize_admin'))
      <a href="{{ route('admin.dashboard') }}" role="menuitem">Dashboard panitia</a>
    @else
      <a href="{{ route('login') }}" role="menuitem">Masuk sebagai panitia</a>
    @endif
  </div>
</div>

<script>
(() => {
  const pojok  = document.getElementById('menuPojok');
  const tombol = document.getElementById('menuTombol');

  const setBuka = (buka) => {
    pojok.classList.toggle('buka', buka);
    tombol.setAttribute('aria-expanded', buka ? 'true' : 'false');
    tombol.setAttribute('aria-label', buka ? 'Tutup menu layar' : 'Buka menu layar');
  };

  tombol.addEventListener('click', () => setBuka(!pojok.classList.contains('buka')));
  document.addEventListener('click', e => { if (!pojok.contains(e.target)) setBuka(false); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') setBuka(false); });

  document.getElementById('layarPenuh').addEventListener('click', () => {
    document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen();
    setBuka(false);
  });

  const saklar = document.getElementById('saklarSuara');
  if (saklar) {
    const label = () => {
      const bisu = localStorage.getItem('doorprize.bisu') === '1';
      saklar.textContent = bisu ? 'Suara: mati' : 'Suara: nyala';
    };
    label();
    saklar.addEventListener('click', () => {
      window.Suara ? window.Suara.ganti()
                   : localStorage.setItem('doorprize.bisu', localStorage.getItem('doorprize.bisu') === '1' ? '' : '1');
      label();
    });
  }
})();
</script>

@if($prize)
<script>
(() => {
  @php
    $modePapan   = $settings['board'];
    $urlUndi     = route('draw.spin', $prize);
    $durasiPutar = max(1, (int) $settings['duration']) * 1000;
  @endphp

  const mode     = @json($modePapan);            // 'daftar' atau 'kosong'
  const gayaStop = @json($settings['stop_style']); // 'langsung' atau 'melambat'
  const kolomAtur = @json($kolom);                 // 'auto' atau angka 1-10
  const gayaLuber = @json($settings['overflow']);  // 'kecilkan' atau 'gulir'
  const hurufMin  = {{ (int) $settings['min_font'] }};
  const saatUndi  = @json($settings['draw_moment']); // 'start' atau 'stop'
  const autoStop = {{ $settings['auto_stop'] ? 'true' : 'false' }};
  const durasi   = {{ $durasiPutar }};
  const caption  = @json($settings['caption']); // 'tidak', 'nip', 'unit', atau 'keduanya'
  const url      = @json($urlUndi);
  const namaHadiah = @json($prize->name);
  const kuota    = {{ (int) $prize->quota }};
  const token    = document.querySelector('meta[name=csrf-token]').content;

  let peserta = @json($pool);

  /* ---------- suara ----------
     Kalau panitia mengunggah berkas sendiri, berkas itu yang dipakai.
     Kalau tidak, nada dibangkitkan langsung oleh Web Audio sehingga
     aplikasi tetap bersuara tanpa perlu koneksi internet maupun berkas tambahan. */
  const Suara = (() => {
    const nyala      = {{ $settings['sound'] ? 'true' : 'false' }};
    const berkasPutar  = @json($settings['spin_sound']);
    const berkasMenang = @json($settings['win_sound']);

    const audioPutar  = berkasPutar ? new Audio(berkasPutar) : null;
    const audioMenang = berkasMenang ? new Audio(berkasMenang) : null;
    if (audioPutar) { audioPutar.loop = true; audioPutar.volume = .8; }

    let ctx = null, simpul = [], bisu = localStorage.getItem('doorprize.bisu') === '1';

    const konteks = () => {
      if (!ctx) ctx = new (window.AudioContext || window.webkitAudioContext)();
      if (ctx.state === 'suspended') ctx.resume();
      return ctx;
    };

    const hidup = () => nyala && !bisu;

    /** Dengung menegangkan: nada dasar rendah, denyut cepat, dan nada yang naik terus. */
    function teganganSintetis() {
      const c = konteks(), t = c.currentTime;
      const keluar = c.createGain();
      keluar.gain.setValueAtTime(0, t);
      keluar.gain.linearRampToValueAtTime(.22, t + .4);
      keluar.connect(c.destination);

      const saring = c.createBiquadFilter();
      saring.type = 'lowpass';
      saring.frequency.setValueAtTime(600, t);
      saring.connect(keluar);

      const dengung = c.createOscillator();
      dengung.type = 'sawtooth';
      dengung.frequency.setValueAtTime(55, t);
      dengung.connect(saring);
      dengung.start(t);

      // nada yang perlahan naik supaya terasa makin tegang
      const naik = c.createOscillator();
      const naikGain = c.createGain();
      naik.type = 'triangle';
      naik.frequency.setValueAtTime(180, t);
      naik.frequency.exponentialRampToValueAtTime(720, t + 18);
      naikGain.gain.setValueAtTime(.05, t);
      naik.connect(naikGain).connect(saring);
      naik.start(t);

      // denyut cepat seperti detak
      const denyut = c.createOscillator();
      const denyutGain = c.createGain();
      denyut.type = 'sine';
      denyut.frequency.setValueAtTime(9, t);
      denyutGain.gain.setValueAtTime(.5, t);
      denyut.connect(denyutGain).connect(keluar.gain);
      denyut.start(t);

      simpul = [dengung, naik, denyut, keluar];
    }

    /** Bunyi lonceng "criiing" saat pemenang muncul. */
    function loncengSintetis() {
      const c = konteks(), t = c.currentTime;
      const nada = [1046.5, 1318.5, 1568, 2093, 2637];

      nada.forEach((hz, i) => {
        const osc = c.createOscillator();
        const g   = c.createGain();
        const mulaiNada = t + i * .045;

        osc.type = 'sine';
        osc.frequency.setValueAtTime(hz, mulaiNada);
        g.gain.setValueAtTime(0, mulaiNada);
        g.gain.linearRampToValueAtTime(.28 / (i * .5 + 1), mulaiNada + .01);
        g.gain.exponentialRampToValueAtTime(.0001, mulaiNada + 2.4);

        osc.connect(g).connect(c.destination);
        osc.start(mulaiNada);
        osc.stop(mulaiNada + 2.5);
      });

      // kilau tipis di atasnya
      const kilau = c.createOscillator();
      const kg = c.createGain();
      kilau.type = 'triangle';
      kilau.frequency.setValueAtTime(3136, t);
      kilau.frequency.exponentialRampToValueAtTime(4186, t + .5);
      kg.gain.setValueAtTime(.09, t);
      kg.gain.exponentialRampToValueAtTime(.0001, t + 1.6);
      kilau.connect(kg).connect(c.destination);
      kilau.start(t);
      kilau.stop(t + 1.7);
    }

    return {
      mulai() {
        if (!hidup()) return;
        if (audioPutar) { audioPutar.currentTime = 0; audioPutar.play().catch(() => {}); return; }
        try { teganganSintetis(); } catch (e) {}
      },
      berhenti() {
        if (audioPutar) { audioPutar.pause(); audioPutar.currentTime = 0; }
        simpul.forEach(n => { try { n.stop ? n.stop() : n.disconnect(); } catch (e) {} });
        simpul = [];
      },
      menang() {
        if (!hidup()) return;
        if (audioMenang) { audioMenang.currentTime = 0; audioMenang.play().catch(() => {}); return; }
        try { loncengSintetis(); } catch (e) {}
      },
      bisu()   { return bisu; },
      ganti()  {
        bisu = !bisu;
        localStorage.setItem('doorprize.bisu', bisu ? '1' : '');
        if (bisu) this.berhenti();
        return bisu;
      },
      tersedia: nyala,
    };
  })();
  window.Suara = Suara;

  const papan    = document.getElementById('papan');
  const daftar   = document.getElementById('daftar');
  const mulai    = document.getElementById('mulai');
  const berhenti = document.getElementById('berhenti');
  const jumlah   = document.getElementById('jumlah');
  const tirai    = document.getElementById('tirai');
  const kartu    = document.getElementById('kartu');
  const sisaEl   = document.getElementById('sisa');

  let timer = null, timerAuto = null, aktif = null, berputar = false;

  // Hasil undian yang sudah dipesan ke server saat Start ditekan.
  let hasilTertunda = null;

  /**
   * Server tetap yang mengundi dengan random_int, hanya waktunya yang bergeser.
   * Dengan memesan hasil sejak Start, jawaban sudah tiba jauh sebelum Stop
   * ditekan sehingga nama pemenang keluar seketika tanpa menunggu jaringan.
   * Momen penekanan Stop memang tidak pernah memengaruhi hasil pada rancangan
   * ini, jadi tidak ada keadilan yang berkurang.
   */
  function mintaHasil() {
    return fetch(url, {
      method: 'POST',
      headers: {'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},
      body: JSON.stringify({ count: Number(jumlah.value) || 1 })
    }).then(res => res.json());
  }

  const acak = (arr) => arr[Math.floor(Math.random() * arr.length)];

  /* ---------- papan kosong ---------- */

  function gambarSlot(n) {
    if (!papan) return;

    // Tidak pernah lebih banyak dari jumlah pemenang, supaya satu pemenang
    // tetap tampil besar di tengah dan tidak menyisakan kolom kosong.
    // Pada mode otomatis, kolom pemenang mengikuti jumlah pemenang tetapi
    // dibatasi empat supaya kartunya tidak mengecil berlebihan.
    const lebar = kolomAtur === 'auto'
      ? Math.max(1, Math.min(4, n))
      : Math.max(1, Math.min(kolomAtur, n));

    // Papan tidak dipaksa selebar layar. Tiap kolom punya lebar ideal sekitar
    // 620 px, jadi undian satu orang tampil sebagai satu kartu proporsional di
    // tengah layar, bukan kotak memanjang dari ujung ke ujung.
    papan.style.gridTemplateColumns = `repeat(${lebar},minmax(0,1fr))`;
    papan.style.maxWidth = Math.min(1920, lebar * 620) + 'px';
    papan.style.setProperty('--ukuran-nama', ukuranSlot(lebar) + 'px');

    papan.innerHTML = '';
    for (let i = 0; i < n; i++) {
      const slot = document.createElement('div');
      slot.className = 'slot';
      slot.innerHTML = '<div class="tunggu">Menunggu undian</div>';
      papan.appendChild(slot);
    }

    // Kalau barisnya melebihi tinggi layar, isi dirapatkan ke atas supaya
    // baris pertama tidak terpotong dan seluruhnya bisa digulir.
    papan.style.alignContent = papan.scrollHeight > papan.clientHeight ? 'start' : 'center';
  }

  /**
   * Ukuran huruf kolom pemenang dihitung dari lebar kolom dan nama terpanjang,
   * bukan dari nilai tetap, supaya nama sepanjang apa pun tetap utuh dan tidak
   * berkedip berubah ukuran saat nama berganti cepat.
   */
  function ukuranSlot(lebar) {
    const lebarKolom = (papan.clientWidth - (lebar - 1) * 16) / lebar;
    const hurufTerpanjang = peserta.reduce((n, p) => Math.max(n, p.name.length), 8);
    const muat = (lebarKolom - 48) / (hurufTerpanjang * 0.56);

    return Math.max(15, Math.min(lebar === 1 ? 54 : 40, Math.floor(muat)));
  }

  /** Keterangan di bawah nama pemenang, mengikuti pengaturan yang sama. */
  function keterangan(orang) {
    if (caption === 'nip') return orang.nip || '';
    if (caption === 'unit') return orang.unit || '';
    if (caption === 'keduanya') return [orang.nip, orang.unit].filter(Boolean).join(' · ');
    return '';
  }

  function isiSlot(slot, orang, kelas, kabur = 0) {
    slot.className = 'slot' + (kelas ? ' ' + kelas : '');
    const bawah = keterangan(orang);
    slot.innerHTML = `<div class="isi">${orang.name}</div><div class="unit">${bawah || ''}</div>`;
    slot.style.filter = kabur > 0.05 ? `blur(${kabur.toFixed(2)}px)` : '';
  }

  /** Semakin cepat putaran, semakin kabur teksnya. */
  const kaburDari = (jeda) => Math.max(0, (190 - jeda) / 52);

  const semuaSlot = () => papan ? [...papan.querySelectorAll('.slot')] : [];

  /* ---------- papan daftar ---------- */

  const kartuNama = () => daftar ? [...daftar.querySelectorAll('.nama')] : [];

  /* ---------- jumlah kolom ---------- */

  const LEBAR_KOLOM_IDEAL = 300; // piksel, cukup untuk nama sepanjang tiga kata

  /**
   * Kolom hanya bertambah kalau memang dibutuhkan.
   *
   * Dihitung dari berapa baris yang muat dalam tinggi papan, lalu jumlah nama
   * dibagi kapasitas itu. Dua puluh nama cukup dua kolom dan papan tidak
   * melebar sia-sia; tiga ratus nama akan memakai kolom sebanyak yang muat.
   */
  function hitungKolom(ruang) {
    const jumlahNama = kartuNama().length;
    if (!jumlahNama) return 1;

    const muatMelebar = Math.max(1, Math.floor(ruang / LEBAR_KOLOM_IDEAL));

    if (kolomAtur !== 'auto') return Math.max(1, Math.min(10, kolomAtur));

    const tinggiBaris = hurufMin * 1.25 + 20;
    const barisMuat = Math.max(1, Math.floor(daftar.clientHeight / tinggiBaris));

    return Math.max(1, Math.min(10, muatMelebar, Math.ceil(jumlahNama / barisMuat)));
  }

  function aturKolomDaftar() {
    if (!daftar) return;

    // Lebar tersedia diukur tanpa batas dulu, supaya angka yang dipakai
    // adalah ruang sebenarnya dan bukan batas dari perhitungan sebelumnya.
    daftar.style.maxWidth = 'none';
    const ruang = daftar.clientWidth;

    const n = hitungKolom(ruang);
    daftar.style.gridTemplateColumns = `repeat(${n},minmax(0,1fr))`;
    daftar.style.maxWidth = Math.min(ruang, Math.max(320, n * LEBAR_KOLOM_IDEAL)) + 'px';
  }

  /**
   * Kecilkan huruf sampai seluruh nama muat dalam tinggi papan.
   * Halaman undian tidak boleh punya scroll: kalau ada nama yang harus
   * digulir, nama itu tidak pernah terlihat penonton.
   */
  function paskanDaftar() {
    if (!daftar || !kartuNama().length) return;

    hentikanGulir();
    aturKolomDaftar();

    // Mode 'gulir' berhenti mengecilkan huruf di batas yang masih terbaca dari
    // jauh, lalu sisanya ditangani dengan menggulir papan. Mode 'kecilkan'
    // memaksa semuanya muat sekaligus.
    const batasBawah = gayaLuber === 'kecilkan' ? 7 : hurufMin;

    let ukuran = 26;
    daftar.style.fontSize = ukuran + 'px';

    while (ukuran > batasBawah && daftar.scrollHeight > daftar.clientHeight) {
      ukuran -= ukuran > 14 ? 1 : 0.5;
      daftar.style.fontSize = ukuran + 'px';
    }

    aturPetunjuk();
    if (gayaLuber === 'gulir') mulaiGulir();   // 'gulir_manual' hanya menyisakan scrollbar
  }

  const meluber = () => daftar && daftar.scrollHeight > daftar.clientHeight + 2;

  function aturPetunjuk() {
    const petunjuk = document.getElementById('petunjukGulir');
    if (!petunjuk) return;

    petunjuk.hidden = !(gayaLuber !== 'kecilkan' && meluber());
    petunjuk.textContent = gayaLuber === 'gulir'
      ? 'Daftar bergulir otomatis · arahkan kursor ke daftar untuk menggulir sendiri'
      : 'Daftar bisa digulir untuk melihat nama selanjutnya';
  }

  /* ---------- gulir otomatis ---------- */

  let bingkaiGulir = null, posisiGulir = 0, arahGulir = 1, jedaGulir = 0;

  function hentikanGulir() {
    if (bingkaiGulir) cancelAnimationFrame(bingkaiGulir);
    bingkaiGulir = null;
  }

  /**
   * Papan bergulir pelan turun lalu naik lagi, berhenti sejenak di tiap ujung.
   * Bolak-balik dipilih daripada lompat ke atas karena lompatan membuat
   * penonton kehilangan posisi bacaannya.
   */
  function mulaiGulir() {
    hentikanGulir();
    if (!meluber()) return;

    const laju = 22; // piksel per detik
    let sebelumnya = performance.now();
    posisiGulir = daftar.scrollTop;
    jedaGulir = 2;

    const langkah = (waktu) => {
      const selang = Math.min(0.05, (waktu - sebelumnya) / 1000);
      sebelumnya = waktu;

      const batas = daftar.scrollHeight - daftar.clientHeight;

      if (jedaGulir > 0) {
        jedaGulir -= selang;
      } else {
        posisiGulir += laju * selang * arahGulir;

        if (posisiGulir >= batas) { posisiGulir = batas; arahGulir = -1; jedaGulir = 2; }
        else if (posisiGulir <= 0) { posisiGulir = 0; arahGulir = 1; jedaGulir = 2; }

        daftar.scrollTop = posisiGulir;
      }

      bingkaiGulir = requestAnimationFrame(langkah);
    };

    bingkaiGulir = requestAnimationFrame(langkah);
  }

  /** Bawa nama pemenang ke tengah layar dan hentikan gulir otomatis. */
  function bawaKeTengah(el) {
    hentikanGulir();
    if (!el || !meluber()) return;
    el.scrollIntoView({ block: 'center', behavior: 'smooth' });
    posisiGulir = daftar.scrollTop;
  }

  function sorotAcak() {
    const semua = kartuNama();
    if (!semua.length) return;
    if (aktif) aktif.classList.remove('sorot');
    aktif = acak(semua);
    aktif.classList.add('sorot');
  }

  /* ---------- putaran ---------- */

  function bisaMulai() {
    return peserta.length > 0;
  }

  function mulaiPutar() {
    if (berputar || !bisaMulai()) return;
    berputar = true;
    mulai.disabled = true;
    berhenti.disabled = false;
    jumlah.disabled = true; // dikunci supaya jumlah yang dipesan sama dengan yang tampil

    Suara.mulai();
    hentikanGulir();

    if (saatUndi === 'start') {
      hasilTertunda = mintaHasil().catch(() => ({ ok: false, message: 'Koneksi ke server terputus. Periksa jaringan lalu ulangi undian.' }));
    }

    if (mode === 'kosong') {
      const n = Math.min(Number(jumlah.value) || 1, peserta.length);
      gambarSlot(n);
      timer = setInterval(() => {
        semuaSlot().forEach(slot => isiSlot(slot, acak(peserta), 'berputar', kaburDari(60)));
      }, 60);
    } else {
      timer = setInterval(sorotAcak, 70);
    }

    if (autoStop) {
      timerAuto = setTimeout(() => { if (berputar) hentikan(); }, durasi);
    }
  }

  async function hentikan() {
    if (!berputar) return;
    berputar = false;
    berhenti.disabled = true;
    clearTimeout(timerAuto);

    // Putaran sengaja dibiarkan jalan selama menunggu jawaban server supaya
    // layar tidak membeku sesaat, yang justru terlihat mencurigakan.
    try {
      // Kalau hasil sudah dipesan sejak Start, ini selesai seketika.
      const data = await (hasilTertunda || mintaHasil());
      hasilTertunda = null;
      clearInterval(timer);

      if (!data.ok) { tampilkanGalat(data.message || 'Undian gagal dijalankan.'); return; }

      if (mode === 'kosong') {
        if (gayaStop === 'melambat') await mendarat(data.winners);
        else bukaSlot(data.winners);
      } else {
        if (gayaStop === 'melambat') await sorotPemenang(data.winners);
        else sorotLangsung(data.winners);
      }

      Suara.berhenti();
      Suara.menang();

      if (mode === 'kosong') selesai(data.winners, data.remaining);
      else umumkan(data.winners, data.remaining);
    } catch (e) {
      clearInterval(timer);
      hasilTertunda = null;
      tampilkanGalat('Koneksi ke server terputus. Periksa jaringan lalu ulangi undian.');
    }
  }

  /** Semua kolom berhenti dan terbuka serentak di nama pemenang. */
  function bukaSlot(pemenang) {
    if (semuaSlot().length !== pemenang.length) gambarSlot(pemenang.length);
    semuaSlot().forEach((kolom, i) => {
      if (pemenang[i]) isiSlot(kolom, pemenang[i], 'menang', 0);
    });
  }

  /**
   * Perlambat putaran sampai berhenti tepat di nama pemenang.
   *
   * Ini yang membuat undian tidak terlihat seperti settingan: nama tidak
   * melompat begitu saja, tetapi melambat lalu mendarat di pemenang persis
   * seperti gerak mesin undian yang kehabisan momentum.
   */
  function mendarat(pemenang) {
    return new Promise(resolve => {
      let jeda = 60;

      const putar = () => {
        if (jeda > 330) {
          bukaSlot(pemenang);
          resolve();
          return;
        }
        semuaSlot().forEach(slot => isiSlot(slot, acak(peserta), 'berputar', kaburDari(jeda)));
        jeda *= 1.3;
        setTimeout(putar, jeda);
      };

      putar();
    });
  }

  /** Sorotan langsung pindah ke pemenang begitu Stop ditekan. */
  function sorotLangsung(pemenang) {
    if (aktif) aktif.classList.remove('sorot');
    const target = daftar.querySelector(`.nama[data-id="${pemenang[0]?.id}"]`);
    if (target) { target.classList.add('sorot'); aktif = target; bawaKeTengah(target); }
  }

  /** Perlambat sorotan lalu berhenti tepat di nama pemenang pertama. */
  function sorotPemenang(pemenang) {
    return new Promise(resolve => {
      const target = daftar.querySelector(`.nama[data-id="${pemenang[0]?.id}"]`);
      let jeda = 80, langkah = 0;

      const putar = () => {
        sorotAcak();
        langkah++;
        jeda += 18;
        if (langkah < 14) {
          setTimeout(putar, jeda);
        } else {
          if (aktif) aktif.classList.remove('sorot');
          if (target) { target.classList.add('sorot'); aktif = target; bawaKeTengah(target); }
          setTimeout(resolve, 500);
        }
      };
      putar();
    });
  }

  function umumkan(pemenang, sisa) {
    const daftarNama = pemenang.map(w =>
      `<div class="menang">${w.name}${keterangan(w) ? `<small>${keterangan(w)}</small>` : ''}</div>`
    ).join('');

    kartu.innerHTML = `
      <h2>Selamat kepada</h2>
      ${daftarNama}
      <p>Pemenang ${namaHadiah}. Sisa hadiah: ${sisa}.</p>
      <button class="tombol" id="tutup">Lanjut</button>`;
    tirai.classList.add('tampil');
    const tutup = document.getElementById('tutup');
    tutup.focus();
    tutup.onclick = () => { tirai.classList.remove('tampil'); selesai(pemenang, sisa); };
  }

  function tampilkanGalat(pesan) {
    Suara.berhenti();
    kartu.innerHTML = `<div class="galat">${pesan}</div><button class="tombol" id="tutup">Tutup</button>`;
    tirai.classList.add('tampil');
    document.getElementById('tutup').onclick = () => {
      tirai.classList.remove('tampil');
      mulai.disabled = !bisaMulai();
      jumlah.disabled = false;
    };
  }

  /** Pemenang dikeluarkan dari kumpulan supaya tidak terundi dua kali. */
  function selesai(pemenang, sisa) {
    const idMenang = pemenang.map(w => w.id);
    peserta = peserta.filter(p => !idMenang.includes(p.id));
    idMenang.forEach(id => daftar?.querySelector(`.nama[data-id="${id}"]`)?.remove());
    paskanDaftar();
    aktif = null;
    jumlah.disabled = false;

    sisaEl.textContent = sisa < 1
      ? 'Semua hadiah ini sudah habis diundi'
      : 'Sisa hadiah: ' + sisa + ' dari ' + kuota;

    if (sisa < 1) {
      mulai.disabled = true;
      return;
    }

    jumlah.max = sisa;
    if (Number(jumlah.value) > sisa) jumlah.value = sisa;
    mulai.disabled = !bisaMulai();
  }

  /* ---------- pemasangan ---------- */

  let tundaPas = null;
  window.addEventListener('resize', () => {
    clearTimeout(tundaPas);
    tundaPas = setTimeout(() => {
      paskanDaftar();
      if (mode === 'kosong' && !berputar) {
        gambarSlot(Math.min(Number(jumlah.value) || 1, Math.max(1, peserta.length)));
      }
    }, 150);
  });

  if (mode === 'kosong') {
    gambarSlot(Math.min(Number(jumlah.value) || 1, Math.max(1, peserta.length)));
    jumlah.addEventListener('change', () => {
      if (!berputar) gambarSlot(Math.min(Number(jumlah.value) || 1, Math.max(1, peserta.length)));
    });
  }

  if (daftar) {
    daftar.addEventListener('pointerenter', hentikanGulir);
    daftar.addEventListener('wheel', hentikanGulir, { passive: true });
    daftar.addEventListener('pointerleave', () => {
      if (gayaLuber === 'gulir' && !berputar) mulaiGulir();
    });
  }

  paskanDaftar();
  mulai.disabled = !bisaMulai();

  mulai.addEventListener('click', mulaiPutar);
  berhenti.addEventListener('click', hentikan);

  // Spasi = start/stop, supaya pembawa acara tidak perlu mencari kursor
  document.addEventListener('keydown', e => {
    if (e.code !== 'Space') return;
    if (['INPUT', 'BUTTON', 'A', 'SELECT'].includes(e.target.tagName)) return;
    if (document.getElementById('menuPojok')?.classList.contains('buka')) return;
    e.preventDefault();
    berputar ? hentikan() : mulaiPutar();
  });
})();
</script>
@endif
</body>
</html>
