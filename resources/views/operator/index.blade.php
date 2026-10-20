@extends('layouts.admin')
@section('judul','Operator')

@section('isi')
<div class="kepala-halaman">
  <div>
    <h1>Layar undian</h1>
    <p>Buka layar di komputer proyektor, lalu tekan F11 agar layar penuh.</p>
  </div>
</div>

<div class="angka">
  <div><b>{{ $stats['pemenang'] }}</b><span>Pemenang terundi</span></div>
  <div><b>{{ max(0, $stats['kuota'] - $stats['pemenang']) }}</b><span>Hadiah tersisa</span></div>
</div>

<div class="baris">
  <div class="kotak">
    <h2>Layar utama</h2>
    <p style="font-size:14px;margin-bottom:14px">
      Undian satu hadiah per putaran. Pindah hadiah lewat menu di pojok kiri bawah layar.
    </p>
    <a class="tombol" href="{{ route('draw') }}" target="_blank">Buka layar undian</a>
    <a class="tombol garis" href="{{ route('draw.winners') }}" target="_blank">Buka layar pemenang</a>
  </div>

  <div class="kotak">
    <h2>Sesi undian</h2>
    @if($sessions->isEmpty())
      <p style="font-size:14px">Belum ada sesi. Sesi dibuat oleh panitia lewat dashboard.</p>
    @else
      <p style="font-size:14px;margin-bottom:10px">Banyak hadiah terundi sekaligus dalam satu layar.</p>
      @foreach($sessions as $s)
        <a class="tombol garis" style="margin:0 6px 6px 0" href="{{ route('draw.session', $s) }}" target="_blank">
          {{ $s->name }}
        </a>
      @endforeach
    @endif
  </div>
</div>

<div class="kotak">
  <h2>Hadiah</h2>
  @if($prizes->isEmpty())
    <p>Belum ada hadiah.</p>
  @else
    <table>
      <thead><tr><th>Hadiah</th><th>Pemenang</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @foreach($prizes as $p)
          <tr>
            <td><strong>{{ $p->name }}</strong></td>
            <td>{{ $p->winners_count }} / {{ $p->quota }}</td>
            <td>
              @if($p->is_selected)<span class="lencana">Tampil di layar</span>
              @elseif($p->winners_count >= $p->quota)<span class="lencana hijau">Selesai</span>
              @else<span class="lencana abu">Menunggu</span>@endif
            </td>
            <td style="text-align:right">
              @unless($p->is_selected || $p->winners_count >= $p->quota)
                <form method="post" action="{{ route('draw.select', $p) }}">
                  @csrf
                  <input type="hidden" name="kembali" value="operator">
                  <button class="tombol kecil garis">Tampilkan di layar</button>
                </form>
              @endunless
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif
</div>
@endsection
