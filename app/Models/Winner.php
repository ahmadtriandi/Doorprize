<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Winner extends Model
{
    protected $fillable = [
        'prize_id', 'participant_id', 'round', 'is_claimed', 'drawn_at',
    ];

    protected $casts = [
        'is_claimed' => 'boolean',
        'drawn_at'   => 'datetime',
    ];

    public function prize(): BelongsTo
    {
        return $this->belongsTo(Prize::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
