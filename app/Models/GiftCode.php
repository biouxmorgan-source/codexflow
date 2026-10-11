<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Code cadeau : quelques mois de Premium, offerts par un contributeur du Kickstarter
 * (ou par l'administrateur). Utilisable une seule fois ; celui qui l'offre voit qui l'a utilisé.
 */
class GiftCode extends Model
{
    /** Lettres et chiffres sans ambiguïté à l'écrit (ni 0/O, ni 1/I/L). */
    private const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    protected function casts(): array
    {
        return [
            'months' => 'integer',
            'redeemed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (GiftCode $gift) {
            $gift->code ??= self::newCode();
        });
    }

    /** Ex. SW-K7QM-3XPA. */
    public static function newCode(): string
    {
        do {
            $chars = collect(range(1, 8))->map(fn () => self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)])->implode('');
            $code = 'SW-'.substr($chars, 0, 4).'-'.substr($chars, 4);
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /** Ce que la personne a tapé (minuscules, espaces, sans tirets) ramené au format du code. */
    public static function normalize(string $input): string
    {
        $chars = preg_replace('/[^A-Z0-9]/', '', strtoupper($input));
        $chars = str_starts_with($chars, 'SW') ? substr($chars, 2) : $chars;

        return 'SW-'.substr($chars, 0, 4).'-'.substr($chars, 4);
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return BelongsTo<User, $this> */
    public function redeemer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'redeemed_by');
    }

    public function isRedeemed(): bool
    {
        return $this->redeemed_at !== null;
    }

    public function url(): string
    {
        return route('gifts.show', $this->code);
    }
}
