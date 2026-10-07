<span class="flex items-center gap-3 sm:gap-4">
    @if ($campaign)
        <a href="{{ route('messages.index', $campaign) }}" @class(['rounded-md px-2 py-1 font-medium text-codex hover:bg-codex-soft', 'bg-codex-soft' => $active === 'messages']) wire:navigate>
            Messages
            @if ($unreadMessages > 0)
                <span class="ml-1 rounded-full bg-flow px-1.5 py-0.5 text-xs font-semibold text-white">{{ $unreadMessages }} <span class="sr-only">non lus</span></span>
            @endif
        </a>
    @endif
    <a href="{{ route('notifications.index') }}" @class(['relative rounded-md px-2 py-1 text-codex hover:bg-codex-soft', 'bg-codex-soft' => $active === 'notifications']) title="Notifications" wire:navigate>
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
        <span class="sr-only">Notifications{{ $unreadNotifications > 0 ? ' : '.$unreadNotifications.' non lue'.($unreadNotifications > 1 ? 's' : '') : '' }}</span>
        @if ($unreadNotifications > 0)
            <span class="absolute -top-1 -right-1 rounded-full bg-flow px-1.5 text-xs font-semibold text-white" aria-hidden="true">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
        @endif
    </a>
</span>
