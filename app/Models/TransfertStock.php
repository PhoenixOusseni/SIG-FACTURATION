<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransfertStock extends Model
{
    use HasFactory;

    protected $table = 'transferts_stock';

    protected $guarded = [];

    public function serviceSource()
    {
        return $this->belongsTo(Service::class, 'service_source_id');
    }

    public function serviceDestination()
    {
        return $this->belongsTo(Service::class, 'service_destination_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function elements()
    {
        return $this->hasMany(ElementTransfertStock::class, 'transfert_stock_id');
    }
}
