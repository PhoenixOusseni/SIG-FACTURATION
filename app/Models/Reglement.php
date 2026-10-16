<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reglement extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function Facture()
    {
        return $this->belongsTo(Facture::class, 'facture_id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
