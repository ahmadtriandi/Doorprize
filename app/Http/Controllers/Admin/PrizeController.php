<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrizeController extends Controller
{
    public function index()
    {
        $prizes = Prize::with('exclusionGroups')
            ->withCount(['winners', 'picks'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.prizes.index', compact('prizes'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('hadiah', 'public');
        }

        $data['sort_order'] = $data['sort_order'] ?? (Prize::max('sort_order') + 1);

        Prize::create($data);

        return back()->with('status', 'Hadiah ditambahkan.');
    }

    public function update(Request $request, Prize $prize)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            if ($prize->image_path) {
                Storage::disk('public')->delete($prize->image_path);
            }
            $data['image_path'] = $request->file('image')->store('hadiah', 'public');
        }

        if ($data['quota'] < $prize->winners()->count()) {
            return back()->with('error', 'Jumlah pemenang tidak boleh lebih kecil dari pemenang yang sudah terundi.');
        }

        $prize->update($data);

        return back()->with('status', 'Hadiah diperbarui.');
    }

    public function destroy(Prize $prize)
    {
        if ($prize->image_path) {
            Storage::disk('public')->delete($prize->image_path);
        }

        $prize->delete();

        return back()->with('status', 'Hadiah dihapus beserta data pemenangnya.');
    }

    /** Tentukan hadiah yang akan diundi di layar utama. */
    public function select(Prize $prize)
    {
        Prize::where('is_selected', true)->update(['is_selected' => false]);
        $prize->update(['is_selected' => true]);

        return back()->with('status', "Layar undian sekarang menampilkan: {$prize->name}.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'       => ['required', 'string', 'max:150'],
            'sponsor'    => ['nullable', 'string', 'max:100'],
            'quota'      => ['required', 'integer', 'min:1', 'max:1000'],
            'per_draw'   => ['required', 'integer', 'min:1', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image'      => ['nullable', 'image', 'max:2048'],
        ], [], [
            'name'     => 'nama hadiah',
            'quota'    => 'jumlah pemenang',
            'per_draw' => 'pemenang per putaran',
            'image'    => 'gambar',
        ]);
    }
}
