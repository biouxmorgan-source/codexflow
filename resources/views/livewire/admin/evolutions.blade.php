<div>
    <h1 class="text-2xl font-semibold">{{ __('Administration') }}</h1>
    <p class="mt-1 mb-4 text-sm text-stone-600">{{ __('Ce que vous prévoyez pour la plateforme. Les bugs et les retours de recette restent dans le backlog.') }}</p>
    <x-admin-nav />

    @php($counts = $this->counts)
    <div class="mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label for="evolution-status" class="label">{{ __('Statut') }}</label>
            <select id="evolution-status" wire:model.live="status" class="field">
                <option value="open">{{ __('À venir') }} ({{ collect($counts)->except(\App\Models\Evolution::CLOSED)->sum() }})</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }} ({{ $counts[$value] ?? 0 }})</option>
                @endforeach
                <option value="all">{{ __('Tout') }} ({{ array_sum($counts) }})</option>
            </select>
        </div>
        <button type="button" wire:click="$toggle('adding')" class="btn-secondary">{{ $adding ? __('Annuler') : __('+ Ajouter') }}</button>
    </div>

    @if ($adding)
        <form wire:submit="add" class="mb-6 grid gap-3 rounded-xl border border-stone-200 bg-white p-4 shadow-sm sm:grid-cols-4">
            <div class="sm:col-span-2">
                <label for="new-evolution-title" class="label">{{ __('Titre') }}</label>
                <input id="new-evolution-title" type="text" wire:model="newTitle" class="field">
                @error('newTitle') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="new-evolution-target" class="label">{{ __('Échéance') }}</label>
                <input id="new-evolution-target" type="text" wire:model="newTarget" class="field" placeholder="{{ __('v1.0, décembre…') }}">
                @error('newTarget') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="new-evolution-priority" class="label">{{ __('Priorité') }}</label>
                <select id="new-evolution-priority" wire:model="newPriority" class="field">
                    @foreach ($priorities as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-4">
                <label for="new-evolution-detail" class="label">{{ __('Détail') }}</label>
                <textarea id="new-evolution-detail" wire:model="newDetail" rows="3" class="field"></textarea>
                @error('newDetail') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-4"><button type="submit" class="btn-primary">{{ __('Ajouter l’évolution') }}</button></div>
        </form>
    @endif

    <ul class="space-y-3">
        @forelse ($this->items as $item)
            <li wire:key="evolution-{{ $item->id }}" @class(['rounded-xl border border-stone-200 bg-white p-4 shadow-sm', 'opacity-70' => ! $item->isOpen()])>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="flex flex-wrap items-center gap-2 text-xs">
                            <span @class(['font-medium', 'text-red-700' => $item->priority === 'high', 'text-stone-600' => $item->priority === 'normal', 'text-stone-400' => $item->priority === 'low'])>{{ __('Priorité :priority', ['priority' => mb_strtolower($priorities[$item->priority] ?? $item->priority)]) }}</span>
                            @if ($item->target)
                                <span class="rounded-full bg-codex-soft px-2 py-0.5 font-medium text-codex">{{ $item->target }}</span>
                            @endif
                            <span class="text-stone-500">· {{ $item->created_at->isoFormat('L') }}</span>
                        </p>
                        <p class="mt-1 font-medium">{{ $item->title }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <label class="sr-only" for="evolution-status-{{ $item->id }}">{{ __('Statut') }}</label>
                        <select id="evolution-status-{{ $item->id }}" wire:change="setStatus({{ $item->id }}, $event.target.value)" class="field py-1 text-sm">
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected($item->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <label class="sr-only" for="evolution-priority-{{ $item->id }}">{{ __('Priorité') }}</label>
                        <select id="evolution-priority-{{ $item->id }}" wire:change="setPriority({{ $item->id }}, $event.target.value)" class="field py-1 text-sm">
                            @foreach ($priorities as $value => $label)
                                <option value="{{ $value }}" @selected($item->priority === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <details class="mt-2" x-data="{ title: @js($item->title), target: @js((string) $item->target), detail: @js((string) $item->detail) }">
                    <summary class="cursor-pointer text-sm text-stone-500 hover:text-codex">{{ __('Détails') }}</summary>
                    @if ($item->detail)
                        <p class="mt-2 whitespace-pre-line text-stone-800">{{ $item->detail }}</p>
                    @endif
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <div class="sm:col-span-2">
                            <label class="label" for="evolution-title-{{ $item->id }}">{{ __('Titre') }}</label>
                            <input id="evolution-title-{{ $item->id }}" type="text" x-model="title" class="field py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="label" for="evolution-target-{{ $item->id }}">{{ __('Échéance') }}</label>
                            <input id="evolution-target-{{ $item->id }}" type="text" x-model="target" class="field py-1.5 text-sm">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="label" for="evolution-detail-{{ $item->id }}">{{ __('Détail') }}</label>
                            <textarea id="evolution-detail-{{ $item->id }}" x-model="detail" rows="4" class="field text-sm"></textarea>
                        </div>
                        <div class="flex flex-wrap gap-2 sm:col-span-3">
                            <button type="button" x-on:click="$wire.saveDetails({{ $item->id }}, title, target, detail)" class="btn-primary py-1 text-sm">{{ __('Enregistrer') }}</button>
                            <button type="button" wire:click="delete({{ $item->id }})" wire:confirm="{{ __('Supprimer cette évolution ?') }}" class="btn-secondary py-1 text-sm text-red-700">{{ __('Supprimer') }}</button>
                        </div>
                    </div>
                </details>
            </li>
        @empty
            <li class="rounded-xl border border-dashed border-stone-300 p-6 text-center text-stone-500">{{ __('Aucune évolution prévue.') }}</li>
        @endforelse
    </ul>
</div>
