<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contribuable extends Model
{
    use HasFactory;

    protected $guarded = [

    ];


    function Categorie() {
        return $this->belongsTo(Categorie::class, 'categories_id');
    }
}
