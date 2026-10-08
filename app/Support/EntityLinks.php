<?php

namespace App\Support;

use App\Models\Campaign;
use App\Models\Entity;
use App\Models\Rule;
use App\Models\Scene;
use App\Models\SessionNote;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

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
     * Texte mis en forme (Markdown : gras, italique, titres, listes, citations), liens internes
     * cliquables. Le HTML saisi est échappé ; un simple saut de ligne reste un saut de ligne.
     *
     * @param  (callable(Entity): ?string)|null  $url  adresse du lien, ou null pour du texte simple
     *                                                 (vue joueur : seules les fiches qu'il connaît)
     */
    public static function render(?string $text, Campaign $campaign, ?callable $url = null, bool $inline = false): HtmlString
    {
        $restricted = $url !== null;
        $url ??= fn (Entity $entity) => route('entities.show', [$campaign, $entity]);

        if ($text === null || $text === '') {
            return new HtmlString('');
        }

        preg_match_all(self::PATTERN, $text, $matches, PREG_SET_ORDER);
        [$byId, $byName] = self::resolve($matches, $campaign);

        $links = [];
        $marked = preg_replace_callback(self::PATTERN, function (array $match) use ($byId, $byName, $url, $restricted, &$links) {
            $label = trim($match[1]);
            $entity = isset($match[2]) && $match[2] !== '' ? $byId->get((int) $match[2]) : $byName->get(mb_strtolower($label));
            $href = $entity === null ? null : $url($entity);

            $links[] = match (true) {
                $href !== null => '<a href="'.e($href).'" class="font-medium text-codex underline decoration-codex/40 underline-offset-2 hover:decoration-codex" wire:navigate>'.e($entity->name).'</a>',
                $entity === null && ! $restricted => '<span class="text-stone-500" title="'.e(__('Fiche introuvable dans cette campagne')).'">'.e($label).'</span>',
                default => e($label),
            };

            return self::token(count($links) - 1);
        }, $text);

        return new HtmlString(self::markdown($marked, $links, $inline));
    }

    /** Une ligne mise en forme (valeur de champ, note courte) : pas de paragraphe ni de liste. */
    public static function inline(?string $text, Campaign $campaign, ?callable $url = null): HtmlString
    {
        return self::render($text, $campaign, $url, inline: true);
    }

    /**
     * Texte mis en forme sans aucun lien : les noms cités restent lisibles, sans révéler
     * de fiche que le lecteur ne peut pas ouvrir.
     */
    public static function plain(?string $text, bool $inline = false): HtmlString
    {
        if ($text === null || $text === '') {
            return new HtmlString('');
        }

        $links = [];
        $marked = preg_replace_callback(self::PATTERN, function (array $match) use (&$links) {
            $links[] = e(trim($match[1]));

            return self::token(count($links) - 1);
        }, $text);

        return new HtmlString(self::markdown($marked, $links, $inline));
    }

    /** Texte brut d'une ligne, sans mise en forme ni lien (aperçus tronqués). */
    public static function excerpt(?string $text): string
    {
        $html = self::plain($text)->toHtml();

        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(str_replace(['<br>', '</p>', '</li>'], ' ', $html)), ENT_QUOTES | ENT_HTML5)));
    }

    /** Marque provisoire d'un lien, hors de la syntaxe Markdown (zone d'usage privé d'Unicode). */
    private static function token(int $index): string
    {
        return "\u{E000}{$index}\u{E001}";
    }

    /** @param  list<string>  $links  HTML des liens, remis à la place de leurs marques */
    private static function markdown(string $text, array $links, bool $inline): string
    {
        $options = [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 10,
            'renderer' => ['soft_break' => "<br>\n"],
        ];

        $html = $inline ? Str::inlineMarkdown($text, $options) : Str::markdown($text, $options);
        // Pas d'image distante : elle préviendrait un site tiers de chaque lecture.
        $html = preg_replace('/<img\b[^>]*>/i', '', $html);
        $html = preg_replace_callback('/\x{E000}(\d+)\x{E001}/u', fn (array $m) => $links[(int) $m[1]] ?? '', $html);

        return $inline ? trim($html) : '<div class="rich">'.trim($html).'</div>';
    }

    /**
     * Fiches citées dans un texte, dans l'ordre d'apparition et sans doublon.
     *
     * @return Collection<int, Entity>
     */
    public static function referenced(?string $text, Campaign $campaign): Collection
    {
        if ($text === null || $text === '') {
            return collect();
        }

        preg_match_all(self::PATTERN, $text, $matches, PREG_SET_ORDER);
        [$byId, $byName] = self::resolve($matches, $campaign);

        return collect($matches)
            ->map(fn (array $m) => isset($m[2]) && $m[2] !== '' ? $byId->get((int) $m[2]) : $byName->get(mb_strtolower(trim($m[1]))))
            ->filter()
            ->unique('id')
            ->values();
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
     * Règles utilisables dans la campagne qui citent la fiche.
     *
     * @return Collection<int, Rule>
     */
    public static function rules(Entity $entity, Campaign $campaign): Collection
    {
        [$byId, $byName] = self::needles($entity);

        return $campaign->availableRules()
            ->where(function ($q) use ($byId, $byName) {
                foreach (['summary', 'procedure', 'gm_notes'] as $column) {
                    $q->orWhere("rules.{$column}", 'like', $byId)->orWhereRaw("lower(rules.{$column}) like ?", [$byName]);
                }
            })
            ->orderByRaw('lower(title)')
            ->get();
    }

    /**
     * Notes de session de la campagne qui citent la fiche, des plus récentes aux plus anciennes.
     *
     * @return Collection<int, SessionNote>
     */
    public static function sessionNotes(Entity $entity, Campaign $campaign): Collection
    {
        [$byId, $byName] = self::needles($entity);

        return SessionNote::query()
            ->whereHas('playSession', fn ($q) => $q->where('campaign_id', $campaign->getKey()))
            ->where(fn ($q) => $q->where('body', 'like', $byId)->orWhereRaw('lower(body) like ?', [$byName]))
            ->with('playSession')
            ->latest('id')
            ->limit(20)
            ->get();
    }

    /** @return array{0: string, 1: string} motifs LIKE d'un lien [[…|id]] ou [[nom]] vers la fiche */
    private static function needles(Entity $entity): array
    {
        return ['%|'.$entity->getKey().']]%', '%[['.addcslashes(mb_strtolower($entity->name), '%_\\').']]%'];
    }

    /**
     * Scènes de la campagne qui citent la fiche, par une liaison ou dans leur préparation.
     *
     * @return Collection<int, Scene>
     */
    public static function scenes(Entity $entity, Campaign $campaign): Collection
    {
        $byId = '%|'.$entity->getKey().']]%';
        $byName = '%[['.addcslashes(mb_strtolower($entity->name), '%_\\').']]%';

        return $campaign->scenes()
            ->where(fn ($q) => $q
                ->whereHas('entities', fn ($e) => $e->whereKey($entity->getKey()))
                ->orWhere('scenes.description', 'like', $byId)
                ->orWhereRaw('lower(scenes.description) like ?', [$byName]))
            ->with('scenario')
            ->orderBy('scenarios.position')
            ->orderBy('scenes.position')
            ->get();
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
