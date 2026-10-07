<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <div class="mb-6">
        <h1 class="text-2xl font-semibold">{{ __('Historique des révélations') }}</h1>
        <p class="mt-1 text-sm text-stone-600">{{ __('Qui a appris quoi, quand, et pendant quelle séance. Une révélation annulée retire l’élément au personnage et reste visible dans le journal.') }}</p>
    </div>

    <div class="mb-4 flex flex-wrap gap-3">
        <div>
            <label for="filter-character" class="sr-only">{{ __('Personnage') }}</label>
            <select id="filter-character" wire:model.live="character" class="field w-auto">
                <option value="">{{ __('Tous les personnages') }}</option>
                @foreach ($characters as $option)
                    <option value="{{ $option->id }}">{{ $option->entity->name }}</option>
                @endforeach
            </select>
        </div>
        @if ($sessions->isNotEmpty())
            <div>
                <label for="filter-session" class="sr-only">{{ __('Session') }}</label>
                <select id="filter-session" wire:model.live="session" class="field w-auto">
                    <option value="">{{ __('Toutes les sessions') }}</option>
                    @foreach ($sessions as $option)
                        <option value="{{ $option->id }}">{{ $option->label() }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    @if ($this->reveals->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
            <p class="text-lg font-medium">{{ __('Rien de révélé pour l’instant.') }}</p>
        </div>
    @else
        <ul class="divide-y divide-stone-100 rounded-xl border border-stone-200 bg-white shadow-sm">
            @foreach ($this->reveals as $grant)
                <li wire:key="reveal-{{ $grant->id }}" class="flex flex-wrap items-start gap-3 px-4 py-3">
                    <time class="w-28 shrink-0 text-xs text-stone-500" datetime="{{ $grant->created_at->toIso8601String() }}">{{ $grant->created_at->timezone(config('app.timezone'))->isoFormat('D MMM, LT') }}</time>
                    <span class="min-w-0 flex-1">
                        <span class="block">
                            <span class="rounded-full bg-stone-100 px-2 py-0.5 text-xs text-stone-600">{{ $grant->secret_id ? __('Secret') : (\App\Models\CharacterGrant::kinds()[$grant->kind] ?? $grant->kind) }}</span>
                            <span class="font-medium">{{ $grant->label() }}</span>
                            → <a href="{{ route('characters.show', [$campaign, $grant->character]) }}" class="link" wire:navigate>{{ $grant->character->entity->name }}</a>
                        </span>
                        <span class="block text-xs text-stone-500">
                            @if ($grant->giver)
                                {{ __('par :name', ['name' => $grant->giver->name]) }}
                            @endif
                            @if ($grant->playSession)
                                · {{ $grant->playSession->label() }}
                            @endif
                            @if ($grant->scene)
                                · {{ $grant->scene->name }}
                            @endif
                        </span>
                    </span>
                    <button type="button" wire:click="undo({{ $grant->id }})" wire:confirm="{{ __('Annuler : :label ne sera plus connu de :name ?', ['label' => $grant->label(), 'name' => $grant->character->entity->name]) }}" class="shrink-0 text-sm text-red-700 hover:underline">{{ __('Annuler') }}</button>
                </li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $this->reveals->links() }}</div>
    @endif
</div>
