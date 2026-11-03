<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    /** Clés d'objet ; libellés dans lang/{fr,en}.json (form.subjects.*) */
    public const SUBJECTS = ['sales', 'partnership', 'delivery', 'other'];

    protected $fillable = ['name', 'email', 'phone', 'subject', 'message', 'locale', 'ip_address'];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }
}
