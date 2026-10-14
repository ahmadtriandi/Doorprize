<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\Prize;
use App\Models\PrizePick;
use App\Models\Setting;
use App\Models\Winner;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DrawService
{
    /**
     * Daftar peserta yang masih berhak ikut undian hadiah ini.
     */
    public function eligible(Prize $prize): Collection
    {
        $query = Participant::query()->active();

        if ($this->onlyOneWinPerPerson()) {
            // Sudah pernah menang hadiah apa pun -> tidak ikut lagi
            $query->neverWon();
        } else {
            // Boleh menang lagi, tapi tidak untuk hadiah yang sama
            $query->whereDoesntHave('winners', fn ($q) => $q->where('prize_id', $prize->id));
        }

        // Grup pengecualian: anggotanya tidak ikut undian hadiah ini, tetapi
        // tetap ikut hadiah lain yang tidak memakai grup tersebut.
        $grupDikecualikan = $prize->exclusionGroups()->pluck('exclusion_groups.id');

        if ($grupDikecualikan->isNotEmpty()) {
            $query->whereDoesntHave(
                'exclusionGroups',
                fn ($q) => $q->whereIn('exclusion_groups.id', $grupDikecualikan)
            );
        }

        // Nama yang dikunci di hadiah lain ditahan dulu, supaya tidak terpanggil
        // lebih awal lewat undian acak sebelum tiba giliran hadiahnya.
        $dipesan = $this->dipesanHadiahLain($prize);

        if ($dipesan->isNotEmpty()) {
            $query->whereNotIn('id', $dipesan);
        }

        $peserta = $query->orderBy('name')->get();

        // Nama yang dikunci harus tetap tampil di papan meski tersaring aturan
        // umum, karena namanya akan dipanggil pada posisinya.
        $sudah = $prize->winners()->pluck('participant_id');
        $dikecualikan = $this->anggotaDikecualikan($prize);

        $terkunci = $prize->picks()
            ->with('participant')
            ->get()
            ->reject(fn ($pick) => $sudah->contains($pick->participant_id))
            ->reject(fn ($pick) => $dikecualikan->contains($pick->participant_id))
            ->pluck('participant')
            ->filter();

        return $peserta->concat($terkunci)->unique('id')->sortBy('name')->values();
    }

    /**
     * Undi pemenang untuk satu putaran.
     *
     * Posisi yang dikunci panitia dipakai apa adanya; posisi lain diundi acak.
     * Hadiah berkuota 50 dengan satu nama terkunci di posisi 7 berarti pemenang
     * ketujuh sudah pasti, sedangkan 49 sisanya tetap hasil undian.
     */
    public function spin(Prize $prize, ?int $count = null): Collection
    {
        $sisa = $prize->remaining_slot;

        if ($sisa < 1) {
            throw new RuntimeException('Kuota hadiah ini sudah terpenuhi.');
        }

        $count = max(1, (int) ($count ?: $prize->per_draw));
        $count = min($count, $sisa);

        $sudah = $prize->winners()->pluck('participant_id');
        $dikecualikan = $this->anggotaDikecualikan($prize);
        $mulai = $prize->winners()->count() + 1;

        $dikunci = $prize->picks()->with('participant')->get()->keyBy('position');
        $idDikunci = $dikunci->pluck('participant_id');

        // Nama yang dikunci dikeluarkan dari kumpulan acak supaya tidak
        // terpilih dua kali lewat dua jalur berbeda.
        $pool = $this->eligible($prize)
            ->reject(fn ($p) => $idDikunci->contains($p->id))
            ->values();

        $terpilih = collect();

        for ($i = 0; $i < $count; $i++) {
            $pick = $dikunci->get($mulai + $i);

            $bisaDipakai = $pick
                && ! $sudah->contains($pick->participant_id)
                && ! $dikecualikan->contains($pick->participant_id)
                && ! $terpilih->pluck('id')->contains($pick->participant_id);

            if ($bisaDipakai) {
                $terpilih->push($pick->participant);
                continue;
            }

            if ($pool->isEmpty()) {
                break;
            }

            $index = random_int(0, $pool->count() - 1);
            $terpilih->push($pool[$index]);
            $pool = $pool->forget($index)->values();
        }

        if ($terpilih->isEmpty()) {
            throw new RuntimeException('Tidak ada peserta yang memenuhi syarat untuk diundi.');
        }

        return DB::transaction(function () use ($prize, $terpilih) {
            $round = (int) $prize->winners()->max('round') + 1;

            return $terpilih->map(fn ($participant) => Winner::create([
                'prize_id'       => $prize->id,
                'participant_id' => $participant->id,
                'round'          => $round,
                'drawn_at'       => now(),
            ])->load('participant'))->values();
        });
    }

    /**
     * Peserta yang sedang dipesan oleh hadiah lain.
     *
     * Sebuah nama yang dikunci di posisi tertentu pada hadiah lain ditahan dari
     * undian acak hadiah ini sampai hadiah pemesannya benar-benar memanggilnya.
     * Tanpa penahanan ini, nama tersebut bisa keluar lebih dulu di hadiah lain
     * dan berakhir menang dua kali, atau justru batal menang hadiah yang sudah
     * disiapkan untuknya.
     */
    public function dipesanHadiahLain(Prize $prize): Collection
    {
        $pick = PrizePick::where('prize_id', '!=', $prize->id)->get(['prize_id', 'participant_id']);

        if ($pick->isEmpty()) {
            return collect();
        }

        // Pesanan dianggap selesai begitu hadiah pemesannya sudah memanggil nama itu.
        $sudahDipanggil = Winner::whereIn('prize_id', $pick->pluck('prize_id')->unique())
            ->get(['prize_id', 'participant_id'])
            ->map(fn ($w) => $w->prize_id.'-'.$w->participant_id)
            ->flip();

        return $pick
            ->reject(fn ($p) => $sudahDipanggil->has($p->prize_id.'-'.$p->participant_id))
            ->pluck('participant_id')
            ->unique()
            ->values();
    }

    /**
     * Peserta yang tersaring grup pengecualian pada hadiah ini.
     *
     * Dipakai untuk menyaring undian acak sekaligus sebagai jaring pengaman
     * bagi posisi yang dikunci: nama yang grupnya dikecualikan tetap dilewati
     * meski dikunci di suatu posisi.
     */
    public function anggotaDikecualikan(Prize $prize): Collection
    {
        $grup = $prize->exclusionGroups()->pluck('exclusion_groups.id');

        if ($grup->isEmpty()) {
            return collect();
        }

        return Participant::query()
            ->whereHas('exclusionGroups', fn ($q) => $q->whereIn('exclusion_groups.id', $grup))
            ->pluck('id');
    }

    private function onlyOneWinPerPerson(): bool
    {
        return (bool) Setting::get('one_win_per_person', '1');
    }
}
