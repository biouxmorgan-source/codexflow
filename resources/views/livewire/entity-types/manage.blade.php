<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
    </nav>

    <h1 class="text-2xl font-semibold">{{ __('Types de fiche') }}</h1>
    <p class="mt-1 mb-6 text-sm text-stone-600">{{ __('Les six types standards sont toujours là. Ajoutez les vôtres pour coller à votre univers : Faction, Divinité, Vaisseau, Sort…') }}</p>

    @error('delete') <p class="error mb-4">{{ $message }}</p> @enderror

    <div class="grid gap-6 lg:grid-cols-3">
        <ul class="divide-y divide-stone-200 overflow-hidden rounded-xl border border-stone-200 bg-white lg:col-span-2">
            @foreach ($this->types as $type)
                <li wire:key="type-{{ $type->id }}" @class(['flex flex-wrap items-center gap-3 px-4 py-3', 'bg-codex-soft' => $editingId === $type->id])>
                    <span class="min-w-0 flex-1 font-medium">{{ $type->name }}</span>
                    <span class="text-sm text-stone-500">
                        {{ trans_choice(':count fiche|:count fiches', $type->entities_count) }}
                    </span>
                    @if ($type->isStandard())
                        <span class="rounded-full bg-stone-100 px-2 py-0.5 text-xs text-stone-600">{{ __('Standard') }}</span>
                    @else
                        <button type="button" wire:click="edit({{ $type->id }})" class="text-sm link">{{ __('Renommer') }}</button>
                        <button type="button" wire:click="delete({{ $type->id }})"
                            wire:confirm="{{ $type->field_definitions_count ? __('Supprimer le type :name et ses :count champ(s) propres ?', ['name' => $type->name, 'count' => $type->field_definitions_count]) : __('Supprimer le type :name ?', ['name' => $type->name]) }}"
                            class="text-sm text-red-700 hover:underline">{{ __('Supprimer') }}</button>
                    @endif
                </li>
            @endforeach
        </ul>

        <form wire:submit="save" class="space-y-4 self-start rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="font-semibold">{{ $editingId ? __('Renommer le type') : __('Nouveau type') }}</h2>
            <div>
                <label for="name" class="label">{{ __('Nom') }}</label>
                <input id="name" type="text" wire:model="name" class="field" placeholder="{{ __('Faction, Divinité…') }}">
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-primary">{{ $editingId ? __('Enregistrer') : __('Ajouter') }}</button>
                @if ($editingId)
                    <button type="button" wire:click="cancel" class="btn-secondary">{{ __('Annuler') }}</button>
                @endif
            </div>
        </form>
    </div>
</div>
