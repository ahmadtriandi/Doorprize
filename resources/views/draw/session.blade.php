@php
  $tulis = function ($nama) use ($settings) {
      return match ($settings['case']) {
          'kapital' => mb_strtoupper($nama, 'UTF-8'),
          'awal'    => mb_convert_case(mb_strtolower($nama, 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),
          default   => $nama,
      };
  };

  $urlUndi   = route('draw.session.spin', $sesi);
  $totalKotak = 0;
  $kosong     = 0;
  foreach ($kelompok as $b) {
      foreach ($b['prizes'] as $p) {
          $totalKotak += $p['quota'];
          $kosong     += max(0, $p['quota'] - count($p['winners']));
      }
  }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $sesi->name }} — {{ $settings['title'] }}</title>
<style>
  :root{
    --ungu:#8B5CF6; --sian:#22D3EE; --pink:#F472B6; --kuning:#FBBF24;
    --krem:#FDF4FF; --jingga:#FB7185; --nila:#1E1B4B;
    --kepala-hadiah:#2E1065; --sel-a:#F5F3FF; --sel-b:#EDE9FE;
  }
  *{box-sizing:border-box;margin:0;padding:0}
  html,body{height:100%}
  body{
    font-family:'Trebuchet MS','Segoe UI',system-ui,sans-serif;
    background:{{ $settings['bg_color'] ?: '#1E1B4B' }};
    @if($settings['background'])
      background-image:url('{{ asset('storage/'.$settings['background']) }}');
      background-size:cover;background-position:center;background-attachment:fixed;
    @endif
    color:#fff;overflow:hidden;
  }

  .panggung{
    height:100vh;display:grid;grid-template-rows:auto minmax(0,1fr) auto;
    gap:10px;padding:14px 26px 16px;
  }

  /* --- kepala --- */
  .kepala{display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:20px}
  .kepala .kiri{justify-self:start}
  .kepala .kanan{justify-self:end}
  .logo{max-height:60px;max-width:220px;object-fit:contain}
  .judul-sesi{
    font-size:clamp(28px,4.4vh,54px);font-weight:800;letter-spacing:.02em;line-height:1;
    text-shadow:0 3px 10px rgba(0,0,0,.45);text-align:center;
  }

  /* --- papan sesi ---
     Setiap baris kelompok mendapat tinggi sebanding jumlah barisnya, dan setiap
     hadiah mendapat lebar sebanding jumlah kolomnya, sehingga seluruh tabel
     selalu pas satu layar tanpa digulir. */
  .papan{display:flex;flex-direction:column;gap:12px;min-height:0}
  .baris-sesi{
    display:grid;grid-template-columns:clamp(90px,8vw,150px) minmax(0,1fr);
    min-height:0;border-radius:10px;overflow:hidden;
    box-shadow:0 6px 18px rgba(0,0,0,.35);
  }
  .label-baris{
    background:var(--kepala-hadiah);display:grid;place-items:center;
    font-weight:800;color:var(--kuning);text-align:center;padding:6px;
    font-size:clamp(16px,2.4vh,32px);line-height:1.1;
    border-right:2px solid rgba(255,255,255,.15);
  }
  .daftar-hadiah{display:flex;min-width:0;min-height:0;background:rgba(46,16,101,.55)}

  .hadiah-sesi{
    display:grid;min-width:0;min-height:0;
    border-left:2px solid rgba(255,255,255,.18);
  }
  .hadiah-sesi:first-child{border-left:0}
  .nama-hadiah{
    background:var(--kepala-hadiah);color:#fff;font-weight:800;text-align:center;
    display:grid;place-items:center;padding:2px 8px;overflow:hidden;
    font-size:var(--huruf-judul,18px);line-height:1.12;
    border-bottom:2px solid var(--kuning);
  }
  .sel-grid{display:grid;grid-auto-flow:column;min-height:0}

  .sel{
    display:flex;align-items:center;gap:.45em;min-width:0;overflow:hidden;
    padding:0 .6em;font-size:var(--huruf,16px);line-height:1.1;
    color:#3B0764;background:var(--sel-a);
    border-bottom:1px solid rgba(76,29,149,.14);border-right:1px solid rgba(76,29,149,.10);
  }
  .sel:nth-child(even){background:var(--sel-b)}
  .sel .no{flex-shrink:0;font-weight:700;opacity:.55;min-width:1.6em}
  /* Nama boleh turun ke baris kedua bila kotaknya cukup tinggi, supaya nama
     panjang tidak harus dikecilkan sampai sulit dibaca dari kursi belakang. */
  .sel .nm{
    flex:1;min-width:0;max-height:100%;overflow:hidden;font-weight:700;
    white-space:normal;overflow-wrap:anywhere;line-height:1.05;
  }
  .sel.putar .nm{color:#7C3AED;filter:blur(1.6px)}
  .sel.terisi{background:var(--krem)}
  .sel.terisi .no{opacity:.8;color:#BE185D}
  .sel.baru{background:#FEF3C7;animation:muncul .5s ease-out}
  @keyframes muncul{from{background:var(--kuning)}to{background:#FEF3C7}}

  /* --- kendali --- */
  .kendali{display:flex;justify-content:center;align-items:center;gap:14px}
  .tombol{
    font-family:inherit;font-size:19px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;
    padding:9px 38px;border:3px solid #92400E;border-radius:6px;cursor:pointer;
    background:var(--kuning);color:#7C2D12;box-shadow:0 5px 0 #92400E;
  }
  .tombol:active{transform:translateY(3px);box-shadow:0 2px 0 #92400E}
  .tombol[disabled]{opacity:.45;cursor:not-allowed}
  .tombol.stop{background:var(--jingga);color:#fff;border-color:#9F1239;box-shadow:0 5px 0 #9F1239}
  .sisa{font-size:14px;font-weight:700;background:rgba(0,0,0,.35);padding:6px 14px;border-radius:20px}

  .kabar{
    position:fixed;left:50%;bottom:84px;transform:translateX(-50%);z-index:40;max-width:min(760px,90vw);
    background:rgba(220,38,38,.94);color:#fff;padding:10px 18px;border-radius:10px;
    font-size:14px;font-weight:600;box-shadow:0 8px 20px rgba(0,0,0,.35);display:none;
  }
  .kabar.tampil{display:block}
  .kabar.info{background:rgba(30,27,75,.94);border:1px solid var(--kuning)}

  .kosong-sesi{
    margin:auto;max-width:620px;text-align:center;font-size:18px;line-height:1.5;
    background:rgba(0,0,0,.35);padding:30px;border-radius:14px;
  }

  /* --- menu pojok --- */
  .menu-pojok{position:fixed;left:18px;bottom:16px;z-index:30}
  .menu-tombol{
    width:44px;height:44px;display:grid;place-content:center;gap:5px;
    background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.4);
    border-radius:12px;cursor:pointer;opacity:.5;
  }
  .menu-tombol:hover,.menu-pojok.buka .menu-tombol{opacity:1;background:rgba(255,255,255,.3)}
  .menu-tombol i{display:block;width:22px;height:2.5px;border-radius:2px;background:#fff}
  .menu-isi{
    position:absolute;left:0;bottom:54px;width:300px;display:none;
    background:rgba(30,27,75,.92);border:1px solid rgba(255,255,255,.28);border-radius:14px;
    padding:12px;max-height:70vh;overflow:auto;
  }
  .menu-pojok.buka .menu-isi{display:block}
  .menu-isi h3{font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#C4B5FD;margin:0 4px 8px}
  .menu-isi a,.menu-isi button{
    display:block;width:100%;text-align:left;background:none;border:0;color:#fff;
    font:inherit;font-size:14px;padding:8px 10px;border-radius:8px;cursor:pointer;text-decoration:none;
  }
  .menu-isi a:hover,.menu-isi button:hover{background:rgba(255,255,255,.14)}
  .menu-isi a.kini{background:rgba(251,191,36,.2);border-left:3px solid var(--kuning)}
  .menu-isi .pisah{height:1px;background:rgba(255,255,255,.18);margin:10px 4px}

  @media (prefers-reduced-motion:reduce){.sel.baru{animation:none}}
</style>
</head>
<body>
<div class="panggung">

  <header class="kepala">
    <div class="kiri">
      @if($settings['logo_left'])<img class="logo" src="{{ asset('storage/'.$settings['logo_left']) }}" alt="">@endif
    </div>
    <div class="judul-sesi">{{ $sesi->name }}</div>
    <div class="kanan">
      @if($settings['logo_right'])<img class="logo" src="{{ asset('storage/'.$settings['logo_right']) }}" alt="">@endif
    </div>
  </header>

  <main class="papan" id="papan">
    @forelse($kelompok as $b)
      @php $tinggi = max(1, collect($b['prizes'])->max('rows')); @endphp
      <section class="baris-sesi" style="flex:{{ $tinggi + 1.2 }} 1 0">
        <div class="label-baris">{{ $b['label'] }}</div>
        <div class="daftar-hadiah">
          @foreach($b['prizes'] as $p)
            <div class="hadiah-sesi" style="flex:{{ $p['subcols'] }} 1 0;grid-template-rows:minmax(0,1.2fr) minmax(0,{{ $tinggi }}fr)">
              <div class="nama-hadiah"><span>{{ $p['name'] }}</span></div>
              <div class="sel-grid" style="grid-template-columns:repeat({{ $p['subcols'] }},minmax(0,1fr));grid-template-rows:repeat({{ $tinggi }},minmax(0,1fr))">
                @for($i = 0; $i < $p['quota']; $i++)
                  @php $w = $p['winners'][$i] ?? null; @endphp
                  <div class="sel {{ $w ? 'terisi' : '' }}" data-hadiah="{{ $p['id'] }}" data-urut="{{ $i }}">
                    <span class="no">{{ $i + 1 }}.</span>
                    <span class="nm">{{ $w ? $tulis($w['name']) : '' }}</span>
                  </div>
                @endfor
              </div>
            </div>
          @endforeach
        </div>
      </section>
    @empty
      <div class="kosong-sesi">
        Sesi ini belum berisi hadiah. Atur lewat dashboard panitia → Sesi undian,
        lalu centang hadiah yang masuk sesi ini.
      </div>
    @endforelse
  </main>

  <div class="kendali">
    <span class="sisa" id="sisa">{{ $kosong }} dari {{ $totalKotak }} kotak belum terisi</span>
    <button class="tombol" id="mulai" @disabled($kosong < 1)>Start</button>
    <button class="tombol stop" id="berhenti" disabled>Stop</button>
  </div>
</div>

<div class="kabar" id="kabar"></div>

<div class="menu-pojok" id="menuPojok">
  <button class="menu-tombol" id="menuTombol" aria-label="Buka menu layar"><i></i><i></i><i></i></button>
  <div class="menu-isi">
    <h3>Sesi undian</h3>
    @foreach($sessions as $s)
      <a href="{{ route('draw.session', $s) }}" class="{{ $s->id === $sesi->id ? 'kini' : '' }}">{{ $s->name }}</a>
    @endforeach
    <div class="pisah"></div>
    <a href="{{ route('draw') }}">Layar undian per hadiah</a>
    <a href="{{ route('draw.winners') }}">Daftar pemenang</a>
    <button type="button" id="layarPenuh">Layar penuh</button>
    <button type="button" onclick="location.reload()">Muat ulang</button>
    @if(session('doorprize_admin'))
      <a href="{{ route('admin.sessions.show', $sesi) }}">Atur sesi ini</a>
    @endif
  </div>
</div>

<script>
(() => {
  const url      = @json($urlUndi);
  const token    = document.querySelector('meta[name=csrf-token]').content;
  const pool     = @json($pool);
  const gayaNama = @json($settings['case']);
  const saatUndi = @json($settings['draw_moment']);
  const nyalaSuara   = {{ $settings['sound'] ? 'true' : 'false' }};
  const berkasPutar  = @json($settings['spin_sound']);
  const berkasMenang = @json($settings['win_sound']);

  const papan    = document.getElementById('papan');
  const mulai    = document.getElementById('mulai');
  const berhenti = document.getElementById('berhenti');
  const sisaEl   = document.getElementById('sisa');
  const kabar    = document.getElementById('kabar');

  const semuaSel = () => [...papan.querySelectorAll('.sel')];
  const selKosong = () => semuaSel().filter(s => !s.classList.contains('terisi'));

  function tulisNama(nama) {
    if (gayaNama === 'kapital') return nama.toUpperCase();
    if (gayaNama === 'awal') {
      return nama.toLowerCase().replace(/(^|[\s'-])(\p{L})/gu, (_, a, b) => a + b.toUpperCase());
    }
    return nama;
  }

  function tampilkanKabar(pesan, info = false) {
    kabar.textContent = pesan;
    kabar.className = 'kabar tampil' + (info ? ' info' : '');
    clearTimeout(kabar._t);
    kabar._t = setTimeout(() => kabar.className = 'kabar', 7000);
  }

  /* ---------- ukuran huruf ----------
     Ukuran dasar dihitung dari tinggi kotak terkecil, lalu tiap kotak dan
     judul hadiah dikecilkan sendiri kalau isinya masih kepanjangan. */

  function aturUkuran() {
    const sel = semuaSel();
    if (!sel.length) return;

    const tinggi = Math.min(...sel.map(s => s.clientHeight));
    papan.style.setProperty('--huruf', Math.max(10, Math.min(26, tinggi * 0.56)) + 'px');

    const judul = [...papan.querySelectorAll('.nama-hadiah')];
    const tinggiJudul = Math.min(...judul.map(j => j.clientHeight));
    papan.style.setProperty('--huruf-judul', Math.max(11, Math.min(26, tinggiJudul * 0.48)) + 'px');

    judul.forEach(paskanJudul);
    papan.querySelectorAll('.baris-sesi').forEach(paskanBaris);
  }

  /**
   * Semua nama dalam satu baris kelompok memakai ukuran huruf yang sama,
   * mengikuti nama yang paling sulit muat. Ukuran per kotak membuat tabel
   * terlihat acak: nama pendek raksasa di sebelah nama panjang yang kecil.
   */
  function paskanBaris(baris) {
    const sel = [...baris.querySelectorAll('.sel')];
    sel.forEach(s => s.style.fontSize = '');
    if (!sel.length) return;

    const meluber = () => sel.some(s => {
      const nm = s.querySelector('.nm');
      return nm.textContent
        && (nm.scrollHeight > nm.clientHeight + 1 || nm.scrollWidth > nm.clientWidth + 1);
    });

    let ukuran = parseFloat(getComputedStyle(sel[0]).fontSize);
    let n = 0;
    while (meluber() && ukuran > 9 && n++ < 40) {
      ukuran -= 1;
      sel.forEach(s => s.style.fontSize = ukuran + 'px');
    }
  }

  function paskanJudul(el) {
    el.style.fontSize = '';
    let ukuran = parseFloat(getComputedStyle(el).fontSize);
    let n = 0;
    while ((el.scrollHeight > el.clientHeight || el.scrollWidth > el.clientWidth) && ukuran > 9 && n++ < 30) {
      ukuran -= 1;
      el.style.fontSize = ukuran + 'px';
    }
  }

  /* ---------- suara ---------- */

  const Suara = (() => {
    const audioPutar  = berkasPutar ? new Audio(berkasPutar) : null;
    const audioMenang = berkasMenang ? new Audio(berkasMenang) : null;
    if (audioPutar) audioPutar.loop = true;
    let ctx = null, simpul = [];
    const bisu = () => localStorage.getItem('doorprize.bisu') === '1';
    const hidup = () => nyalaSuara && !bisu();
    const konteks = () => {
      if (!ctx) ctx = new (window.AudioContext || window.webkitAudioContext)();
      if (ctx.state === 'suspended') ctx.resume();
      return ctx;
    };

    return {
      mulai() {
        if (!hidup()) return;
        if (audioPutar) { audioPutar.currentTime = 0; audioPutar.play().catch(() => {}); return; }
        try {
          const c = konteks(), t = c.currentTime;
          const keluar = c.createGain();
          keluar.gain.setValueAtTime(0, t);
          keluar.gain.linearRampToValueAtTime(.22, t + .4);
          keluar.connect(c.destination);
          const saring = c.createBiquadFilter();
          saring.type = 'lowpass'; saring.frequency.value = 600; saring.connect(keluar);
          const dengung = c.createOscillator();
          dengung.type = 'sawtooth'; dengung.frequency.value = 55; dengung.connect(saring); dengung.start(t);
          const naik = c.createOscillator(), ng = c.createGain();
          naik.type = 'triangle';
          naik.frequency.setValueAtTime(180, t);
          naik.frequency.exponentialRampToValueAtTime(720, t + 18);
          ng.gain.value = .05; naik.connect(ng).connect(saring); naik.start(t);
          const denyut = c.createOscillator(), dg = c.createGain();
          denyut.frequency.value = 9; dg.gain.value = .5;
          denyut.connect(dg).connect(keluar.gain); denyut.start(t);
          simpul = [dengung, naik, denyut];
        } catch (e) {}
      },
      berhenti() {
        if (audioPutar) { audioPutar.pause(); audioPutar.currentTime = 0; }
        simpul.forEach(n => { try { n.stop(); } catch (e) {} });
        simpul = [];
      },
      menang() {
        if (!hidup()) return;
        if (audioMenang) { audioMenang.currentTime = 0; audioMenang.play().catch(() => {}); return; }
        try {
          const c = konteks(), t = c.currentTime;
          [1046.5, 1318.5, 1568, 2093, 2637].forEach((hz, i) => {
            const o = c.createOscillator(), g = c.createGain(), m = t + i * .045;
            o.frequency.value = hz;
            g.gain.setValueAtTime(0, m);
            g.gain.linearRampToValueAtTime(.28 / (i * .5 + 1), m + .01);
            g.gain.exponentialRampToValueAtTime(.0001, m + 2.4);
            o.connect(g).connect(c.destination); o.start(m); o.stop(m + 2.5);
          });
        } catch (e) {}
      },
    };
  })();

  /* ---------- putaran ---------- */

  let berputar = false, timer = null, pesanan = null;

  const acak = () => pool[Math.floor(Math.random() * pool.length)];

  function mintaHasil() {
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
      body: '{}'
    }).then(r => r.json()).catch(() => ({ ok: false, message: 'Koneksi ke server terputus. Periksa jaringan lalu ulangi.' }));
  }

  function perbaruiSisa() {
    const total = semuaSel().length, kosong = selKosong().length;
    sisaEl.textContent = kosong < 1
      ? 'Semua kotak di sesi ini sudah terisi'
      : `${kosong} dari ${total} kotak belum terisi`;
    mulai.disabled = kosong < 1 || !pool.length;
  }

  function mulaiPutar() {
    if (berputar) return;
    const kosong = selKosong();
    if (!kosong.length) return;
    if (!pool.length) { tampilkanKabar('Tidak ada peserta yang bisa diundi untuk sesi ini.'); return; }

    berputar = true;
    mulai.disabled = true;
    berhenti.disabled = false;
    Suara.mulai();

    // Hasil dipesan sejak Start supaya nama keluar seketika saat Stop.
    // Server tetap yang mengundi; waktu penekanan Stop tidak mengubah hasil.
    if (saatUndi !== 'stop') pesanan = mintaHasil();

    kosong.forEach(s => s.classList.add('putar'));
    timer = setInterval(() => {
      kosong.forEach(s => { s.querySelector('.nm').textContent = tulisNama(acak().name); });
    }, 70);
  }

  async function hentikan() {
    if (!berputar) return;
    berputar = false;
    berhenti.disabled = true;

    const data = await (pesanan || mintaHasil());
    pesanan = null;
    clearInterval(timer);
    Suara.berhenti();

    const sedangPutar = [...papan.querySelectorAll('.sel.putar')];
    sedangPutar.forEach(s => { s.classList.remove('putar'); s.querySelector('.nm').textContent = ''; });

    if (!data.ok) {
      tampilkanKabar(data.message || 'Undian gagal dijalankan.');
      perbaruiSisa();
      return;
    }

    data.prizes.forEach(hadiah => {
      const kotak = selKosong().filter(s => s.dataset.hadiah === String(hadiah.id));
      hadiah.winners.forEach((w, i) => {
        const s = kotak[i];
        if (!s) return;
        s.querySelector('.nm').textContent = tulisNama(w.name);
        s.title = [w.name, w.nip, w.unit].filter(Boolean).join(' · ');
        s.classList.add('terisi', 'baru');
      });
    });

    papan.querySelectorAll('.baris-sesi').forEach(paskanBaris);

    Suara.menang();
    perbaruiSisa();

    if (data.warnings && data.warnings.length) {
      tampilkanKabar('Sebagian kotak tidak terisi — ' + data.warnings.join(' · '), true);
    }
  }

  mulai.addEventListener('click', mulaiPutar);
  berhenti.addEventListener('click', hentikan);

  document.addEventListener('keydown', e => {
    if (e.code !== 'Space') return;
    if (['INPUT', 'BUTTON', 'A', 'SELECT'].includes(e.target.tagName)) return;
    if (document.getElementById('menuPojok').classList.contains('buka')) return;
    e.preventDefault();
    berputar ? hentikan() : mulaiPutar();
  });

  /* ---------- menu & tata ulang ---------- */

  const pojok = document.getElementById('menuPojok');
  document.getElementById('menuTombol').addEventListener('click', () => pojok.classList.toggle('buka'));
  document.addEventListener('click', e => { if (!pojok.contains(e.target)) pojok.classList.remove('buka'); });
  document.getElementById('layarPenuh').addEventListener('click', () => {
    document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen();
    pojok.classList.remove('buka');
  });

  let ukuranJendela = innerWidth + 'x' + innerHeight, tunda = null;
  addEventListener('resize', () => {
    const kini = innerWidth + 'x' + innerHeight;
    if (kini === ukuranJendela) return;
    ukuranJendela = kini;
    clearTimeout(tunda);
    tunda = setTimeout(aturUkuran, 150);
  });

  aturUkuran();
  perbaruiSisa();
})();
</script>
</body>
</html>
