<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnershipRequest extends Model
{
    /** Clés de secteur ; libellés dans lang/{fr,en}.json (form.sectors.*) */
    public const SECTORS = ['fmcg', 'agro', 'fashion', 'food_service'];

    protected $fillable = ['company', 'country', 'contact_name', 'email', 'phone', 'sector', 'message', 'locale', 'ip_address'];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
        ];
    }
}
