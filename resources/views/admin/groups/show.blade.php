@extends('layouts.admin')
@section('judul', $group->name)

@section('isi')
<div class="kepala-halaman">
  <div>
    <h1>{{ $group->name }}</h1>
    <p>{{ $group->note ?: 'Grup pengecualian peserta.' }}</p>
  </div>
  <a class="tombol netral" href="{{ route('admin.groups.index') }}">Kembali</a>
</div>

<div class="kotak">
  <h2>Hadiah yang mengecualikan grup ini</h2>

  @if($prizes->isEmpty())
    <p>Belum ada hadiah. Buat hadiah dulu, lalu kembali ke sini.</p>
  @else
    <form method="post" action="{{ route('admin.groups.prizes', $group) }}">
      @csrf @method('PUT')
      @foreach($prizes as $prize)
        <div class="centang">
          <input type="checkbox" id="hadiah-{{ $prize->id }}" name="prizes[]" value="{{ $prize->id }}"
                 @checked($terpasang->contains($prize->id))>
          <label for="hadiah-{{ $prize->id }}">
            {{ $prize->name }}
            <span class="bantuan" style="display:inline">({{ $prize->winners_count }}/{{ $prize->quota }} terundi)</span>
          </label>
        </div>
      @endforeach

      <div class="bantuan" style="margin:10px 0 14px">
        Anggota grup ini tidak akan muncul di layar undian untuk hadiah yang dicentang,
        dan tetap ikut undian hadiah lainnya. Perubahan berlaku pada putaran berikutnya;
        pemenang yang sudah terlanjur terundi tidak ikut terhapus.
      </div>

      <button class="tombol">Simpan pilihan hadiah</button>
    </form>
  @endif
</div>

<div class="baris">
  <div class="kotak">
    <h2>Tambah anggota</h2>

    {{-- Tetap berupa form agar pencarian masih jalan kalau JavaScript mati. --}}
    <form method="get" id="formCari" style="display:flex;gap:8px;margin-bottom:12px">
      <input type="text" id="kotakCari" name="q" value="{{ $cari }}"
             placeholder="Ketik nama atau NIP peserta" autocomplete="off">
      <button class="tombol netral">Cari</button>
    </form>

    <div class="bantuan" id="statusCari" style="margin-bottom:10px">Ketik minimal 2 huruf.</div>

    <table><tbody id="hasilCari">
      @foreach($kandidat as $p)
        <tr data-id="{{ $p->id }}">
          <td>
            {{ $p->name }}
            <div class="bantuan">{{ $p->nip }}{{ $p->department ? ' · '.$p->department : '' }}</div>
          </td>
          <td style="text-align:right">
            <button class="tombol kecil" data-tambah="{{ $p->id }}">Tambah</button>
          </td>
        </tr>
      @endforeach
    </tbody></table>

    @if($units->isNotEmpty())
      <form method="post" action="{{ route('admin.groups.members.add', $group) }}"
            style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;margin-top:18px">
        @csrf
        <div class="medan" style="margin:0;flex:1;min-width:180px">
          <label for="department">Tambah satu unit sekaligus</label>
          <select id="department" name="department">
            <option value="">Pilih unit kerja</option>
            @foreach($units as $unit)
              <option value="{{ $unit }}">{{ $unit }}</option>
            @endforeach
          </select>
        </div>
        <button class="tombol netral">Tambahkan unit</button>
      </form>
    @endif

    <form method="post" action="{{ route('admin.groups.members.bulk', $group) }}" style="margin-top:20px">
      @csrf
      <div class="medan">
        <label for="daftar">Tempel daftar NIP sekaligus</label>
        <textarea id="daftar" name="daftar" rows="6" required
                  placeholder="198701012010011001&#10;198903152012012002&#10;199005202015011003"
                  style="width:100%;padding:8px 11px;border:1px solid var(--garis);border-radius:8px;font:inherit;resize:vertical"></textarea>
        <div class="bantuan">
          Salin kolom NIP dari Excel lalu tempel di sini. Pemisah boleh baris baru,
          koma, titik koma, atau tab. Nama juga diterima, tetapi hanya kalau nama itu
          dimiliki satu orang saja — untuk sisanya pakai NIP agar tidak salah orang.
          NIP yang sudah ada di grup dilewati, jadi menempel ulang daftar yang sama aman.
          Muat sekitar 25.000 NIP sekali tempel; kalau lebih, bagi jadi beberapa kali.
        </div>
      </div>
      <button class="tombol">Tambahkan semua</button>
    </form>
  </div>

  <div class="kotak">
    <h2>Anggota grup (<span id="jumlahAnggota">{{ $group->participants->count() }}</span>)</h2>

    <p id="anggotaKosong" @if($group->participants->isNotEmpty()) hidden @endif>
      Belum ada anggota. Cari peserta di sebelah kiri, atau tambahkan satu unit sekaligus.
    </p>

    <table><tbody id="daftarAnggota">
      @foreach($group->participants->sortBy('name') as $p)
        <tr data-id="{{ $p->id }}">
          <td>
            {{ $p->name }}
            <div class="bantuan">{{ $p->nip }}{{ $p->department ? ' · '.$p->department : '' }}</div>
          </td>
          <td style="text-align:right">
            <button class="tombol kecil bahaya" data-keluar="{{ $p->id }}">Keluarkan</button>
          </td>
        </tr>
      @endforeach
    </tbody></table>
  </div>
