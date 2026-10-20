<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function show(Request $request)
    {
        if ($request->session()->get('doorprize_admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($request->session()->get('doorprize_operator')) {
            return redirect()->route('operator.home');
        }

        return view('login');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $peran = $this->cocokkan($data['username'], $data['password']);

        if (! $peran) {
            throw ValidationException::withMessages([
                'username' => 'Nama pengguna atau kata sandi salah.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->forget(['doorprize_admin', 'doorprize_operator']);

        if ($peran === 'admin') {
            $request->session()->put('doorprize_admin', $data['username']);

            return redirect()->intended(route('admin.dashboard'));
        }

        // Alamat tujuan sebelum login diabaikan untuk operator, karena biasanya
        // itu halaman dashboard yang memang tidak boleh ia buka.
        $request->session()->forget('url.intended');
        $request->session()->put('doorprize_operator', $data['username']);

        return redirect()->route('operator.home');
    }

    public function destroy(Request $request)
    {
        $request->session()->forget(['doorprize_admin', 'doorprize_operator']);
        $request->session()->regenerate();

        return redirect()->route('login');
    }

    /** @return 'admin'|'operator'|null */
    private function cocokkan(string $user, string $sandi): ?string
    {
        if ($this->sandiBenar($sandi, config('doorprize.admin_password_hash'), config('doorprize.admin_password'))
            && hash_equals((string) config('doorprize.admin_user'), $user)) {
            return 'admin';
        }

        $hashOp  = config('doorprize.operator_password_hash');
        $sandiOp = config('doorprize.operator_password');

        // Operator hanya aktif kalau sandinya memang diisi di .env.
        if (($hashOp || $sandiOp)
            && $this->sandiBenar($sandi, $hashOp, $sandiOp)
            && hash_equals((string) config('doorprize.operator_user'), $user)) {
            return 'operator';
        }

        return null;
    }

    private function sandiBenar(string $sandi, ?string $hash, ?string $polos): bool
    {
        if ($hash) {
            return Hash::check($sandi, $hash);
        }

        return $polos !== null && $polos !== '' && hash_equals($polos, $sandi);
    }
}
