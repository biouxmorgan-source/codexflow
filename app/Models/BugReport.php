<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Élément du backlog : un problème signalé depuis l'application, un bug relevé en recette
 * ou une évolution, avec son statut, sa priorité et la version qui l'a réglé.
 */
class BugReport extends Model
{
    public const STATUSES = ['new', 'planned', 'in_progress', 'fixed', 'rejected'];

    /** Statuts qui sortent l'élément de la liste à traiter. */
    public const CLOSED = ['fixed', 'rejected'];

    public const PRIORITIES = ['high', 'normal', 'low'];

    public const KINDS = ['bug', 'evolution'];

    protected $guarded = ['id'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @param  Builder<BugReport>  $query */
    public function scopeOpen(Builder $query): void
    {
        $query->whereNotIn('status', self::CLOSED);
    }

    public function isOpen(): bool
    {
        return ! in_array($this->status, self::CLOSED, true);
    }

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return ['new' => __('Nouveau'), 'planned' => __('Planifié'), 'in_progress' => __('En cours'), 'fixed' => __('Corrigé'), 'rejected' => __('Rejeté')];
    }

    /** @return array<string, string> */
    public static function priorities(): array
    {
        return ['high' => __('Haute'), 'normal' => __('Normale'), 'low' => __('Basse')];
    }

    /** @return array<string, string> */
    public static function kinds(): array
    {
        return ['bug' => __('Bug'), 'evolution' => __('Évolution')];
    }

    /** @return array<string, string> */
    public static function sources(): array
    {
        return ['report' => __('Signalement'), 'recette' => __('Recette'), 'admin' => __('Administration')];
    }

    /** Titre affiché : celui donné par l'administrateur, sinon le début du message. */
    public function heading(): string
    {
        return $this->title ?: Str::limit($this->message, 90);
    }
}
