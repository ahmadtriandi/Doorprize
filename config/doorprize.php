<?php

return [
    /*
     * Akun panitia. Isi di file .env:
     *   DOORPRIZE_USER=panitia
     *   DOORPRIZE_PASSWORD=rahasia123
     *
     * Untuk produksi sebaiknya pakai hash bcrypt:
     *   DOORPRIZE_PASSWORD_HASH="$2y$12$..."
     */
    'admin_user'          => env('DOORPRIZE_USER', 'panitia'),
    'admin_password'      => env('DOORPRIZE_PASSWORD', 'doorprize2026'),
    'admin_password_hash' => env('DOORPRIZE_PASSWORD_HASH'),
];
