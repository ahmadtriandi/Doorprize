<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Satu baris antrean pemenang yang sudah ditentukan panitia. */
class PrizePick extends Model
{
    protected $fillable = ['prize_id', 'participant_id', 'position'];

    public function prize(): BelongsTo
    {
        return $this->belongsTo(Prize::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
