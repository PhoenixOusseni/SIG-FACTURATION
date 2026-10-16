<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReglementDepense extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date'    => 'date',
        'montant' => 'decimal:2',
    ];

    public function depense()
    {
        return $this->belongsTo(Depense::class, 'depense_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
