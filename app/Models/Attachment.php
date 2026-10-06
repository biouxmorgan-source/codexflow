<?php

namespace App\Models;

use App\Enums\Zone;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['zone', 'disk', 'path', 'original_name', 'mime_type', 'size'])]
class Attachment extends Model
{
    protected function casts(): array
    {
        return [
            'zone' => Zone::class,
            'size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Le fichier disparaît avec sa ligne, y compris quand la fiche est supprimée.
        static::deleted(fn (Attachment $attachment) => Storage::disk($attachment->disk)->delete($attachment->path));
    }

    /** @return BelongsTo<Entity, $this> */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function humanSize(): string
    {
        return match (true) {
            $this->size >= 1_048_576 => number_format($this->size / 1_048_576, 1, ',', ' ').' Mo',
            $this->size >= 1024 => number_format($this->size / 1024, 0, ',', ' ').' Ko',
            default => $this->size.' o',
        };
    }
}
