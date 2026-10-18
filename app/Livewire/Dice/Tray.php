<?php

namespace App\Livewire\Dice;

use App\Models\Campaign;
use App\Models\DiceRequest;
use App\Models\DiceRoll;
use App\Models\PlayerCharacter;
use App\Support\CampaignFeatures;
use App\Support\Dice\DiceFormula;
use App\Support\Dice\SheetValues;
use App\Support\Dice\TableDice;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Plateau de dés, ancré en bas de chaque page d'une campagne : lancer, relire les derniers
 * lancers, et pour le MJ demander un lancer à toute la table ou à certains personnages.
 * Une demande qui attend le joueur s'ouvre d'elle-même, où qu'il soit.
 */
class Tray extends Component
{
    #[Locked]
    public Campaign $campaign;

    public bool $open = false;

    /** Onglet : roll, history ou ask (MJ). */
    public string $tab = 'roll';

    /** Personnage au nom duquel le joueur lance. */
    public ?int $characterId = null;

    /** Dernier lancer de cette personne, montré en grand (animé) dans le plateau. */
    #[Locked]
    public ?int $lastRollId = null;

    /** Réponse à une demande : la demande, le personnage, le seuil et le bonus saisis. */
    public ?int $answerRequestId = null;

    public ?int $answerCharacterId = null;

    public string $answerThreshold = '';

    public string $answerBonus = '';

    /** Lancer qui vient de répondre à la demande, affiché dans la fenêtre avant de la fermer. */
    #[Locked]
    public ?int $answeredRollId = null;

    public function mount(Campaign $campaign): void
    {
        $this->authorize('play', $campaign);

        $saved = session('dice.'.$campaign->id, []);
        $this->open = (bool) ($saved['open'] ?? false);
        $this->tab = in_array($saved['tab'] ?? null, $this->tabs(), true) ? $saved['tab'] : 'roll';
        $this->characterId = $this->characters->first()?->id;
    }

    /** @return array<string, string> mises à jour en direct (Reverb) */
    public function getListeners(): array
    {
        return ['echo-private:users.'.auth()->id().',.activity' => '$refresh'];
    }

    #[Computed]
    public function isGameMaster(): bool
    {
        return $this->campaign->isGameMaster(auth()->user());
    }

    #[Computed]
    public function characters(): Collection
    {
        return TableDice::charactersOf(auth()->user(), $this->campaign);
    }

    /** @return list<string> */
    private function tabs(): array
    {
        return $this->isGameMaster ? ['roll', 'ask', 'history'] : ['roll', 'history'];
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
        $this->remember();
    }

    public function select(string $tab): void
    {
        abort_unless(in_array($tab, $this->tabs(), true), 404);
        $this->tab = $tab;
        $this->open = true;
        $this->resetValidation();
        $this->remember();
    }

    /** @param array<string, mixed> $formula */
    public function roll(array $formula, string $visibility = TableDice::TABLE, string $label = ''): void
    {
        $character = $this->isGameMaster ? null : $this->myCharacter($this->characterId);
        $roll = TableDice::roll($this->campaign, auth()->user(), DiceFormula::fromArray($formula), $visibility, $label, $character);
        $this->lastRollId = $roll->id;
    }

    /**
     * Enregistre le lancer composé, pour soi (mine) ou pour toute la table (MJ) ; avec $id, met à jour ce lancer.
     *
     * @param  array<string, mixed>  $formula
     */
    public function savePreset(array $formula, string $label, string $scope = 'mine', ?string $id = null): ?string
    {
        if ($scope === 'table') {
            $this->authorize('update', $this->campaign);
        }

        if (trim($label) === '') {
            $this->addError('presetLabel', __('Donnez un nom au lancer.'));

            return null;
        }
        $this->resetErrorBag('presetLabel');

        return TableDice::savePreset($this->campaign->refresh(), $label, DiceFormula::fromArray($formula), $scope === 'table' ? null : auth()->user(), $id);
    }

    public function removePreset(string $id, string $scope = 'mine'): void
    {
        if ($scope === 'table') {
            $this->authorize('update', $this->campaign);
        }

        TableDice::removePreset($this->campaign->refresh(), $id, $scope === 'table' ? null : auth()->user());
    }

    /**
     * @param  array<string, mixed>  $formula
     * @param  list<int>|null  $targets  null : toute la table
     */
    public function ask(array $formula, string $label, ?array $targets, string $visibility = TableDice::TABLE): void
    {
        $this->authorize('update', $this->campaign);
        TableDice::request($this->campaign, auth()->user(), $label, DiceFormula::fromArray($formula), $targets, $visibility);
    }

    public function closeRequest(int $id): void
    {
        $this->authorize('update', $this->campaign);
        TableDice::close($this->campaign->diceRequests()->findOrFail($id));
    }

