<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Proposition tirée d'une réponse d'IA : résumé, événement joué, relation, statut,
 * note de campagne ou révélation. Elle reste « pending » jusqu'à la décision du MJ.
 */
#[Fillable(['kind', 'payload', 'status'])]
class AiSuggestion extends Model
{
    public const KINDS = ['summary', 'event', 'relation', 'status', 'note', 'reveal'];

    protected $attributes = ['status' => 'pending'];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    /** @return array<string, string> */
    public static function kinds(): array
    {
        return [
            'summary' => __('Résumé de séance'),
            'event' => __('Événement joué'),
            'relation' => __('Relation'),
            'status' => __('Statut'),
            'note' => __('Note de campagne'),
            'reveal' => __('Révélation'),
        ];
    }

    /** @return BelongsTo<AiAnalysis, $this> */
    public function analysis(): BelongsTo
    {
        return $this->belongsTo(AiAnalysis::class, 'ai_analysis_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
