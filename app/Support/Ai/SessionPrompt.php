<?php

namespace App\Support\Ai;

use App\Enums\SceneStatus;
use App\Models\Campaign;
use App\Models\CharacterNote;
use App\Models\Entity;
use App\Models\PlayerCharacter;
use App\Models\PlaySession;
use App\Models\Scene;
use App\Models\User;
use App\Support\Locale;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Texte que le MJ colle dans l'IA de son choix : le contexte de la campagne et de la séance,
 * puis la réponse attendue, un objet JSON que SuggestionParser transforme en propositions.
 *
 * Il contient des notes MJ : il n'est construit que pour un MJ de la campagne, et seules
 * les notes de joueurs que ce MJ a le droit de lire y entrent (jamais les notes « Moi seul »).
 */
class SessionPrompt
{
    /** Au-delà, les fiches les moins récemment modifiées sont laissées de côté. */
    public const MAX_ENTITIES = 300;

    public const MAX_SCENES = 20;

    /** Longueur maximale des notes recopiées, en caractères. */
    public const MAX_NOTES = 30000;

    public function __construct(
        private readonly Campaign $campaign,
        private readonly User $gameMaster,
        private readonly ?PlaySession $session = null,
        private readonly string $extraNotes = '',
    ) {}

    public function build(): string
    {
        $sections = [
            __('Tu aides le MJ d’une campagne de jeu de rôle à tenir ses notes à jour. Lis le contexte et les notes de séance ci-dessous, puis propose des mises à jour. Le MJ validera chacune d’elles.'),
            $this->campaignSection(),
            $this->entitiesSection(),
            $this->charactersSection(),
            $this->scenesSection(),
            $this->notesSection(),
            $this->instructions(),
        ];

        return implode("\n\n", array_filter($sections))."\n";
    }

    private function campaignSection(): string
    {
        $lines = ['## '.__('Campagne'), __('Nom : :name', ['name' => $this->campaign->name])];
        $lines[] = __('Jeu : :name', ['name' => $this->campaign->gameSystem?->name]);

        if ($this->campaign->world) {
            $lines[] = __('Monde : :name', ['name' => $this->campaign->world->name]);
        }

        if ($this->campaign->description) {
            $lines[] = $this->short($this->campaign->description, 1000);
        }

        if ($this->session) {
            $lines[] = __('Séance analysée : :label', ['label' => $this->session->label()]);
        }

        return implode("\n", $lines);
    }

