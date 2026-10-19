<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Campagne prête à jouer de la bibliothèque de démos : une archive SagaWyn figée, son jeu,
 * son auteur ou éditeur et le lien où acheter le jeu. Tout MJ en charge une copie dans son compte.
 */
class DemoLibraryItem extends Model
{
    public const DISK = 'local';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(fn (DemoLibraryItem $item) => Storage::disk(self::DISK)->delete($item->archive_path));
    }

    /** @param  Builder<DemoLibraryItem>  $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('published', true);
    }

    /** @return BelongsTo<Campaign, $this> */
    public function sourceCampaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'source_campaign_id');
    }
}
