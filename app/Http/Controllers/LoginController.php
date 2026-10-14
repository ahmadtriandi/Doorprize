<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function show()
    {
        if (request()->session()->get('doorprize_admin')) {
            return redirect()->route('admin.dashboard');
        }

        return view('login');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = config('doorprize.admin_user');
        $pass = config('doorprize.admin_password');
        $hash = config('doorprize.admin_password_hash');

        $passwordOk = $hash
            ? Hash::check($data['password'], $hash)
            : hash_equals((string) $pass, $data['password']);

        if (! hash_equals((string) $user, $data['username']) || ! $passwordOk) {
            throw ValidationException::withMessages([
                'username' => 'Nama pengguna atau kata sandi salah.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('doorprize_admin', $data['username']);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request)
    {
        $request->session()->forget('doorprize_admin');
        $request->session()->regenerate();

        return redirect()->route('login');
    }
}