    /** @return Collection<int, Entity> */
    public function entities(): Collection
    {
        return $this->campaign->availableEntities()
            ->with(['type', 'campaignStates' => fn ($q) => $q->where('campaign_id', $this->campaign->id)])
            ->latest('updated_at')
            ->limit(self::MAX_ENTITIES)
            ->get()
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    private function entitiesSection(): string
    {
        $lines = ['## '.__('Fiches de la campagne (identifiant, type, nom, statut, résumé)')];

        foreach ($this->entities() as $entity) {
            $status = $entity->campaignStates->first()?->status;
            $line = '- #'.$entity->id.' · '.$entity->type?->name.' · '.$entity->name;
            $line .= $status ? ' ['.$status.']' : '';
            $line .= $entity->summary ? ' : '.$this->short($entity->summary, 200) : '';
            $lines[] = $line;
        }

        return count($lines) > 1 ? implode("\n", $lines) : '';
    }

    /** @return Collection<int, PlayerCharacter> */
    public function characters(): Collection
    {
        return $this->campaign->playerCharacters()->active()
            ->with(['entity', 'player', 'grants' => fn ($q) => $q->where('kind', 'entity')])
            ->get();
    }

    private function charactersSection(): string
    {
        $lines = ['## '.__('Personnages des joueurs (identifiant de personnage, fiche, fiches déjà connues)')];

        foreach ($this->characters() as $character) {
            $known = $character->grants->pluck('entity_id')->filter()->unique()->map(fn ($id) => '#'.$id)->implode(', ');
            $line = '- '.__('personnage :id', ['id' => $character->id]).' · '.$character->entity?->name.' (#'.$character->entity_id.')';
            $line .= $character->player ? ' · '.__('joué par :name', ['name' => $character->player->name]) : '';
            $line .= ' · '.__('connaît : :list', ['list' => $known ?: __('rien encore')]);
            $lines[] = $line;
        }

        return count($lines) > 1 ? implode("\n", $lines) : '';
    }

    /** Scènes de la séance (notes prises dessus, scène en cours), sinon les dernières jouées. */
    private function scenesSection(): string
    {
        $ids = collect();

        if ($this->session) {
            $ids = $this->session->notes()->whereNotNull('scene_id')->pluck('scene_id')->push($this->session->current_scene_id);
        }

        $scenes = $this->campaign->scenes()
            ->where(fn ($q) => $q
                ->whereIn('scenes.id', $ids->filter()->unique()->all())
                ->when(! $this->session, fn ($q) => $q->orWhereIn('scenes.status', [SceneStatus::InProgress->value, SceneStatus::Played->value])))
            ->latest('scenes.updated_at')
            ->limit(self::MAX_SCENES)
            ->get()
            ->sortBy('position');

        if ($scenes->isEmpty()) {
            return '';
        }

        $lines = ['## '.__('Scènes concernées')];

        foreach ($scenes as $scene) {
            /** @var Scene $scene */
            $title = trim(($scene->chapter ? $scene->chapter.' · ' : '').$scene->name);
            $lines[] = '- '.$title.' ('.$scene->status->label().')'.($scene->description ? ' : '.$this->short($this->plain($scene->description), 400) : '');
        }

        return implode("\n", $lines);
    }

    private function notesSection(): string
    {
        $parts = [];

        if ($this->session) {
            $notes = $this->session->notes()->with('scene')->oldest()->get();

            if ($notes->isNotEmpty()) {
                $parts[] = '## '.__('Notes du MJ pendant la séance')."\n".$notes
                    ->map(fn ($note) => '- '.($note->scene ? '['.$note->scene->name.'] ' : '').$this->plain($note->body))
                    ->implode("\n");
            }

            $playerNotes = CharacterNote::query()
                ->visibleTo($this->gameMaster, $this->campaign)
                ->where('play_session_id', $this->session->id)
                ->with('character.entity')
                ->oldest()
                ->get();

            if ($playerNotes->isNotEmpty()) {
                $parts[] = '## '.__('Notes des joueurs sur la séance')."\n".$playerNotes
                    ->map(fn (CharacterNote $note) => '- '.$note->character?->entity?->name.' : '.$this->plain($note->body))
                    ->implode("\n");
            }
        }

        if (trim($this->extraNotes) !== '') {
            $parts[] = '## '.__('Notes ajoutées par le MJ')."\n".trim($this->extraNotes);
        }

        if ($parts === []) {
            return '## '.__('Notes de séance')."\n".__('(aucune note : propose seulement ce que le contexte permet d’affirmer, sinon renvoie des listes vides)');
        }

        return Str::limit(implode("\n\n", $parts), self::MAX_NOTES, ' […]');
    }

    private function instructions(): string
    {
        $language = Locale::available()[app()->getLocale()] ?? app()->getLocale();

        $schema = <<<'JSON'
        {
          "summary": "…",
          "events": [{"title": "…", "description": "…", "public": true}],
          "relations": [{"from": 12, "to": 34, "label": "…", "reverse_label": "…", "public": false}],
          "statuses": [{"entity": 12, "status": "…"}],
          "notes": [{"entity": 12, "text": "…"}],
          "reveals": [{"entity": 34, "characters": [5]}]
        }
        JSON;

        return implode("\n", [
            '## '.__('Ce que tu dois répondre'),
            __('Réponds uniquement par un objet JSON de cette forme, sans texte autour :'),
            $schema,
            '- summary : '.__('résumé de la séance en quelques paragraphes.'),
            '- events : '.__('les événements marquants joués, dans l’ordre.'),
            '- relations : '.__('les nouvelles relations entre deux fiches (de « from » vers « to »), avec leur libellé inverse si utile.'),
            '- statuses : '.__('le nouvel état d’une fiche dans la campagne, en quelques mots (60 caractères au plus) : « blessé », « disparu », « allié »…'),
            '- notes : '.__('une information à ajouter aux notes de campagne d’une fiche.'),
            '- reveals : '.__('les fiches que des personnages ont découvertes en jeu et ne connaissent pas encore.'),
            __('Règles :'),
            '- '.__('Appuie-toi seulement sur les notes et le contexte ci-dessus ; n’invente rien.'),
            '- '.__('Utilise uniquement les identifiants de fiche (#) et de personnage donnés plus haut, sans le #. Une fiche qui n’existe pas encore : mentionne-la dans le résumé, sans rien proposer d’autre.'),
            '- '.__('« public » vaut true seulement pour ce que les joueurs ont vu ou appris en jeu ; un secret du MJ reste à false.'),
            '- '.__('Une liste sans proposition reste vide : [].'),
            '- '.__('Écris tous les textes en :language.', ['language' => $language]),
        ]);
    }

    private function short(?string $text, int $length): string
    {
        return Str::limit($this->plain((string) $text), $length, '…');
    }

    /** Texte sur une ligne, sans balises ; les liens [[Nom|id]] deviennent « Nom (#id) ». */
    private function plain(?string $text): string
    {
        $text = preg_replace('/\[\[([^\]|]+)\|(\d+)\]\]/u', '$1 (#$2)', (string) $text);
        $text = preg_replace('/\[\[([^\]]+)\]\]/u', '$1', $text);

        return trim(preg_replace('/\s+/u', ' ', strip_tags($text)));
    }
}
