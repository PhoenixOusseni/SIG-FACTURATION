<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElementTransfertStock extends Model
{
    use HasFactory;

    protected $table = 'element_transferts_stock';

    protected $guarded = [];

    public function transfert()
    {
        return $this->belongsTo(TransfertStock::class, 'transfert_stock_id');
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }
}
