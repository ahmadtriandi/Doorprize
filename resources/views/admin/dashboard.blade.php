@extends('layouts.admin')
@section('judul','Ringkasan')

@section('isi')
<div class="kepala-halaman">
  <div>
    <h1>Ringkasan acara</h1>
    <p>Pilih hadiah yang akan diundi, lalu buka layar undian di proyektor.</p>
  </div>
  <div>
    <a class="tombol garis" href="{{ route('draw.winners') }}" target="_blank">Layar pemenang</a>
    <a class="tombol" href="{{ route('draw') }}" target="_blank">Buka layar undian</a>
  </div>
</div>

<div class="angka">
  <div><b>{{ $stats['peserta'] }}</b><span>Peserta aktif</span></div>
  <div><b>{{ $stats['belum_menang'] }}</b><span>Belum pernah menang</span></div>
  <div><b>{{ $stats['hadiah'] }}</b><span>Jenis hadiah</span></div>
  <div><b>{{ $stats['kuota'] }}</b><span>Total kuota hadiah</span></div>
  <div><b>{{ $stats['pemenang'] }}</b><span>Sudah terundi</span></div>
</div>

<div class="kotak">
  <h2>Hadiah yang sedang diundi</h2>

  @if($selected)
    <p style="margin-bottom:14px">
      Layar undian menampilkan <strong>{{ $selected->name }}</strong> —
      sisa {{ $selected->remaining_slot }} dari {{ $selected->quota }} pemenang.
    </p>
  @else
    <p style="margin-bottom:14px">Belum ada hadiah yang dipilih. Pilih satu hadiah di bawah ini.</p>
  @endif

  @if($prizes->isEmpty())
    <p>Belum ada hadiah. <a href="{{ route('admin.prizes.index') }}">Tambahkan hadiah pertama</a> untuk mulai mengundi.</p>
  @else
    <table>
      <thead>
        <tr><th>Hadiah</th><th>Pemenang</th><th>Per putaran</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        @foreach($prizes as $prize)
          <tr>
            <td>
              <strong>{{ $prize->name }}</strong>
              @if($prize->sponsor)<div class="bantuan">{{ $prize->sponsor }}</div>@endif
            </td>
            <td>{{ $prize->winners_count }} / {{ $prize->quota }}</td>
            <td>{{ $prize->per_draw }} orang</td>
            <td>
              @if($prize->is_selected)<span class="lencana">Sedang diundi</span>
              @elseif($prize->winners_count >= $prize->quota)<span class="lencana hijau">Selesai</span>
              @else<span class="lencana abu">Menunggu</span>@endif
            </td>
            <td style="text-align:right">
              @unless($prize->is_selected)
                <form method="post" action="{{ route('admin.prizes.select', $prize) }}">
                  @csrf
                  <button class="tombol kecil garis">Undi hadiah ini</button>
                </form>
              @endunless
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif
</div>

<div class="kotak">
  <h2>Pemenang terbaru</h2>
  @if($recent->isEmpty())
    <p>Belum ada pemenang. Hasil undian akan muncul di sini begitu tombol Stop ditekan.</p>
  @else
    <table>
      <thead><tr><th>Nama</th><th>NIP</th><th>Hadiah</th><th>Waktu</th></tr></thead>
      <tbody>
        @foreach($recent as $w)
          <tr>
            <td>{{ $w->participant->name }}</td>
            <td>{{ $w->participant->nip }}</td>
            <td>{{ $w->prize->name }}</td>
            <td>{{ optional($w->drawn_at)->format('d/m/Y H:i') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <p style="margin-top:12px"><a href="{{ route('admin.winners.index') }}">Lihat semua pemenang</a></p>
  @endif
</div>
@endsection
