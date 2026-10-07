<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        @if ($this->isGameMaster)
            › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
        @elseif ($this->myCharacter)
            › <a href="{{ route('characters.show', [$campaign, $this->myCharacter]) }}" class="crumb" wire:navigate>{{ $this->myCharacter->entity->name }}</a>
        @endif
    </nav>

    <div class="mb-6">
        <h1 class="text-2xl font-semibold">{{ __('Messages') }}</h1>
        <p class="mt-1 text-sm text-stone-600">
            @if ($this->isGameMaster)
                {{ __('Écrivez à un personnage en privé ou à tout le groupe. Une fiche, un document ou une règle joints sont révélés aux destinataires.') }}
            @else
                {{ __('Vos échanges privés avec le MJ et la discussion de tout le groupe. Les autres joueurs ne voient pas vos messages privés.') }}
            @endif
        </p>
    </div>

    <div class="grid gap-6 lg:grid-cols-4">
        @if ($this->isGameMaster)
            <nav aria-label="{{ __('Conversations') }}" class="lg:col-span-1">
                <ul class="space-y-1">
                    <li>
                        <button type="button" wire:click="open('')" @class(['flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-left text-sm hover:bg-codex-soft', 'bg-codex-soft font-semibold text-codex' => $conversation === '']) @if ($conversation === '') aria-current="true" @endif>
                            <span>{{ __('Tout le groupe') }}</span>
                            @if ($this->groupUnread > 0)
                                <span class="rounded-full bg-flow px-2 py-0.5 text-xs font-semibold text-on-accent">{{ $this->groupUnread }}</span>
                            @endif
                        </button>
                    </li>
                    @foreach ($this->characters as $character)
                        <li wire:key="conversation-{{ $character->id }}">
                            <button type="button" wire:click="open('{{ $character->id }}')" @class(['flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-left text-sm hover:bg-codex-soft', 'bg-codex-soft font-semibold text-codex' => $conversation === (string) $character->id]) @if ($conversation === (string) $character->id) aria-current="true" @endif>
                                <span class="min-w-0">
                                    <span class="block truncate">{{ $character->entity->name }}</span>
                                    <span class="block truncate text-xs font-normal text-stone-500">{{ $character->player?->name ?? __('sans joueur') }}{{ $character->is_active ? '' : ' · '.__('archivé') }}</span>
                                </span>
                                @if ($character->unread > 0)
                                    <span class="shrink-0 rounded-full bg-flow px-2 py-0.5 text-xs font-semibold text-on-accent">{{ $character->unread }}</span>
                                @endif
                            </button>
                        </li>
                    @endforeach
                </ul>
                @if ($this->characters->isEmpty())
                    <p class="mt-3 text-sm text-stone-600">{!! __('Aucun personnage confié à un joueur. Créez-en un depuis la page :link.', ['link' => '<a href="'.e(route('characters.index', $campaign)).'" class="link" wire:navigate>'.e(__('Personnages')).'</a>']) !!}</p>
                @endif
            </nav>
        @endif

        <section @class(['rounded-xl border border-stone-200 bg-white p-6 shadow-sm', 'lg:col-span-3' => $this->isGameMaster, 'lg:col-span-4' => ! $this->isGameMaster]) aria-labelledby="thread-title">
            <h2 id="thread-title" class="mb-4 font-semibold">
                @if (! $this->isGameMaster)
                    {{ __('Avec le MJ') }}
                @elseif ($this->currentCharacter)
                    {{ $this->currentCharacter->entity->name }} <span class="font-normal text-stone-500">· {{ __(':name, en privé', ['name' => $this->currentCharacter->player?->name ?? __('sans joueur')]) }}</span>
                @else
                    {{ __('Tout le groupe') }}
                @endif
            </h2>

            @if ($this->messages->isEmpty())
                <p class="mb-4 text-sm text-stone-600">{{ __("Aucun message pour l'instant.") }}</p>
            @else
                <ol class="mb-6 space-y-3">
                    @foreach ($this->messages as $msg)
                        @php($mine = $msg->sender_id === auth()->id())
                        @php($reference = $this->reference($msg))
                        <li wire:key="message-{{ $msg->id }}" @class(['max-w-[85%] rounded-xl px-4 py-3', 'ml-auto bg-codex-soft' => $mine, 'bg-stone-100' => ! $mine])>
                            <p class="mb-1 text-xs text-stone-500">
                                <span class="font-medium text-ink">{{ $mine ? __('Vous') : $msg->senderLabel() }}</span>
                                @if (! $this->isGameMaster && $msg->isForGroup())
                                    · <span class="font-medium text-flow">{{ __('à tout le groupe') }}</span>
                                @endif
                                · <time datetime="{{ $msg->created_at->toIso8601String() }}">{{ $msg->created_at->translatedFormat('j M, H:i') }}</time>
                            </p>
                            <p class="text-sm">{!! nl2br(e($msg->body), false) !!}</p>
                            @if ($reference)
                                <p class="mt-2 text-sm">📎 <a href="{{ $reference['url'] }}" class="link font-medium" wire:navigate>{{ $reference['label'] }}</a></p>
                            @elseif ($msg->hasReference())
                                <p class="mt-2 text-xs text-stone-500">{{ __('Pièce jointe indisponible.') }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            @endif

            @if ($this->isGameMaster || $this->myCharacter)
                <form wire:submit="send" class="space-y-3 border-t border-stone-200 pt-4">
                    <div>
                        <label for="message-body" class="label">
                            @if (! $this->isGameMaster)
                                {{ $to === 'group' ? __('Écrire à tout le groupe') : __('Écrire au MJ, en privé') }}
                            @elseif ($this->currentCharacter)
                                {{ __('Écrire à :name', ['name' => $this->currentCharacter->entity->name]) }}
                            @else
                                {{ __('Écrire au groupe') }}
                            @endif
                        </label>
                        @unless ($this->isGameMaster)
                            <div class="mb-2 flex gap-4 text-sm" role="radiogroup" aria-label="{{ __('Destinataire') }}">
                                <label class="flex items-center gap-2"><input type="radio" wire:model.live="to" value="gm"> {{ __('Au MJ, en privé') }}</label>
                                <label class="flex items-center gap-2"><input type="radio" wire:model.live="to" value="group"> {{ __('À tout le groupe') }}</label>
                            </div>
                        @endunless
                        <textarea id="message-body" wire:model="body" rows="3" class="field" maxlength="5000"></textarea>
                        @error('body') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    @if ($this->isGameMaster)
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div>
                                <label for="ref-kind" class="label">{{ __('Joindre') }} <span class="font-normal text-stone-500">{{ __('(facultatif)') }}</span></label>
                                <select id="ref-kind" wire:model.live="refKind" class="field">
                                    <option value="">{{ __('Rien') }}</option>
                                    <option value="entity">{{ __('Une fiche') }}</option>
                                    <option value="document">{{ __('Un document') }}</option>
                                    <option value="rule">{{ __('Une règle') }}</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                @if ($refKind === 'entity')
                                    <label for="ref-entity" class="label">{{ __('Fiche') }}</label>
                                    <x-entity-picker id="ref-entity" model="refEntityId" />
                                    @error('refEntityId') <p class="error">{{ $message }}</p> @enderror
                                @elseif ($refKind === 'document')
                                    <label for="ref-document" class="label">{{ __('Document') }}</label>
                                    <select id="ref-document" wire:model="refDocumentId" class="field">
                                        <option value="">{{ __('Choisir…') }}</option>
                                        @foreach ($this->documents as $document)
                                            <option value="{{ $document->id }}">{{ $document->title }}</option>
                                        @endforeach
                                    </select>
                                    @error('refDocumentId') <p class="error">{{ $message }}</p> @enderror
                                @elseif ($refKind === 'rule')
                                    <label for="ref-rule" class="label">{{ __('Règle') }} <span class="font-normal text-stone-500">{{ __('(consultables par les joueurs)') }}</span></label>
                                    <select id="ref-rule" wire:model="refRuleId" class="field">
                                        <option value="">{{ __('Choisir…') }}</option>
                                        @foreach ($this->rules as $rule)
                                            <option value="{{ $rule->id }}">{{ $rule->title }}</option>
                                        @endforeach
                                    </select>
                                    @error('refRuleId') <p class="error">{{ $message }}</p> @enderror
                                @endif
                            </div>
                        </div>
                        @if ($refKind !== '')
                            <p class="text-xs text-stone-600">{{ $this->currentCharacter ? __("L'élément joint sera révélé à ce personnage : ils en verront la zone publique, jamais la zone MJ.") : __("L'élément joint sera révélé aux destinataires : ils en verront la zone publique, jamais la zone MJ.") }}</p>
                        @endif

                        @if ($conversation === '' && $this->characters->where('is_active', true)->isNotEmpty())
                            <fieldset>
                                <legend class="label">{{ __('Seulement à') }} <span class="font-normal text-stone-500">{{ __('(facultatif : sinon tout le groupe)') }}</span></legend>
                                <div class="flex flex-wrap gap-x-4 gap-y-1">
                                    @foreach ($this->characters->where('is_active', true) as $character)
                                        <label wire:key="only-{{ $character->id }}" class="flex items-center gap-2 text-sm">
                                            <input type="checkbox" wire:model="selected" value="{{ $character->id }}">
                                            {{ $character->entity->name }}
                                        </label>
                                    @endforeach
                                </div>
                                @error('selected.*') <p class="error">{{ $message }}</p> @enderror
                            </fieldset>
                        @endif
                    @endif

                    <div class="flex items-center gap-3">
                        <button type="submit" class="btn-primary">{{ __('Envoyer') }}</button>
                        @if ($flash)
                            <p class="text-sm text-green-800" role="status">{{ $flash }}</p>
                        @endif
                    </div>
                </form>
            @else
                <p class="border-t border-stone-200 pt-4 text-sm text-stone-600">{{ __('Votre MJ ne vous a pas encore confié de personnage : vous pourrez lui écrire ensuite.') }}</p>
            @endif
        </section>
    </div>
</div>
