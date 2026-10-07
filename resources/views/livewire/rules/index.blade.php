<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('Règles et aides de jeu') }}</h1>
            <p class="text-sm text-stone-600">{{ __('Les règles du jeu :name servent à toutes ses campagnes ; les autres restent propres à celle-ci.', ['name' => $campaign->gameSystem->name]) }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('imports.create', [$campaign, 'mode' => 'rules']) }}" class="btn-secondary" wire:navigate>{{ __('Importer') }}</a>
            <a href="{{ route('exports.download', [$campaign, 'regles']) }}" class="btn-secondary">{{ __('Exporter') }}</a>
            <a href="{{ route('rules.create', $campaign) }}" class="btn-primary" wire:navigate>{{ __('Nouvelle règle') }}</a>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <label for="status" class="sr-only">{{ __('Statut') }}</label>
        <select id="status" wire:model.live="status" class="field w-auto py-1.5 text-sm">
            <option value="">{{ __('Tous les statuts') }}</option>
            @foreach (\App\Enums\RuleStatus::cases() as $ruleStatus)
                <option value="{{ $ruleStatus->value }}">{{ $ruleStatus->label() }}</option>
            @endforeach
        </select>
        @foreach ($this->tags as $tagName)
            <button type="button" wire:click="$set('tag', @js($tag === $tagName ? '' : $tagName))"
                @class(['rounded-full px-2.5 py-0.5 text-sm', 'bg-codex text-on-accent' => $tag === $tagName, 'bg-stone-100 text-stone-700 hover:bg-codex-soft' => $tag !== $tagName])>{{ $tagName }}</button>
        @endforeach
    </div>

    @if ($this->rules->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
            @if ($status !== '' || $tag !== '')
                <p class="text-stone-600">{{ __('Aucune règle ne correspond à ce filtre.') }}</p>
            @else
                <p class="text-lg font-medium">{{ __("Aucune règle pour l'instant.") }}</p>
                <p class="mt-1 text-stone-600">{{ __('Notez une règle maison, un point de règle souvent oublié ou une variante à tester.') }}</p>
                <a href="{{ route('rules.create', $campaign) }}" class="btn-primary mt-4" wire:navigate>{{ __('Nouvelle règle') }}</a>
            @endif
        </div>
    @else
        <div class="space-y-6">
            @foreach ($this->rules as $category => $rules)
                <section wire:key="category-{{ md5($category) }}">
                    <h2 class="mb-2 text-sm font-semibold tracking-wide text-stone-500 uppercase">{{ $category }}</h2>
                    <ul class="divide-y divide-stone-100 rounded-xl border border-stone-200 bg-white shadow-sm">
                        @foreach ($rules as $rule)
                            <li wire:key="rule-{{ $rule->id }}">
                                <a href="{{ route('rules.show', [$campaign, $rule]) }}" class="flex flex-wrap items-center gap-x-3 gap-y-1 px-4 py-3 hover:bg-codex-soft" wire:navigate>
                                    <span class="font-medium text-codex">{{ $rule->title }}</span>
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $rule->status->badge() }}">{{ $rule->status->label() }}</span>
                                    <span class="text-xs text-stone-500">{{ $rule->origin->label() }} · {{ $rule->isShared() ? __('Jeu') : __('Campagne') }}</span>
                                    @if ($rule->summary)
                                        <span class="w-full text-sm text-stone-600">{{ \Illuminate\Support\Str::limit($rule->summary, 160) }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    @endif
</div>
