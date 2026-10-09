<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
    </nav>

    <div class="mb-6">
        <h1 class="text-2xl font-semibold">{{ __('Tags') }}</h1>
        <p class="mt-1 text-sm text-stone-600">{{ __('Vos tags, dans toutes vos campagnes. Renommez-les, donnez-leur une couleur, réunissez les doublons.') }}</p>
    </div>

    @if ($this->tags->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
            <p class="text-lg font-medium">{{ __("Aucun tag pour l'instant.") }}</p>
            <p class="mt-1 text-stone-600">{{ __('Ajoutez-en depuis une fiche, une scène, une règle ou un document.') }}</p>
        </div>
    @else
        <ul class="divide-y divide-stone-100 rounded-xl border border-stone-200 bg-white shadow-sm">
            @foreach ($this->tags as $tag)
                <li wire:key="tag-{{ $tag->id }}" class="px-4 py-3">
                    @if ($editingId === $tag->id)
                        <form wire:submit="save" class="space-y-3">
                            <div>
                                <label for="tag-name" class="label">{{ __('Nom') }}</label>
                                <input id="tag-name" type="text" wire:model="name" class="field" autofocus>
                                @error('name') <p class="error">{{ $message }}</p> @enderror
                            </div>
                            <fieldset>
                                <legend class="label">{{ __('Couleur') }}</legend>
                                <div class="flex flex-wrap gap-2">
                                    <label class="flex cursor-pointer items-center gap-1.5 rounded-full border border-stone-200 px-2.5 py-1 text-sm has-[:checked]:border-codex has-[:checked]:bg-codex-soft">
                                        <input type="radio" wire:model="color" value="" class="sr-only">
                                        {{ __('Aucune') }}
                                    </label>
                                    @foreach ($colors as $key => $label)
                                        <label class="flex cursor-pointer items-center gap-1.5 rounded-full border border-stone-200 px-2.5 py-1 text-sm has-[:checked]:border-codex has-[:checked]:bg-codex-soft">
                                            <input type="radio" wire:model="color" value="{{ $key }}" class="sr-only">
                                            <span class="h-3 w-3 rounded-full" style="background: {{ \App\Models\Tag::COLORS[$key] }}" aria-hidden="true"></span>
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                                @error('color') <p class="error">{{ $message }}</p> @enderror
                            </fieldset>
                            <div class="flex gap-2">
                                <button type="submit" class="btn-primary">{{ __('Enregistrer') }}</button>
                                <button type="button" wire:click="cancel" class="btn-secondary">{{ __('Annuler') }}</button>
                            </div>
                        </form>
                    @else
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="min-w-0 flex-1">
                                <x-tag :tag="$tag" class="font-medium" />
                                <span class="ml-2 text-sm text-stone-500">
                                    @php($parts = array_filter([
                                        $tag->entities_count ? trans_choice(':count fiche|:count fiches', $tag->entities_count) : null,
                                        $tag->scenes_count ? trans_choice(':count scène|:count scènes', $tag->scenes_count) : null,
                                        $tag->rules_count ? trans_choice(':count règle|:count règles', $tag->rules_count) : null,
                                        $tag->documents_count ? trans_choice(':count document|:count documents', $tag->documents_count) : null,
                                    ]))
                                    @if ($parts)
                                        <button type="button" wire:click="toggleItems({{ $tag->id }})" class="link" aria-expanded="{{ $openId === $tag->id ? 'true' : 'false' }}" title="{{ __('Voir les éléments qui portent ce tag') }}">{{ implode(' · ', $parts) }}</button>
                                    @else
                                        {{ __('Inutilisé') }}
                                    @endif
                                </span>
                            </span>
                            <span class="flex shrink-0 items-center gap-1 text-sm">
                                <button type="button" wire:click="edit({{ $tag->id }})" class="rounded px-2 py-1 link">{{ __('Modifier') }}</button>
                                @if ($this->tags->count() > 1)
                                    <button type="button" wire:click="startMerge({{ $tag->id }})" class="rounded px-2 py-1 link">{{ __('Fusionner') }}</button>
                                @endif
                                <button type="button" wire:click="delete({{ $tag->id }})" wire:confirm="{{ __('Supprimer le tag :name ? Les éléments qui le portent sont conservés.', ['name' => $tag->name]) }}" class="rounded px-2 py-1 text-red-700 hover:underline">{{ __('Supprimer') }}</button>
                            </span>
                        </div>
                        @if ($openId === $tag->id)
                            <ul class="mt-3 space-y-1 border-t border-stone-100 pt-3 text-sm">
                                @foreach ($this->items as $item)
                                    <li class="flex flex-wrap items-baseline gap-x-2">
                                        <span class="w-20 shrink-0 text-xs text-stone-500">{{ $item['type'] }}</span>
                                        @if ($item['url'])
                                            <a href="{{ $item['url'] }}" class="link" wire:navigate>{{ $item['label'] }}</a>
                                            <span class="text-xs text-stone-500">{{ $item['place'] }}</span>
                                        @else
                                            <span>{{ $item['label'] }}</span>
                                            <span class="text-xs text-stone-500">{{ __('(dans aucune de vos campagnes)') }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        @if ($mergingId === $tag->id)
                            <form wire:submit="merge" class="mt-3 flex flex-wrap items-end gap-2 rounded-lg bg-codex-soft p-3">
                                <div class="min-w-48 flex-1">
                                    <label for="merge-target" class="label">{{ __('Fusionner « :name » dans', ['name' => $tag->name]) }}</label>
                                    <select id="merge-target" wire:model="mergeTargetId" class="field">
                                        <option value="">{{ __('Choisir un tag…') }}</option>
                                        @foreach ($this->tags as $other)
                                            @continue($other->id === $tag->id)
                                            <option value="{{ $other->id }}">{{ $other->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('mergeTargetId') <p class="error">{{ $message }}</p> @enderror
                                </div>
                                <button type="submit" class="btn-primary">{{ __('Fusionner') }}</button>
                                <button type="button" wire:click="cancel" class="btn-secondary">{{ __('Annuler') }}</button>
                                <p class="w-full text-xs text-stone-600">{{ __('Tout ce qui porte ce tag passe sous le tag choisi, puis celui-ci disparaît.') }}</p>
                            </form>
                        @endif
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>
