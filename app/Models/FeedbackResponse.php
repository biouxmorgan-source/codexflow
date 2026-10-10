<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Avis d'un joueur. Pour une demande anonyme, l'auteur ne sert qu'à n'accepter qu'une réponse. */
#[Fillable(['rating', 'liked', 'improve'])]
#[Hidden(['user_id'])]
class FeedbackResponse extends Model
{
    protected function casts(): array
    {
        return ['rating' => 'integer'];
    }

    /** @return BelongsTo<FeedbackRequest, $this> */
    public function request(): BelongsTo
    {
        return $this->belongsTo(FeedbackRequest::class, 'feedback_request_id');
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
