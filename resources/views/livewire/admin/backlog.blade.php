<div>
    <h1 class="text-2xl font-semibold">{{ __('Administration') }}</h1>
    <p class="mt-1 mb-4 text-sm text-stone-600">{{ __('Les problèmes signalés depuis l’application arrivent ici, avec les bugs et évolutions relevés en recette.') }}</p>
    <x-admin-nav />

    @php($counts = $this->counts)
    <div class="mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label for="backlog-status" class="label">{{ __('Statut') }}</label>
            <select id="backlog-status" wire:model.live="status" class="field">
                <option value="open">{{ __('À traiter') }} ({{ collect($counts)->except(\App\Models\BugReport::CLOSED)->sum() }})</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }} ({{ $counts[$value] ?? 0 }})</option>
                @endforeach
                <option value="all">{{ __('Tout') }} ({{ array_sum($counts) }})</option>
            </select>
        </div>
        <div>
            <label for="backlog-kind" class="label">{{ __('Nature') }}</label>
            <select id="backlog-kind" wire:model.live="kind" class="field">
                <option value="">{{ __('Bugs et évolutions') }}</option>
                @foreach ($kinds as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-48 flex-1">
            <label for="backlog-search" class="label">{{ __('Rechercher') }}</label>
            <input id="backlog-search" type="search" wire:model.live.debounce.300ms="search" class="field" autocomplete="off">
        </div>
        <button type="button" wire:click="$toggle('adding')" class="btn-secondary">{{ $adding ? __('Annuler') : __('+ Ajouter') }}</button>
    </div>

    @if ($adding)
        <form wire:submit="add" class="mb-6 grid gap-3 rounded-xl border border-stone-200 bg-white p-4 shadow-sm sm:grid-cols-4">
            <div class="sm:col-span-2">
                <label for="new-title" class="label">{{ __('Titre') }}</label>
                <input id="new-title" type="text" wire:model="newTitle" class="field">
                @error('newTitle') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="new-kind" class="label">{{ __('Nature') }}</label>
                <select id="new-kind" wire:model="newKind" class="field">
                    @foreach ($kinds as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="new-priority" class="label">{{ __('Priorité') }}</label>
                <select id="new-priority" wire:model="newPriority" class="field">
                    @foreach ($priorities as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-4">
                <label for="new-message" class="label">{{ __('Détail') }}</label>
                <textarea id="new-message" wire:model="newMessage" rows="3" class="field"></textarea>
            </div>
            <div class="sm:col-span-4"><button type="submit" class="btn-primary">{{ __('Ajouter au backlog') }}</button></div>
        </form>
    @endif

    <ul class="space-y-3">
        @forelse ($this->items as $item)
            <li wire:key="item-{{ $item->id }}" @class(['rounded-xl border border-stone-200 bg-white p-4 shadow-sm', 'opacity-70' => ! $item->isOpen()])>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="flex flex-wrap items-center gap-2 text-xs">
                            <span @class(['rounded-full px-2 py-0.5 font-medium', 'bg-red-100 text-red-800' => $item->kind === 'bug', 'bg-codex-soft text-codex' => $item->kind === 'evolution'])>{{ $kinds[$item->kind] ?? $item->kind }}</span>
                            <span @class(['font-medium', 'text-red-700' => $item->priority === 'high', 'text-stone-600' => $item->priority === 'normal', 'text-stone-400' => $item->priority === 'low'])>{{ __('Priorité :priority', ['priority' => mb_strtolower($priorities[$item->priority] ?? $item->priority)]) }}</span>
                            <span class="text-stone-500">· {{ $sources[$item->source] ?? $item->source }} · {{ $item->created_at->isoFormat('L') }}</span>
                            @if ($item->status === 'fixed' && $item->fixed_in)
                                <span class="rounded-full bg-green-100 px-2 py-0.5 font-medium text-green-800">{{ __('corrigé en :version', ['version' => $item->fixed_in]) }}</span>
                            @endif
                        </p>
                        <p class="mt-1 font-medium">{{ $item->heading() }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <label class="sr-only" for="status-{{ $item->id }}">{{ __('Statut') }}</label>
                        <select id="status-{{ $item->id }}" wire:change="setStatus({{ $item->id }}, $event.target.value)" class="field py-1 text-sm">
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected($item->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <label class="sr-only" for="priority-{{ $item->id }}">{{ __('Priorité') }}</label>
                        <select id="priority-{{ $item->id }}" wire:change="setPriority({{ $item->id }}, $event.target.value)" class="field py-1 text-sm">
                            @foreach ($priorities as $value => $label)
                                <option value="{{ $value }}" @selected($item->priority === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <details class="mt-2" x-data="{ title: @js((string) $item->title), fixedIn: @js((string) $item->fixed_in), note: @js((string) $item->admin_note) }">
                    <summary class="cursor-pointer text-sm text-stone-500 hover:text-codex">{{ __('Détails') }}</summary>
                    @if ($item->message && $item->message !== $item->title)
                        <p class="mt-2 whitespace-pre-line text-stone-800">{{ $item->message }}</p>
                    @endif
                    @if ($item->source === 'report')
                        <p class="mt-2 text-xs break-all text-stone-500">
                            {{ $item->user?->email ?? ($item->contact_email ? __('Sans compte : :email', ['email' => $item->contact_email]) : __('Compte supprimé')) }}
                            @if ($item->url) · {{ $item->url }} @endif
                            · {{ $item->locale }} · LoreMundi {{ $item->version }} · {{ $item->user_agent }}
                        </p>
                    @endif
                    <div class="mt-3 grid gap-3 sm:grid-cols-4">
                        <div class="sm:col-span-2">
                            <label class="label" for="title-{{ $item->id }}">{{ __('Titre') }}</label>
                            <input id="title-{{ $item->id }}" type="text" x-model="title" class="field py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="label" for="kind-{{ $item->id }}">{{ __('Nature') }}</label>
                            <select id="kind-{{ $item->id }}" wire:change="setKind({{ $item->id }}, $event.target.value)" class="field py-1.5 text-sm">
                                @foreach ($kinds as $value => $label)
                                    <option value="{{ $value }}" @selected($item->kind === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label" for="fixed-{{ $item->id }}">{{ __('Corrigé en version') }}</label>
                            <input id="fixed-{{ $item->id }}" type="text" x-model="fixedIn" class="field py-1.5 text-sm" placeholder="0.15.0">
                        </div>
                        <div class="sm:col-span-4">
                            <label class="label" for="note-{{ $item->id }}">{{ __('Note de l’administrateur') }}</label>
                            <textarea id="note-{{ $item->id }}" x-model="note" rows="2" class="field text-sm"></textarea>
                        </div>
                        <div class="flex flex-wrap gap-2 sm:col-span-4">
                            <button type="button" x-on:click="$wire.saveDetails({{ $item->id }}, title, fixedIn, note)" class="btn-primary py-1 text-sm">{{ __('Enregistrer') }}</button>
                            <button type="button" wire:click="delete({{ $item->id }})" wire:confirm="{{ __('Supprimer cet élément du backlog ?') }}" class="btn-secondary py-1 text-sm text-red-700">{{ __('Supprimer') }}</button>
                        </div>
                    </div>
                </details>
            </li>
        @empty
            <li class="rounded-xl border border-dashed border-stone-300 p-6 text-center text-stone-500">{{ __('Rien à traiter.') }}</li>
        @endforelse
    </ul>
</div>
