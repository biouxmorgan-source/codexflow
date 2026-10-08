<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
        › <a href="{{ route('rules.index', $campaign) }}" class="crumb" wire:navigate>{{ __('Règles') }}</a>
    </nav>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            @if ($rule->category)
                <p class="text-xs font-semibold tracking-wide text-stone-500 uppercase">{{ $rule->category }}</p>
            @endif
            <h1 class="text-2xl font-semibold">{{ $rule->title }}</h1>
            <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                <label for="status" class="sr-only">{{ __('Statut') }}</label>
                <select id="status" wire:change="setStatus($event.target.value)" class="rounded-full border-0 py-1 pr-8 pl-3 text-sm font-medium {{ $rule->status->badge() }}">
                    @foreach (\App\Enums\RuleStatus::cases() as $ruleStatus)
                        <option value="{{ $ruleStatus->value }}" @selected($rule->status === $ruleStatus)>{{ $ruleStatus->label() }}</option>
                    @endforeach
                </select>
                <span class="text-stone-500">{{ $rule->origin->label() }} · {{ $rule->isShared() ? __('Règle du jeu :name', ['name' => $campaign->gameSystem->name]) : __('Propre à cette campagne') }} · {{ $rule->zone === \App\Enums\Zone::Public ? __("Consultable par les personnages à qui vous l'ouvrez") : __('MJ seulement') }}</span>
            </div>
            @if ($rule->tags->isNotEmpty())
                <p class="mt-2 flex flex-wrap gap-1 text-xs">
                    @foreach ($rule->tags as $tag)
                        <x-tag :tag="$tag" :href="route('rules.index', [$campaign, 'tag' => $tag->name])" />
                    @endforeach
                </p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            <livewire:table.show-button :campaign="$campaign" kind="rule" :item-id="$rule->id" wire:key="table-rule" />
            @if ($pendingToPlay)
                <span class="btn-secondary cursor-default text-stone-500">{{ __('Dans « À jouer »') }}</span>
            @else
                <button type="button" wire:click="addToPlay" class="btn-secondary">{{ __('Ajouter à « À jouer »') }}</button>
            @endif
            <a href="{{ route('journal.index', [$campaign, 'sujet' => 'rule:'.$rule->id]) }}" class="btn-secondary" wire:navigate>{{ __('Historique') }}</a>
            <a href="{{ route('rules.edit', [$campaign, $rule]) }}" class="btn-secondary" wire:navigate>{{ __('Modifier') }}</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                @if ($rule->summary)
                    <p class="mb-4 font-medium text-stone-800">{{ $rule->summary }}</p>
                @endif
                <h2 class="mb-2 font-semibold">{{ __('Procédure') }}</h2>
                @if ($rule->procedure)
                    <div class="text-stone-700">{{ $procedure }}</div>
                @else
                    <p class="text-sm text-stone-500">{{ __('Pas encore de procédure.') }}</p>
                @endif
                @if ($rule->source)
                    <p class="mt-4 text-sm text-stone-500">{{ __('Source : :source', ['source' => $rule->source]) }}</p>
                @endif
            </section>

            @if ($rule->gm_notes)
                <section class="rounded-xl border border-flow/30 bg-white p-6 shadow-sm">
                    <h2 class="mb-2 font-semibold text-flow">{{ __('Notes MJ') }}</h2>
                    <div class="text-stone-700">{{ \App\Support\EntityLinks::render($rule->gm_notes, $campaign) }}</div>
                </section>
            @endif
        </div>

        <aside class="space-y-6">
            @if ($rule->zone === \App\Enums\Zone::Public)
                <livewire:characters.give :campaign="$campaign" fixed-kind="rule" :rule-id="$rule->id" :key="'give-rule-'.$rule->id" />
            @else
                <p class="rounded-xl border border-dashed border-stone-300 bg-white p-4 text-sm text-stone-600">{{ __("Règle réservée au MJ : passez-la en « Consultable par les joueurs » pour pouvoir l'ouvrir à certains personnages.") }}</p>
            @endif

            <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-3 font-semibold">{{ __('Documents') }}</h2>
                <x-document-list :documents="$documents" :campaign="$campaign" unlink="unlinkDocument" />
                @if ($documentOptions->isNotEmpty())
                    <div class="mt-3 flex gap-2">
                        <label for="pickedDocumentId" class="sr-only">{{ __('Lier un document') }}</label>
                        <select id="pickedDocumentId" wire:model="pickedDocumentId" class="field min-w-0 flex-1 py-1.5 text-sm">
                            <option value="">{{ __('Lier un document…') }}</option>
                            @foreach ($documentOptions as $option)
                                <option value="{{ $option->id }}">{{ $option->title }}</option>
                            @endforeach
                        </select>
                        <button type="button" wire:click="linkDocument" class="btn-secondary min-h-0 py-1 text-sm">{{ __('Lier') }}</button>
                    </div>
                    @error('pickedDocumentId') <p class="error">{{ $message }}</p> @enderror
                @endif
            </section>

            <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-3 font-semibold">{{ __('Scènes') }}</h2>
                @forelse ($scenes as $scene)
                    <a href="{{ route('scenes.show', [$campaign, $scene]) }}" class="block py-1 text-sm link" wire:navigate>{{ $scene->name }} <span class="text-stone-500">· {{ $scene->scenario->name }}</span></a>
                @empty
                    <p class="text-sm text-stone-500">{{ __("Liez la règle à une scène depuis la scène : elle s'affichera en mode Session.") }}</p>
                @endforelse
            </section>

            <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-2 font-semibold">{{ __('Supprimer') }}</h2>
                <p class="mb-3 text-sm text-stone-600">{{ $rule->isShared() ? __('La règle disparaîtra de toutes les campagnes du jeu.') : __('Les documents liés sont conservés.') }}</p>
                <button type="button" wire:click="delete" wire:confirm="{{ __('Supprimer la règle :title ?', ['title' => $rule->title]) }}" class="text-sm font-medium text-red-700 hover:underline">{{ __('Supprimer la règle') }}</button>
            </div>
        </aside>
    </div>
</div>
