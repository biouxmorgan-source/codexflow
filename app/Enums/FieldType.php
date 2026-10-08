<?php

namespace App\Enums;

use App\Models\Document;
use App\Support\EntityLinks;
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
    case Counter = 'counter';
    case Link = 'link';
    case File = 'file';
    case EntityRef = 'entity';

    public function label(): string
    {
        return match ($this) {
            self::Text => __('Texte court'),
            self::LongText => __('Texte long'),
            self::Number => __('Nombre'),
            self::Boolean => __('Oui/non'),
            self::Date => __('Date'),
            self::Select => __('Liste de choix'),
            self::Counter => __('Compteur (valeur / maximum)'),
            self::Link => __('Lien web'),
            self::File => __('Fichier (document de la campagne)'),
            self::EntityRef => __('Référence à une fiche'),
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
            'compteur', 'jauge', 'counter', 'tracker' => self::Counter,
            'lien', 'lienweb', 'link', 'url', 'site' => self::Link,
            'fichier', 'document', 'file' => self::File,
            'reference', 'referencealunefiche', 'fiche', 'entite', 'entity', 'ref' => self::EntityRef,
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
            self::Text => mb_strlen($raw) > 1000 ? [null, __('dépasse :max caractères', ['max' => 1000])] : [$raw, null],
            self::LongText => mb_strlen($raw) > 20000 ? [null, __('dépasse :max caractères', ['max' => 20000])] : [$raw, null],
            self::Number => self::parseNumber($raw),
            self::Boolean => self::parseBoolean($raw),
            self::Date => self::parseDate($raw),
            self::Select => self::parseChoice($raw, $options ?? []),
            self::Counter => self::parseCounter($raw),
            self::Link => self::parseLink($raw),
            self::File => ctype_digit($raw) ? [(int) $raw, null] : [null, __('« :value » : choisissez un document de la campagne', ['value' => $raw])],
            self::EntityRef => self::parseReference($raw),
        };
    }

    /**
     * Valeur lisible, dans la langue courante ou dans $locale (« fr » pour l'export réimportable).
     */
    public function format(mixed $value, ?string $locale = null): string
    {
        return match ($this) {
            self::Number => str_replace('.', ',', (string) $value),
            self::Boolean => $value ? __('Oui', [], $locale) : __('Non', [], $locale),
            self::Date => Carbon::parse($value)->locale($locale ?? app()->getLocale())->isoFormat('L'),
            self::Counter => self::formatNumber($value['value'] ?? 0).(isset($value['max']) ? ' / '.self::formatNumber($value['max']) : ''),
            self::File => (string) (Document::query()->whereKey($value)->value('title') ?? ''),
            self::EntityRef => preg_match(EntityLinks::PATTERN, (string) $value, $match) ? trim($match[1]) : (string) $value,
            default => (string) $value,
        };
    }

    /**
     * Valeur stockée remise sous forme de saisie (champ de formulaire).
     */
    public function input(mixed $value): string|bool
    {
        return match (true) {
            $this === self::Boolean => (bool) $value,
            $value === null => '',
            $this === self::Counter, $this === self::EntityRef => $this->format($value),
            default => (string) $value,
        };
    }

    /**
     * Compteur ajusté de $delta (PV perdus, munitions dépensées…), sans descendre sous zéro.
     *
     * @param  array{value: int|float, max?: int|float|null}|null  $counter
     * @return array{value: int|float, max?: int|float}
     */
    public static function adjustCounter(?array $counter, int|float $delta): array
    {
        $counter ??= ['value' => 0];
        $counter['value'] = max(0, ($counter['value'] ?? 0) + $delta);

        return $counter;
    }

    private static function formatNumber(int|float $number): string
    {
        return str_replace('.', ',', (string) $number);
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

    /**
     * Adresse web complète : seuls http et https, jamais javascript: ni data:.
     *
     * @return array{0: string|null, 1: string|null}
     */
    private static function parseLink(string $raw): array
    {
        if (! preg_match('#^https?://#i', $raw)) {
            $raw = 'https://'.$raw;
        }

        if (mb_strlen($raw) > 2000 || filter_var($raw, FILTER_VALIDATE_URL) === false || ! preg_match('#^https?://[^\s/]+\.[^\s/]+#i', $raw)) {
            return [null, __("« :value » n'est pas une adresse web", ['value' => $raw])];
        }

        return [$raw, null];
    }

    /**
     * Fiche citée : « [[Nom|42]] » (choisie dans la liste) ou un simple nom, résolu dans la campagne.
     *
     * @return array{0: string|null, 1: string|null}
     */
    private static function parseReference(string $raw): array
    {
        if (preg_match('/^\[\[[^\[\]|\n]+?(?:\|\d+)?\]\]$/u', $raw)) {
            return [$raw, null];
        }

        $name = trim(str_replace(['[', ']', '|'], '', $raw));

        return mb_strlen($name) > 255 || $name === '' ? [null, __("« :value » n'est pas un nom de fiche", ['value' => $raw])] : ['[['.$name.']]', null];
    }

    /** @return array{0: int|float|null, 1: string|null} */
    private static function parseNumber(string $raw): array
    {
        $normalized = str_replace([' ', "\u{00A0}", ','], ['', '', '.'], $raw);

        if (! is_numeric($normalized)) {
            return [null, __("« :value » n'est pas un nombre", ['value' => $raw])];
        }

        return [$normalized + 0, null];
    }

    /** @return array{0: bool|null, 1: string|null} */
    private static function parseBoolean(string $raw): array
    {
        return match (Str::of($raw)->ascii()->lower()->toString()) {
            'oui', 'o', 'vrai', 'true', 'yes', 'y', '1', 'x' => [true, null],
            'non', 'n', 'faux', 'false', 'no', '0' => [false, null],
            default => [null, __("« :value » n'est ni oui ni non", ['value' => $raw])],
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

        return [null, __("« :value » n'est pas une date (JJ/MM/AAAA)", ['value' => $raw])];
    }

    /**
     * « 12 », « 9/12 » ou « 9 / 12 » : valeur actuelle et maximum facultatif.
     *
     * @return array{0: array{value: int|float, max?: int|float}|null, 1: string|null}
     */
    private static function parseCounter(string $raw): array
    {
        $parts = array_map('trim', explode('/', $raw));

        if (count($parts) > 2) {
            return [null, __("« :value » n'est pas un compteur (valeur ou valeur / maximum)", ['value' => $raw])];
        }

        $numbers = [];

        foreach ($parts as $part) {
            [$number, $error] = self::parseNumber($part);

            if ($error !== null) {
                return [null, __("« :value » n'est pas un compteur (valeur ou valeur / maximum)", ['value' => $raw])];
            }

            $numbers[] = $number;
        }

        return [isset($numbers[1]) ? ['value' => $numbers[0], 'max' => $numbers[1]] : ['value' => $numbers[0]], null];
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

        return [null, __('« :value » ne fait pas partie des choix', ['value' => $raw])];
    }
}
