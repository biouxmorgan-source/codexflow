<?php

namespace App\Support\Search;

use App\Enums\Zone;
use App\Models\Campaign;
use App\Models\CharacterGrant;
use App\Models\CharacterNote;
use App\Models\Document;
use App\Models\Entity;
use App\Models\PlayerCharacter;
use App\Models\Rule;
use App\Models\Scene;
use App\Models\Secret;
use App\Models\SessionNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Recherche globale d'une campagne : fiches, scènes, règles, documents, notes (MJ et joueurs),
 * secrets et informations ou objets donnés aux personnages.
 *
 * Chaque mot doit apparaître quelque part dans le texte cherchable, sans tenir compte
 * des accents ni de la casse. Le texte cherchable est construit côté serveur selon les
 * droits de la personne : la zone MJ n'entre jamais dans la recherche d'un joueur.
 */
final class GlobalSearch
{
    public const KINDS = [
        'entities' => 'Fiches',
        'scenes' => 'Scènes',
        'rules' => 'Règles',
        'documents' => 'Documents',
        'notes' => 'Notes de session',
        'knowledge' => 'Informations, objets et règles',
    ];

    /** @return array<string, string> libellés traduits des catégories (mêmes clés que KINDS) */
    public static function kinds(): array
    {
        return [
            'entities' => __('Fiches'),
            'scenes' => __('Scènes'),
            'rules' => __('Règles'),
            'documents' => __('Documents'),
            'notes' => __('Notes de session'),
            'knowledge' => __('Informations, objets et règles'),
        ];
    }

    /** Ce qu'un joueur peut chercher : seulement ce que son personnage a reçu, et les notes qu'il peut lire. */
    public const PLAYER_KINDS = ['entities', 'knowledge', 'documents', 'notes'];

    private const PER_KIND = 20;

    /** @var list<string> */
    private array $words;

    public function __construct(
        private readonly Campaign $campaign,
        private readonly User $user,
        string $query,
        // Mode « Voir comme… » : le MJ cherche à travers ce personnage, comme son joueur.
        // L'appelant vérifie que l'utilisateur est MJ de la campagne et que le personnage en fait partie.
        private readonly ?PlayerCharacter $asCharacter = null,
    ) {
        $this->words = self::words($query);
    }

    /** @return list<string> */
    public static function words(string $query): array
    {
        return collect(preg_split('/\s+/u', mb_substr(trim($query), 0, 200)) ?: [])
            ->map(fn (string $word) => trim($word, " \t\"'«»,;:.!?()[]"))
            ->filter(fn (string $word) => mb_strlen($word) >= 2)
            ->unique(fn (string $word) => mb_strtolower($word))
            ->take(8)
            ->values()
            ->all();
    }

    public function isEmpty(): bool
    {
        return $this->words === [];
    }

    /**
     * @param  list<string>|null  $kinds
     * @return array<string, Collection<int, SearchResult>>
     */
    public function run(?array $kinds = null, ?int $entityTypeId = null): array
    {
        if ($this->isEmpty()) {
            return [];
        }

        if ($this->asCharacter !== null) {
            return $this->runForPlayer($this->asCharacter, $kinds);
        }

        if (! $this->campaign->isGameMaster($this->user)) {
            $character = $this->campaign->playerCharacters()->active()->where('user_id', $this->user->id)->first();

            return $character === null ? [] : $this->runForPlayer($character, $kinds);
        }

        $kinds ??= array_keys(self::KINDS);
        $results = [];

        foreach ($kinds as $kind) {
            $found = match ($kind) {
                'entities' => $this->entities($entityTypeId),
                'scenes' => $entityTypeId ? collect() : $this->scenes(),
                'rules' => $entityTypeId ? collect() : $this->rules(),
                'documents' => $entityTypeId ? collect() : $this->documents(),
                'notes' => $entityTypeId ? collect() : $this->notes(),
                'knowledge' => $entityTypeId ? collect() : $this->secrets()->concat($this->givenItems()),
                default => collect(),
            };

            if ($found->isNotEmpty()) {
                $results[$kind] = $found;
            }
        }

        return $results;
    }

