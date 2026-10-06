<?php

namespace App\Support;

use App\Models\Campaign;
use App\Models\Entity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

/**
 * Liens internes entre fiches.
 *
 * Syntaxe stockée : [[Nom|42]] (lien par identifiant, insensible aux renommages) ou
 * [[Nom]] (saisi à la main, résolu par nom dans la campagne). Seules les entités
 * accessibles dans la campagne deviennent des liens ; le reste s'affiche en texte.
 */
class EntityLinks
{
    public const PATTERN = '/\[\[([^\[\]|\n]+?)(?:\|(\d+))?\]\]/u';

    /**
     * Texte échappé, sauts de ligne conservés, liens internes cliquables.
     */
    public static function render(?string $text, Campaign $campaign): HtmlString
    {
        if ($text === null || $text === '') {
            return new HtmlString('');
        }

        preg_match_all(self::PATTERN, $text, $matches, PREG_SET_ORDER);
        [$byId, $byName] = self::resolve($matches, $campaign);

        $html = '';
        $offset = 0;

        foreach (self::matchesWithOffsets($text) as [$full, $label, $id, $position]) {
            $html .= e(substr($text, $offset, $position - $offset));
            $offset = $position + strlen($full);

            $entity = $id !== null ? $byId->get((int) $id) : $byName->get(mb_strtolower(trim($label)));

            $html .= $entity === null
                ? '<span class="text-stone-500" title="Fiche introuvable dans cette campagne">'.e(trim($label)).'</span>'
                : '<a href="'.e(route('entities.show', [$campaign, $entity])).'" class="font-medium text-codex underline decoration-codex/40 underline-offset-2 hover:decoration-codex" wire:navigate>'.e($entity->name).'</a>';
        }

        $html .= e(substr($text, $offset));

        return new HtmlString(nl2br($html, false));
    }

    /**
     * Fiches de la campagne qui citent cette entité.
     *
     * @return Collection<int, Entity>
     */
    public static function backlinks(Entity $entity, Campaign $campaign): Collection
    {
        $byId = '%|'.$entity->getKey().']]%';
        $byName = '%[['.addcslashes(mb_strtolower($entity->name), '%_\\').']]%';

        return $campaign->availableEntities()
            ->whereKeyNot($entity->getKey())
            ->where(function ($q) use ($byId, $byName) {
                foreach (['description', 'gm_notes'] as $column) {
                    $q->orWhere($column, 'like', $byId)->orWhereRaw("lower({$column}) like ?", [$byName]);
                }
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * @return list<array{0: string, 1: string, 2: ?string, 3: int}>
     */
    private static function matchesWithOffsets(string $text): array
    {
        preg_match_all(self::PATTERN, $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        return array_map(fn (array $m) => [
            $m[0][0],
            $m[1][0],
            isset($m[2]) && $m[2][1] !== -1 ? $m[2][0] : null,
            $m[0][1],
        ], $matches);
    }

    /**
     * @param  array<int, array<int, string>>  $matches
     * @return array{0: Collection<int, Entity>, 1: Collection<string, Entity>}
     */
    private static function resolve(array $matches, Campaign $campaign): array
    {
        $ids = [];
        $names = [];

        foreach ($matches as $m) {
            if (($m[2] ?? '') !== '') {
                $ids[] = (int) $m[2];
            } else {
                $names[] = mb_strtolower(trim($m[1]));
            }
        }

        $byId = $ids === []
            ? collect()
            : $campaign->availableEntities()->whereKey(array_unique($ids))->get()->keyBy('id');

        $byName = $names === []
            ? collect()
            : $campaign->availableEntities()
                ->whereIn(DB::raw('lower(name)'), array_unique($names))
                ->orderBy('id')
                ->get()
                ->unique(fn (Entity $e) => mb_strtolower($e->name))
                ->keyBy(fn (Entity $e) => mb_strtolower($e->name));

        return [$byId, $byName];
    }
}
