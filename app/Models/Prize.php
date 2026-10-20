<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Prize extends Model
{
    protected $fillable = [
        'name', 'sponsor', 'draw_session_id', 'session_row', 'group_label', 'session_order', 'image_path', 'quota', 'per_draw', 'sort_order', 'is_selected',
    ];

    protected $casts = ['is_selected' => 'boolean'];

    public function winners(): HasMany
    {
        return $this->hasMany(Winner::class);
    }

    /** Sesi undian tempat hadiah ini diundi bersama hadiah lain, kalau ada. */
    public function drawSession(): BelongsTo
    {
        return $this->belongsTo(DrawSession::class);
    }

    /** Posisi pemenang yang dikunci panitia; sisanya tetap diundi acak. */
    public function picks(): HasMany
    {
        return $this->hasMany(PrizePick::class)->orderBy('position');
    }

    /** Grup peserta yang tidak boleh menang hadiah ini. */
    public function exclusionGroups(): BelongsToMany
    {
        return $this->belongsToMany(ExclusionGroup::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }

    public function getRemainingSlotAttribute(): int
    {
        return max(0, $this->quota - $this->winners()->count());
    }

    public function getIsFinishedAttribute(): bool
    {
        return $this->remaining_slot === 0;
    }
}
