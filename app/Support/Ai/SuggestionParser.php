<?php

namespace App\Support\Ai;

use App\Models\Campaign;
use Illuminate\Support\Str;

/**
 * Lit la réponse collée par le MJ et en tire des propositions sûres : seules les fiches de
 * la campagne et ses personnages actifs sont acceptés, les textes sont tronqués, et ce qui
 * n'est pas marqué « public » reste en zone MJ. Le reste est compté comme ignoré.
 */
class SuggestionParser
{
    /** Au plus autant de propositions par sorte, pour qu'une réponse folle reste lisible. */
    public const MAX_PER_KIND = 50;

    /** Liste de la réponse JSON => sorte de proposition. */
    private const LISTS = [
        'events' => 'event',
        'relations' => 'relation',
        'statuses' => 'status',
        'notes' => 'note',
        'reveals' => 'reveal',
    ];

    public int $ignored = 0;

    /** @var array<int, true>|null */
    private ?array $entityIds = null;

    /** @var array<int, true>|null */
    private ?array $characterIds = null;

    public function __construct(private readonly Campaign $campaign) {}

    /**
     * @return list<array{kind: string, payload: array<string, mixed>}>
     *
     * @throws UnreadableResponse
     */
    public function parse(string $response): array
    {
        $data = $this->decode($response);
        $suggestions = [];

        $summary = $this->text($data['summary'] ?? null, 10000);

        if ($summary !== '') {
            $suggestions[] = ['kind' => 'summary', 'payload' => ['text' => $summary, 'public' => false]];
        }

        foreach (self::LISTS as $key => $kind) {
            $items = $data[$key] ?? [];

            if (! is_array($items) || ! array_is_list($items)) {
                $this->ignored += $items === [] || $items === null ? 0 : 1;

                continue;
            }

            $kept = 0;

            foreach ($items as $item) {
                $payload = is_array($item) ? $this->normalize($kind, $item) : null;

                if ($payload === null || $kept >= self::MAX_PER_KIND) {
                    $this->ignored++;

                    continue;
                }

                $suggestions[] = ['kind' => $kind, 'payload' => $payload];
                $kept++;
            }
        }

        return $suggestions;
    }

    /**
     * Proposition vérifiée, ou null si elle ne tient pas : fiche inconnue, texte vide…
     * Sert aussi à revérifier une proposition modifiée par le MJ avant de l'appliquer.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>|null
     */
    public function normalize(string $kind, array $item): ?array
    {
        $public = filter_var($item['public'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $payload = match ($kind) {
            'summary' => ['text' => $this->text($item['text'] ?? null, 10000), 'public' => $public],
            'event' => [
                'title' => $this->text($item['title'] ?? null, 255),
                'description' => $this->text($item['description'] ?? null, 5000),
                'public' => $public,
            ],
            'relation' => [
                'from' => $this->entity($item['from'] ?? null),
                'to' => $this->entity($item['to'] ?? null),
                'label' => $this->text($item['label'] ?? null, 100),
                'reverse_label' => $this->text($item['reverse_label'] ?? null, 100),
                'public' => $public,
            ],
            'status' => ['entity' => $this->entity($item['entity'] ?? null), 'status' => $this->text($item['status'] ?? null, 60)],
            'note' => ['entity' => $this->entity($item['entity'] ?? null), 'text' => $this->text($item['text'] ?? null, 5000)],
            'reveal' => ['entity' => $this->entity($item['entity'] ?? null), 'characters' => $this->characters($item['characters'] ?? [])],
            default => null,
        };

        if ($payload === null) {
            return null;
        }

        $valid = match ($kind) {
            'summary' => $payload['text'] !== '',
            'event' => $payload['title'] !== '',
            'relation' => $payload['from'] && $payload['to'] && $payload['from'] !== $payload['to'] && $payload['label'] !== '',
            'status' => $payload['entity'] && $payload['status'] !== '',
            'note' => $payload['entity'] && $payload['text'] !== '',
            'reveal' => $payload['entity'] && $payload['characters'] !== [],
        };

        return $valid ? $payload : null;
    }

    /** @return array<string, mixed> */
    private function decode(string $response): array
    {
        // Les IA entourent souvent le JSON de ```json … ``` ou d'une phrase : on garde l'objet.
        $start = strpos($response, '{');
        $end = strrpos($response, '}');

        if ($start === false || $end === false || $end < $start) {
            throw new UnreadableResponse(__('La réponse ne contient pas d’objet JSON. Vérifiez que vous avez collé toute la réponse de l’IA.'));
        }

        $data = json_decode(substr($response, $start, $end - $start + 1), true);

        if (! is_array($data) || array_is_list($data)) {
            throw new UnreadableResponse(__('La réponse n’est pas un JSON valide. Demandez à l’IA de répondre uniquement par l’objet JSON, puis collez-le à nouveau.'));
        }

        return $data;
    }

    private function text(mixed $value, int $max): string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return '';
        }

        return Str::limit(trim(strip_tags((string) $value)), $max, '…');
    }

    /** Identifiant d'une fiche de la campagne ; « #12 » et « 12 » sont acceptés. */
    private function entity(mixed $value): ?int
    {
        $this->entityIds ??= array_fill_keys($this->campaign->availableEntities()->pluck('id')->all(), true);
        $id = $this->id($value);

        return $id !== null && isset($this->entityIds[$id]) ? $id : null;
    }

    /** @return list<int> personnages actifs de la campagne, sans doublon */
    private function characters(mixed $value): array
    {
        $this->characterIds ??= array_fill_keys($this->campaign->playerCharacters()->active()->pluck('id')->all(), true);

        return collect(is_array($value) ? $value : [$value])
            ->map(fn ($id) => $this->id($id))
            ->filter(fn ($id) => $id !== null && isset($this->characterIds[$id]))
            ->unique()->values()->all();
    }

    private function id(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^\s*#?(\d{1,18})\s*$/', $value, $match)) {
            return (int) $match[1];
        }

        return null;
    }
}
