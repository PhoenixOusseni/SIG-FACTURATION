<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'nom_utilisateur',
        'action',
        'entite',
        'entite_id',
        'details',
        'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Enregistre une entrée inaltérable dans la piste d'audit DGI.
     */
    public static function enregistrer(string $action, string $entite, ?string $details = null, $entiteId = null, ?User $user = null): self
    {
        $currentUser = $user ?: Auth::user();
        $nomUser = $currentUser ? trim(($currentUser->prenom ?? '') . ' ' . ($currentUser->nom ?? $currentUser->name ?? '')) : 'Système';
        if (empty($nomUser)) {
            $nomUser = $currentUser ? ($currentUser->email ?? 'Utilisateur #' . $currentUser->id) : 'Système';
        }

        return self::create([
            'user_id'         => $currentUser?->id,
            'nom_utilisateur' => $nomUser,
            'action'          => strtoupper($action),
            'entite'          => strtoupper($entite),
            'entite_id'       => $entiteId ? (string) $entiteId : null,
            'details'         => $details,
            'ip_address'      => Request::ip() ?: '127.0.0.1',
        ]);
    }
}
