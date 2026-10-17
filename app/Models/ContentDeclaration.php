<?php

namespace App\Models;

use App\Support\ContentDeclarations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Une déclaration de droits acceptée avant un import : qui, quand, quelle version du texte,
 * et si l'import a réussi. Aucune copie des fichiers importés n'est gardée.
 */
class ContentDeclaration extends Model
{
    use HasUuids, MassPrunable;

    /** Gardée 5 ans après la suppression du compte (prescription), puis effacée (model:prune). */
    public const KEEP_YEARS_AFTER_DELETION = 5;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'declaration_accepted' => 'boolean',
            'accepted_at' => 'datetime',
            'account_deleted_at' => 'datetime',
        ];
    }

    public static function accept(User $user, string $importType, ?Campaign $campaign = null, string $kind = 'import'): self
    {
        return static::create([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'campaign_id' => $campaign?->id,
            'kind' => $kind,
            'import_type' => $importType,
            'declaration_version' => ContentDeclarations::current($kind),
            'declaration_accepted' => true,
            'locale' => app()->getLocale(),
            'accepted_at' => now(),
        ]);
    }

    public function finish(bool $success, ?Campaign $campaign = null): void
    {
        $this->update(array_filter([
            'import_status' => $success ? 'success' : 'failed',
            'campaign_id' => $campaign?->id,
        ]));
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prunable(): Builder
    {
        $limit = now()->subYears(self::KEEP_YEARS_AFTER_DELETION);

        // Compte supprimé autrement que par « Supprimer mon compte » : la dernière mise à jour fait foi.
        return static::where(fn (Builder $query) => $query->where('account_deleted_at', '<', $limit)
            ->orWhere(fn (Builder $query) => $query->whereNull('user_id')->whereNull('account_deleted_at')->where('updated_at', '<', $limit)));
    }
}
