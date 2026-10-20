<?php

return [
    /*
     * Akun panitia (akses penuh ke dashboard). Isi di file .env:
     *   DOORPRIZE_USER=panitia
     *   DOORPRIZE_PASSWORD_HASH='$2y$12$...'
     */
    'admin_user'          => env('DOORPRIZE_USER', 'panitia'),
    'admin_password'      => env('DOORPRIZE_PASSWORD', 'doorprize2026'),
    'admin_password_hash' => env('DOORPRIZE_PASSWORD_HASH'),

    /*
     * Akun operator: hanya membuka layar undian dan melihat daftar pemenang.
     * Tidak bisa mengubah peserta, hadiah, pengaturan, atau menghapus pemenang.
     *
     *   DOORPRIZE_OPERATOR_USER=operator
     *   DOORPRIZE_OPERATOR_PASSWORD_HASH='$2y$12$...'
     *
     * Kalau sandi operator tidak diisi sama sekali, akun operator nonaktif.
     */
    'operator_user'          => env('DOORPRIZE_OPERATOR_USER', 'operator'),
    'operator_password'      => env('DOORPRIZE_OPERATOR_PASSWORD'),
    'operator_password_hash' => env('DOORPRIZE_OPERATOR_PASSWORD_HASH'),
];
