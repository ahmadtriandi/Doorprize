<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Models\Prize;
use App\Models\PrizePick;
use App\Services\DrawService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PickController extends Controller
{
    public function index(Prize $prize, DrawService $draw)
    {
        $prize->load(['picks.participant', 'winners.participant', 'exclusionGroups']);

        $dikecualikan = $draw->anggotaDikecualikan($prize);

        return view('admin.prizes.picks', compact('prize', 'dikecualikan'));
    }

    /** Pencarian langsung untuk kotak tambah pemenang. */
    public function search(Request $request, Prize $prize)
    {
        $cari = trim((string) $request->query('q'));

        if (mb_strlen($cari) < 2) {
            return response()->json(['results' => []]);
        }

        $sudah = $prize->picks()->pluck('participant_id');

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

    public function store(Request $request, Prize $prize)
    {
        $request->validate([
            'participant_id' => ['required', 'exists:participants,id'],
            'position'       => ['nullable', 'integer', 'min:1', 'max:1000'],
        ], [], ['position' => 'posisi']);

        $id = (int) $request->input('participant_id');
        $posisi = (int) ($request->input('position') ?: $this->posisiKosong($prize));

        $bentrok = $prize->picks()->where('position', $posisi)->exists();
        $sudahAda = $prize->picks()->where('participant_id', $id)->exists();

        if ($bentrok) {
            $pesan = "Posisi {$posisi} sudah dipakai nama lain.";

            return $request->expectsJson()
                ? response()->json(['ok' => false, 'message' => $pesan], 422)
                : back()->with('error', $pesan);
        }

        if (! $sudahAda) {
            $prize->picks()->create(['participant_id' => $id, 'position' => $posisi]);
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('status', "Nama dikunci di posisi {$posisi}.");
    }

    /** Ubah posisi satu nama yang sudah dikunci. */
    public function reposition(Request $request, Prize $prize, PrizePick $pick)
    {
        abort_unless($pick->prize_id === $prize->id, 404);

        $data = $request->validate([
            'position' => ['required', 'integer', 'min:1', 'max:1000'],
        ], [], ['position' => 'posisi']);

        $bentrok = $prize->picks()
            ->where('position', $data['position'])
            ->where('id', '!=', $pick->id)
            ->first();

        // Kalau posisinya sudah dipakai, keduanya bertukar tempat supaya tidak
        // ada nama yang diam-diam kehilangan posisinya.
        if ($bentrok) {
            $bentrok->update(['position' => $pick->position]);
        }

        $pick->update(['position' => $data['position']]);

        return back()->with('status', $bentrok
            ? 'Posisi ditukar dengan nama yang sudah menempati posisi itu.'
            : 'Posisi diperbarui.');
    }

    private function posisiKosong(Prize $prize): int
    {
        $terpakai = $prize->picks()->pluck('position')->all();

        for ($i = 1; $i <= 1000; $i++) {
            if (! in_array($i, $terpakai, true)) {
                return $i;
            }
        }

        return 1;
    }

    /**
     * Tempel banyak posisi sekaligus dengan format "posisi,nip" per baris.
     * Baris yang hanya berisi NIP ditaruh di posisi kosong berikutnya.
     */
    public function bulk(Request $request, Prize $prize)
    {
        $request->validate([
            'daftar' => ['required', 'string', 'max:200000'],
            'ganti'  => ['nullable', 'boolean'],
        ], [], ['daftar' => 'daftar NIP']);

        $baris = collect(preg_split('/[\r\n]+/', $request->input('daftar')))
            ->map(fn ($b) => trim($b))
            ->filter()
            ->values();

        $catatan = [];
        $berhasil = 0;

        DB::transaction(function () use ($prize, $baris, $request, &$catatan, &$berhasil) {
            if ($request->boolean('ganti')) {
                $prize->picks()->delete();
            }

            foreach ($baris as $i => $isi) {
                $bagian = preg_split('/[,;\t]+/', $isi);
                $bagian = array_values(array_filter(array_map('trim', $bagian), fn ($b) => $b !== ''));

                if (count($bagian) >= 2 && ctype_digit($bagian[0])) {
                    $posisi = (int) $bagian[0];
                    $nip = $bagian[1];
                } else {
                    $posisi = null;
                    $nip = $bagian[0] ?? '';
                }

                $peserta = Participant::where('nip', $nip)->first();

                if (! $peserta) {
                    $catatan[] = 'Baris '.($i + 1).": NIP tidak ditemukan ({$nip})";
                    continue;
                }

                if ($prize->picks()->where('participant_id', $peserta->id)->exists()) {
                    $catatan[] = 'Baris '.($i + 1).": {$peserta->name} sudah dikunci di posisi lain";
                    continue;
                }

                $posisi = $posisi ?: $this->posisiKosong($prize);

                if ($prize->picks()->where('position', $posisi)->exists()) {
                    $catatan[] = 'Baris '.($i + 1).": posisi {$posisi} sudah dipakai";
                    continue;
                }

                $prize->picks()->create(['participant_id' => $peserta->id, 'position' => $posisi]);
                $berhasil++;
            }
        });

        return back()
            ->with('status', "{$berhasil} posisi dikunci.")
            ->with('import_errors', array_slice($catatan, 0, 20));
    }

    public function destroy(Prize $prize, PrizePick $pick)
    {
        abort_unless($pick->prize_id === $prize->id, 404);

        $pick->delete();

        return back()->with('status', 'Nama dikeluarkan dari antrean.');
    }
}
