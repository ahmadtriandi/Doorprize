<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DrawSession;
use App\Models\Prize;
use App\Models\Winner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    public function index()
    {
        $sessions = DrawSession::withCount('prizes')->orderBy('sort_order')->orderBy('id')->get();

        $kuota = Prize::whereNotNull('draw_session_id')
            ->selectRaw('draw_session_id, SUM(quota) as total')
            ->groupBy('draw_session_id')
            ->pluck('total', 'draw_session_id');

        return view('admin.sessions.index', compact('sessions', 'kuota'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ], [], ['name' => 'nama sesi']);

        $sesi = DrawSession::create($data + [
            'rows_per_column' => 6,
            'sort_order'      => (int) DrawSession::max('sort_order') + 1,
        ]);

        return redirect()
            ->route('admin.sessions.show', $sesi)
            ->with('status', 'Sesi dibuat. Sekarang pilih hadiah yang masuk sesi ini.');
    }

    public function show(DrawSession $sesi)
    {
        $prizes = Prize::with('drawSession')
            ->withCount('winners')
            ->orderByRaw('CASE WHEN draw_session_id = ? THEN 0 ELSE 1 END', [$sesi->id])
            ->orderBy('session_row')
            ->orderBy('session_order')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $labels = Prize::whereNotNull('group_label')->distinct()->orderBy('group_label')->pluck('group_label');

        return view('admin.sessions.show', compact('sesi', 'prizes', 'labels'));
    }

    /**
     * Simpan nama sesi dan susunan hadiahnya sekaligus.
     *
     * Hadiah yang dicentang masuk sesi ini (dan otomatis keluar dari sesi lain),
     * yang tidak dicentang dikeluarkan dari sesi ini.
     */
    public function update(Request $request, DrawSession $sesi)
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:100'],
            'rows_per_column'       => ['required', 'integer', 'min:1', 'max:20'],
            'prizes'                => ['nullable', 'array'],
            'prizes.*.include'      => ['nullable', 'boolean'],
            'prizes.*.row'          => ['nullable', 'integer', 'min:1', 'max:50'],
            'prizes.*.group_label'  => ['nullable', 'string', 'max:50'],
            'prizes.*.order'        => ['nullable', 'integer', 'min:0', 'max:999'],
        ], [], [
            'name'            => 'nama sesi',
            'rows_per_column' => 'baris per kolom',
        ]);

        DB::transaction(function () use ($sesi, $data) {
            $sesi->update([
                'name'            => $data['name'],
                'rows_per_column' => $data['rows_per_column'],
            ]);

            foreach ($data['prizes'] ?? [] as $id => $baris) {
                $prize = Prize::find($id);
                if (! $prize) {
                    continue;
                }

                if (! empty($baris['include'])) {
                    $prize->update([
                        'draw_session_id' => $sesi->id,
                        'session_row'     => max(1, (int) ($baris['row'] ?? 1)),
                        'group_label'     => trim((string) ($baris['group_label'] ?? '')) ?: null,
                        'session_order'   => (int) ($baris['order'] ?? 0),
                    ]);
                } elseif ((int) $prize->draw_session_id === (int) $sesi->id) {
                    $prize->update(['draw_session_id' => null]);
                }
            }
        });

        return back()->with('status', 'Susunan sesi disimpan.');
    }

    public function destroy(DrawSession $sesi)
    {
        // Hadiah dan pemenangnya tetap ada; hanya pengelompokannya yang hilang.
        $sesi->delete();

        return redirect()->route('admin.sessions.index')->with('status', 'Sesi dihapus. Hadiahnya tetap ada.');
    }

    /** Hapus pemenang seluruh hadiah dalam sesi, biasanya setelah gladi bersih. */
    public function reset(Request $request, DrawSession $sesi)
    {
        if (strtoupper(trim((string) $request->input('konfirmasi'))) !== 'HAPUS') {
            return back()->with('error', 'Ketik HAPUS pada kotak konfirmasi untuk mengulang sesi ini.');
        }

        $jumlah = Winner::whereIn('prize_id', $sesi->prizes()->pluck('id'))->delete();

        return back()->with('status', "{$jumlah} pemenang di sesi ini dihapus. Seluruh kotak kembali kosong.");
    }
}
