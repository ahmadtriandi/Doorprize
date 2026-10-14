<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExclusionGroup;
use App\Models\Participant;
use App\Models\Prize;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index()
    {
        $groups = ExclusionGroup::withCount(['participants', 'prizes'])->orderBy('name')->get();

        return view('admin.groups.index', compact('groups'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:exclusion_groups,name'],
            'note' => ['nullable', 'string', 'max:200'],
        ], [], ['name' => 'nama grup']);

        $group = ExclusionGroup::create($data);

        return redirect()
            ->route('admin.groups.show', $group)
            ->with('status', 'Grup dibuat. Sekarang tambahkan anggotanya.');
    }

    public function show(Request $request, ExclusionGroup $group)
    {
        $group->load('participants');

        $cari = trim((string) $request->query('q'));
        $sudah = $group->participants->pluck('id');

        // Kandidat hanya ditampilkan saat panitia mencari, supaya daftar ribuan
        // peserta tidak dimuat sekaligus di halaman ini.
        $kandidat = $cari === '' ? collect() : Participant::query()
            ->whereNotIn('id', $sudah)
            ->where(fn ($q) => $q->where('name', 'like', "%{$cari}%")->orWhere('nip', 'like', "%{$cari}%"))
            ->orderBy('name')
            ->limit(30)
            ->get();

        $units = Participant::query()
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        $prizes = Prize::withCount('winners')->orderBy('sort_order')->orderBy('id')->get();
        $terpasang = $group->prizes->pluck('id');

        return view('admin.groups.show', compact('group', 'kandidat', 'cari', 'units', 'prizes', 'terpasang'));
    }

    /** Pencarian langsung saat panitia mengetik, dipanggil dari halaman grup. */
    public function search(Request $request, ExclusionGroup $group)
    {
        $cari = trim((string) $request->query('q'));

        if (mb_strlen($cari) < 2) {
            return response()->json(['results' => []]);
        }

        $sudah = $group->participants()->pluck('participants.id');

        $hasil = Participant::query()
            ->whereNotIn('id', $sudah)
            ->where(fn ($q) => $q->where('name', 'like', "%{$cari}%")->orWhere('nip', 'like', "%{$cari}%"))
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'nip', 'department']);

        return response()->json([
            'results' => $hasil->map(fn ($p) => [
                'id'   => $p->id,
                'name' => $p->name,
                'sub'  => trim($p->nip.($p->department ? ' · '.$p->department : '')),
            ])->values(),
        ]);
    }

    public function update(Request $request, ExclusionGroup $group)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:exclusion_groups,name,'.$group->id],
            'note' => ['nullable', 'string', 'max:200'],
        ], [], ['name' => 'nama grup']);

        $group->update($data);

        return back()->with('status', 'Grup diperbarui.');
    }

    public function destroy(ExclusionGroup $group)
    {
        $group->delete();

        return redirect()->route('admin.groups.index')->with('status', 'Grup dihapus.');
    }

    /** Tambah satu peserta, atau seluruh anggota satu unit sekaligus. */
    public function addMember(Request $request, ExclusionGroup $group)
    {
        $request->validate([
            'participant_id' => ['nullable', 'exists:participants,id'],
            'department'     => ['nullable', 'string', 'max:100'],
        ]);

        if ($id = $request->input('participant_id')) {
            $group->participants()->syncWithoutDetaching([$id]);

            if ($request->expectsJson()) {
                $p = Participant::find($id);

                return response()->json([
                    'ok'      => true,
                    'anggota' => [
                        'id'   => $p->id,
                        'name' => $p->name,
                        'sub'  => trim($p->nip.($p->department ? ' · '.$p->department : '')),
                    ],
                    'jumlah'  => $group->participants()->count(),
                ]);
            }

            return back()->with('status', 'Peserta ditambahkan ke grup.');
        }

        if ($unit = $request->input('department')) {
            $ids = Participant::where('department', $unit)->pluck('id');
            $group->participants()->syncWithoutDetaching($ids);

            return back()->with('status', "{$ids->count()} peserta dari unit {$unit} ditambahkan.");
        }

        return back()->with('error', 'Pilih peserta atau unit terlebih dahulu.');
    }

    /**
     * Tambah banyak anggota sekaligus dari daftar yang ditempel panitia.
     *
     * Dipisah oleh baris baru, koma, titik koma, atau tab, sehingga satu kolom
     * NIP yang disalin langsung dari Excel bisa ditempel apa adanya. Dicocokkan
     * ke NIP lebih dulu karena unik; nama dipakai hanya kalau NIP tidak cocok
     * dan namanya cuma dimiliki satu orang.
     */
    public function bulkMembers(Request $request, ExclusionGroup $group)
    {
        $request->validate([
            'daftar' => ['required', 'string', 'max:50000'],
        ], [], ['daftar' => 'daftar peserta']);

        $baris = collect(preg_split('/[\r\n,;\t]+/', $request->input('daftar')))
            ->map(fn ($b) => trim($b))
            ->filter()
            ->unique()
            ->values();

        if ($baris->isEmpty()) {
            return back()->with('error', 'Daftar yang ditempel kosong.');
        }

        $sudah = $group->participants()->pluck('participants.id')->all();

        $cocokNip = Participant::whereIn('nip', $baris)->get();
        $sisaBaris = $baris->diff($cocokNip->pluck('nip'));

        // Nama hanya dipakai untuk baris yang belum tertangani NIP, dan hanya
        // kalau nama itu tidak dimiliki lebih dari satu peserta.
        $cocokNama = collect();
        $ganda = collect();

        if ($sisaBaris->isNotEmpty()) {
            $kandidat = Participant::whereIn('name', $sisaBaris)->get()->groupBy('name');

            foreach ($kandidat as $nama => $orang) {
                $orang->count() === 1 ? $cocokNama->push($orang->first()) : $ganda->push($nama);
            }
        }

        $ketemu = $cocokNip->concat($cocokNama)->unique('id');
        $baru = $ketemu->reject(fn ($p) => in_array($p->id, $sudah, true));

        $group->participants()->syncWithoutDetaching($ketemu->pluck('id')->all());

        $tidakKetemu = $sisaBaris
            ->diff($cocokNama->pluck('name'))
            ->diff($ganda)
            ->values();

        $pesan = "{$baru->count()} peserta ditambahkan";
        if ($ketemu->count() - $baru->count() > 0) {
            $pesan .= ', '.($ketemu->count() - $baru->count()).' sudah ada di grup';
        }
        $pesan .= '.';

        $catatan = [];
        foreach ($tidakKetemu as $b) {
            $catatan[] = "Tidak ditemukan: {$b}";
        }
        foreach ($ganda as $n) {
            $catatan[] = "Nama dimiliki lebih dari satu peserta, tambahkan lewat NIP: {$n}";
        }

        return back()
            ->with('status', $pesan)
            ->with('import_errors', array_slice($catatan, 0, 30));
    }

    public function removeMember(Request $request, ExclusionGroup $group, Participant $participant)
    {
        $group->participants()->detach($participant->id);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'jumlah' => $group->participants()->count()]);
        }

        return back()->with('status', 'Peserta dikeluarkan dari grup.');
    }

    /** Tentukan hadiah mana saja yang memakai grup ini sebagai pengecualian. */
    public function syncPrizes(Request $request, ExclusionGroup $group)
    {
        $request->validate([
            'prizes'   => ['nullable', 'array'],
            'prizes.*' => ['exists:prizes,id'],
        ]);

        $group->prizes()->sync($request->input('prizes', []));

        return back()->with('status', 'Hadiah yang memakai grup ini diperbarui.');
    }
}
