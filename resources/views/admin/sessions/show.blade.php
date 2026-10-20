@extends('layouts.admin')
@section('judul', $sesi->name)

@section('isi')
<div class="kepala-halaman">
  <div>
    <h1>{{ $sesi->name }}</h1>
    <p>Centang hadiah yang masuk sesi ini, lalu beri label kelompok untuk tiap baris di layar.</p>
  </div>
  <div>
    <a class="tombol netral" href="{{ route('admin.sessions.index') }}">Kembali</a>
    <a class="tombol" href="{{ route('draw.session', $sesi) }}" target="_blank">Buka layar sesi</a>
  </div>
</div>

<form method="post" action="{{ route('admin.sessions.update', $sesi) }}">
  @csrf @method('PUT')

  <div class="kotak">
    <h2>Pengaturan sesi</h2>
    <div class="baris">
      <div class="medan">
        <label for="name">Nama sesi</label>
        <input id="name" name="name" value="{{ $sesi->name }}" required>
      </div>
      <div class="medan">
        <label for="rows_per_column">Baris per kolom</label>
        <input id="rows_per_column" type="number" name="rows_per_column" min="1" max="20"
               value="{{ $sesi->rows_per_column }}" required>
        <div class="bantuan">
          Hadiah yang jumlah pemenangnya lebih dari angka ini dipecah jadi beberapa
          kolom. Dengan 6 baris, hadiah 11 pemenang tampil dua kolom: 1–6 dan 7–11.
        </div>
      </div>
    </div>
  </div>

  <div class="kotak">
    <h2>Hadiah dalam sesi</h2>

    @if($prizes->isEmpty())
      <p>Belum ada hadiah. <a href="{{ route('admin.prizes.index') }}">Tambahkan hadiah</a> dulu.</p>
    @else
      <datalist id="daftarLabel">
        @foreach($labels as $l)<option value="{{ $l }}">@endforeach
      </datalist>

      <table>
        <thead>
          <tr>
            <th style="width:60px">Masuk</th>
            <th>Hadiah</th>
            <th style="width:90px">Pemenang</th>
            <th style="width:80px">Baris</th>
            <th style="width:180px">Label baris</th>
            <th style="width:80px">Urutan</th>
          </tr>
        </thead>
        <tbody>
          @foreach($prizes as $p)
            @php
              $diSini = (int) $p->draw_session_id === (int) $sesi->id;
              $diLain = $p->draw_session_id && ! $diSini;
            @endphp
            <tr>
              <td>
                <input type="checkbox" name="prizes[{{ $p->id }}][include]" value="1" @checked($diSini)
                       style="width:18px;height:18px">
              </td>
              <td>
                <strong>{{ $p->name }}</strong>
                @if($diLain)
                  <div class="bantuan">Sekarang di sesi “{{ $p->drawSession->name }}” — mencentang akan memindahkannya ke sini</div>
                @endif
              </td>
              <td>{{ $p->winners_count }} / {{ $p->quota }}</td>
              <td>
                <input type="number" name="prizes[{{ $p->id }}][row]" min="1" max="50"
                       value="{{ $diSini ? $p->session_row : 1 }}">
              </td>
              <td>
                <input type="text" name="prizes[{{ $p->id }}][group_label]" list="daftarLabel"
                       value="{{ $diSini ? $p->group_label : '' }}" placeholder="25 PV">
              </td>
              <td>
                <input type="number" name="prizes[{{ $p->id }}][order]" min="0"
                       value="{{ $diSini ? $p->session_order : $loop->iteration }}">
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

      <div class="bantuan" style="margin-top:12px">
        <strong>Baris</strong> menentukan hadiah tampil di baris ke berapa di layar, dan
        <strong>Urutan</strong> menentukan posisi kolomnya dari kiri ke kanan dalam baris itu.
        <strong>Label baris</strong> cukup diisi di salah satu hadiah per baris — tulisan itu
        tampil di sisi kiri baris. Dua baris boleh berlabel sama, misalnya baris 2 dan 3
        sama-sama <code>50 PV</code>.
      </div>
    @endif
  </div>

  <button class="tombol">Simpan susunan</button>
</form>

<div class="baris" style="margin-top:18px">
  <div class="kotak" style="border-color:#F5C2C2">
    <h2>Ulangi sesi ini</h2>
    <p style="font-size:14px">
      Menghapus pemenang semua hadiah di sesi ini, sehingga seluruh kotak kembali kosong.
      Gunakan setelah gladi bersih.
    </p>
    <form method="post" action="{{ route('admin.sessions.reset', $sesi) }}"
          style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;margin-top:10px">
      @csrf @method('DELETE')
      <div class="medan" style="margin:0;max-width:200px">
        <label for="konfirmasi">Ketik HAPUS</label>
        <input id="konfirmasi" name="konfirmasi" autocomplete="off" required>
      </div>
      <button class="tombol bahaya">Kosongkan sesi</button>
    </form>
  </div>

  <div class="kotak">
    <h2>Hapus sesi</h2>
    <p style="font-size:14px">Hadiah dan pemenangnya tetap ada, hanya pengelompokannya yang hilang.</p>
    <form method="post" action="{{ route('admin.sessions.destroy', $sesi) }}" style="margin-top:10px"
          onsubmit="return confirm('Hapus sesi {{ $sesi->name }}?')">
      @csrf @method('DELETE')
      <button class="tombol netral">Hapus sesi</button>
    </form>
  </div>
</div>
@endsection
