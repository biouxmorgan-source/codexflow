<?php

namespace App\Support;

use App\Models\Campaign;
use App\Models\GameSystem;
use App\Models\User;
use App\Models\World;
use Illuminate\Database\Eloquent\Model;

/**
 * Accès des co-MJ au contenu de préparation. Le contenu appartient au compte qui l'a créé
 * (le propriétaire de la campagne) ; un co-MJ y accède tant qu'il est MJ d'une campagne
 * qui le contient : la campagne elle-même, son monde ou son jeu.
 */
class CoGameMaster
{
    public static function canPrepare(User $user, Model $model): bool
    {
        if ($model->getAttribute('user_id') === $user->getKey()) {
            return true;
        }

        $scopes = array_filter([
            'id' => $model instanceof Campaign ? $model->getKey() : $model->getAttribute('campaign_id'),
            'world_id' => $model instanceof World ? $model->getKey() : $model->getAttribute('world_id'),
            'game_system_id' => $model instanceof GameSystem ? $model->getKey() : $model->getAttribute('game_system_id'),
        ]);

        if ($scopes === []) {
            return false;
        }

        return Campaign::query()
            ->runBy($user)
            ->where('user_id', $model->getAttribute('user_id'))
            ->where(function ($query) use ($scopes) {
                foreach ($scopes as $column => $id) {
                    $query->orWhere($column, $id);
                }
            })
            ->exists();
    }
}
