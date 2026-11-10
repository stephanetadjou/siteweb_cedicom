<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Paramètre du site modifiable dans le back office (clé => valeur JSON). */
class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    protected function casts(): array
    {
        return ['value' => 'json'];
    }
}
