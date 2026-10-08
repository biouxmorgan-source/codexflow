<?php

namespace App\Livewire\Ai;

use App\Actions\Ai\ApplySuggestion;
use App\Models\AiAnalysis;
use App\Models\AiSuggestion;
use App\Models\Campaign;
use App\Models\PlaySession;
use App\Support\Ai\AiProviders;
use App\Support\Ai\Clients\AiRequestFailed;
use App\Support\Ai\SessionPrompt;
use App\Support\Ai\SuggestionParser;
use App\Support\Ai\UnreadableResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Assistant IA, mode « texte à coller » : l'application prépare un texte avec le contexte de
 * la séance, le MJ le colle dans l'IA de son choix, puis colle la réponse ici. Chaque proposition
 * s'accepte, se modifie ou se rejette : rien ne change dans la campagne sans le MJ.
 */
class Index extends Component
{
    public Campaign $campaign;

    /** Séance analysée ; vide pour s'en tenir aux notes ajoutées à la main. */
    public string $sessionId = '';

    public string $extraNotes = '';

    public string $response = '';

    /** Propositions en attente, modifiables avant d'être acceptées : [id => champs]. */
    public array $drafts = [];

    public function mount(Campaign $campaign): void
    {
        $this->authorize('update', $campaign);

        $session = $campaign->openSession() ?? $campaign->playSessions()->latest('number')->first();
        $this->sessionId = (string) $session?->id;
        $this->loadDrafts();
    }

    /** @return Collection<int, PlaySession> */
    #[Computed]
    public function sessions(): Collection
    {
        return $this->campaign->playSessions()->withCount('notes')->orderByDesc('number')->get();
    }

    #[Computed]
    public function session(): ?PlaySession
    {
        return $this->sessionId === '' ? null : $this->sessions->firstWhere('id', (int) $this->sessionId);
    }

    #[Computed]
    public function prompt(): string
    {
        return (new SessionPrompt($this->campaign, auth()->user(), $this->session, $this->extraNotes))->build();
    }

    /** @return Collection<int, AiAnalysis> */
    #[Computed]
    public function analyses(): Collection
    {
        return $this->campaign->aiAnalyses()
            ->with(['playSession', 'suggestions' => fn ($q) => $q->orderBy('id')])
            ->latest('id')
            ->limit(10)
            ->get();
    }

    /** Noms des fiches et des personnages, pour afficher les propositions. */
    #[Computed]
    public function names(): array
    {
        return [
            'entities' => $this->campaign->availableEntities()->pluck('name', 'id')->all(),
            'characters' => $this->campaign->playerCharacters()->active()->with('entity')->get()->mapWithKeys(fn ($c) => [$c->id => $c->entity?->name])->all(),
        ];
    }

    /** Fournisseur de la clé personnelle du MJ, ou null : seul le mode « texte à coller » est proposé. */
    #[Computed]
    public function directProvider(): ?string
    {
        return auth()->user()->hasAiKey() ? AiProviders::name(auth()->user()->ai_provider) : null;
    }

    /** Envoie le texte à l'IA du MJ, avec sa clé et à ses frais, puis lit la réponse comme si elle avait été collée. */
    public function analyseDirectly(): void
    {
        $this->authorize('update', $this->campaign);
        $this->resetValidation();
        $this->validate(['sessionId' => ['nullable', Rule::in($this->sessions->modelKeys())]]);

        $client = AiProviders::clientFor(auth()->user());

        if ($client === null) {
            $this->addError('direct', __('Enregistrez d’abord votre clé d’API dans vos préférences.'));

            return;
        }

        // Chaque appel coûte au MJ : un double clic ou une boucle ne doit pas vider son crédit.
        $key = 'ai-direct:'.auth()->id();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('direct', __('Trop d’analyses à la suite : patientez une minute.'));

            return;
        }

        RateLimiter::hit($key, 60);
        @set_time_limit(240);

        try {
            $this->response = $client->complete($this->prompt);
        } catch (AiRequestFailed $e) {
            $this->addError('direct', $e->getMessage());

            return;
        }