    /** Le joueur lance pour une demande du MJ. */
    public function answer(): void
    {
        $this->resetErrorBag();
        $pending = $this->pending->first(fn (array $item) => $item['request']->id === $this->answerRequestId && $item['character']->id === $this->answerCharacterId);
        abort_if($pending === null, 404);

        $request = $pending['request'];
        $threshold = trim($this->answerThreshold);
        $bonus = trim($this->answerBonus);

        $formula = SheetValues::apply($request->formula(), $this->campaign, $pending['character']);
        if ($formula->needsThreshold() && $formula->threshold === null && ! is_numeric($threshold)) {
            $this->addError('answerThreshold', __('Indiquez le seuil.'));

            return;
        }
        if ($bonus !== '' && ! is_numeric($bonus)) {
            $this->addError('answerBonus', __('Le bonus est un nombre, par exemple +2 ou -1.'));

            return;
        }

        $roll = TableDice::answer($request, auth()->user(), $pending['character'], is_numeric($threshold) ? (int) $threshold : null, (int) $bonus);
        $this->answeredRollId = $roll->id;
        $this->reset('answerThreshold', 'answerBonus');
        unset($this->pending);
    }

    public function dismissAnswer(): void
    {
        $this->answeredRollId = null;
    }

    /** Demandes qui attendent un lancer de cette personne. */
    #[Computed]
    public function pending(): Collection
    {
        return $this->isGameMaster ? collect() : TableDice::pendingFor(auth()->user(), $this->campaign);
    }

    private function myCharacter(?int $id): ?PlayerCharacter
    {
        if ($this->characters->isEmpty()) {
            return null;
        }

        return $this->characters->firstWhere('id', $id) ?? abort(403);
    }

    private function remember(): void
    {
        session(['dice.'.$this->campaign->id => ['open' => $this->open, 'tab' => $this->tab]]);
    }

    private function presentRoll(?int $id): ?array
    {
        $roll = $id === null ? null : DiceRoll::query()->with(['character.entity', 'user', 'campaign'])->where('campaign_id', $this->campaign->id)->find($id);

        return $roll !== null && TableDice::canSee($roll, auth()->user()) ? TableDice::present($roll, $this->isGameMaster) : null;
    }

    /** Demandes du MJ : ouvertes, puis les dernières closes, avec qui a lancé quoi. */
    private function requests(): Collection
    {
        $characters = TableDice::playedCharacters($this->campaign)->keyBy('id');

        return $this->campaign->diceRequests()
            ->where(fn ($q) => $q->whereNull('closed_at')->orWhere('closed_at', '>=', now()->subHour()))
            ->with(['rolls.character.entity', 'rolls.user', 'rolls.campaign'])
            ->latest('id')->limit(8)->get()
            ->map(fn (DiceRequest $request) => [
                'request' => $request,
                'describe' => $request->formula()->describe(),
                'targets' => collect($request->characterIds())->map(fn (int $id) => [
                    'name' => $characters->get($id)?->entity?->name ?? __('Personnage retiré'),
                    'roll' => ($roll = $request->rolls->firstWhere('player_character_id', $id)) ? TableDice::present($roll, true) : null,
                ]),
            ]);
    }

    public function render()
    {
        $this->campaign->refresh();
        $usable = CampaignFeatures::usable($this->campaign, 'dice');
        $first = $usable ? $this->pending->first() : null;

        // Une demande ouverte depuis la fenêtre : on vise la première en attente.
        if ($first !== null && ($this->answerRequestId !== $first['request']->id || $this->answerCharacterId !== $first['character']->id)) {
            $this->answerRequestId = $first['request']->id;
            $this->answerCharacterId = $first['character']->id;
        }

        return view('livewire.dice.tray', [
            'usable' => $usable,
            'presets' => TableDice::presets($this->campaign),
            'mine' => TableDice::presets($this->campaign, auth()->user()),
            // Les champs de la fiche ne s'écrivent que dans le plateau ouvert.
            'sheetFields' => $this->open ? SheetValues::choices($this->campaign) : [],
            'last' => $this->presentRoll($this->lastRollId),
            'answered' => $this->presentRoll($this->answeredRollId),
            'prompt' => $first,
            'history' => $this->open && $this->tab === 'history'
                ? TableDice::visibleRolls($this->campaign, auth()->user())->each->setRelation('campaign', $this->campaign)->map(fn (DiceRoll $roll) => TableDice::present($roll, $this->isGameMaster))
                : collect(),
            'requests' => $this->isGameMaster && $this->open && $this->tab === 'ask' ? $this->requests() : collect(),
            'played' => $this->isGameMaster ? TableDice::playedCharacters($this->campaign) : collect(),
        ]);
    }
}
