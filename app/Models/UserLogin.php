<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Une connexion : la date seulement, pour les indicateurs de la console d'administration. */
class UserLogin extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['logged_in_at' => 'datetime'];
    }
}
