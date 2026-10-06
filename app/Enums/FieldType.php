<?php

namespace App\Enums;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Types de champs libres. Aucun n'est propre à un jeu : le MJ les nomme lui-même.
 */
enum FieldType: string
{
    case Text = 'text';
    case LongText = 'long_text';
    case Number = 'number';
    case Boolean = 'boolean';
    case Date = 'date';
    case Select = 'select';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Texte court',
            self::LongText => 'Texte long',
            self::Number => 'Nombre',
            self::Boolean => 'Oui/non',
            self::Date => 'Date',
            self::Select => 'Liste de choix',
        };
    }

    /**
     * Reconnaît un type écrit librement dans un fichier d'import (« nombre », « oui/non », « number »…).
     */
    public static function fromLabel(string $label): ?self
    {
        $label = Str::of($label)->ascii()->lower()->replaceMatches('/[^a-z]/', '')->toString();

        return match ($label) {
            '', 'texte', 'text', 'textecourt', 'court' => self::Text,
            'textelong', 'longtext', 'long', 'paragraphe' => self::LongText,
            'nombre', 'number', 'numerique', 'entier' => self::Number,
            'ouinon', 'booleen', 'boolean', 'bool', 'case', 'caseacocher' => self::Boolean,
            'date' => self::Date,
            'liste', 'listedechoix', 'choix', 'select' => self::Select,
            default => null,
        };
    }

    /**
     * Convertit une saisie brute (formulaire ou fichier) en valeur stockée.
     *
     * @param  list<string>|null  $options
     * @return array{0: mixed, 1: string|null} [valeur ou null si vide, message d'erreur]
     */
    public function parse(mixed $raw, ?array $options = null): array
    {
        if (is_bool($raw)) {
            return $this === self::Boolean ? [$raw, null] : [$raw ? '1' : null, null];
        }

        $raw = trim((string) $raw);

        if ($raw === '') {
            return [null, null];
        }

        return match ($this) {
            self::Text => mb_strlen($raw) > 1000 ? [null, 'dépasse 1000 caractères'] : [$raw, null],
            self::LongText => mb_strlen($raw) > 20000 ? [null, 'dépasse 20000 caractères'] : [$raw, null],
            self::Number => self::parseNumber($raw),
            self::Boolean => self::parseBoolean($raw),
            self::Date => self::parseDate($raw),
            self::Select => self::parseChoice($raw, $options ?? []),
        };
    }

    /**
     * Valeur lisible en français.
     */
    public function format(mixed $value): string
    {
        return match ($this) {
            self::Number => str_replace('.', ',', (string) $value),
            self::Boolean => $value ? 'Oui' : 'Non',
            self::Date => Carbon::parse($value)->format('d/m/Y'),
            default => (string) $value,
        };
    }

    /**
     * Devine le type d'une colonne importée d'après ses valeurs. Une colonne aux deux tiers
     * numérique reste « Nombre » : les quelques valeurs fautives seront signalées, pas absorbées.
     *
     * @param  iterable<string>  $values
     */
    public static function guess(iterable $values): self
    {
        $filled = [];

        foreach ($values as $value) {
            $value = trim((string) $value);

            if ($value === '') {
                continue;
            }

            if (mb_strlen($value) > 120 || str_contains($value, "\n")) {
                return self::LongText;
            }

            $filled[] = $value;
        }

        foreach ($filled === [] ? [] : [self::Number, self::Boolean, self::Date] as $candidate) {
            $valid = count(array_filter($filled, fn (string $value) => $candidate->parse($value)[1] === null));

            if ($valid / count($filled) >= 2 / 3) {
                return $candidate;
            }
        }

        return self::Text;
    }

    /** @return array{0: int|float|null, 1: string|null} */
    private static function parseNumber(string $raw): array
    {
        $normalized = str_replace([' ', "\u{00A0}", ','], ['', '', '.'], $raw);

        if (! is_numeric($normalized)) {
            return [null, '« '.$raw.' » n\'est pas un nombre'];
        }

        return [$normalized + 0, null];
    }

    /** @return array{0: bool|null, 1: string|null} */
    private static function parseBoolean(string $raw): array
    {
        return match (Str::of($raw)->ascii()->lower()->toString()) {
            'oui', 'o', 'vrai', 'true', 'yes', 'y', '1', 'x' => [true, null],
            'non', 'n', 'faux', 'false', 'no', '0' => [false, null],
            default => [null, '« '.$raw.' » n\'est ni oui ni non'],
        };
    }

    /** @return array{0: string|null, 1: string|null} */
    private static function parseDate(string $raw): array
    {
        foreach (['!Y-m-d', '!d/m/Y', '!d-m-Y', '!d.m.Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $raw);

            if ($date !== false && $date->format(ltrim($format, '!')) === $raw) {
                return [$date->format('Y-m-d'), null];
            }
        }

        return [null, '« '.$raw.' » n\'est pas une date (JJ/MM/AAAA)'];
    }

    /**
     * @param  list<string>  $options
     * @return array{0: string|null, 1: string|null}
     */
    private static function parseChoice(string $raw, array $options): array
    {
        foreach ($options as $option) {
            if (mb_strtolower($option) === mb_strtolower($raw)) {
                return [$option, null];
            }
        }

        return [null, '« '.$raw.' » ne fait pas partie des choix'];
    }
}
