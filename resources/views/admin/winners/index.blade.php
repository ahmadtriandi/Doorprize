@extends('layouts.admin')
@section('judul','Pemenang')

@section('isi')
<div class="kepala-halaman">
  <div>
    <h1>Pemenang undian</h1>
    <p>Catat siapa yang sudah mengambil hadiahnya, atau batalkan pemenang yang tidak hadir.</p>
  </div>
  <div>
    <a class="tombol garis" href="{{ route('draw.winners') }}" target="_blank">Tampilkan di layar</a>
    <a class="tombol" href="{{ route('admin.winners.export') }}">Ekspor CSV</a>
  </div>
</div>

@php $terpilih = $prizeId ? $prizes->firstWhere('id', (int) $prizeId) : null; @endphp

<div class="kotak">
  <h2>Saring per hadiah</h2>
  <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <form method="get" style="display:flex;gap:8px">
      <select name="prize" style="max-width:340px">
        <option value="">Semua hadiah</option>
        @foreach($prizes as $p)
          <option value="{{ $p->id }}" @selected($prizeId == $p->id)>
            {{ $p->name }} ({{ $p->winners_count }}/{{ $p->quota }})
          </option>
        @endforeach
      </select>
      <button class="tombol netral">Tampilkan</button>
    </form>

    @if($terpilih)
      <form method="post" action="{{ route('admin.winners.reset', $terpilih) }}"
            onsubmit="return confirm('Hapus semua pemenang {{ $terpilih->name }} dan ulangi undian dari awal?')">
        @csrf @method('DELETE')
        <button class="tombol bahaya">Ulangi undian hadiah ini</button>
      </form>
    @endif
  </div>
</div>

<div class="kotak">
  <h2>Daftar pemenang ({{ $winners->total() }})</h2>

  @if($winners->isEmpty())
    <p>Belum ada pemenang untuk pilihan ini. Hasil undian masuk ke sini begitu tombol Stop ditekan di layar.</p>
  @else
    @foreach($winners as $w)
      <form id="ambil-{{ $w->id }}" method="post" action="{{ route('admin.winners.claim', $w) }}">@csrf</form>
      <form id="batal-{{ $w->id }}" method="post" action="{{ route('admin.winners.destroy', $w) }}"
            onsubmit="return confirm('Batalkan {{ $w->participant->name }}? Namanya kembali masuk undian.')">@csrf @method('DELETE')</form>
    @endforeach

    <table>
      <thead>
        <tr><th>Nama</th><th>NIP</th><th>Unit</th><th>Hadiah</th><th>Putaran</th><th>Waktu</th><th>Pengambilan</th><th></th></tr>
      </thead>
      <tbody>
        @foreach($winners as $w)
          <tr>
            <td><strong>{{ $w->participant->name }}</strong></td>
            <td>{{ $w->participant->nip }}</td>
            <td>{{ $w->participant->department ?: '—' }}</td>
            <td>{{ $w->prize->name }}</td>
            <td>{{ $w->round }}</td>
            <td>{{ optional($w->drawn_at)->format('d/m/Y H:i') }}</td>
            <td>
              <button class="tombol kecil {{ $w->is_claimed ? '' : 'netral' }}" form="ambil-{{ $w->id }}">
                {{ $w->is_claimed ? 'Sudah diambil' : 'Tandai diambil' }}
              </button>
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
  <h2>Bersihkan seluruh data pemenang</h2>

  <p style="font-size:14px">
    Menghapus <strong>semua</strong> pemenang dari <strong>semua</strong> hadiah, dan
    mengembalikan setiap hadiah ke kuota penuh. Peserta, hadiah, grup pengecualian, dan
    posisi yang dikunci tidak ikut terhapus.
  </p>
  <div class="bantuan" style="margin:8px 0 14px">
    Gunakan ini untuk membersihkan sisa uji coba sebelum acara dimulai. Ekspor CSV dulu
    kalau data yang ada masih ingin disimpan — penghapusan tidak bisa dibatalkan.
  </div>

  <form method="post" action="{{ route('admin.winners.resetAll') }}"
        style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap">
    @csrf @method('DELETE')
    <div class="medan" style="margin:0;max-width:220px">
      <label for="konfirmasi">Ketik HAPUS untuk melanjutkan</label>
      <input id="konfirmasi" name="konfirmasi" placeholder="HAPUS" autocomplete="off" required>
    </div>
    <button class="tombol bahaya"
            onclick="return confirm('Hapus seluruh data pemenang dari semua hadiah?')">
      Hapus semua pemenang
    </button>
  </form>
</div>
@endsection
