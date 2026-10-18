<?php

namespace App\Support;

use App\Enums\SceneStatus;
use App\Models\Campaign;
use App\Models\PlaySession;
use App\Models\Scene;
use Illuminate\Support\Collection;

/**
 * Livre des aventures d'une campagne : les scènes jouées, dans l'ordre des scénarios, racontées
 * par ce qui s'est passé à la table, puis les résumés des séances. Réservé au MJ (notes MJ).
 *
 * Le récit d'une scène, du plus précis au plus simple : « Ce qui s'est passé » ; sinon une section
 * « ### Ce qui s'est passé » dans la description (scènes importées) ; sinon, pour une scène jouée,
 * ce qui était prévu (cochée « Jouée comme prévu », ou rien d'écrit : à confirmer par le MJ).
 */
class AdventureBook
{
    /** Titre de section « Ce qui s'est passé » (avec « (séance 2) » éventuel) dans une description. */
    private const HAPPENED = "/^#{1,6}\s*Ce qui s['’]est passé\b.*$/miu";

    /** Titre de section « Ce qui est prévu » en tête de description. */
    private const PLANNED = "/^#{1,6}\s*Ce qui est prévu\s*$/miu";

    public function __construct(private readonly Campaign $campaign) {}

    /**
     * Scénarios ayant au moins une scène racontée, avec leurs scènes.
     *
     * @return Collection<int, array{name: string, summary: ?string, scenes: list<array{scene: Scene, text: string, source: string}>}>
     */
    public function chapters(): Collection
    {
        return $this->campaign->scenarios()
            ->with(['scenes' => fn ($q) => $q->orderBy('position')->orderBy('id')])
            ->get()
            ->map(fn ($scenario) => [
                'name' => $scenario->name,
                'summary' => $scenario->summary,
                'scenes' => $scenario->scenes
                    ->map(fn (Scene $scene) => self::entry($scene))
                    ->filter()
                    ->values()
                    ->all(),
            ])
            ->filter(fn (array $chapter) => $chapter['scenes'] !== [])
            ->values();
    }

    /** @return Collection<int, PlaySession> séances terminées ou en cours qui ont un résumé */
    public function sessions(): Collection
    {
        return $this->campaign->playSessions()->whereNotNull('summary')->orderBy('number')->get();
    }

    public function isEmpty(): bool
    {
        return $this->chapters()->isEmpty() && $this->sessions()->isEmpty();
    }

    /**
     * Le récit d'une scène, ou null si elle n'a pas (encore) été jouée.
     * source : « happened » (écrit), « planned » (jouée comme prévu) ou « unconfirmed » (jouée, rien d'écrit).
     *
     * @return array{scene: Scene, text: string, source: string}|null
     */
    public static function entry(Scene $scene): ?array
    {
        $happened = self::happened($scene);

        if ($happened !== '') {
            return ['scene' => $scene, 'text' => $happened, 'source' => 'happened'];
        }

        if (! $scene->played_as_planned && $scene->status !== SceneStatus::Played) {
            return null;
        }

        return [
            'scene' => $scene,
            'text' => self::planned($scene),
            'source' => $scene->played_as_planned ? 'planned' : 'unconfirmed',
        ];
    }

    /** Ce qui s'est passé : le champ dédié, sinon la section du même nom dans la description. */
    public static function happened(Scene $scene): string
    {
        $text = trim((string) $scene->what_happened);

        if ($text !== '') {
            return $text;
        }

        $description = (string) $scene->description;

        if (! preg_match(self::HAPPENED, $description, $match, PREG_OFFSET_CAPTURE)) {
            return '';
        }

        // Tout ce qui suit le premier titre ; les titres suivants (autre séance) deviennent du gras.
        $rest = substr($description, $match[0][1]);

        return trim(preg_replace_callback(self::HAPPENED, fn (array $m) => '**'.trim(ltrim($m[0], '# ')).'**', $rest));
    }

    /** Ce qui était prévu : la description, sans son titre « Ce qui est prévu » ni la section « Ce qui s'est passé ». */
    public static function planned(Scene $scene): string
    {
        $description = (string) $scene->description;

        if (preg_match(self::HAPPENED, $description, $match, PREG_OFFSET_CAPTURE)) {
            $description = substr($description, 0, $match[0][1]);
        }

        return trim(preg_replace(self::PLANNED, '', $description, 1));
    }

    /** Le livre en texte simple (Markdown), pour le copier ou le confier à une IA. */
    public function markdown(): string
    {
        $lines = ['# '.$this->campaign->name];

        foreach ($this->chapters() as $chapter) {
            $lines[] = '## '.$chapter['name'];
            $currentChapter = null;

            foreach ($chapter['scenes'] as $entry) {
                $scene = $entry['scene'];

                if ($scene->chapter && $scene->chapter !== $currentChapter) {
                    $currentChapter = $scene->chapter;
                    $lines[] = '### '.$currentChapter;
                }

                $lines[] = '#### '.$scene->name;
                $lines[] = $entry['text'] !== '' ? $entry['text'] : '_'.__('(rien d’écrit)').'_';
            }
        }

        $sessions = $this->sessions();

        if ($sessions->isNotEmpty()) {
            $lines[] = '## '.__('Les séances');

            foreach ($sessions as $session) {
                $lines[] = '### '.$session->label();
                $lines[] = trim((string) $session->summary);
            }
        }

        // Les liens [[Fiche|12]] se lisent par leur nom.
        return preg_replace('/\[\[([^\[\]|\n]+?)(?:\|\d+)?\]\]/u', '$1', implode("\n\n", $lines))."\n";
    }

    /** Texte à confier à une IA (celle du MJ) pour rédiger le livre comme un récit. */
    public function prompt(): string
    {
        return implode("\n\n", [
            __('Tu es l’auteur du livre des aventures d’une campagne de jeu de rôle. Voici ce qui s’est passé, scène par scène, puis les résumés des séances, tels que le MJ les a notés.'),
            __('Rédige un récit vivant, au passé, comme une chronique des aventures des personnages. Garde les faits, les noms et l’ordre des événements ; n’invente rien d’important. Un titre par scénario, des paragraphes plutôt que des listes. Écris en :language et réponds seulement par le récit, en Markdown.', ['language' => Locale::available()[app()->getLocale()] ?? app()->getLocale()]),
            '---',
            $this->markdown(),
        ])."\n";
    }
}
