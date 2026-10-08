<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <div class="mb-6">
        <h1 class="text-2xl font-semibold">{{ __('Assistant IA') }}</h1>
        <p class="mt-1 max-w-3xl text-sm text-stone-600">{{ __('L’IA propose, vous décidez. LoreMundi prépare un texte avec le contexte de la séance ; collez-le dans l’IA de votre choix (ChatGPT, Claude, Le Chat, Gemini…), puis collez sa réponse ici. Chaque proposition s’accepte, se modifie ou se rejette : rien ne change dans la campagne sans vous.') }}</p>
    </div>

    @if (session('status'))
        <p class="mb-6 rounded-md bg-codex-soft px-3 py-2 text-sm text-codex" role="status">{{ session('status') }}</p>
    @endif

    <div class="mb-8 grid gap-6 lg:grid-cols-2">
        <section class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="font-semibold">{{ __('1. Copier le texte pour l’IA') }}</h2>
            <div>
                <label for="ai-session" class="label">{{ __('Séance à analyser') }}</label>
                <select id="ai-session" wire:model.live="sessionId" class="field">
                    <option value="">{{ __('Aucune séance : seulement les notes ci-dessous') }}</option>
                    @foreach ($this->sessions as $session)
                        <option value="{{ $session->id }}">{{ $session->label() }}{{ $session->isOpen() ? ' · '.__('en cours') : '' }} · {{ trans_choice(':count note|:count notes', $session->notes_count) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="ai-extra" class="label">{{ __('Notes à ajouter') }} <span class="font-normal text-stone-500">{{ __('(facultatif : notes prises ailleurs, ce dont vous vous souvenez)') }}</span></label>
                <textarea id="ai-extra" wire:model.live.debounce.600ms="extraNotes" rows="4" class="field" maxlength="20000"></textarea>
            </div>
            <div x-data="{ copied: false }">
                <label for="ai-prompt" class="label">{{ __('Texte à coller dans l’IA') }}</label>
                <textarea id="ai-prompt" x-ref="prompt" readonly rows="10" class="field font-mono text-xs">{{ $this->prompt }}</textarea>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <button type="button" class="btn-primary" x-on:click="navigator.clipboard.writeText($refs.prompt.value).then(() => { copied = true; setTimeout(() => copied = false, 2500) }, () => { $refs.prompt.select() })">{{ __('Copier le texte') }}</button>
                    <span x-show="copied" x-cloak class="text-sm text-emerald-700" role="status">{{ __('Copié !') }}</span>
                </div>
                <p class="mt-2 text-xs text-stone-500">
                    @if ($this->directProvider)
                        {{ __('Ce texte contient vos notes MJ et des secrets de la campagne : collez-le seulement dans une IA en laquelle vous avez confiance. LoreMundi ne l’envoie qu’à votre demande, à l’IA de votre clé.') }}
                    @else
                        {{ __('Ce texte contient vos notes MJ et des secrets de la campagne : collez-le seulement dans une IA en laquelle vous avez confiance. LoreMundi n’envoie rien lui-même.') }}
                    @endif
                </p>
            </div>
            <div class="border-t border-stone-200 pt-4">
                @if ($this->directProvider)
                    <button type="button" wire:click="analyseDirectly" wire:loading.attr="disabled" wire:target="analyseDirectly" class="btn-secondary">
                        <span wire:loading.remove wire:target="analyseDirectly">{{ __('Analyser directement avec :provider', ['provider' => $this->directProvider]) }}</span>
                        <span wire:loading wire:target="analyseDirectly">{{ __('Analyse en cours… (jusqu’à quelques minutes)') }}</span>
                    </button>
                    <p class="mt-1 text-xs text-stone-500">{{ __('Envoie ce texte avec votre clé, à vos frais, sans copier-coller. Les propositions arrivent plus bas.') }}</p>
                    @error('direct') <p class="error" role="alert">{{ $message }}</p> @enderror
                @else
                    <p class="text-xs text-stone-500">{!! __('Vous avez une clé d’API Claude, ChatGPT ou Mistral ? Enregistrez-la dans :link pour analyser sans copier-coller.', ['link' => '<a href="'.route('preferences').'" class="link" wire:navigate>'.e(__('vos préférences')).'</a>']) !!}</p>
                @endif
            </div>
        </section>

        <form wire:submit="analyse" class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="font-semibold">{{ __('2. Coller la réponse de l’IA') }}</h2>
            <div>
                <label for="ai-response" class="label">{{ __('Réponse de l’IA') }}</label>
                <textarea id="ai-response" wire:model="response" rows="14" class="field font-mono text-xs" placeholder="{ &quot;summary&quot;: … }"></textarea>
                @error('response') <p class="error">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="analyse">{{ __('Lire les propositions') }}</button>
        </form>
    </div>

    <section>
        <h2 class="mb-3 text-lg font-semibold">{{ __('3. Examiner les propositions') }}</h2>

        @forelse ($this->analyses as $analysis)
            @php($pending = $analysis->suggestions->where('status', 'pending'))
            <article wire:key="analysis-{{ $analysis->id }}" class="mb-6 rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
                <header class="mb-4 flex flex-wrap items-center gap-3">
                    <h3 class="mr-auto font-medium">
                        {{ $analysis->playSession?->label() ?? __('Notes ajoutées à la main') }}
                        <span class="text-sm font-normal text-stone-500">· {{ $analysis->created_at->isoFormat('LLL') }}</span>
                    </h3>
                    <span class="text-sm text-stone-500">
                        {{ trans_choice(':count acceptée|:count acceptées', $analysis->suggestions->where('status', 'accepted')->count()) }},
                        {{ trans_choice(':count rejetée|:count rejetées', $analysis->suggestions->where('status', 'rejected')->count()) }},
                        {{ trans_choice(':count en attente|:count en attente', $pending->count()) }}
                    </span>
                    @if ($pending->count() > 1)
                        <button type="button" wire:click="acceptAll({{ $analysis->id }})" wire:confirm="{{ __('Accepter toutes les propositions en attente, avec vos modifications ?') }}" class="btn-secondary">{{ __('Tout accepter') }}</button>
                    @endif
                    <button type="button" wire:click="deleteAnalysis({{ $analysis->id }})" wire:confirm="{{ __('Effacer cette analyse ? Ce qui a déjà été accepté reste dans la campagne.') }}" class="text-sm link">{{ __('Effacer') }}</button>
                </header>

                @if ($pending->isEmpty())
                    <p class="text-sm text-stone-500">{{ __('Tout a été examiné.') }}</p>
                @endif

                <ul class="space-y-3">
                    @foreach ($pending as $suggestion)
                        @php($id = $suggestion->id)
                        @php($draft = $drafts[$id] ?? $suggestion->payload)
                        <li wire:key="suggestion-{{ $id }}" class="rounded-lg border border-flow/30 p-4">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-flow">{{ $kinds[$suggestion->kind] ?? $suggestion->kind }}</p>

                            @switch($suggestion->kind)
                                @case('summary')
                                    <label for="draft-{{ $id }}-text" class="sr-only">{{ __('Résumé') }}</label>
                                    <textarea id="draft-{{ $id }}-text" wire:model="drafts.{{ $id }}.text" rows="5" class="field"></textarea>
                                    <p class="mt-1 text-xs text-stone-500">{{ __('Accepté, il devient un événement joué de la chronologie.') }}</p>
                                    @break
                                @case('event')
                                    <label for="draft-{{ $id }}-title" class="sr-only">{{ __('Événement') }}</label>
                                    <input id="draft-{{ $id }}-title" type="text" wire:model="drafts.{{ $id }}.title" class="field mb-2" maxlength="255">
                                    <label for="draft-{{ $id }}-description" class="sr-only">{{ __('Détails') }}</label>
                                    <textarea id="draft-{{ $id }}-description" wire:model="drafts.{{ $id }}.description" rows="2" class="field"></textarea>
                                    @break
                                @case('relation')
                                    <div class="flex flex-wrap items-center gap-2 text-sm">
                                        <span class="font-medium">{{ $this->names['entities'][$draft['from']] ?? '?' }}</span>
                                        <label for="draft-{{ $id }}-label" class="sr-only">{{ __('Relation') }}</label>
                                        <input id="draft-{{ $id }}-label" type="text" wire:model="drafts.{{ $id }}.label" class="field w-48" maxlength="100">
                                        <span class="font-medium">{{ $this->names['entities'][$draft['to']] ?? '?' }}</span>
                                    </div>
                                    <div class="mt-2 flex items-center gap-2 text-sm">
                                        <label for="draft-{{ $id }}-reverse" class="text-stone-600">{{ __('Relation inverse') }}</label>
                                        <input id="draft-{{ $id }}-reverse" type="text" wire:model="drafts.{{ $id }}.reverse_label" class="field w-48" maxlength="100">
                                    </div>
                                    <p class="mt-1 text-xs text-stone-500">{{ __('Ajoutée à cette campagne seulement, pas au monde partagé.') }}</p>
                                    @break
                                @case('status')
                                    <div class="flex flex-wrap items-center gap-2 text-sm">
                                        <span class="font-medium">{{ $this->names['entities'][$draft['entity']] ?? '?' }}</span>
                                        <label for="draft-{{ $id }}-status" class="sr-only">{{ __('Statut') }}</label>
                                        <input id="draft-{{ $id }}-status" type="text" wire:model="drafts.{{ $id }}.status" class="field w-56" maxlength="60">
                                    </div>
                                    @break
                                @case('note')
                                    <p class="mb-1 text-sm font-medium">{{ $this->names['entities'][$draft['entity']] ?? '?' }}</p>
                                    <label for="draft-{{ $id }}-note" class="sr-only">{{ __('Note de campagne') }}</label>
                                    <textarea id="draft-{{ $id }}-note" wire:model="drafts.{{ $id }}.text" rows="2" class="field"></textarea>
                                    <p class="mt-1 text-xs text-stone-500">{{ __('Ajoutée aux notes de campagne de la fiche (zone MJ).') }}</p>
                                    @break
                                @case('reveal')
                                    <p class="text-sm">{!! __('Révéler :entity à :', ['entity' => '<span class="font-medium">'.e($this->names['entities'][$draft['entity']] ?? '?').'</span>']) !!}</p>
                                    <div class="mt-1 flex flex-wrap gap-4 text-sm">
                                        @foreach ($this->names['characters'] as $characterId => $characterName)
                                            <label class="flex items-center gap-2">
                                                <input type="checkbox" wire:model="drafts.{{ $id }}.characters" value="{{ $characterId }}">
                                                {{ $characterName }}
                                            </label>
                                        @endforeach
                                    </div>
                                    @break
                            @endswitch

                            @error("drafts.$id") <p class="error">{{ $message }}</p> @enderror

                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                @if (in_array($suggestion->kind, ['summary', 'event', 'relation'], true))
                                    <label class="mr-auto flex items-center gap-2 text-sm">
                                        <input type="checkbox" wire:model="drafts.{{ $id }}.public">
                                        {{ __('Visible des joueurs') }}
                                    </label>
                                @else
                                    <span class="mr-auto"></span>
                                @endif
                                <button type="button" wire:click="reject({{ $id }})" class="btn-secondary">{{ __('Rejeter') }}</button>
                                <button type="button" wire:click="accept({{ $id }})" class="btn-primary">{{ __('Accepter') }}</button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </article>
        @empty
            <p class="rounded-xl border border-dashed border-stone-300 bg-white p-6 text-sm text-stone-600">{{ __('Aucune proposition pour l’instant. Copiez le texte, passez-le à votre IA, puis collez sa réponse.') }}</p>
        @endforelse
    </section>
</div>
