<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    protected $fillable = ['nip', 'name', 'department', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function winners(): HasMany
    {
        return $this->hasMany(Winner::class);
    }

    public function exclusionGroups(): BelongsToMany
    {
        return $this->belongsToMany(ExclusionGroup::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Peserta yang belum pernah menang sama sekali. */
    public function scopeNeverWon($query)
    {
        return $query->whereDoesntHave('winners');
    }
}
