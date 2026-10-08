<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Évolution prévue pour la plateforme (feuille de route de l'administrateur). */
class Evolution extends Model
{
    public const STATUSES = ['idea', 'planned', 'in_progress', 'done', 'dropped'];

    public const CLOSED = ['done', 'dropped'];

    protected $guarded = ['id'];

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            'idea' => __('Idée'),
            'planned' => __('Prévue'),
            'in_progress' => __('En cours'),
            'done' => __('Faite'),
            'dropped' => __('Abandonnée'),
        ];
    }

    public function isOpen(): bool
    {
        return ! in_array($this->status, self::CLOSED, true);
    }
}