</div>

@php
  // Dihitung di sini, bukan langsung di dalam @json(): argumen bertingkat
  // membuat parser directive Blade tersandung.
  $csrfToken = csrf_token();
  $urlCari   = route('admin.groups.search', $group);
  $urlTambah = route('admin.groups.members.add', $group);
  $urlKeluar = route('admin.groups.members.remove', [$group->id, 0]);
@endphp

<script>
(() => {
  const token   = @json($csrfToken);
  const urlCari = @json($urlCari);
  const urlAdd  = @json($urlTambah);
  const urlDel  = @json($urlKeluar);

  const kotak   = document.getElementById('kotakCari');
  const hasil   = document.getElementById('hasilCari');
  const status  = document.getElementById('statusCari');
  const anggota = document.getElementById('daftarAnggota');
  const kosong  = document.getElementById('anggotaKosong');
  const jumlah  = document.getElementById('jumlahAnggota');

  document.getElementById('formCari').addEventListener('submit', e => e.preventDefault());

  const baris = (data, label, atribut) => {
    const tr = document.createElement('tr');
    tr.dataset.id = data.id;
    tr.innerHTML =
      `<td>${data.name}<div class="bantuan">${data.sub || ''}</div></td>` +
      `<td style="text-align:right"><button class="tombol kecil ${atribut === 'keluar' ? 'bahaya' : ''}" ` +
      `data-${atribut}="${data.id}">${label}</button></td>`;
    return tr;
  };

  /* ---------- pencarian langsung ---------- */

  let tunda = null, permintaan = 0;

  async function cari() {
    const q = kotak.value.trim();

    if (q.length < 2) {
      hasil.innerHTML = '';
      status.textContent = 'Ketik minimal 2 huruf.';
      return;
    }

    const nomor = ++permintaan;
    status.textContent = 'Mencari…';

    try {
      const res = await fetch(`${urlCari}?q=${encodeURIComponent(q)}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = await res.json();

      // Jawaban yang datang terlambat diabaikan supaya hasil tidak melompat
      // kembali ke kata kunci lama saat panitia mengetik cepat.
      if (nomor !== permintaan) return;

      hasil.innerHTML = '';
      data.results.forEach(p => hasil.appendChild(baris(p, 'Tambah', 'tambah')));

      status.textContent = data.results.length
        ? `${data.results.length} peserta ditemukan.`
        : 'Tidak ada peserta cocok yang belum masuk grup ini.';
    } catch (e) {
      status.textContent = 'Pencarian gagal. Periksa koneksi lalu coba lagi.';
    }
  }

  kotak.addEventListener('input', () => {
    clearTimeout(tunda);
    tunda = setTimeout(cari, 200);
  });

  if (kotak.value.trim().length >= 2) cari();

  /* ---------- tambah dan keluarkan tanpa memuat ulang ---------- */

  hasil.addEventListener('click', async (e) => {
    const tombol = e.target.closest('[data-tambah]');
    if (!tombol) return;

    tombol.disabled = true;

    try {
      const res = await fetch(urlAdd, {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},
        body: JSON.stringify({ participant_id: tombol.dataset.tambah })
      });
      const data = await res.json();
      if (!data.ok) throw new Error();

      tombol.closest('tr').remove();
      anggota.appendChild(baris(data.anggota, 'Keluarkan', 'keluar'));
      jumlah.textContent = data.jumlah;
      kosong.hidden = true;
    } catch (err) {
      tombol.disabled = false;
      status.textContent = 'Gagal menambahkan peserta. Coba lagi.';
    }
  });

  anggota.addEventListener('click', async (e) => {
    const tombol = e.target.closest('[data-keluar]');
    if (!tombol) return;

    tombol.disabled = true;

    try {
      // DELETE dikirim apa adanya; penyamaran lewat _method tidak terbaca
      // Laravel kalau badannya JSON.
      const res = await fetch(urlDel.replace(/\/0$/, '/' + tombol.dataset.keluar), {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN':token,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
      });
      const data = await res.json();
      if (!data.ok) throw new Error();

      tombol.closest('tr').remove();
      jumlah.textContent = data.jumlah;
      kosong.hidden = data.jumlah > 0;
      if (kotak.value.trim().length >= 2) cari();
    } catch (err) {
      tombol.disabled = false;
    }
  });
})();
</script>

<div class="kotak">
  <h2>Ubah atau hapus grup</h2>

  <form method="post" action="{{ route('admin.groups.update', $group) }}">
    @csrf @method('PUT')
    <div class="baris">
      <div class="medan">
        <label for="nama">Nama grup</label>
        <input id="nama" name="name" value="{{ $group->name }}" required>
      </div>
      <div class="medan">
        <label for="ket">Keterangan</label>
        <input id="ket" name="note" value="{{ $group->note }}">
      </div>
    </div>
    <button class="tombol">Simpan perubahan</button>
  </form>

  <form method="post" action="{{ route('admin.groups.destroy', $group) }}" style="margin-top:14px"
        onsubmit="return confirm('Hapus grup {{ $group->name }}? Pesertanya sendiri tidak ikut terhapus.')">
    @csrf @method('DELETE')
    <button class="tombol bahaya">Hapus grup</button>
  </form>
</div>
@endsection
