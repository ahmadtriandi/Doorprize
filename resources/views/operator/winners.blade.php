@extends('layouts.admin')
@section('judul','Daftar pemenang')

@section('isi')
<div class="kepala-halaman">
  <div>
    <h1>Daftar pemenang</h1>
    <p>Hanya untuk dilihat. Pembatalan dan pengubahan data dilakukan oleh panitia.</p>
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

<div class="kotak">
  <h2>{{ $winners->total() }} pemenang</h2>

  @if($winners->isEmpty())
    <p>Belum ada pemenang untuk pilihan ini.</p>
  @else
    <table>
      <thead><tr><th>Nama</th><th>NIP</th><th>Unit</th><th>Hadiah</th><th>Waktu</th><th>Hadiah diambil</th></tr></thead>
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
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="halaman">{{ $winners->links('pagination') }}</div>
  @endif
</div>
@endsection
