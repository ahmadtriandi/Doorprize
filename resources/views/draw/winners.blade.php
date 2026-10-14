@php
  $jumlah = $winners->count();

  // Kolom hanya bertambah kalau daftarnya panjang, supaya nama tetap besar
  // saat pemenangnya sedikit.
  $kolom = $jumlah <= 8 ? 1 : ($jumlah <= 20 ? 2 : ($jumlah <= 40 ? 3 : 4));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pemenang — {{ $settings['title'] }}</title>
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

  /* Hadiah selalu di kiri dan daftar pemenang di kanan; pada layar sempit
     keduanya menumpuk supaya tetap terbaca di laptop panitia. */
  .panggung{
    position:relative;z-index:2;height:100vh;padding:18px 26px 22px;
    display:grid;gap:14px 30px;
    grid-template-columns:minmax(0,760px) minmax(0,1fr);
    grid-template-rows:auto 1fr;
    grid-template-areas:"kepala kepala" "hadiah daftar";
  }
  @media (max-width:900px){
    .panggung{
      grid-template-columns:1fr;grid-template-rows:auto auto 1fr;
      grid-template-areas:"kepala" "hadiah" "daftar";
    }
  }

  .kepala{grid-area:kepala;display:flex;align-items:flex-start;justify-content:space-between;gap:20px}
  .logo{max-height:64px;max-width:200px;object-fit:contain}
  .judul{
    text-align:center;background:#1E1B4B;border:4px solid var(--sian);border-radius:14px;
    padding:8px 30px;box-shadow:0 8px 0 rgba(0,0,0,.22);
  }
  .judul b{display:block;font-size:26px;line-height:1;color:var(--kuning);letter-spacing:.06em;text-transform:uppercase}
  .judul span{display:block;font-size:13px;color:var(--sian);letter-spacing:.28em;margin-top:4px;text-transform:uppercase}

  /* --- kiri: hadiah --- */
  .hadiah{grid-area:hadiah;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:0}
  .hadiah img{
    max-height:46vh;max-width:100%;background:#fff;border-radius:10px;padding:10px;
    box-shadow:0 10px 0 rgba(0,0,0,.18);object-fit:contain;
  }
  .hadiah .kosong{
    height:34vh;aspect-ratio:1;background:rgba(255,255,255,.5);border:3px dashed #fff;
    border-radius:10px;display:grid;place-items:center;color:#fff;font-size:14px;padding:14px;text-align:center;
  }
  .pita{
    margin-top:16px;width:100%;text-align:center;
    background:linear-gradient(#EDE9FE,#DDD6FE);border:3px solid #fff;border-radius:8px;
    padding:10px 26px;font-size:clamp(18px,2vw,28px);font-weight:800;letter-spacing:.03em;
    text-transform:uppercase;color:#4C1D95;box-shadow:0 6px 0 rgba(0,0,0,.18);
  }
  .cacah{margin-top:10px;font-size:14px;font-weight:700;color:#fff;background:rgba(0,0,0,.28);padding:5px 16px;border-radius:20px}

  /* --- kanan: daftar pemenang --- */
  .kanan{grid-area:daftar;display:flex;flex-direction:column;min-height:0}
  .kanan h2{
    color:#fff;font-size:15px;letter-spacing:.28em;text-transform:uppercase;
    text-align:center;text-shadow:0 2px 4px rgba(0,0,0,.35);margin-bottom:10px;flex-shrink:0;
  }
  .daftar-menang{
    flex:1;min-height:0;display:grid;gap:10px 14px;align-content:start;
    grid-template-columns:repeat({{ $kolom }},minmax(0,1fr));
    overflow-x:hidden;overflow-y:auto;padding-right:6px;
    scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.55) transparent;
  }
  .daftar-menang::-webkit-scrollbar{width:8px}
  .daftar-menang::-webkit-scrollbar-thumb{background:rgba(255,255,255,.55);border-radius:8px}
  .daftar-menang::-webkit-scrollbar-track{background:transparent}

  .menang{
    background:var(--krem);border:3px solid var(--kuning);border-radius:12px;
    padding:10px 16px;display:flex;align-items:center;gap:14px;
    box-shadow:0 4px 0 rgba(0,0,0,.18);
  }
  .menang .nomor{
    flex-shrink:0;width:2em;height:2em;border-radius:50%;background:var(--ungu);color:#fff;
    display:grid;place-items:center;font-weight:800;font-size:.85em;
  }
  .menang .rincian{min-width:0}
  .menang .nama{
    font-size:clamp(17px,{{ $kolom > 2 ? '1.3vw' : '1.9vw' }},{{ $kolom > 2 ? 26 : 34 }}px);
    font-weight:800;color:#4C1D95;line-height:1.2;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
  }
  .menang .sub{font-size:13px;color:#7C3AED;margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .menang.diambil{border-color:#34D399}

  .belum{
    grid-column:1/-1;text-align:center;color:#fff;font-size:18px;
    padding:40px;background:rgba(0,0,0,.2);border-radius:12px;
  }

  /* --- menu pojok kiri bawah --- */
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
    display:block;width:100%;text-align:left;color:#fff;text-decoration:none;
    padding:8px 10px;border-radius:8px;margin-bottom:2px;border-left:3px solid transparent;
  }
  .undian:hover{background:rgba(255,255,255,.16)}
  .undian .nama-undian{display:block;font-size:14px;font-weight:600;line-height:1.25}
  .undian .sisa-undian{display:block;font-size:12px;color:#C4B5FD;margin-top:1px}
  .undian.kini{background:rgba(251,191,36,.2);border-left-color:var(--kuning)}
  .undian.kini .sisa-undian{color:var(--kuning)}
</style>
</head>
<body>
<div class="panggung">

  <header class="kepala">
    <div>
      @if($settings['logo_left'])
        <img class="logo" src="{{ asset('storage/'.$settings['logo_left']) }}" alt="">
      @endif
    </div>

    <div class="judul">
      <b>{{ $settings['title'] }}</b>
      <span>Daftar Pemenang</span>
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
      <div class="cacah">{{ $jumlah }} dari {{ $prize->quota }} hadiah sudah diundi</div>
    </section>

    <section class="kanan">
      <h2>Selamat kepada</h2>

      <div class="daftar-menang">
        @forelse($winners as $i => $w)
          <div class="menang {{ $w->is_claimed ? 'diambil' : '' }}">
            <span class="nomor">{{ $i + 1 }}</span>
            <span class="rincian">
              <span class="nama">{{ $w->participant->name }}</span>
              @php
                $p = $w->participant;
                $ket = match ($settings['caption']) {
                    'nip'      => $p->nip,
                    'unit'     => $p->department,
                    'keduanya' => trim($p->nip.($p->department ? ' · '.$p->department : '')),
                    default    => null,
                };
              @endphp
              @if($ket)<span class="sub">{{ $ket }}</span>@endif
            </span>
          </div>
        @empty
          <div class="belum">Hadiah ini belum diundi.</div>
        @endforelse
      </div>
    </section>
  @else
    <section class="kanan">
      <div class="belum">Belum ada hadiah. Buat hadiah dulu di dashboard panitia.</div>
    </section>
  @endif
</div>

<div class="menu-pojok" id="menuPojok">
  <button class="menu-tombol" id="menuTombol" aria-expanded="false" aria-controls="menuIsi" aria-label="Buka menu layar">
    <i></i><i></i><i></i>
  </button>

  <div class="menu-isi" id="menuIsi" role="menu">
    <h3>Pemenang hadiah</h3>

    @forelse($prizes as $p)
      <a class="undian {{ $p->id === optional($prize)->id ? 'kini' : '' }}"
         href="{{ route('draw.winners', ['hadiah' => $p->id]) }}" role="menuitem">
        <span class="nama-undian">{{ $p->name }}</span>
        <span class="sisa-undian">{{ $p->winners_count }} dari {{ $p->quota }} pemenang</span>
      </a>
    @empty
      <small>Belum ada hadiah.</small>
    @endforelse

    <div class="pisah"></div>

    <a href="{{ route('draw') }}" role="menuitem">Kembali ke layar undian</a>
    <button type="button" id="layarPenuh" role="menuitem">Layar penuh</button>
    <button type="button" onclick="location.reload()" role="menuitem">Muat ulang daftar</button>
    @if(session('doorprize_admin'))
      <a href="{{ route('admin.winners.index') }}" role="menuitem">Kelola pemenang</a>
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
  };

  tombol.addEventListener('click', () => setBuka(!pojok.classList.contains('buka')));
  document.addEventListener('click', e => { if (!pojok.contains(e.target)) setBuka(false); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') setBuka(false); });

  document.getElementById('layarPenuh').addEventListener('click', () => {
    document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen();
    setBuka(false);
  });

  // Halaman ini sering dipasang di layar kedua sementara undian berjalan di
  // layar utama, jadi isinya bisa disegarkan berkala. Penyegaran ditunda selama
  // menu dibuka supaya pilihan panitia tidak hilang di tengah jalan.
  const jeda = {{ (int) $settings['refresh'] }};

  if (jeda > 0) {
    setInterval(() => {
      if (!pojok.classList.contains('buka') && !document.hidden) location.reload();
    }, jeda * 1000);
  }
})();
</script>
</body>
</html>