        $this->analyse();
    }

    public function analyse(): void
    {
        $this->authorize('update', $this->campaign);

        $this->validate([
            'sessionId' => ['nullable', Rule::in($this->sessions->modelKeys())],
            'response' => ['required', 'string', 'max:200000'],
        ], attributes: ['response' => __('réponse de l’IA')]);

        $parser = new SuggestionParser($this->campaign);

        try {
            $suggestions = $parser->parse($this->response);
        } catch (UnreadableResponse $e) {
            $this->addError('response', $e->getMessage());

            return;
        }

        if ($suggestions === []) {
            $this->addError('response', __('Aucune proposition utilisable dans cette réponse.'));

            return;
        }

        DB::transaction(function () use ($suggestions) {
            $analysis = new AiAnalysis(['response' => $this->response]);
            $analysis->campaign()->associate($this->campaign);
            $analysis->author()->associate(auth()->user());
            $analysis->play_session_id = $this->session?->id;
            $analysis->save();
            $analysis->suggestions()->createMany($suggestions);
        });

        session()->now('status', $parser->ignored > 0
            ? trans_choice(':count proposition à examiner, :ignored ignorée (fiche inconnue ou texte vide).|:count propositions à examiner, :ignored ignorée(s) (fiche inconnue ou texte vide).', count($suggestions), ['ignored' => $parser->ignored])
            : trans_choice(':count proposition à examiner.|:count propositions à examiner.', count($suggestions)));

        $this->reset('response');
        $this->refresh();
    }

    public function accept(int $id, ApplySuggestion $apply): void
    {
        $this->resetValidation();
        $suggestion = $this->pending($id);
        $apply->handle($suggestion, auth()->user(), $this->drafts[$id] ?? []);
        $this->refresh();
    }

    public function reject(int $id): void
    {
        $this->pending($id)->update(['status' => 'rejected']);
        $this->refresh();
    }

    /** Accepte telles quelles (ou telles que modifiées) toutes les propositions d'une analyse. */
    public function acceptAll(int $analysisId, ApplySuggestion $apply): void
    {
        $this->authorize('update', $this->campaign);
        $this->resetValidation();

        $analysis = $this->campaign->aiAnalyses()->findOrFail($analysisId);
        $failed = 0;

        foreach ($analysis->suggestions()->where('status', 'pending')->orderBy('id')->get() as $suggestion) {
            try {
                $apply->handle($suggestion, auth()->user(), $this->drafts[$suggestion->id] ?? []);
            } catch (ValidationException $e) {
                $this->setErrorBag($this->getErrorBag()->merge($e->errors()));
                $failed++;
            }
        }

        if ($failed > 0) {
            session()->now('status', trans_choice(':count proposition n’a pas pu être appliquée : corrigez-la ou rejetez-la.|:count propositions n’ont pas pu être appliquées : corrigez-les ou rejetez-les.', $failed));
        }

        $this->refresh();
    }

    public function deleteAnalysis(int $analysisId): void
    {
        $this->authorize('update', $this->campaign);

        $this->campaign->aiAnalyses()->findOrFail($analysisId)->delete();
        $this->refresh();
    }

    private function pending(int $id): AiSuggestion
    {
        $this->authorize('update', $this->campaign);

        return AiSuggestion::query()
            ->whereHas('analysis', fn ($q) => $q->where('campaign_id', $this->campaign->id))
            ->where('status', 'pending')
            ->findOrFail($id);
    }

    private function refresh(): void
    {
        unset($this->analyses);
        $this->loadDrafts();
    }

    /** Recopie les propositions en attente dans le formulaire, sans écraser ce que le MJ modifie. */
    private function loadDrafts(): void
    {
        $pending = $this->analyses->flatMap->suggestions->where('status', 'pending');
        $drafts = [];

        foreach ($pending as $suggestion) {
            $payload = $this->drafts[$suggestion->id] ?? $suggestion->payload;

            if ($suggestion->kind === 'reveal') {
                $payload['characters'] = array_map('strval', (array) $payload['characters']);
            }

            $drafts[$suggestion->id] = $payload;
        }

        $this->drafts = $drafts;
    }

    public function render()
    {
        return view('livewire.ai.index', [
            'kinds' => AiSuggestion::kinds(),
        ])->title(__('Assistant IA · :name', ['name' => $this->campaign->name]));
    }
}
