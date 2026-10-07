<div class="fixed inset-x-2 bottom-2 z-40 sm:inset-x-auto sm:right-4 sm:bottom-4 sm:w-96">
    @if (! $open)
        <div class="flex justify-end">
            <button type="button" wire:click="toggle" class="flex items-center gap-2 rounded-full bg-codex px-4 py-2.5 text-sm font-semibold text-on-accent shadow-lg hover:bg-ink">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12Z"/></svg>
                {{ __('Discussion') }}
                @if ($this->unread > 0)
                    <span class="rounded-full bg-flow px-2 py-0.5 text-xs">{{ $this->unread }} <span class="sr-only">{{ __('non lus') }}</span></span>
                @endif
            </button>
        </div>
    @else
        <section class="flex h-[28rem] max-h-[80vh] flex-col overflow-hidden rounded-xl border border-stone-200 bg-white shadow-2xl" aria-label="{{ __('Discussion') }}">
            <header class="flex items-center gap-2 border-b border-stone-200 bg-stone-50 px-3 py-2">
                <h2 class="text-sm font-semibold">{{ __('Discussion') }}</h2>
                <a href="{{ route('messages.index', $campaign) }}" class="ml-auto text-xs text-codex hover:underline" wire:navigate>{{ __("Tout l'historique") }}</a>
                <button type="button" wire:click="toggle" class="rounded px-2 py-0.5 text-stone-500 hover:bg-stone-200 hover:text-ink" aria-label="{{ __('Réduire la discussion') }}">—</button>
            </header>

            <nav class="flex gap-1 overflow-x-auto border-b border-stone-200 px-2 py-1.5" aria-label="{{ __('Conversations') }}">
                @foreach ($this->tabs as $item)
                    <button type="button" wire:key="tab-{{ $item['key'] }}" wire:click="select('{{ $item['key'] }}')"
                        @class(['flex shrink-0 items-center gap-1 rounded-md px-2 py-1 text-xs', 'bg-codex text-on-accent' => $tab === $item['key'], 'text-stone-700 hover:bg-codex-soft' => $tab !== $item['key'], 'font-semibold' => $item['unread'] > 0])
                        @if ($tab === $item['key']) aria-current="true" @endif>
                        {{ $item['label'] }}
                        @if ($item['unread'] > 0 && $tab !== $item['key'])
                            <span class="rounded-full bg-flow px-1.5 text-[10px] text-on-accent">{{ $item['unread'] }}</span>
                        @endif
                    </button>
                @endforeach
            </nav>

            <ol class="flex-1 space-y-2 overflow-y-auto px-3 py-3" x-data x-init="$el.scrollTop = $el.scrollHeight" x-on:chat-updated.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)">
                @forelse ($this->messages as $msg)
                    @php($mine = $msg->sender_id === auth()->id())
                    @php($reference = $this->reference($msg))
                    <li wire:key="chat-{{ $msg->id }}" @class(['max-w-[85%] rounded-lg px-3 py-2 text-sm', 'ml-auto bg-codex-soft' => $mine, 'bg-stone-100' => ! $mine])>
                        <p class="text-[11px] text-stone-500">
                            <span class="font-medium text-ink">{{ $mine ? __('Vous') : $msg->senderLabel() }}</span>
                            · <time datetime="{{ $msg->created_at->toIso8601String() }}">{{ $msg->created_at->translatedFormat('j M, H:i') }}</time>
                        </p>
                        <p>{!! nl2br(e($msg->body), false) !!}</p>
                        @if ($reference)
                            <p class="mt-1">📎 <a href="{{ $reference['url'] }}" class="link font-medium" wire:navigate>{{ $reference['label'] }}</a></p>
                        @endif
                    </li>
                @empty
                    <li class="text-center text-sm text-stone-500">{{ __("Aucun message pour l'instant.") }}</li>
                @endforelse
            </ol>

            @if ($this->isGameMaster || $this->myCharacter)
                <form wire:submit="send" class="border-t border-stone-200 p-2">
                    <label for="chat-body" class="sr-only">{{ __('Message') }}</label>
                    <div class="flex items-end gap-2">
                        <textarea id="chat-body" wire:model="body" rows="2" maxlength="5000" class="field flex-1 resize-none text-sm"
                            placeholder="{{ $tab === 'group' ? __('Écrire au groupe…') : ($tab === 'gm' ? __('Écrire au MJ, en privé…') : __('Écrire en privé…')) }}"
                            x-on:keydown.enter="if (! $event.shiftKey) { $event.preventDefault(); $wire.send() }"></textarea>
                        <button type="submit" class="btn-primary px-3 py-2 text-sm">{{ __('Envoyer') }}</button>
                    </div>
                    @error('body') <p class="error">{{ $message }}</p> @enderror
                </form>
            @endif
        </section>
    @endif
</div>
