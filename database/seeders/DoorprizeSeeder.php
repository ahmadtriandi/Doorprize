<?php

namespace Database\Seeders;

use App\Models\Participant;
use App\Models\Prize;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class DoorprizeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'event_title'        => 'Excellent Together',
            'event_subtitle'     => 'Doorprize Gathering',
            'background_color'   => '#8B5CF6',
            'name_columns'       => 'auto',
            'spin_duration'      => '5',
            'board_mode'         => 'daftar',
            'stop_style'         => 'langsung',
            'overflow_mode'      => 'gulir_manual',
            'min_font'           => '16',
            'draw_moment'        => 'start',
            'layout_mode'        => 'otomatis',
            'auto_stop'          => '',
            'sound_enabled'      => '1',
            'one_win_per_person' => '1',
            'show_nip'           => '',
            'name_caption'       => 'tidak',
            'winner_refresh'     => '20',
            'lock_screen_menu'   => '',
        ] as $key => $value) {
            Setting::put($key, $value);
        }

        $nama = [
            'Ditha Paramitha', 'Dwi Sadela Maharangitha', 'Yunus Anggoro Muchlis', 'Moch Nurudin',
            'Kusman', 'Agus Rifai', 'Agustian Rudiana', 'Nana Supriana', 'Iskandar Zulkarnaen Lubis',
            'Nail Widya Satya', 'Nasum Mugiana', 'Indira Anjanique', 'Dian Purnama',
            'Tri Candra Sakti Kalispati', 'Raddy Kurniady Arismansyah', 'Andrianto Hapsoro',
            'Ary Sutejo', 'Ari Gumilar', 'Rifal Gusmara Maulana', 'Atep Supriyadi',
            'Alirman Djamereng', 'Deni Nugraha', 'R. Iman Sigit Soeharto', 'Andri Setiawan',
            'Kamidi', 'Mochammad Taufik', 'Nenden Nova Nurhaliza', 'Erik Ongko Dwiyanto',
        ];

        foreach ($nama as $i => $n) {
            Participant::updateOrCreate(
                ['nip' => sprintf('19%02d%03d', $i + 1, 1000 + $i)],
                ['name' => $n, 'department' => null, 'is_active' => true]
            );
        }

        Prize::updateOrCreate(
            ['name' => 'Mini Blender Kris 0.5L - Putih'],
            ['quota' => 5, 'per_draw' => 1, 'sort_order' => 1, 'is_selected' => true]
        );

        Prize::updateOrCreate(
            ['name' => 'Sepeda Lipat'],
            ['quota' => 1, 'per_draw' => 1, 'sort_order' => 2]
        );

        Prize::updateOrCreate(
            ['name' => 'Voucher Belanja Rp500.000'],
            ['quota' => 10, 'per_draw' => 2, 'sort_order' => 3]
        );
    }
}
