<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Models\Prize;
use App\Models\Winner;

class DashboardController extends Controller
{
    public function index()
    {
        $prizes = Prize::withCount('winners')->orderBy('sort_order')->orderBy('id')->get();

        $stats = [
            'peserta'       => Participant::active()->count(),
            'belum_menang'  => Participant::active()->neverWon()->count(),
            'hadiah'        => $prizes->count(),
            'kuota'         => (int) $prizes->sum('quota'),
            'pemenang'      => Winner::count(),
        ];

        $selected = $prizes->firstWhere('is_selected', true);

        $recent = Winner::with(['participant', 'prize'])
            ->latest('drawn_at')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'prizes', 'selected', 'recent'));
    }
}
