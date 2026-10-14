<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prize;
use App\Models\Winner;
use Illuminate\Http\Request;

class WinnerController extends Controller
{
    public function index(Request $request)
    {
        $prizeId = $request->query('prize');

        $winners = Winner::with(['participant', 'prize'])
            ->when($prizeId, fn ($q) => $q->where('prize_id', $prizeId))
            ->latest('drawn_at')
            ->paginate(30)
            ->withQueryString();

        $prizes = Prize::withCount('winners')->orderBy('sort_order')->get();

        return view('admin.winners.index', compact('winners', 'prizes', 'prizeId'));
    }

    public function claim(Winner $winner)
    {
        $winner->update(['is_claimed' => ! $winner->is_claimed]);

        return back()->with('status', 'Status pengambilan hadiah diperbarui.');
    }

    /** Batalkan satu pemenang, misalnya karena orangnya tidak hadir. */
    public function destroy(Winner $winner)
    {
        $nama = $winner->participant->name;
        $winner->delete();

        return back()->with('status', "{$nama} dibatalkan sebagai pemenang dan kembali masuk undian.");
    }

    /** Ulangi undian satu hadiah dari nol. */
    public function reset(Prize $prize)
    {
        $prize->winners()->delete();

        return back()->with('status', "Undian {$prize->name} direset.");
    }

    /**
     * Hapus seluruh pemenang dari semua hadiah.
     *
     * Dipakai untuk membersihkan sisa uji coba sebelum acara. Panitia harus
     * mengetik kata konfirmasi karena tindakan ini tidak bisa dibatalkan dan
     * paling berbahaya kalau tidak sengaja ditekan saat acara berlangsung.
     */
    public function resetAll(Request $request)
    {
        $request->validate([
            'konfirmasi' => ['required', 'string'],
        ], [], ['konfirmasi' => 'kata konfirmasi']);

        if (strtoupper(trim($request->input('konfirmasi'))) !== 'HAPUS') {
            return back()->with('error', 'Ketik HAPUS pada kotak konfirmasi untuk melanjutkan.');
        }

        $jumlah = Winner::count();

        if ($jumlah === 0) {
            return back()->with('status', 'Belum ada pemenang yang perlu dihapus.');
        }

        Winner::query()->delete();

        return back()->with('status', "{$jumlah} data pemenang dihapus. Seluruh hadiah kembali ke kuota penuh.");
    }

    public function export()
    {
        $winners = Winner::with(['participant', 'prize'])->orderBy('prize_id')->orderBy('id')->get();

        return response()->streamDownload(function () use ($winners) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['hadiah', 'putaran', 'nip', 'nama', 'unit', 'waktu_undi', 'sudah_diambil']);

            foreach ($winners as $w) {
                fputcsv($out, [
                    $w->prize->name,
                    $w->round,
                    $w->participant->nip,
                    $w->participant->name,
                    $w->participant->department,
                    optional($w->drawn_at)->format('d/m/Y H:i'),
                    $w->is_claimed ? 'ya' : 'belum',
                ]);
            }

            fclose($out);
        }, 'pemenang-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
