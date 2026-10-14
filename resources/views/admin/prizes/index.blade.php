@extends('layouts.admin')
@section('judul','Hadiah')

@section('isi')
<div class="kepala-halaman">
  <div>
    <h1>Hadiah</h1>
    <p>Tentukan jumlah pemenang tiap hadiah dan berapa nama yang keluar sekali putar.</p>
  </div>
</div>

<div class="kotak">
  <h2>Tambah hadiah</h2>
  <form method="post" action="{{ route('admin.prizes.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="baris">
      <div class="medan">
        <label for="name">Nama hadiah</label>
        <input id="name" name="name" value="{{ old('name') }}" placeholder="Mini Blender Kris 0.5L - Putih" required>
      </div>
      <div class="medan">
        <label for="sponsor">Sponsor</label>
        <input id="sponsor" name="sponsor" value="{{ old('sponsor') }}">
      </div>
    </div>
    <div class="baris">
      <div class="medan">
        <label for="quota">Jumlah pemenang</label>
        <input id="quota" type="number" name="quota" min="1" value="{{ old('quota', 1) }}" required>
        <div class="bantuan">Berapa unit hadiah ini yang tersedia.</div>
      </div>
      <div class="medan">
        <label for="per_draw">Pemenang per putaran</label>
        <input id="per_draw" type="number" name="per_draw" min="1" value="{{ old('per_draw', 1) }}" required>
        <div class="bantuan">Jumlah nama yang keluar sekali tekan Stop.</div>
      </div>
      <div class="medan">
        <label for="image">Foto hadiah</label>
        <input id="image" type="file" name="image" accept="image/*">
      </div>
    </div>
    <button class="tombol">Simpan hadiah</button>
  </form>
</div>

<div class="kotak">
  <h2>Daftar hadiah</h2>

  @if($prizes->isEmpty())
    <p>Belum ada hadiah. Isi formulir di atas untuk menambahkan hadiah pertama.</p>
  @else
    @foreach($prizes as $prize)
      <form id="ubah-{{ $prize->id }}" method="post" enctype="multipart/form-data"
            action="{{ route('admin.prizes.update', $prize) }}">@csrf @method('PUT')</form>
      <form id="pilih-{{ $prize->id }}" method="post" action="{{ route('admin.prizes.select', $prize) }}">@csrf</form>
      <form id="hapus-{{ $prize->id }}" method="post" action="{{ route('admin.prizes.destroy', $prize) }}"
            onsubmit="return confirm('Hapus hadiah {{ $prize->name }} beserta data pemenangnya?')">@csrf @method('DELETE')</form>
    @endforeach

    <table>
      <thead>
        <tr><th style="width:80px">Foto</th><th>Hadiah</th><th style="width:110px">Pemenang</th>
            <th style="width:110px">Per putaran</th><th style="width:90px">Urutan</th><th></th></tr>
      </thead>
      <tbody>
        @foreach($prizes as $prize)
          <tr>
            <td>
              @if($prize->image_url)
                <img src="{{ $prize->image_url }}" alt="" style="width:52px;height:52px;object-fit:contain;background:#F3F0FB;border-radius:6px">
              @endif
              <input type="file" name="image" accept="image/*" form="ubah-{{ $prize->id }}" style="width:64px;font-size:11px;margin-top:4px">
            </td>
            <td>
              <input type="text" name="name" form="ubah-{{ $prize->id }}" value="{{ $prize->name }}">
              <input type="text" name="sponsor" form="ubah-{{ $prize->id }}" value="{{ $prize->sponsor }}" placeholder="Sponsor" style="margin-top:5px">
              @if($prize->picks_count)
                <a class="lencana kuning" style="margin-top:6px;text-decoration:none"
                   href="{{ route('admin.picks.index', $prize) }}">{{ $prize->picks_count }} posisi dikunci</a>
              @endif
              @foreach($prize->exclusionGroups as $grup)
                <a class="lencana kuning" style="margin-top:6px;text-decoration:none"
                   href="{{ route('admin.groups.show', $grup) }}">Tanpa {{ $grup->name }}</a>
              @endforeach
              @if($prize->is_selected)
                <span class="lencana" style="margin-top:6px">Sedang diundi</span>
              @elseif($prize->winners_count >= $prize->quota)
                <span class="lencana hijau" style="margin-top:6px">Selesai</span>
              @endif
            </td>
            <td>
              <input type="number" name="quota" min="1" form="ubah-{{ $prize->id }}" value="{{ $prize->quota }}">
              <div class="bantuan">{{ $prize->winners_count }} terundi</div>
            </td>
            <td><input type="number" name="per_draw" min="1" form="ubah-{{ $prize->id }}" value="{{ $prize->per_draw }}"></td>
            <td><input type="number" name="sort_order" min="0" form="ubah-{{ $prize->id }}" value="{{ $prize->sort_order }}"></td>
            <td style="text-align:right;white-space:nowrap">
              <button class="tombol kecil netral" form="ubah-{{ $prize->id }}">Simpan</button>
                <a class="tombol kecil garis" href="{{ route('admin.picks.index', $prize) }}">Posisi pemenang</a>
              @unless($prize->is_selected)
                <button class="tombol kecil garis" form="pilih-{{ $prize->id }}">Undi ini</button>
              @endunless
              <button class="tombol kecil bahaya" form="hapus-{{ $prize->id }}">Hapus</button>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif
</div>
@endsection
