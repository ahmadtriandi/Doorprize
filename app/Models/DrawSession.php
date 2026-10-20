<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DrawSession extends Model
{
    protected $fillable = ['name', 'rows_per_column', 'sort_order'];

    public function prizes(): HasMany
    {
        return $this->hasMany(Prize::class)
            ->orderBy('session_row')->orderBy('session_order')->orderBy('id');
    }

    /**
     * Hadiah disusun per nomor baris. Label baris diambil dari hadiah pertama
     * yang punya label, jadi dua baris boleh berlabel sama ("50 PV" dua kali).
     *
     * @return \Illuminate\Support\Collection<int, array{label: string, prizes: \Illuminate\Support\Collection}>
     */
    public function baris()
    {
        return $this->prizes
            ->groupBy('session_row')
            ->sortKeys()
            ->map(fn ($hadiah) => [
                'label'  => (string) optional($hadiah->first(fn ($p) => filled($p->group_label)))->group_label,
                'prizes' => $hadiah->values(),
            ])
            ->values();
    }
}
