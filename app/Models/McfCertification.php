<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class McfCertification extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date_certification' => 'datetime',
        'detail_taxation'    => 'array',
    ];

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    public function factureAvoir()
    {
        return $this->belongsTo(FactureAvoir::class, 'facture_avoir_id');
    }

    public function referenceCertification()
    {
        return $this->belongsTo(McfCertification::class, 'reference_certification_id');
    }

    public function getMidAttribute(): string
    {
        return $this->nim;
    }

    public function getEstAnnuleeAttribute(): bool
    {
        return $this->statut === 'annulee';
    }
}
