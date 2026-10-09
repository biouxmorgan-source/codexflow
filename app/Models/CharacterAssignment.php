<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Une période pendant laquelle un joueur a tenu un personnage (ended_at nul : il le joue encore). */
class CharacterAssignment extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'ended_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
