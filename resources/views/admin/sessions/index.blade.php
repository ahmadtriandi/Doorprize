@extends('layouts.admin')
@section('judul','Sesi undian')

@section('isi')
<div class="kepala-halaman">
  <div>
    <h1>Sesi undian</h1>
    <p>Beberapa hadiah diundi bersamaan dalam satu layar dengan sekali tekan Start dan Stop.</p>
  </div>
</div>

<div class="kotak">
  <h2>Buat sesi baru</h2>
  <form method="post" action="{{ route('admin.sessions.store') }}" style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap">
    @csrf
    <div class="medan" style="margin:0;flex:1;min-width:240px">
      <label for="name">Nama sesi</label>
      <input id="name" name="name" value="{{ old('name') }}" placeholder="Doorprize PV" required>
      <div class="bantuan">Tampil sebagai judul besar di atas layar sesi.</div>
    </div>
    <button class="tombol">Buat sesi</button>
  </form>
</div>

<div class="kotak">
  <h2>Daftar sesi</h2>

  @if($sessions->isEmpty())
    <p>
      Belum ada sesi. Sesi cocok untuk hadiah kecil yang jumlahnya banyak, misalnya
      pendant, sarung tangan, dan pelindung lutut dalam satu tabel — semuanya terundi
      dalam satu putaran.
    </p>
  @else
    <table>
      <thead><tr><th>Sesi</th><th>Hadiah</th><th>Total pemenang</th><th></th></tr></thead>
      <tbody>
        @foreach($sessions as $s)
          <tr>
            <td><strong>{{ $s->name }}</strong></td>
            <td>{{ $s->prizes_count }} hadiah</td>
            <td>{{ (int) ($kuota[$s->id] ?? 0) }} orang</td>
            <td style="text-align:right;white-space:nowrap">
              <a class="tombol kecil garis" href="{{ route('admin.sessions.show', $s) }}">Atur</a>
              <a class="tombol kecil" href="{{ route('draw.session', $s) }}" target="_blank">Buka layar</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif
</div>
@endsection