    /**
     * Recherche d'un joueur, à travers son personnage actif (ou celui que le MJ « voit comme ») : zone publique des fiches révélées,
     * informations et objets reçus, documents donnés, notes qu'il a le droit de lire.
     * La zone MJ n'entre jamais dans le texte cherché.
     *
     * @param  list<string>|null  $kinds
     * @return array<string, Collection<int, SearchResult>>
     */
    private function runForPlayer(PlayerCharacter $character, ?array $kinds): array
    {
        $results = [];

        foreach (array_intersect($kinds ?? self::PLAYER_KINDS, self::PLAYER_KINDS) as $kind) {
            $found = match ($kind) {
                'entities' => $this->knownEntities($character),
                'knowledge' => $this->receivedItems($character),
                'documents' => $this->receivedDocuments($character),
                'notes' => $this->readableNotes($character),
            };

            if ($found->isNotEmpty()) {
                $results[$kind] = $found;
            }
        }

        return $results;
    }

    /** @return array<string, int> paramètre qui garde le mode « Voir comme… » dans les liens des résultats */
    private function viewAsQuery(): array
    {
        return $this->asCharacter ? ['comme' => 1] : [];
    }

    /** @return Collection<int, SearchResult> */
    private function knownEntities(PlayerCharacter $character): Collection
    {
        // Seules les valeurs des champs publics sont cherchables.
        $publicKeys = $this->campaign->gameSystem->fieldDefinitions()->where('zone', Zone::Public)->pluck('id')
            ->map(fn (int $id) => "'".$id."'")->implode(',') ?: "''";
        $haystack = "concat_ws(' ', entities.name, entities.summary, entities.description,
            (select string_agg(value, ' ') from jsonb_each_text(entities.field_values) where key in ({$publicKeys})),
            (select entity_types.name from entity_types where entity_types.id = entities.entity_type_id))";

