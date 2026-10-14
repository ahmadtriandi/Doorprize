@extends('layouts.admin')
@section('judul', 'Posisi pemenang ' . $prize->name)

@php
  $urlCari   = route('admin.picks.search', $prize);
  $urlTambah = route('admin.picks.store', $prize);
  $csrfToken = csrf_token();
  $terundi   = $prize->winners->pluck('participant_id');
  $sudahKe   = $prize->winners->count();
  $sisaAcak  = max(0, $prize->quota - $prize->picks->count());
@endphp

@section('isi')
<div class="kepala-halaman">
  <div>
    <h1>Posisi pemenang yang dikunci</h1>
    <p>{{ $prize->name }} — kuota {{ $prize->quota }} pemenang, {{ $sudahKe }} sudah keluar.</p>
  </div>
  <a class="tombol netral" href="{{ route('admin.prizes.index') }}">Kembali ke Hadiah</a>
</div>

<div class="kotak">
  <h2>Cara kerjanya</h2>
  <p style="font-size:14px">
    Hadiah ini tetap diundi acak. Yang dikunci hanyalah posisi tertentu: kalau posisi 7
    dikunci atas nama Budi, pemenang ketujuh pasti Budi, sedangkan {{ $sisaAcak }} posisi
    sisanya tetap hasil undian.
  </p>
  <div class="bantuan" style="margin-top:8px">
    Posisi dihitung dari urutan keluarnya pemenang hadiah ini, bukan urutan putaran.
    Kalau sekali tekan Stop memanggil 2 nama, putaran pertama mengisi posisi 1 dan 2.
  </div>
  <div class="bantuan" style="margin-top:8px">
    Nama yang dikunci di sini ditahan dari undian hadiah lain sampai posisinya dipanggil,
    jadi tidak mungkin keluar lebih dulu di hadiah yang diundi sebelumnya.
  </div>

  @if($prize->exclusionGroups->isNotEmpty())
    <div class="bantuan" style="margin-top:8px">
      Hadiah ini mengecualikan grup
      <strong>{{ $prize->exclusionGroups->pluck('name')->join(', ') }}</strong>.
      Anggotanya dilewati walaupun dikunci di suatu posisi.
    </div>
  @endif
</div>

<div class="kotak">
  <h2>Posisi yang sudah dikunci ({{ $prize->picks->count() }})</h2>

  @if($prize->picks->isEmpty())
    <p>
      Belum ada posisi yang dikunci, jadi seluruh {{ $prize->quota }} pemenang akan
      diundi acak. Tambahkan nama lewat kotak di bawah.
    </p>
  @else
    @foreach($prize->picks as $pick)
      <form id="ubah-{{ $pick->id }}" method="post" action="{{ route('admin.picks.reposition', [$prize, $pick]) }}">
        @csrf @method('PUT')
      </form>
      <form id="buang-{{ $pick->id }}" method="post" action="{{ route('admin.picks.destroy', [$prize, $pick]) }}">
        @csrf @method('DELETE')
      </form>
    @endforeach

    <table>
      <thead><tr><th style="width:110px">Posisi ke-</th><th>Nama</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @foreach($prize->picks as $pick)
          <tr>
            <td>
              <input type="number" name="position" form="ubah-{{ $pick->id }}"
                     value="{{ $pick->position }}" min="1" style="width:80px">
            </td>
            <td>
              {{ $pick->participant->name }}
              <div class="bantuan">
                {{ $pick->participant->nip }}{{ $pick->participant->department ? ' · '.$pick->participant->department : '' }}
              </div>
            </td>
            <td>
              @if($terundi->contains($pick->participant_id))
                <span class="lencana hijau">Sudah keluar</span>
              @elseif($dikecualikan->contains($pick->participant_id))
                <span class="lencana kuning">Dilewati</span>
                <div class="bantuan">Anggota grup yang dikecualikan</div>
              @elseif($pick->position <= $sudahKe)
                <span class="lencana kuning">Posisi terlewat</span>
                <div class="bantuan">Undian sudah melewati posisi ini</div>
              @elseif($pick->position > $prize->quota)
                <span class="lencana kuning">Di luar kuota</span>
              @else
                <span class="lencana abu">Menunggu</span>
              @endif
            </td>
            <td style="text-align:right;white-space:nowrap">
              <button class="tombol kecil netral" form="ubah-{{ $pick->id }}">Simpan posisi</button>
              <button class="tombol kecil bahaya" form="buang-{{ $pick->id }}">Hapus</button>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="bantuan" style="margin-top:12px">
      Mengisi posisi yang sudah dipakai nama lain akan menukar keduanya, bukan menimpanya.
    </div>
  @endif
