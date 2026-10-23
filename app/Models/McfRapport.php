<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class McfRapport extends Model
{
    use HasFactory;

    protected $table = 'mcf_rapports';

    protected $fillable = [
        'type_rapport',
        'numero_rapport',
        'date_rapport',
        'date_debut',
        'date_fin',
        'chiffre_affaires_ht',
        'total_tva',
        'total_ts',
        'total_psvb',
        'total_timbre',
        'total_ttc',
        'reductions_commerciales',
        'nb_factures_vente',
        'nb_factures_avoir',
        'ventes_incompletes',
        'details_taxes',
        'details_reglements',
        'details_bruts',
        'statut',
        'raw_mcf_response',
        'user_id',
    ];

    protected $casts = [
        'date_rapport'            => 'datetime',
        'date_debut'              => 'datetime',
        'date_fin'                => 'datetime',
        'chiffre_affaires_ht'     => 'decimal:2',
        'total_tva'               => 'decimal:2',
        'total_ts'                => 'decimal:2',
        'total_psvb'              => 'decimal:2',
        'total_timbre'            => 'decimal:2',
        'total_ttc'               => 'decimal:2',
        'reductions_commerciales' => 'decimal:2',
        'details_taxes'           => 'array',
        'details_reglements'      => 'array',
        'details_bruts'           => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Génère un numéro de rapport séquentiel (ex: Z000042)
     */
    public static function prochainNumero(string $type): string
    {
        $type = strtoupper($type);
        $dernier = self::where('type_rapport', $type)->orderBy('id', 'desc')->first();
        $next = ($dernier ? (int) preg_replace('/\D/', '', $dernier->numero_rapport) : 0) + 1;
        return sprintf('%s%06d', $type, $next);
    }
}
