<?php

namespace App\Http\Controllers;

use App\Models\Prize;
use App\Models\Setting;
use App\Services\DrawService;
use Illuminate\Http\Request;
use Throwable;

class DrawController extends Controller
{
    public function __construct(private DrawService $draw)
    {
    }

    /** Pengaturan tampilan yang dipakai bersama oleh layar undian dan layar pemenang. */
    private function tampilan(): array
    {
        return [
                'title'      => Setting::get('event_title', 'Excellent Together'),
            'show_title' => Setting::get('show_title') === null ? true : (bool) Setting::get('show_title'),
            'case'       => Setting::get('name_case', 'asli'),
                'subtitle'   => Setting::get('event_subtitle', 'Doorprize'),
                'background' => Setting::get('background_path'),
                'bg_color'   => Setting::get('background_color', '#8B5CF6'),
                'logo_left'  => Setting::get('logo_left_path'),
                'logo_right' => Setting::get('logo_right_path'),
                // 'auto' berarti jumlah kolom dihitung sendiri dari jumlah peserta
                // dan ruang yang tersedia; selain itu dipakai angka tetap 1-10.
                'columns'    => Setting::get('name_columns', 'auto') === 'auto'
                    ? 'auto'
                    : min(10, max(1, (int) Setting::get('name_columns'))),
                'duration'   => (int) Setting::get('spin_duration', 5),
                'board'      => Setting::get('board_mode', 'daftar') === 'kosong' ? 'kosong' : 'daftar',
                'auto_stop'  => (bool) Setting::get('auto_stop', '1'),
                // Nilai lama show_nip tetap dihormati untuk pemasangan yang belum
            // pernah menyimpan pengaturan baru ini.
            // 0 detik berarti layar pemenang tidak menyegarkan dirinya sendiri.
            'refresh'    => max(0, min(300, (int) Setting::get('winner_refresh', 20))),
            'caption'    => Setting::get('name_caption')
                ?: (Setting::get('show_nip') ? 'nip' : 'tidak'),
                'stop_style' => Setting::get('stop_style', 'langsung') === 'melambat' ? 'melambat' : 'langsung',
                'overflow'   => in_array(Setting::get('overflow_mode', 'gulir_manual'), ['kecilkan', 'gulir'], true)
                    ? Setting::get('overflow_mode')
                    : 'gulir_manual',
                'min_font'   => min(34, max(10, (int) Setting::get('min_font', 16))),
                'draw_moment'=> Setting::get('draw_moment', 'start') === 'stop' ? 'stop' : 'start',
                'layout'     => in_array(Setting::get('layout_mode', 'otomatis'), ['tegak', 'samping'], true)
                    ? Setting::get('layout_mode')
                    : 'otomatis',
                'sound'      => (bool) Setting::get('sound_enabled', '1'),
                'spin_sound' => ($v = Setting::get('spin_sound_path')) ? asset('storage/'.$v) : null,
                'win_sound'  => ($v = Setting::get('win_sound_path')) ? asset('storage/'.$v) : null,
        ];
    }

    /** Layar undian yang ditampilkan ke proyektor. */
    public function index()
    {
        $prize = Prize::where('is_selected', true)->first()
            ?? Prize::orderBy('sort_order')->first();

        $participants = $prize ? $this->draw->eligible($prize) : collect();

        $settings = $this->tampilan();

        // Disiapkan di sini, bukan di Blade: @json() tidak bisa mengurai closure.
        $pool = $participants->map(fn ($p) => [
            'id'   => $p->id,
            'name' => $p->name,
            'nip'  => $p->nip,
            'unit' => $p->department,
        ])->values();

        // Dipakai menu pintas di pojok layar untuk berpindah hadiah tanpa membuka panel.
        $prizes = Prize::withCount('winners')->orderBy('sort_order')->orderBy('id')->get();

        return view('draw.index', compact('prize', 'prizes', 'participants', 'pool', 'settings'));
    }

    /** Endpoint yang dipanggil tombol STOP. */
    public function spin(Request $request, Prize $prize)
    {
        $request->validate(['count' => ['nullable', 'integer', 'min:1', 'max:100']]);

        try {
            $winners = $this->draw->spin($prize, $request->integer('count') ?: null);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'ok'      => true,
            'winners' => $winners->map(fn ($w) => [
                'id'   => $w->participant->id,
                'nip'  => $w->participant->nip,
                'name' => $w->participant->name,
                'unit' => $w->participant->department,
            ])->values(),
            'remaining' => $prize->fresh()->remaining_slot,
        ]);
    }

    /**
     * Pindah hadiah langsung dari menu di layar undian.
     *
     * Terbuka tanpa login supaya operator proyektor bisa berpindah cepat.
     * Panitia bisa menguncinya lewat Tampilan layar kalau layar dipasang
     * di tempat yang bisa disentuh siapa saja.
     */
    public function select(Prize $prize)
    {
        $terkunci = (bool) Setting::get('lock_screen_menu', '');

        if ($terkunci && ! session('doorprize_admin')) {
            return redirect()->route('draw')
                ->with('error', 'Menu layar dikunci. Masuk sebagai panitia untuk mengganti hadiah.');
        }

        Prize::where('is_selected', true)->update(['is_selected' => false]);
        $prize->update(['is_selected' => true]);

        return redirect()->route('draw');
    }

    /**
     * Layar daftar pemenang: foto hadiah di kiri, nama pemenang di kanan.
     *
     * Terbuka tanpa login karena ditampilkan di proyektor, dan hanya membaca
     * data sehingga aman diakses siapa pun yang ada di jaringan acara.
     */
    public function winners(Request $request)
    {
        $prizes = Prize::withCount('winners')->orderBy('sort_order')->orderBy('id')->get();

        $diminta = $request->integer('hadiah');
        $prize = $diminta
            ? $prizes->firstWhere('id', $diminta)
            : ($prizes->firstWhere('is_selected', true) ?? $prizes->first());

        $winners = $prize
            ? $prize->winners()->with('participant')->orderBy('round')->orderBy('id')->get()
            : collect();

        return view('draw.winners', [
            'prize'    => $prize,
            'prizes'   => $prizes,
            'winners'  => $winners,
            'settings' => $this->tampilan(),
        ]);
    }

    /** Dipakai layar untuk menyegarkan daftar peserta tanpa reload penuh. */
    public function state(Prize $prize)
    {
        return response()->json([
            'prize'     => ['id' => $prize->id, 'name' => $prize->name, 'remaining' => $prize->remaining_slot],
            'pool'      => $this->draw->eligible($prize)->map(fn ($p) => ['id' => $p->id, 'name' => $p->name])->values(),
        ]);
    }
}
