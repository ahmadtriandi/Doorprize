<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public const KEYS = [
        'event_title', 'event_subtitle', 'background_path', 'background_color',
        'logo_left_path', 'logo_right_path', 'name_columns', 'spin_duration',
        'one_win_per_person', 'show_nip', 'name_caption', 'winner_refresh', 'lock_screen_menu', 'board_mode', 'auto_stop',
        'sound_enabled', 'spin_sound_path', 'win_sound_path', 'stop_style',
        'overflow_mode', 'min_font', 'draw_moment', 'layout_mode',
    ];

    public function edit()
    {
        $settings = collect(self::KEYS)->mapWithKeys(fn ($k) => [$k => Setting::get($k)]);

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'event_title'      => ['nullable', 'string', 'max:80'],
            'event_subtitle'   => ['nullable', 'string', 'max:80'],
            'background_color' => ['nullable', 'string', 'max:20'],
            // Boleh 'auto' atau angka 1-10.
            'name_columns'     => ['required', 'regex:/^(auto|10|[1-9])$/'],
            'layout_mode'      => ['required', 'in:otomatis,tegak,samping'],
            'name_caption'     => ['required', 'in:tidak,nip,unit,keduanya'],
            'winner_refresh'   => ['required', 'integer', 'min:0', 'max:300'],
            'board_mode'       => ['required', 'in:daftar,kosong'],
            'stop_style'       => ['required', 'in:langsung,melambat'],
            'overflow_mode'    => ['required', 'in:kecilkan,gulir_manual,gulir'],
            'draw_moment'      => ['required', 'in:start,stop'],
            'min_font'         => ['required', 'integer', 'min:10', 'max:34'],
            'spin_duration'    => ['nullable', 'integer', 'min:1', 'max:60', 'required_with:auto_stop'],
            'background'       => ['nullable', 'image', 'max:6144'],
            'logo_left'        => ['nullable', 'image', 'max:2048'],
            'logo_right'       => ['nullable', 'image', 'max:2048'],
            'spin_sound'       => ['nullable', 'file', 'mimes:mp3,mpga,wav,ogg,m4a', 'max:5120'],
            'win_sound'        => ['nullable', 'file', 'mimes:mp3,mpga,wav,ogg,m4a', 'max:5120'],
        ], [], [
            'background'    => 'gambar latar',
            'name_columns'  => 'jumlah kolom nama',
            'board_mode'    => 'tampilan papan',
            'stop_style'    => 'cara berhenti',
            'overflow_mode' => 'penanganan nama berlebih',
            'draw_moment'   => 'waktu pengundian',
            'layout_mode'   => 'tata letak',
            'name_caption'  => 'keterangan di bawah nama',
            'winner_refresh' => 'penyegaran layar pemenang',
            'min_font'      => 'ukuran huruf minimal',
            'spin_duration' => 'durasi putaran',
            'spin_sound'    => 'musik putaran',
            'win_sound'     => 'suara pemenang',
        ]);

        $berkas = [
            'background' => ['background_path', 'tampilan'],
            'logo_left'  => ['logo_left_path', 'tampilan'],
            'logo_right' => ['logo_right_path', 'tampilan'],
            'spin_sound' => ['spin_sound_path', 'suara'],
            'win_sound'  => ['win_sound_path', 'suara'],
        ];

        foreach ($berkas as $input => [$key, $folder]) {
            if ($request->hasFile($input)) {
                $old = Setting::get($key);
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
                Setting::put($key, $request->file($input)->store($folder, 'public'));
            }
        }

        foreach (['event_title', 'event_subtitle', 'background_color', 'name_columns', 'board_mode', 'stop_style', 'overflow_mode', 'min_font', 'draw_moment', 'layout_mode', 'name_caption', 'winner_refresh'] as $key) {
            Setting::put($key, $data[$key] ?? null);
        }

        Setting::put('auto_stop', $request->boolean('auto_stop') ? '1' : '');
        Setting::put('spin_duration', $data['spin_duration'] ?? 5);
        Setting::put('sound_enabled', $request->boolean('sound_enabled') ? '1' : '');

        Setting::put('one_win_per_person', $request->boolean('one_win_per_person') ? '1' : '');
        Setting::put('lock_screen_menu', $request->boolean('lock_screen_menu') ? '1' : '');

        return back()->with('status', 'Tampilan layar undian diperbarui.');
    }

    public function removeImage(string $key)
    {
        abort_unless(in_array($key, [
            'background_path', 'logo_left_path', 'logo_right_path',
            'spin_sound_path', 'win_sound_path',
        ], true), 404);

        if ($path = Setting::get($key)) {
            Storage::disk('public')->delete($path);
        }

        Setting::put($key, null);

        return back()->with('status', 'Berkas dihapus.');
    }
}
