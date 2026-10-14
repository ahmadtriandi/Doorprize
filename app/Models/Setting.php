<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Cache satu request saja.
     *
     * Sebelumnya nilai disimpan di Cache::rememberForever(). Cara itu rapuh:
     * kalau cache store berbeda antar proses (misalnya file cache dengan izin
     * berbeda, atau server dijalankan lewat beberapa worker), nilai lama bisa
     * ikut terbaca sehingga perubahan di dashboard tidak muncul di layar.
     * Tabel ini hanya belasan baris, jadi satu query per request sudah murah.
     */
    protected static ?array $memo = null;

    public static function get(string $key, $default = null)
    {
        static::$memo ??= static::pluck('value', 'key')->all();

        // Nilai kosong tetap dihormati: centang yang dimatikan tersimpan
        // sebagai string kosong dan tidak boleh jatuh kembali ke bawaan.
        return static::$memo[$key] ?? $default;
    }

    public static function put(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        static::$memo = null;
    }

    public static function lupakan(): void
    {
        static::$memo = null;
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::$memo = null);
        static::deleted(fn () => static::$memo = null);
    }
}
