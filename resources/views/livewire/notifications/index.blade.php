<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">Notifications</h1>
            <p class="mt-1 text-sm text-stone-600">Ce que vous avez reçu dans vos campagnes : éléments révélés ou donnés, messages, demandes des joueurs.</p>
        </div>
        <div class="flex items-center gap-4">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" wire:model.live="unreadOnly"> Non lues seulement
            </label>
            <button type="button" wire:click="markAllRead" class="btn-secondary">Tout marquer comme lu</button>
        </div>
    </div>

    @if ($this->notifications->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
            <p class="text-stone-600">{{ $unreadOnly ? 'Aucune notification non lue.' : 'Aucune notification pour l\'instant.' }}</p>
        </div>
    @else
        <ul class="divide-y divide-stone-200 overflow-hidden rounded-xl border border-stone-200 bg-white">
            @foreach ($this->notifications as $notification)
                @php($data = $notification->data)
                <li wire:key="notification-{{ $notification->id }}">
                    <a href="{{ route('notifications.open', $notification->id) }}" @class(['flex items-start gap-3 px-4 py-3 hover:bg-codex-soft', 'bg-flow/5' => $notification->read_at === null])>
                        <span @class(['mt-1.5 h-2 w-2 shrink-0 rounded-full', 'bg-flow' => $notification->read_at === null, 'bg-transparent' => $notification->read_at !== null]) aria-hidden="true"></span>
                        <span class="min-w-0 flex-1">
                            <span @class(['block text-sm', 'font-medium' => $notification->read_at === null])>{{ $data['text'] ?? '' }}</span>
                            <span class="block text-xs text-stone-500">
                                {{ \App\Notifications\CampaignEvent::KINDS[$data['kind'] ?? ''] ?? '' }}
                                · {{ $data['campaign'] ?? '' }}
                                · <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->translatedFormat('j M, H:i') }}</time>
                                @if ($notification->read_at === null) <span class="sr-only">(non lue)</span> @endif
                            </span>
                        </span>
                        <span class="shrink-0 text-codex" aria-hidden="true">›</span>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $this->notifications->links() }}</div>
    @endif
</div>
