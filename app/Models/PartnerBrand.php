<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerBrand extends Model
{
    /** Clés de catégorie ; libellés dans lang/{fr,en}.json (brands.category.*) */
    public const CATEGORIES = ['food', 'pgc'];

    protected $fillable = ['name', 'category', 'logo', 'sort', 'visible', 'featured'];

    protected function casts(): array
    {
        return ['visible' => 'boolean', 'featured' => 'boolean'];
    }
}
