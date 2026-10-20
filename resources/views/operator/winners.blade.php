@extends('layouts.admin')
@section('judul','Daftar pemenang')

@section('isi')
<div class="kepala-halaman">
  <div>
    <h1>Daftar pemenang</h1>
    <p>Batalkan pemenang yang tidak hadir; namanya otomatis kembali masuk undian.</p>
  </div>
  <a class="tombol garis" href="{{ route('draw.winners') }}" target="_blank">Tampilkan di layar</a>
</div>

<div class="kotak">
  <form method="get" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <select name="prize" style="max-width:320px">
      <option value="">Semua hadiah</option>
      @foreach($prizes as $p)
        <option value="{{ $p->id }}" @selected($prizeId == $p->id)>{{ $p->name }} ({{ $p->winners_count }}/{{ $p->quota }})</option>
      @endforeach
    </select>
    <input type="text" name="q" value="{{ $cari }}" placeholder="Cari nama atau NIP" style="max-width:260px">
    <button class="tombol netral">Tampilkan</button>
    @if($prizeId || $cari !== '')
      <a class="tombol netral" href="{{ route('operator.winners') }}">Reset</a>
    @endif
  </form>
</div>

@php $terpilih = $prizeId ? $prizes->firstWhere('id', $prizeId) : null; @endphp
@if($terpilih && $terpilih->winners_count > 0)
  <div class="kotak" style="border-color:#F5C2C2">
    <h2>Ulangi undian {{ $terpilih->name }}</h2>
    <p style="font-size:14px;margin-bottom:10px">
      Menghapus {{ $terpilih->winners_count }} pemenang hadiah ini. Semua namanya kembali masuk undian.
    </p>
    <form method="post" action="{{ route('operator.winners.reset', $terpilih) }}"
          onsubmit="return confirm('Hapus semua pemenang {{ $terpilih->name }}? Tindakan ini tidak bisa dibatalkan.')">
      @csrf @method('DELETE')
      <button class="tombol bahaya">Hapus pemenang hadiah ini</button>
    </form>
  </div>
@endif

<div class="kotak">
  <h2>{{ $winners->total() }} pemenang</h2>

  @if($winners->isEmpty())
    <p>Belum ada pemenang untuk pilihan ini.</p>
  @else
    @foreach($winners as $w)
      <form id="batal-{{ $w->id }}" method="post" action="{{ route('operator.winners.destroy', $w) }}"
            onsubmit="return confirm('Batalkan {{ addslashes($w->participant->name) }} sebagai pemenang {{ addslashes($w->prize->name) }}? Namanya kembali masuk undian.')">
        @csrf @method('DELETE')
      </form>
    @endforeach

    <table>
      <thead><tr><th>Nama</th><th>NIP</th><th>Unit</th><th>Hadiah</th><th>Waktu</th><th>Hadiah diambil</th><th></th></tr></thead>
      <tbody>
        @foreach($winners as $w)
          <tr>
            <td><strong>{{ $w->participant->name }}</strong></td>
            <td>{{ $w->participant->nip }}</td>
            <td>{{ $w->participant->department ?: '—' }}</td>
            <td>{{ $w->prize->name }}</td>
            <td>{{ optional($w->drawn_at)->format('d/m/Y H:i') }}</td>
            <td>
              @if($w->is_claimed)<span class="lencana hijau">Sudah</span>
              @else<span class="lencana abu">Belum</span>@endif
            </td>
            <td style="text-align:right">
              <button class="tombol kecil bahaya" form="batal-{{ $w->id }}">Batalkan</button>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="halaman">{{ $winners->links('pagination') }}</div>
  @endif
</div>

<div class="kotak" style="border-color:#F5C2C2">
  <h2>Hapus semua pemenang</h2>
  <p style="font-size:14px">
    Menghapus <strong>semua</strong> pemenang dari <strong>semua</strong> hadiah, termasuk
    hadiah di sesi undian. Seluruh hadiah kembali ke kuota penuh. Peserta, hadiah, dan
    pengaturan tidak ikut terhapus.
  </p>
  <div class="bantuan" style="margin:8px 0 14px">
    Biasanya dipakai membersihkan hasil gladi bersih. Penghapusan tidak bisa dibatalkan.
  </div>

  <form method="post" action="{{ route('operator.winners.resetAll') }}"
        style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap">
    @csrf @method('DELETE')
    <div class="medan" style="margin:0;max-width:220px">
      <label for="konfirmasi">Ketik HAPUS untuk melanjutkan</label>
      <input id="konfirmasi" name="konfirmasi" placeholder="HAPUS" autocomplete="off" required>
    </div>
    <button class="tombol bahaya" onclick="return confirm('Hapus seluruh data pemenang dari semua hadiah?')">
      Hapus semua pemenang
    </button>
  </form>
</div>
@endsection
