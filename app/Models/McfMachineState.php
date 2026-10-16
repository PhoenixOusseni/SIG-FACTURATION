<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class McfMachineState extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'derniere_facture_date'      => 'datetime',
        'derniere_connexion_serveur' => 'datetime',
    ];

    public function getMidAttribute(): string
    {
        return $this->nim . '-' . $this->acnt;
    }
}
