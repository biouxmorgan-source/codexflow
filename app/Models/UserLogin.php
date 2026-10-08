<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/** Une connexion : la date seulement, pour les indicateurs de la console d'administration. */
class UserLogin extends Model
{
    use MassPrunable;

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['logged_in_at' => 'datetime'];
    }

    /** Gardées un an, le temps des indicateurs ; effacées ensuite (php artisan model:prune). */
    public function prunable(): Builder
    {
        return static::where('logged_in_at', '<', now()->subYear());
    }
}
