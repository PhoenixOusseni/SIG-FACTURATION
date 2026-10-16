<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the activities for the user.
     */
    public function activities()
    {
        return $this->hasMany(UserActivity::class);
    }

    /**
     * Normalize role strings for comparisons (comptoir + legacy inconsistency).
     */
    public function normalizedRole(): string
    {
        $r = strtolower(trim((string) $this->role));
        // strip accents for séniore / opérateur
        if (function_exists('iconv')) {
            $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $r);
            if ($t !== false) {
                $r = strtolower($t);
            }
        }

        return $r;
    }

    /**
     * Cashier: sell, open, arrêt, reprise (and superviseur+).
     */
    public function isCaissierComptoir(): bool
    {
        return in_array($this->normalizedRole(), [
            'junior', 'manager', 'seniore', 'operateur',
            'superviseur', 'administrateur', 'admin',
        ], true);
    }

    /**
     * Superviseur / admin: fermeture, conversion, caisse admin.
     */
    public function isSuperviseurComptoir(): bool
    {
        return in_array($this->normalizedRole(), [
            'superviseur', 'administrateur', 'admin',
        ], true);
    }
}
