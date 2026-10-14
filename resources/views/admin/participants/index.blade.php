@extends('layouts.admin')
@section('judul','Peserta')

@section('isi')
<div class="kepala-halaman">
  <div>
    <h1>Peserta</h1>
    <p>Nama dan NIP yang tampil di layar undian.</p>
  </div>
  <div>
    <a class="tombol garis" href="{{ route('admin.participants.template') }}">Unduh template</a>
    <a class="tombol" href="{{ route('admin.participants.export') }}">Ekspor CSV</a>
  </div>
</div>

<div class="baris">
  <div class="kotak">
    <h2>Tambah satu peserta</h2>
    <form method="post" action="{{ route('admin.participants.store') }}">
      @csrf
      <div class="medan">
        <label for="nip">NIP</label>
        <input id="nip" name="nip" value="{{ old('nip') }}" required>
      </div>
      <div class="medan">
        <label for="name">Nama</label>
        <input id="name" name="name" value="{{ old('name') }}" required>
      </div>
      <div class="medan">
        <label for="department">Unit kerja</label>
        <input id="department" name="department" value="{{ old('department') }}">
        <div class="bantuan">Boleh dikosongkan.</div>
      </div>
      <button class="tombol">Simpan peserta</button>
    </form>
  </div>

  <div class="kotak">
    <h2>Impor dari CSV atau Excel</h2>
    <form method="post" action="{{ route('admin.participants.import') }}" enctype="multipart/form-data">
      @csrf
      <div class="medan">
        <label for="file">Berkas peserta</label>
        <input id="file" type="file" name="file" accept=".csv,.txt,.xlsx,.xls" required>
        <div class="bantuan">Baris pertama berisi judul kolom: <code>nip</code>, <code>nama</code>, dan opsional <code>unit</code>.</div>
      </div>
      <div class="centang">
        <input type="checkbox" id="replace_all" name="replace_all" value="1">
        <label for="replace_all">Hapus semua peserta lama dulu</label>
      </div>
      <div class="bantuan" style="margin-bottom:12px">
        NIP yang sudah terdaftar akan diperbarui namanya, bukan digandakan.
      </div>
      <button class="tombol">Impor peserta</button>
    </form>
  </div>
</div>

<div class="kotak">
  <h2>Daftar peserta ({{ $participants->total() }})</h2>

  <form method="get" style="display:flex;gap:8px;margin-bottom:14px;max-width:460px">
    <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama, NIP, atau unit">
    <button class="tombol netral">Cari</button>
    @if($search)<a class="tombol netral" href="{{ route('admin.participants.index') }}">Reset</a>@endif
  </form>

  @if($participants->isEmpty())
    <p>Belum ada peserta yang cocok. Tambahkan satu per satu atau impor dari berkas.</p>
  @else
    {{-- Form diletakkan di luar tabel; kolom isian terhubung lewat atribut form. --}}
    @foreach($participants as $p)
      <form id="ubah-{{ $p->id }}" method="post" action="{{ route('admin.participants.update', $p) }}">@csrf @method('PUT')</form>
      <form id="hapus-{{ $p->id }}" method="post" action="{{ route('admin.participants.destroy', $p) }}"
            onsubmit="return confirm('Hapus {{ $p->name }} dari daftar peserta?')">@csrf @method('DELETE')</form>
    @endforeach

    <table>
      <thead><tr><th>NIP</th><th>Nama</th><th>Unit</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @foreach($participants as $p)
          <tr>
            <td><input type="text" name="nip" form="ubah-{{ $p->id }}" value="{{ $p->nip }}" style="width:150px"></td>
            <td><input type="text" name="name" form="ubah-{{ $p->id }}" value="{{ $p->name }}"></td>
            <td><input type="text" name="department" form="ubah-{{ $p->id }}" value="{{ $p->department }}" style="width:140px"></td>
            <td>
              <span class="centang" style="margin:0">
                <input type="checkbox" id="aktif-{{ $p->id }}" name="is_active" value="1" form="ubah-{{ $p->id }}" @checked($p->is_active)>
                <label for="aktif-{{ $p->id }}">Ikut undian</label>
              </span>
              @if($p->winners_count)<span class="lencana kuning">Sudah menang</span>@endif
            </td>
            <td style="text-align:right;white-space:nowrap">
              <button class="tombol kecil netral" form="ubah-{{ $p->id }}">Simpan</button>
              <button class="tombol kecil bahaya" form="hapus-{{ $p->id }}">Hapus</button>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="halaman">{{ $participants->links('pagination') }}</div>
  @endif
</div>
@endsection