</div>

<div class="baris">
  <div class="kotak">
    <h2>Kunci satu nama</h2>

    <div class="medan">
      <label for="kotakCari">Cari peserta</label>
      <input type="text" id="kotakCari" placeholder="Ketik nama atau NIP" autocomplete="off">
    </div>

    <div class="medan">
      <label for="posisiBaru">Posisi ke-</label>
      <input type="number" id="posisiBaru" min="1" max="{{ max(1, $prize->quota) }}"
             placeholder="kosongkan untuk posisi bebas">
      <div class="bantuan">Kosongkan kalau posisinya tidak penting; sistem memakai posisi kosong pertama.</div>
    </div>

    <div class="bantuan" id="statusCari" style="margin-bottom:8px">Ketik minimal 2 huruf.</div>
    <table><tbody id="hasilCari"></tbody></table>
  </div>

  <div class="kotak">
    <h2>Tempel banyak posisi</h2>

    <form method="post" action="{{ route('admin.picks.bulk', $prize) }}">
      @csrf
      <div class="medan">
        <textarea name="daftar" rows="7" required
                  placeholder="7,198701012010011001&#10;12,198903152012012002&#10;198801012011011005"
                  style="width:100%;padding:8px 11px;border:1px solid var(--garis);border-radius:8px;font:inherit;resize:vertical"></textarea>
        <div class="bantuan">
          Satu baris satu nama dengan format <code>posisi,nip</code>. Baris yang hanya
          berisi NIP ditaruh di posisi kosong berikutnya. Pemisah boleh koma, titik koma,
          atau tab, sehingga dua kolom dari Excel bisa ditempel apa adanya.
        </div>
      </div>

      <div class="centang">
        <input type="checkbox" id="ganti" name="ganti" value="1">
        <label for="ganti">Hapus semua posisi terkunci yang ada</label>
      </div>

      <button class="tombol">Simpan posisi</button>
    </form>
  </div>
</div>

<script>
(() => {
  const token   = @json($csrfToken);
  const urlCari = @json($urlCari);
  const urlAdd  = @json($urlTambah);

  const kotak  = document.getElementById('kotakCari');
  const posisi = document.getElementById('posisiBaru');
  const hasil  = document.getElementById('hasilCari');
  const status = document.getElementById('statusCari');

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
      if (nomor !== permintaan) return;

      hasil.innerHTML = '';
      data.results.forEach(p => {
        const tr = document.createElement('tr');
        tr.innerHTML =
          `<td>${p.name}<div class="bantuan">${p.sub || ''}</div></td>` +
          `<td style="text-align:right"><button class="tombol kecil" data-kunci="${p.id}">Kunci</button></td>`;
        hasil.appendChild(tr);
      });

      status.textContent = data.results.length
        ? `${data.results.length} peserta ditemukan.`
        : 'Tidak ada peserta cocok yang belum dikunci.';
    } catch (e) {
      status.textContent = 'Pencarian gagal. Periksa koneksi lalu coba lagi.';
    }
  }

  kotak.addEventListener('input', () => {
    clearTimeout(tunda);
    tunda = setTimeout(cari, 200);
  });

  hasil.addEventListener('click', async (e) => {
    const tombol = e.target.closest('[data-kunci]');
    if (!tombol) return;

    tombol.disabled = true;

    try {
      const res = await fetch(urlAdd, {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},
        body: JSON.stringify({
          participant_id: tombol.dataset.kunci,
          position: posisi.value || null,
        })
      });
      const data = await res.json();

      if (!data.ok) {
        tombol.disabled = false;
        status.textContent = data.message || 'Gagal mengunci posisi.';
        return;
      }

      // Dimuat ulang supaya tabel posisi di atas ikut menyesuaikan.
      location.reload();
    } catch (err) {
      tombol.disabled = false;
      status.textContent = 'Gagal mengunci posisi. Coba lagi.';
    }
  });
})();
</script>
@endsection
