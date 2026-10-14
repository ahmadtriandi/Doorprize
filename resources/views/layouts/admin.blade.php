<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('judul', 'Dashboard') — Panitia Doorprize</title>
<style>
  :root{
    --tinta:#241B4A; --tinta-lembut:#6B6390; --garis:#E4E0F2; --kertas:#F7F5FC;
    --ungu:#6D28D9; --ungu-muda:#EDE9FE; --kuning:#F59E0B; --merah:#DC2626; --hijau:#059669;
  }
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Segoe UI',system-ui,-apple-system,sans-serif;background:var(--kertas);color:var(--tinta);font-size:15px;line-height:1.5}
  a{color:var(--ungu)}
  .rangka{display:flex;min-height:100vh}

  /* sisi kiri */
  .sisi{width:230px;flex-shrink:0;background:var(--tinta);color:#fff;padding:22px 0;display:flex;flex-direction:column}
  .merek{padding:0 22px 20px;border-bottom:1px solid rgba(255,255,255,.12)}
  .merek b{font-size:17px;display:block}
  .merek span{font-size:12px;color:#A79FD0}
  .sisi nav{padding:14px 12px;flex:1}
  .sisi nav a{display:block;padding:9px 14px;border-radius:8px;color:#CFC9EA;text-decoration:none;margin-bottom:2px;font-size:14px}
  .sisi nav a:hover{background:rgba(255,255,255,.08);color:#fff}
  .sisi nav a.aktif{background:var(--ungu);color:#fff;font-weight:600}
  .sisi .kaki{padding:14px 22px;border-top:1px solid rgba(255,255,255,.12);font-size:13px}
  .sisi .kaki button{background:none;border:0;color:#F2A2A2;cursor:pointer;font:inherit;padding:0}

  /* isi */
  .isi{flex:1;min-width:0;padding:26px 30px 60px}
  .kepala-halaman{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;margin-bottom:20px}
  .kepala-halaman h1{font-size:22px}
  .kepala-halaman p{color:var(--tinta-lembut);font-size:14px;margin-top:2px}

  /* komponen */
  .kotak{background:#fff;border:1px solid var(--garis);border-radius:12px;padding:20px;margin-bottom:18px}
  .kotak > h2{font-size:15px;margin-bottom:14px}
  .baris{display:flex;gap:18px;flex-wrap:wrap}
  .baris > *{flex:1;min-width:250px}

  label{display:block;font-size:13px;font-weight:600;margin-bottom:5px;color:var(--tinta)}
  input[type=text],input[type=number],input[type=password],input[type=file],select{
    width:100%;padding:8px 11px;border:1px solid var(--garis);border-radius:8px;font:inherit;background:#fff;color:var(--tinta)
  }
  input:focus,select:focus{outline:2px solid var(--ungu);outline-offset:1px;border-color:var(--ungu)}
  .medan{margin-bottom:12px}
  .bantuan{font-size:12px;color:var(--tinta-lembut);margin-top:4px}
  .centang{display:flex;align-items:center;gap:8px;font-size:14px;margin-bottom:10px}
  .centang input{width:16px;height:16px}
  .centang label{margin:0;font-weight:400}

  .tombol{display:inline-block;padding:8px 16px;border:0;border-radius:8px;background:var(--ungu);color:#fff;font:inherit;font-weight:600;cursor:pointer;text-decoration:none}
  .tombol:hover{filter:brightness(1.08)}
  .tombol.garis{background:#fff;color:var(--ungu);border:1px solid var(--ungu)}
  .tombol.bahaya{background:var(--merah)}
  .tombol.netral{background:#EDEAF6;color:var(--tinta)}
  .tombol.kecil{padding:5px 10px;font-size:13px}

  table{width:100%;border-collapse:collapse;font-size:14px}
  th,td{text-align:left;padding:9px 10px;border-bottom:1px solid var(--garis);vertical-align:middle}
  th{font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:var(--tinta-lembut)}
  tbody tr:hover{background:#FAF9FE}

  .lencana{display:inline-block;padding:2px 9px;border-radius:20px;font-size:12px;font-weight:600;background:var(--ungu-muda);color:var(--ungu)}
  .lencana.abu{background:#EEE;color:#666}
  .lencana.hijau{background:#D1FAE5;color:var(--hijau)}
  .lencana.kuning{background:#FEF3C7;color:#92400E}

  .kabar{padding:11px 15px;border-radius:9px;margin-bottom:16px;font-size:14px}
  .kabar.sukses{background:#D1FAE5;color:#065F46}
  .kabar.gagal{background:#FEE2E2;color:#991B1B}
  .kabar ul{margin:6px 0 0 18px}
  .galat-medan{color:var(--merah);font-size:12px;margin-top:4px}

  .angka{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:20px}
  .angka div{background:#fff;border:1px solid var(--garis);border-radius:12px;padding:16px}
  .angka b{display:block;font-size:26px;line-height:1.1}
  .angka span{font-size:13px;color:var(--tinta-lembut)}

  .halaman{margin-top:14px;font-size:14px}
  .halaman a,.halaman span{padding:4px 9px}

  @media (max-width:800px){
    .rangka{flex-direction:column}
    .sisi{width:100%}
    .sisi nav{display:flex;flex-wrap:wrap;gap:4px}
    .isi{padding:18px 16px 50px}
    table{display:block;overflow-x:auto;white-space:nowrap}
  }
</style>
</head>
<body>
<div class="rangka">
  <aside class="sisi">
    <div class="merek">
      <b>Panel Doorprize</b>
      <span>{{ \App\Models\Setting::get('event_title', 'Excellent Together') }}</span>
    </div>
    <nav>
      @php $r = request()->route()->getName(); @endphp
      <a href="{{ route('admin.dashboard') }}" class="{{ $r === 'admin.dashboard' ? 'aktif' : '' }}">Ringkasan</a>
      <a href="{{ route('admin.participants.index') }}" class="{{ str_starts_with($r, 'admin.participants') ? 'aktif' : '' }}">Peserta</a>
      <a href="{{ route('admin.prizes.index') }}" class="{{ str_starts_with($r, 'admin.prizes') ? 'aktif' : '' }}">Hadiah</a>
      <a href="{{ route('admin.groups.index') }}" class="{{ str_starts_with($r, 'admin.groups') ? 'aktif' : '' }}">Grup pengecualian</a>
      <a href="{{ route('admin.winners.index') }}" class="{{ str_starts_with($r, 'admin.winners') ? 'aktif' : '' }}">Pemenang</a>
      <a href="{{ route('admin.settings.edit') }}" class="{{ str_starts_with($r, 'admin.settings') ? 'aktif' : '' }}">Tampilan layar</a>
      <a href="{{ route('draw') }}" target="_blank">Buka layar undian ↗</a>
    </nav>
    <div class="kaki">
      <form method="post" action="{{ route('logout') }}">@csrf<button>Keluar</button></form>
    </div>
  </aside>

  <main class="isi">
    @if(session('status'))<div class="kabar sukses">{{ session('status') }}</div>@endif
    @if(session('error'))<div class="kabar gagal">{{ session('error') }}</div>@endif
    @if(session('import_errors') && count(session('import_errors')))
      <div class="kabar gagal">
        Beberapa baris tidak diimpor:
        <ul>@foreach(session('import_errors') as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif
    @if($errors->any())
      <div class="kabar gagal">
        <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    @yield('isi')
  </main>
</div>
</body>
</html>
