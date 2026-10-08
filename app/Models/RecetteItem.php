<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Fonctionnalité | Résultat attendu | Tests réalisés | Écarts | Résultat obtenu | Note /100. */
class RecetteItem extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];
}
