<div>
    @if ($grant)
        @php($character = $grant->character)
        <div wire:key="received-{{ $notification->id }}" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" role="dialog" aria-modal="true" aria-labelledby="received-title"
            x-data x-init="$nextTick(() => $refs.ok.focus())" x-on:keydown.escape.window="$wire.dismiss(@js($notification->id))">
            <div class="max-h-[85vh] w-full max-w-lg overflow-y-auto rounded-xl border border-stone-200 bg-white p-6 shadow-xl">
                <p class="text-sm font-medium text-flow">
                    {{ $notification->data['campaign'] ?? '' }} · {{ $character->entity->name }}
                    @if ($count > 1)
                        <span class="text-stone-500">· {{ __(':count en attente', ['count' => $count]) }}</span>
                    @endif
                </p>
                <h2 id="received-title" class="mt-1 text-sm text-stone-600">{{ $notification->data['text'] }}</h2>

                <div class="mt-4 flex gap-4">
                    @if ($grant->kind === 'entity' && $grant->entity?->hasImage())
                        <img src="{{ route('characters.entity-image', [$character->campaign_id, $character, $grant->entity]) }}" alt="" class="size-24 shrink-0 rounded-lg object-cover">
                    @endif
                    <div class="min-w-0">
                        <p class="text-xl font-semibold">{{ $grant->label() }}</p>
                        @php($text = match ($grant->kind) {
                            'entity' => $grant->entity?->summary,
                            'rule' => $grant->rule?->summary,
                            'document' => null,
                            default => $grant->body,
                        })
                        @if (filled($text))
                            <div class="mt-2 text-stone-700">{{ \App\Support\EntityLinks::plain($text) }}</div>
                        @endif
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <button type="button" x-ref="ok" wire:click="dismiss(@js($notification->id))" class="btn-primary">{{ $count > 1 ? __('Suivant') : __("C'est noté") }}</button>
                    <button type="button" x-on:click="$wire.open(@js($notification->id), window.location.pathname)" class="btn-secondary">
                        {{ in_array($grant->kind, ['entity', 'document'], true) ? __('Ouvrir') : __('Voir sur ma fiche') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
