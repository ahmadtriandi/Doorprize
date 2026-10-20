<?php

namespace App\Http\Controllers;

use App\Models\DrawSession;
use App\Models\Prize;
use App\Models\Winner;
use Illuminate\Http\Request;

/**
 * Halaman operator: hanya membaca. Operator membuka layar undian dan melihat
 * siapa saja yang sudah menang, tanpa bisa mengubah data apa pun.
 */
class OperatorController extends Controller
{
    public function index()
    {
        $prizes = Prize::withCount('winners')->orderBy('sort_order')->orderBy('id')->get();
        $sessions = DrawSession::withCount('prizes')->orderBy('sort_order')->orderBy('id')->get();

        $stats = [
            'pemenang' => Winner::count(),
            'kuota'    => (int) $prizes->sum('quota'),
        ];

        return view('operator.index', compact('prizes', 'sessions', 'stats'));
    }

    public function winners(Request $request)
    {
        $prizeId = $request->integer('prize') ?: null;
        $cari = trim((string) $request->query('q'));

        $winners = Winner::with(['participant', 'prize'])
            ->when($prizeId, fn ($q) => $q->where('prize_id', $prizeId))
            ->when($cari !== '', fn ($q) => $q->whereHas('participant', fn ($p) => $p
                ->where('name', 'like', "%{$cari}%")
                ->orWhere('nip', 'like', "%{$cari}%")))
            ->latest('drawn_at')
            ->paginate(50)
            ->withQueryString();

        $prizes = Prize::withCount('winners')->orderBy('sort_order')->orderBy('id')->get();

        return view('operator.winners', compact('winners', 'prizes', 'prizeId', 'cari'));
    }
}