        return Entity::query()
            ->whereIn('id', $character->grants()->where('kind', 'entity')->select('entity_id'))
            ->with('type')
            ->tap(fn (Builder $q) => $this->matchAll($q, $haystack))
            ->tap(fn (Builder $q) => $this->rank($q, 'entities.name'))
            ->limit(self::PER_KIND)
            ->get()
            ->map(fn (Entity $entity) => new SearchResult(
                title: $entity->name,
                subtitle: $entity->type->name,
                url: route('characters.entity', [$this->campaign, $character, $entity, ...$this->viewAsQuery()]),
                snippet: $this->snippet([__('Résumé') => $entity->summary, __('Description') => $entity->description], $entity->name),
            ));
    }

    /** @return Collection<int, SearchResult> */
    private function receivedItems(PlayerCharacter $character): Collection
    {
        // Règles ouvertes au personnage : titre, résumé et procédure, jamais les notes MJ.
        $rule = "(select concat_ws(' ', rules.title, rules.category, rules.summary, rules.procedure) from rules where rules.id = character_grants.rule_id)";

        return $character->grants()
            ->where(fn (Builder $q) => $q
                ->whereIn('kind', ['information', 'possession'])
                ->orWhere(fn (Builder $q) => $q->where('kind', 'rule')->whereIn('rule_id', Rule::query()->where('zone', Zone::Public)->select('id'))))
            ->with('rule')
            ->tap(fn (Builder $q) => $this->matchAll($q, "concat_ws(' ', character_grants.title, character_grants.body, {$rule})"))
            ->limit(self::PER_KIND)
            ->get()
            ->map(fn (CharacterGrant $grant) => new SearchResult(
                title: $grant->label(),
                subtitle: CharacterGrant::kinds()[$grant->kind],
                url: route('characters.show', [$this->campaign, $character, ...$this->viewAsQuery()]),
                snippet: $this->snippet($grant->kind === 'rule'
                    ? [__('Résumé') => $grant->rule->summary, __('Procédure') => $grant->rule->procedure]
                    : [__('Texte') => $grant->body], $grant->label()),
            ));
    }

    /** @return Collection<int, SearchResult> */
    private function receivedDocuments(PlayerCharacter $character): Collection
    {
        return Document::query()
            ->whereIn('id', $character->grants()->where('kind', 'document')->select('document_id'))
            ->tap(fn (Builder $q) => $this->matchAll($q, "concat_ws(' ', documents.title, documents.description)"))
            ->tap(fn (Builder $q) => $this->rank($q, 'documents.title'))
            ->limit(self::PER_KIND)
            ->get()
            ->map(fn (Document $document) => new SearchResult(
                title: $document->title,
                subtitle: $document->isPdf() ? 'PDF' : __('Image'),
                url: route('characters.document', [$this->campaign, $character, $document]),
                snippet: $this->snippet([__('Description') => $document->description], $document->title),
            ));
    }

    /** @return Collection<int, SearchResult> */
    private function readableNotes(PlayerCharacter $character): Collection
    {
        return CharacterNote::query()
            ->when($this->asCharacter,
                fn (Builder $q) => $q->visibleToCharacter($this->asCharacter),
                fn (Builder $q) => $q->visibleTo($this->user, $this->campaign))
            ->with('character.entity')
            ->tap(fn (Builder $q) => $this->matchAll($q, 'character_notes.body'))
            ->latest('id')
            ->limit(self::PER_KIND)
            ->get()
            ->map(fn (CharacterNote $note) => new SearchResult(
                title: __('Note de :name', ['name' => $note->character->entity->name]),
                subtitle: $note->created_at->isoFormat('L LT').' · '.$note->visibilityLabel(),
                // Une note partagée par un autre joueur se lit sur la page de son propre personnage.
                url: route('characters.show', [$this->campaign, $character, ...$this->viewAsQuery()]),
                snippet: $this->snippet([__('Note') => $note->body], ''),
            ));
    }

    /** @return Collection<int, SearchResult> */
    private function entities(?int $entityTypeId): Collection
    {
        $campaignId = (int) $this->campaign->getKey();
        $haystack = "concat_ws(' ', entities.name, entities.summary, entities.description, entities.gm_notes,
            (select string_agg(value, ' ') from jsonb_each_text(entities.field_values)),
            (select entity_types.name from entity_types where entity_types.id = entities.entity_type_id),
            (select string_agg(tags.name, ' ') from entity_tag join tags on tags.id = entity_tag.tag_id where entity_tag.entity_id = entities.id),
            (select concat_ws(' ', s.status, s.gm_notes,
                (select string_agg(o.value, ' ') from jsonb_each_text(case when jsonb_typeof(s.overrides) = 'object' then s.overrides else '{}'::jsonb end) o)) from campaign_entity_states s where s.entity_id = entities.id and s.campaign_id = {$campaignId}),
            (select string_agg(scenes.name, ' ') from scene_entity
                join scenes on scenes.id = scene_entity.scene_id
                join scenarios on scenarios.id = scenes.scenario_id
                where scene_entity.entity_id = entities.id and scenarios.campaign_id = {$campaignId}))";

        $entities = $this->campaign->availableEntities()
            ->with(['type', 'tags'])
            ->when($entityTypeId, fn (Builder $q) => $q->where('entity_type_id', $entityTypeId))
            ->tap(fn (Builder $q) => $this->matchAll($q, $haystack))
            ->tap(fn (Builder $q) => $this->rank($q, 'entities.name'))
            ->limit(self::PER_KIND)
            ->get();

        $scenesByEntity = $this->linkedSceneNames($entities->modelKeys());

        return $entities->map(fn (Entity $entity) => new SearchResult(
            title: $entity->name,
            subtitle: $entity->type->name,
            url: route('entities.show', [$this->campaign, $entity]),
            snippet: $this->snippet([
                __('Résumé') => $entity->summary,
                __('Description') => $entity->description,
                __('Zone MJ') => $entity->gm_notes,
                __('Tags') => $entity->tags->pluck('name')->implode(', '),
                __('Scènes') => implode(', ', $scenesByEntity[$entity->id] ?? []),
            ], $entity->name),
        ));
    }

    /** @return Collection<int, SearchResult> */
    private function scenes(): Collection
    {
        $haystack = "concat_ws(' ', scenes.name, scenes.chapter, scenes.description, scenarios.name,
            (select string_agg(tags.name, ' ') from scene_tag join tags on tags.id = scene_tag.tag_id where scene_tag.scene_id = scenes.id))";

        return $this->campaign->scenes()
            ->with(['scenario', 'tags'])
            ->tap(fn (Builder $q) => $this->matchAll($q, $haystack))
            ->tap(fn (Builder $q) => $this->rank($q, 'scenes.name'))
            ->limit(self::PER_KIND)
            ->get()
            ->map(fn (Scene $scene) => new SearchResult(
                title: $scene->name,
                subtitle: $scene->scenario->name.' · '.$scene->status->label(),
                url: route('scenes.show', [$this->campaign, $scene]),
                snippet: $this->snippet([
                    __('Préparation') => $scene->description,
                    __('Tags') => $scene->tags->pluck('name')->implode(', '),
                ], $scene->name),
            ));
    }

    /** @return Collection<int, SearchResult> */
    private function rules(): Collection
    {
        $haystack = "concat_ws(' ', rules.title, rules.category, rules.summary, rules.procedure, rules.source, rules.gm_notes,
            (select string_agg(tags.name, ' ') from rule_tag join tags on tags.id = rule_tag.tag_id where rule_tag.rule_id = rules.id))";

        return $this->campaign->availableRules()
            ->with('tags')
            ->tap(fn (Builder $q) => $this->matchAll($q, $haystack))
            ->tap(fn (Builder $q) => $this->rank($q, 'rules.title'))
            ->limit(self::PER_KIND)
            ->get()
            ->map(fn (Rule $rule) => new SearchResult(
                title: $rule->title,
                subtitle: trim(($rule->category ? $rule->category.' · ' : '').$rule->status->label()),
                url: route('rules.show', [$this->campaign, $rule]),
                snippet: $this->snippet([
                    __('Résumé') => $rule->summary,
                    __('Procédure') => $rule->procedure,
                    __('Zone MJ') => $rule->gm_notes,
                    __('Tags') => $rule->tags->pluck('name')->implode(', '),
                ], $rule->title),
            ));
    }

    /** @return Collection<int, SearchResult> */
    private function documents(): Collection
    {
        $haystack = "concat_ws(' ', documents.title, documents.description, documents.original_name,
            (select string_agg(tags.name, ' ') from document_tag join tags on tags.id = document_tag.tag_id where document_tag.document_id = documents.id))";

        return $this->campaign->availableDocuments()
            ->with('tags')
            ->tap(fn (Builder $q) => $this->matchAll($q, $haystack))
            ->tap(fn (Builder $q) => $this->rank($q, 'documents.title'))
            ->limit(self::PER_KIND)
            ->get()
            ->map(fn (Document $document) => new SearchResult(
                title: $document->title,
                subtitle: ($document->isPdf() ? 'PDF' : __('Image')).' · '.$document->scopeLabel(),
                url: route('documents.show', [$this->campaign, $document]),
                snippet: $this->snippet([
                    __('Description') => $document->description,
                    __('Tags') => $document->tags->pluck('name')->implode(', '),
                ], $document->title),
            ));
    }

    /** @return Collection<int, SearchResult> */
    private function notes(): Collection
    {
        return $this->sessionNotes()->concat($this->playerNotes());
    }

    /** Notes des joueurs que le MJ peut lire : toutes, sauf celles qu'un joueur garde pour lui seul. */
    private function playerNotes(): Collection
    {
        return CharacterNote::query()
            ->visibleTo($this->user, $this->campaign)
            ->with('character.entity')
            ->tap(fn (Builder $q) => $this->matchAll($q, 'character_notes.body'))
            ->latest('id')
            ->limit(self::PER_KIND)
            ->get()
            ->map(fn (CharacterNote $note) => new SearchResult(
                title: __('Note de :name', ['name' => $note->character->entity->name]),
                subtitle: $note->created_at->isoFormat('L LT').' · '.$note->visibilityLabel(),
                url: route('characters.show', [$this->campaign, $note->character]),
                snippet: $this->snippet([__('Note') => $note->body], ''),
            ));
    }

    /** @return Collection<int, SearchResult> secrets de la campagne */
    private function secrets(): Collection
    {
        return $this->campaign->secrets()
            ->tap(fn (Builder $q) => $this->matchAll($q, "concat_ws(' ', secrets.title, secrets.body)"))
            ->tap(fn (Builder $q) => $this->rank($q, 'secrets.title'))
            ->limit(self::PER_KIND)
            ->get()
            ->map(fn (Secret $secret) => new SearchResult(
                title: $secret->title,
                subtitle: __('Secret'),
                url: route('secrets.index', ['campaign' => $this->campaign, 'q' => $secret->title]),
                snippet: $this->snippet([__('Texte') => $secret->body], $secret->title),
            ));
    }

    /** @return Collection<int, SearchResult> informations et objets donnés aux personnages de la campagne */
    private function givenItems(): Collection
    {
        return CharacterGrant::query()
            ->whereIn('kind', ['information', 'possession'])
            ->whereHas('character', fn (Builder $q) => $q->where('campaign_id', $this->campaign->getKey()))
            ->with('character.entity')
            ->tap(fn (Builder $q) => $this->matchAll($q, "concat_ws(' ', character_grants.title, character_grants.body)"))
            ->latest('id')
            ->limit(self::PER_KIND)
            ->get()
            ->map(fn (CharacterGrant $grant) => new SearchResult(
                title: $grant->label(),
                subtitle: CharacterGrant::kinds()[$grant->kind].' · '.$grant->character->entity->name,
                url: route('characters.show', [$this->campaign, $grant->character]),
                snippet: $this->snippet([__('Texte') => $grant->body], $grant->label()),
            ));
    }

    /** @return Collection<int, SearchResult> */
    private function sessionNotes(): Collection
    {
        return SessionNote::query()
            ->whereHas('playSession', fn (Builder $q) => $q->where('campaign_id', $this->campaign->getKey()))
            ->with(['playSession', 'scene'])
            ->tap(fn (Builder $q) => $this->matchAll($q, 'session_notes.body'))
            ->latest()
            ->limit(self::PER_KIND)
            ->get()
            ->map(fn (SessionNote $note) => new SearchResult(
                title: $note->playSession->label().($note->scene ? ' · '.$note->scene->name : ''),
                subtitle: $note->created_at->isoFormat('L LT'),
                url: route('sessions.show', [$this->campaign, $note->playSession]),
                snippet: $this->snippet([__('Note') => $note->body], ''),
            ));
    }

    /**
     * Chaque mot doit figurer dans le texte, accents et casse ignorés.
     */
    private function matchAll(Builder $query, string $haystack): void
    {
        foreach ($this->words as $word) {
            $query->whereRaw("unaccent(lower({$haystack})) like unaccent(lower(?))", ['%'.addcslashes($word, '%_\\').'%']);
        }
    }

    /**
     * Les titres qui commencent par la recherche, puis ceux qui la contiennent, puis le reste.
     */
    private function rank(Builder $query, string $title): void
    {
        $first = addcslashes($this->words[0], '%_\\');

        $query->orderByRaw("unaccent(lower({$title})) like unaccent(lower(?)) desc", [$first.'%'])
            ->orderByRaw("unaccent(lower({$title})) like unaccent(lower(?)) desc", ['%'.$first.'%'])
            ->orderByRaw("lower({$title})");
    }

    /**
     * @param  list<int>  $entityIds
     * @return array<int, list<string>>
     */
    private function linkedSceneNames(array $entityIds): array
    {
        if ($entityIds === []) {
            return [];
        }

        return DB::table('scene_entity')
            ->join('scenes', 'scenes.id', '=', 'scene_entity.scene_id')
            ->join('scenarios', 'scenarios.id', '=', 'scenes.scenario_id')
            ->where('scenarios.campaign_id', $this->campaign->getKey())
            ->whereIn('scene_entity.entity_id', $entityIds)
            ->orderBy('scenes.name')
            ->get(['scene_entity.entity_id', 'scenes.name'])
            ->groupBy('entity_id')
            ->map(fn (Collection $rows) => $rows->pluck('name')->all())
            ->all();
    }

    /**
     * Extrait autour du premier mot trouvé, dans un champ qui n'est pas déjà le titre.
     *
     * @param  array<string, ?string>  $fields
     * @return array{label: string, text: string}|null
     */
    private function snippet(array $fields, string $title): ?array
    {
        $titleFolded = self::fold($title);

        foreach ($this->words as $word) {
            $needle = self::fold($word);

            if (str_contains($titleFolded, $needle)) {
                continue;
            }

            foreach ($fields as $label => $text) {
                if ($text === null || $text === '') {
                    continue;
                }

                $plain = trim(preg_replace('/\s+/u', ' ', preg_replace('/\[\[([^\[\]|]+)(?:\|\d+)?\]\]/u', '$1', $text)));
                $position = mb_strpos(self::fold($plain), $needle);

                if ($position !== false) {
                    $start = max(0, $position - 60);
                    $excerpt = mb_substr($plain, $start, 160);

                    return [
                        'label' => $label,
                        'text' => ($start > 0 ? '…' : '').$excerpt.(mb_strlen($plain) > $start + 160 ? '…' : ''),
                    ];
                }
            }
        }

        foreach ($fields as $label => $text) {
            if ($label !== __('Zone MJ') && $text !== null && $text !== '') {
                return ['label' => $label, 'text' => Str::limit(trim(preg_replace('/\[\[([^\[\]|]+)(?:\|\d+)?\]\]/u', '$1', $text)), 160)];
            }
        }

        return null;
    }

    /** Minuscules sans accents, en gardant une lettre par lettre pour que les positions restent justes. */
    private static function fold(string $text): string
    {
        $folded = mb_strtolower($text);
        $map = ['à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'ç' => 'c', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i', 'í' => 'i', 'ô' => 'o', 'ö' => 'o', 'ó' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ú' => 'u',
            'ÿ' => 'y', 'ñ' => 'n'];

        return strtr($folded, $map);
    }

    /**
     * Termes à surligner dans les résultats.
     *
     * @return list<string>
     */
    public function highlightWords(): array
    {
        return $this->words;
    }

    /**
     * Texte échappé où les mots cherchés sont surlignés, accents ignorés.
     *
     * @param  list<string>  $words
     */
    public static function highlight(string $text, array $words): HtmlString
    {
        $folded = self::fold($text);
        $marks = [];

        foreach ($words as $word) {
            $needle = self::fold($word);
            $length = mb_strlen($needle);
            $offset = 0;

            while ($length > 0 && ($position = mb_strpos($folded, $needle, $offset)) !== false) {
                $marks[$position] = max($marks[$position] ?? 0, $length);
                $offset = $position + $length;
            }
        }

        ksort($marks);
        $html = '';
        $cursor = 0;

        foreach ($marks as $position => $length) {
            if ($position < $cursor) {
                continue;
            }

            $html .= e(mb_substr($text, $cursor, $position - $cursor)).'<mark class="rounded bg-amber-100 px-0.5 text-inherit">'.e(mb_substr($text, $position, $length)).'</mark>';
            $cursor = $position + $length;
        }

        return new HtmlString($html.e(mb_substr($text, $cursor)));
    }
}
