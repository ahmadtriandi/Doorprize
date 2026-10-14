@extends('layouts.admin')
@section('judul','Grup pengecualian')

@section('isi')
<div class="kepala-halaman">
  <div>
    <h1>Grup pengecualian</h1>
    <p>Kumpulan peserta yang tidak ikut diundi pada hadiah tertentu.</p>
  </div>
</div>

<div class="kotak">
  <h2>Buat grup baru</h2>
  <form method="post" action="{{ route('admin.groups.store') }}">
    @csrf
    <div class="baris">
      <div class="medan">
        <label for="name">Nama grup</label>
        <input id="name" name="name" value="{{ old('name') }}" placeholder="Panitia" required>
      </div>
      <div class="medan">
        <label for="note">Keterangan</label>
        <input id="note" name="note" value="{{ old('note') }}" placeholder="Tidak ikut undian hadiah utama">
      </div>
    </div>
    <button class="tombol">Simpan grup</button>
  </form>
</div>

<div class="kotak">
  <h2>Daftar grup</h2>

  @if($groups->isEmpty())
    <p>
      Belum ada grup. Contoh yang biasa dipakai: <em>Panitia</em>, <em>Direksi</em>,
      atau <em>Karyawan magang</em>. Anggota grup tetap ikut undian hadiah lain
      selama grupnya tidak dipasang di hadiah tersebut.
    </p>
  @else
    <table>
      <thead><tr><th>Grup</th><th>Anggota</th><th>Dipakai di hadiah</th><th></th></tr></thead>
      <tbody>
        @foreach($groups as $group)
          <tr>
            <td>
              <strong>{{ $group->name }}</strong>
              @if($group->note)<div class="bantuan">{{ $group->note }}</div>@endif
            </td>
            <td>{{ $group->participants_count }} peserta</td>
            <td>
              @if($group->prizes_count)
                <span class="lencana">{{ $group->prizes_count }} hadiah</span>
              @else
                <span class="lencana abu">Belum dipasang</span>
              @endif
            </td>
            <td style="text-align:right">
              <a class="tombol kecil garis" href="{{ route('admin.groups.show', $group) }}">Kelola</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif
</div>
@endsection
