<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ExclusionGroup extends Model
{
    protected $fillable = ['name', 'note'];

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Participant::class);
    }

    public function prizes(): BelongsToMany
    {
        return $this->belongsToMany(Prize::class);
    }
}
