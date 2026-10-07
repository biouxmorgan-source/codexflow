@php($entity = $this->entity)
@php($counters = $this->fields->where('type', \App\Enums\FieldType::Counter))
@php($others = $this->fields->reject(fn ($definition) => $definition->type === \App\Enums\FieldType::Counter))
<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>Mes campagnes</a>
        @if ($this->isGameMaster)
            › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
            › <a href="{{ route('characters.index', $campaign) }}" class="crumb" wire:navigate>Personnages</a>
        @else
            › {{ $campaign->name }}
        @endif
    </nav>

    @if ($this->isGameMaster)
        <p class="mb-4 rounded-md bg-flow/10 px-3 py-2 text-sm text-ink">
            Vous voyez la fiche comme {{ $character->player?->name ?? 'le joueur' }} la voit : sans la zone MJ.
            <a href="{{ route('entities.show', [$campaign, $entity]) }}" class="link" wire:navigate>Fiche complète</a>
        </p>
    @endif

    <header class="mb-6 flex flex-wrap items-start gap-4">
        @if ($entity->hasImage())
            <img src="{{ route('characters.portrait', [$campaign, $character]) }}?v={{ $entity->updated_at?->timestamp }}" alt="Portrait de {{ $entity->name }}" class="h-24 w-24 shrink-0 rounded-xl object-cover shadow-sm">
        @endif
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-semibold">{{ $entity->name }}</h1>
            @if ($entity->summary)
                <p class="mt-1 text-stone-700">{{ $entity->summary }}</p>
            @endif
            <p class="mt-1 text-sm text-stone-500">
                {{ $character->player?->name ? 'Joué par '.$character->player->name : 'Sans joueur' }}
                @if ($character->locked) · <span class="font-medium text-flow">fiche verrouillée par le MJ</span> @endif
            </p>
        </div>
        @if ($this->isGameMaster && $character->user_id)
            <a href="{{ route('messages.index', [$campaign, 'personnage' => $character->id]) }}" class="btn-secondary" wire:navigate>Écrire au joueur</a>
        @elseif ($this->isOwner)
            @php($unread = \App\Models\Message::unreadCount(auth()->user(), $campaign))
            <a href="{{ route('messages.index', $campaign) }}" class="btn-secondary" wire:navigate>
                Messages
                @if ($unread > 0)
                    <span class="ml-1 rounded-full bg-flow px-2 py-0.5 text-xs font-semibold text-white">{{ $unread }} <span class="sr-only">non lus</span></span>
                @endif
            </a>
            @if ($campaign->table_shared)
                <a href="{{ route('table.screen', $campaign) }}" target="codexflow-table" class="btn-secondary">Écran de table ↗</a>
            @endif
        @endif
    </header>

    @if ($counters->isNotEmpty())
        <section aria-labelledby="counters-title" class="mb-8">
            <h2 id="counters-title" class="mb-2 font-semibold">Compteurs</h2>
            <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($counters as $definition)
                    @php($counter = $entity->fieldValue($definition))
                    @php($canChange = $this->canPlay && $definition->player_editable)
                    <li wire:key="counter-{{ $definition->id }}" class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                        <p class="text-sm text-stone-600">{{ $definition->name }}</p>
                        <div class="mt-1 flex items-center gap-2">
                            @if ($canChange)
                                <button type="button" wire:click="adjust({{ $definition->id }}, -1)" class="btn-secondary h-11 w-11 shrink-0 px-0 text-lg" aria-label="Retirer 1 à {{ $definition->name }}">−</button>
                            @endif
                            <p class="flex-1 text-center text-2xl font-semibold tabular-nums" aria-live="polite">
                                {{ $counter === null ? '—' : $definition->type->format($counter) }}
                            </p>
                            @if ($canChange)
                                <button type="button" wire:click="adjust({{ $definition->id }}, 1)" class="btn-secondary h-11 w-11 shrink-0 px-0 text-lg" aria-label="Ajouter 1 à {{ $definition->name }}">+</button>
                            @endif
                        </div>
                        @if (isset($counter['max']) && $counter['max'] > 0)
                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-stone-100" aria-hidden="true">
                                <div class="h-full rounded-full bg-codex" style="width: {{ min(100, max(0, $counter['value'] / $counter['max'] * 100)) }}%"></div>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <div class="grid gap-6 lg:grid-cols-5">
        <section class="space-y-6 lg:col-span-2">
            @if ($editing)
                <form wire:submit="save" class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                    <h2 class="font-semibold">Modifier ma fiche</h2>
                    <x-field-inputs :definitions="$this->fields->where('player_editable', true)" model="values" />
                    <div class="flex gap-3">
                        <button type="submit" class="btn-primary">Enregistrer</button>
                        <button type="button" wire:click="cancel" class="btn-secondary">Annuler</button>
                    </div>
                </form>
            @else
                <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <h2 class="font-semibold">Caractéristiques</h2>
                        @if ($this->canPlay && $this->fields->where('player_editable', true)->isNotEmpty())
                            <button type="button" wire:click="edit" class="link text-sm">Modifier</button>
                        @endif
                    </div>
                    @php($filled = $others->filter(fn ($definition) => $entity->fieldValue($definition) !== null))
                    @if ($filled->isEmpty())
                        <p class="text-sm text-stone-600">Rien de renseigné pour l'instant.</p>
                    @else
                        <dl class="space-y-1">
                            @foreach ($filled as $definition)
                                <div @class(['flex justify-between gap-3 border-b border-stone-100 pb-1', 'flex-col' => $definition->type === \App\Enums\FieldType::LongText])>
                                    <dt class="text-sm text-stone-600">{{ $definition->name }}</dt>
                                    <dd class="font-medium">
                                        @if ($definition->type === \App\Enums\FieldType::LongText)
                                            <span class="font-normal text-stone-700">{{ \App\Support\EntityLinks::plain($entity->fieldValue($definition)) }}</span>
                                        @else
                                            {{ $definition->type->format($entity->fieldValue($definition)) }}
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                </div>
            @endif

            @if ($entity->description)
                <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-2 font-semibold">Description</h2>
                    <div class="text-stone-700">{{ \App\Support\EntityLinks::render($entity->description, $campaign, $this->knownLink(...)) }}</div>
                </div>
            @endif

            @php($grants = $this->grants)
            @foreach (['knowledge' => 'Connaissances', 'possession' => 'Possessions', 'rule' => 'Règles', 'document' => 'Documents'] as $section => $sectionTitle)
                @php($items = $grants->filter(fn ($grant) => $section === 'knowledge' ? in_array($grant->kind, ['entity', 'information'], true) : $grant->kind === $section))
                <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm" id="section-{{ $section }}" wire:key="section-{{ $section }}">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <h2 class="font-semibold">{{ $sectionTitle }}</h2>
                        @if ($this->canAdd && in_array($section, ['knowledge', 'possession'], true) && $addKind !== ($section === 'knowledge' ? 'information' : 'possession'))
                            <button type="button" wire:click="openAdd('{{ $section === 'knowledge' ? 'information' : 'possession' }}')" class="text-sm text-codex hover:underline">{{ $section === 'knowledge' ? '+ Noter' : '+ Ajouter un objet' }}</button>
                        @endif
                    </div>
                    @if ($this->canAdd && $addKind !== '' && $addKind === ($section === 'knowledge' ? 'information' : ($section === 'possession' ? 'possession' : null)))
                        <form wire:submit="addOwn" class="mb-4 space-y-2 rounded-lg bg-codex-soft p-3">
                            <div class="flex flex-wrap gap-2">
                                <div class="min-w-48 flex-1">
                                    <label for="add-title" class="label">{{ $addKind === 'possession' ? 'Objet' : 'Ce que vous avez appris' }}</label>
                                    <input id="add-title" type="text" wire:model="addTitle" class="field py-1.5 text-sm" maxlength="200" autocomplete="off" placeholder="{{ $addKind === 'possession' ? 'Revolver .38' : 'Le professeur cache quelque chose' }}">
                                </div>
                                @if ($addKind === 'possession')
                                    <div class="w-24">
                                        <label for="add-quantity" class="label">Quantité</label>
                                        <input id="add-quantity" type="number" min="1" wire:model="addQuantity" class="field py-1.5 text-sm">
                                    </div>
                                @endif
                            </div>
                            <div>
                                <label for="add-body" class="label">Détail <span class="font-normal text-stone-500">(facultatif)</span></label>
                                <textarea id="add-body" wire:model="addBody" rows="2" class="field text-sm" maxlength="5000"></textarea>
                            </div>
                            @error('addTitle') <p class="error">{{ $message }}</p> @enderror
                            @error('addQuantity') <p class="error">{{ $message }}</p> @enderror
                            <div class="flex flex-wrap items-center gap-3">
                                <button type="submit" class="btn-primary min-h-0 py-1.5 text-sm">{{ $addKind === 'possession' ? 'Ajouter' : 'Noter' }}</button>
                                <button type="button" wire:click="openAdd('')" class="text-sm text-stone-600 hover:underline">Annuler</button>
                                <p class="text-xs text-stone-600">{{ $addKind === 'possession' ? 'Le MJ en est informé et le valide.' : 'Le MJ en est informé.' }}</p>
                            </div>
                        </form>
                    @endif
                    @if ($items->isEmpty())
                        <p class="text-sm text-stone-600">
                            @switch($section)
                                @case('knowledge') Rien de révélé pour l'instant. @break
                                @case('possession') Aucun objet reçu. @break
                                @case('rule') Aucune règle ouverte par le MJ. @break
                                @default Aucun document reçu.
                            @endswitch
                        </p>
                    @else
                        <ul class="space-y-3">
                            @foreach ($items as $grant)
                                <li wire:key="grant-{{ $grant->id }}" class="flex items-start gap-3">
                                    <div class="min-w-0 flex-1">
                                        @switch($grant->kind)
                                            @case('entity')
                                                <a href="{{ route('characters.entity', [$campaign, $character, $grant->entity]) }}" class="link font-medium" wire:navigate>{{ $grant->entity->name }}</a>
                                                <span class="text-xs text-stone-500">{{ $grant->entity->type->name }}</span>
                                                @if ($grant->entity->summary)
                                                    <p class="text-sm text-stone-600">{{ $grant->entity->summary }}</p>
                                                @endif
                                                @break
                                            @case('rule')
                                                <details>
                                                    <summary class="cursor-pointer font-medium text-codex">{{ $grant->rule->title }} @if ($grant->rule->category)<span class="text-xs font-normal text-stone-500">{{ $grant->rule->category }}</span>@endif</summary>
                                                    @if ($grant->rule->summary)
                                                        <p class="mt-1 text-sm font-medium text-stone-700">{{ $grant->rule->summary }}</p>
                                                    @endif
                                                    @if ($grant->rule->procedure)
                                                        <div class="mt-1 text-sm text-stone-700">{{ \App\Support\EntityLinks::render($grant->rule->procedure, $campaign, $this->knownLink(...)) }}</div>
                                                    @endif
                                                </details>
                                                @break
                                            @case('document')
                                                <a href="{{ route('characters.document', [$campaign, $character, $grant->document]) }}" target="_blank" class="link font-medium">{{ $grant->document->title }}</a>
                                                <span class="text-xs text-stone-500">{{ $grant->document->isPdf() ? 'PDF' : 'Image' }}</span>
                                                @break
                                            @default
                                                <p class="font-medium">{{ $grant->label() }}</p>
                                                @if ($grant->body)
                                                    <p class="text-sm text-stone-700">{{ \App\Support\EntityLinks::render($grant->body, $campaign, $this->knownLink(...)) }}</p>
                                                @endif
                                        @endswitch
                                        <p class="text-xs text-stone-400">
                                            {{ $grant->created_at->locale('fr')->isoFormat('D MMM YYYY, HH:mm') }}
                                            @if ($grant->isPending())
                                                · <span class="font-medium text-flow">ajouté par le joueur, en attente du MJ</span>
                                            @elseif ($grant->added_by_player)
                                                · {{ $grant->kind === 'possession' ? 'ajouté par le joueur, validé' : 'noté par le joueur' }}
                                            @endif
                                        </p>
                                    </div>
                                    @if ($this->isGameMaster && $grant->isPending())
                                        <button type="button" wire:click="validateGrant({{ $grant->id }})" class="shrink-0 text-xs font-medium text-codex hover:underline">Valider</button>
                                    @endif
                                    @if ($this->canAdd && $grant->added_by_player)
                                        <button type="button" wire:click="removeOwn({{ $grant->id }})" wire:confirm="Effacer « {{ $grant->label() }} » de votre fiche ?" class="shrink-0 text-xs text-stone-500 hover:text-red-700">Effacer</button>
                                    @endif
                                    @if ($this->canExchange && $exchangeGrantId !== $grant->id && ! $grant->isPending())
                                        <button type="button" wire:click="startExchange({{ $grant->id }})" class="shrink-0 text-xs text-codex hover:underline">{{ $grant->kind === 'possession' ? 'Donner' : 'Transmettre' }}</button>
                                    @endif
                                    @if ($this->isGameMaster)
                                        <button type="button" wire:click="revoke({{ $grant->id }})" wire:confirm="{{ in_array($grant->kind, ['entity', 'rule'], true) ? 'Cacher à nouveau cet élément au personnage ?' : 'Retirer cet élément au personnage ?' }}" class="shrink-0 text-xs text-red-700 hover:underline">{{ in_array($grant->kind, ['entity', 'rule'], true) ? 'Cacher' : 'Retirer' }}</button>
                                    @endif
                                </li>
                                @if ($exchangeGrantId === $grant->id)
                                    <li wire:key="exchange-{{ $grant->id }}">
                                        <form wire:submit="exchange" class="flex flex-wrap items-end gap-2 rounded-lg bg-codex-soft p-3">
                                            <div class="min-w-40 flex-1">
                                                <label for="exchange-to" class="label">{{ $grant->kind === 'possession' ? 'Donner à' : 'Transmettre à' }}</label>
                                                <select id="exchange-to" wire:model="exchangeTo" class="field py-1.5 text-sm">
                                                    <option value="">Choisir…</option>
                                                    @foreach ($this->companions as $companion)
                                                        <option value="{{ $companion->id }}">{{ $companion->entity->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if ($grant->kind === 'possession' && $grant->quantity > 1)
                                                <div class="w-24">
                                                    <label for="exchange-quantity" class="label">Combien</label>
                                                    <input id="exchange-quantity" type="number" min="1" max="{{ $grant->quantity }}" wire:model="exchangeQuantity" class="field py-1.5 text-sm">
                                                </div>
                                            @endif
                                            <button type="submit" class="btn-primary min-h-0 py-1.5 text-sm">{{ $grant->kind === 'possession' ? 'Donner' : 'Transmettre' }}</button>
                                            <button type="button" wire:click="startExchange(null)" class="text-sm text-stone-600 hover:underline">Annuler</button>
                                            <p class="w-full text-xs text-stone-600">
                                                {{ $grant->kind === 'possession' ? "L'objet quitte votre fiche." : 'Vous gardez cette connaissance.' }} Le MJ en est informé.
                                            </p>
                                            @error('exchangeTo') <p class="error w-full">{{ $message }}</p> @enderror
                                            @error('exchangeQuantity') <p class="error w-full">{{ $message }}</p> @enderror
                                            @error('exchange') <p class="error w-full">{{ $message }}</p> @enderror
                                        </form>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
            @if ($flashExchange)
                <p class="rounded-lg bg-green-50 px-4 py-2 text-sm text-green-800" role="status">{{ $flashExchange }}</p>
            @endif

            @if ($this->isGameMaster)
                <livewire:characters.give :campaign="$campaign" :character-id="$character->id" :key="'give-character-'.$character->id" />
            @endif
        </section>

        <section class="space-y-6 lg:col-span-3">
            <div class="rounded-xl border border-stone-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 px-4 py-3">
                    <h2 class="font-semibold">Feuille de personnage</h2>
                    @if ($character->hasSheet())
                        <a href="{{ route('characters.sheet', [$campaign, $character]) }}" target="_blank" class="link text-sm">Ouvrir en grand</a>
                    @endif
                </div>
                @if ($character->hasSheet())
                    <iframe src="{{ route('characters.sheet', [$campaign, $character]) }}" title="Feuille de {{ $entity->name }}" class="h-[75vh] w-full rounded-b-xl border-t border-stone-200"></iframe>
                @else
                    <p class="border-t border-stone-100 px-4 py-6 text-sm text-stone-600">Le MJ n'a pas encore joint de feuille PDF.</p>
                @endif
            </div>

            {{-- Notes : celles du joueur et celles qu'on a partagées avec lui. --}}
            <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-1 font-semibold">Notes</h2>
                @if ($this->isOwner)
                    <form wire:submit="saveNote" class="mb-4 space-y-3">
                        <label for="noteBody" class="sr-only">Note</label>
                        <textarea id="noteBody" wire:model="noteBody" rows="3" class="field" placeholder="Ce qui s'est passé, ce que vous soupçonnez… Citez une fiche connue avec [[Nom]]."></textarea>
                        @error('noteBody') <p class="error">{{ $message }}</p> @enderror
                        <fieldset>
                            <legend class="label">Qui peut la lire ?</legend>
                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm">
                                @foreach (\App\Models\CharacterNote::VISIBILITIES as $value => $label)
                                    @continue($value === 'players' && $this->companions->isEmpty())
                                    <label class="flex items-center gap-2"><input type="radio" wire:model.live="noteVisibility" value="{{ $value }}"> {{ $label }}</label>
                                @endforeach
                            </div>
                        </fieldset>
                        @if ($noteVisibility === 'players')
                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm">
                                @foreach ($this->companions as $companion)
                                    <label wire:key="share-{{ $companion->id }}" class="flex items-center gap-2"><input type="checkbox" wire:model="noteShares" value="{{ $companion->id }}"> {{ $companion->entity->name }} <span class="text-xs text-stone-500">{{ $companion->player?->name }}</span></label>
                                @endforeach
                            </div>
                            @error('noteShares') <p class="error">{{ $message }}</p> @enderror
                        @endif
                        @if ($noteVisibility === 'private')
                            <p class="text-sm font-medium text-flow">Note privée : personne d'autre, pas même le MJ, ne pourra la lire.</p>
                        @endif
                        <div class="flex gap-3">
                            <button type="submit" class="btn-primary">{{ $editingNoteId ? 'Enregistrer' : 'Ajouter la note' }}</button>
                            @if ($editingNoteId)
                                <button type="button" wire:click="cancelNote" class="btn-secondary">Annuler</button>
                            @endif
                        </div>
                    </form>
                @endif

                @if ($this->notes->isEmpty())
                    <p class="text-sm text-stone-600">Aucune note pour l'instant.</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($this->notes as $note)
                            <li wire:key="note-{{ $note->id }}" @class(['rounded-lg border p-3', 'border-flow/40 bg-flow/5' => $note->visibility === 'private', 'border-stone-200' => $note->visibility !== 'private'])>
                                <div class="mb-1 flex flex-wrap items-baseline gap-x-2 text-xs text-stone-500">
                                    <span class="font-medium text-ink">{{ $note->character->entity->name }}</span>
                                    <span>{{ $note->created_at->locale('fr')->isoFormat('D MMM YYYY, HH:mm') }}</span>
                                    @if ($note->playSession)
                                        <span>· séance du {{ $note->playSession->started_at?->locale('fr')->isoFormat('D MMM') }}</span>
                                    @endif
                                    <span @class(['rounded-full px-1.5 py-0.5 font-medium', 'bg-flow/10 text-flow' => $note->visibility === 'private', 'bg-stone-100 text-stone-600' => $note->visibility !== 'private'])>
                                        {{ $note->visibility === 'players' ? 'Partagée avec '.$note->sharedWith->map(fn ($c) => $c->entity->name)->implode(', ') : $note->visibilityLabel() }}
                                    </span>
                                    @if ($this->isOwner && $note->user_id === auth()->id())
                                        <span class="ml-auto flex gap-3">
                                            <button type="button" wire:click="editNote({{ $note->id }})" class="link">Modifier</button>
                                            <button type="button" wire:click="deleteNote({{ $note->id }})" wire:confirm="Supprimer cette note ?" class="text-red-700 hover:underline">Supprimer</button>
                                        </span>
                                    @endif
                                </div>
                                <div class="text-sm text-stone-700">{{ \App\Support\EntityLinks::render($note->body, $campaign, $this->knownLink(...)) }}</div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Intentions : ce que le joueur veut tenter ; elles arrivent dans « À jouer » du MJ. --}}
            <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-1 font-semibold">À jouer</h2>
                <p class="mb-3 text-sm text-stone-600">{{ $this->isOwner ? 'Dites au MJ ce que vous voulez tenter, ou demandez à tester une règle : il le verra pendant la partie.' : 'Les intentions de ce personnage.' }}</p>
                @if ($this->isOwner)
                    <form wire:submit="addIntention" class="mb-4 space-y-3">
                        <label for="intentionBody" class="sr-only">Intention</label>
                        <input id="intentionBody" type="text" wire:model="intentionBody" class="field" placeholder="Fouiller le bureau de Jackson…">
                        @error('intentionBody') <p class="error">{{ $message }}</p> @enderror
                        @if ($this->publicRules->isNotEmpty())
                            <label for="intentionRuleId" class="sr-only">Règle à tester</label>
                            <select id="intentionRuleId" wire:model="intentionRuleId" class="field">
                                <option value="">Aucune règle</option>
                                @foreach ($this->publicRules as $rule)
                                    <option value="{{ $rule->id }}">Tester : {{ $rule->title }}</option>
                                @endforeach
                            </select>
                        @endif
                        <button type="submit" class="btn-secondary">Envoyer au MJ</button>
                    </form>
                @endif
                @if ($this->intentions->isEmpty())
                    <p class="text-sm text-stone-600">Aucune intention pour l'instant.</p>
                @else
                    <ul class="space-y-1 text-sm">
                        @foreach ($this->intentions as $intention)
                            <li wire:key="intention-{{ $intention->id }}" class="flex items-baseline gap-2">
                                <span @class(['line-through text-stone-400' => $intention->done_at])>{{ $intention->body }}</span>
                                <span class="ml-auto shrink-0 text-xs {{ $intention->done_at ? 'text-green-800' : 'text-stone-500' }}">{{ $intention->done_at ? 'jouée' : 'en attente' }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Journal du personnage : seulement ce qui le concerne. --}}
            <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-2 font-semibold">Journal</h2>
                @if ($this->journal->isEmpty())
                    <p class="text-sm text-stone-600">Rien pour l'instant.</p>
                @else
                    <ol class="space-y-1 text-sm">
                        @foreach ($this->journal as $event)
                            <li wire:key="journal-{{ $event->id }}" class="flex flex-wrap gap-x-2">
                                <span class="text-stone-500">{{ $event->created_at->locale('fr')->isoFormat('D MMM, HH:mm') }}</span>
                                @if ($event->isExchange())
                                    <span>{{ $event->exchangeTitle() }}</span>
                                    <span class="font-medium">{{ $event->subject_label }}</span>
                                @elseif ($event->isPlayerAddition())
                                    <span>{{ $event->event === 'updated' ? 'Le MJ a validé' : 'Le joueur '.$event->verb() }} :</span>
                                    <span class="font-medium">{{ \Illuminate\Support\Str::beforeLast($event->subject_label, ' à '.$entity->name) }}</span>
                                @else
                                    <span>Le MJ {{ $event->verb() }} {{ mb_strtolower($event->subjectName()) }}</span>
                                    <span class="font-medium">{{ \Illuminate\Support\Str::beforeLast($event->subject_label, ' à '.$entity->name) }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </section>
    </div>
</div>
