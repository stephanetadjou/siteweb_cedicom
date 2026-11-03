<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enseigne extends Model
{
    protected $fillable = ['division_id', 'name', 'type', 'logo', 'sort'];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }
}
