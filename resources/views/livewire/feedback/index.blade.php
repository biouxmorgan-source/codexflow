<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <h1 class="text-2xl font-semibold">{{ __('Avis des joueurs') }}</h1>
    <p class="mb-6 text-sm text-stone-600">{{ __('En fin de séance ou de campagne, demandez à vos joueurs une note en étoiles et ce qu’ils ont aimé : de quoi savoir ce qui les intéresse.') }}</p>

    @if (session('status'))
        <p class="mb-6 rounded-md bg-codex-soft px-3 py-2 text-sm text-codex" role="status">{{ session('status') }}</p>
    @endif

    <div class="grid gap-6 *:min-w-0 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            @forelse ($this->requests as $request)
                @php($average = $request->average())
                @php($count = $request->responses->count())
                <article wire:key="feedback-{{ $request->id }}" class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div class="min-w-0">
                            <h2 class="font-semibold">{{ $request->subject() }}</h2>
                            <p class="text-xs text-stone-500">
                                {{ __('Demandé le :date', ['date' => $request->created_at->isoFormat('LL')]) }}
                                · {{ $request->anonymous ? __('anonyme') : __('nominatif') }}
                                · {{ $request->isOpen() ? __('ouverte') : __('close') }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            @if ($request->isOpen())
                                <button type="button" wire:click="close({{ $request->id }})" class="btn-secondary min-h-0 py-1 text-sm">{{ __('Clore') }}</button>
                            @else
                                <button type="button" wire:click="reopen({{ $request->id }})" class="btn-secondary min-h-0 py-1 text-sm">{{ __('Rouvrir') }}</button>
                            @endif
                            <button type="button" wire:click="delete({{ $request->id }})" wire:confirm="{{ __('Supprimer cette demande et ses réponses ?') }}" class="px-1 text-sm text-red-700 hover:underline">{{ __('Supprimer') }}</button>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-6">
                        <div>
                            <p class="text-3xl font-semibold text-codex">{{ $average !== null ? \Illuminate\Support\Number::format($average, 1, locale: app()->getLocale()) : '–' }}<span class="text-base font-normal text-stone-500">/5</span></p>
                            @if ($average !== null)
                                <x-stars :value="$average" />
                            @endif
                            <p class="mt-1 text-xs text-stone-500">{{ __('Réponses : :count sur :total', ['count' => $count, 'total' => $this->playerCount]) }}</p>
                        </div>
                        @if ($count > 0)
                            <dl class="min-w-48 flex-1 space-y-1 text-xs">
                                @foreach ($request->distribution() as $stars => $number)
                                    <div class="flex items-center gap-2">
                                        <dt class="w-14 shrink-0 text-stone-600">{{ trans_choice(':count étoile|:count étoiles', $stars) }}</dt>
                                        <dd class="flex flex-1 items-center gap-2">
                                            <span class="h-2 flex-1 overflow-hidden rounded-full bg-stone-100"><span class="block h-full rounded-full bg-amber-500" style="width: {{ round($number / $count * 100) }}%"></span></span>
                                            <span class="w-5 text-right text-stone-600">{{ $number }}</span>
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                    </div>

                    @php($comments = $request->responses->filter(fn ($response) => $response->liked || $response->improve))
                    @if ($comments->isNotEmpty())
                        {{-- Une demande anonyme est mélangée pour que l'ordre ne trahisse pas qui a répondu en premier. --}}
                        <ul class="mt-4 space-y-3 border-t border-stone-100 pt-4 text-sm">
                            @foreach ($request->anonymous ? $comments->shuffle() : $comments as $response)
                                <li class="rounded-lg bg-stone-50 p-3">
                                    <p class="mb-1 flex flex-wrap items-center gap-2 text-xs text-stone-500">
                                        <x-stars :value="$response->rating" />
                                        @unless ($request->anonymous)
                                            <span>{{ $response->author?->name }}</span>
                                        @endunless
                                    </p>
                                    @if ($response->liked)
                                        <p><span class="font-medium">{{ __('A aimé :') }}</span> {{ $response->liked }}</p>
                                    @endif
                                    @if ($response->improve)
                                        <p class="mt-1"><span class="font-medium">{{ __('À améliorer :') }}</span> {{ $response->improve }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
                    <p class="text-stone-600">{{ __('Aucune demande d’avis pour l’instant.') }}</p>
                </div>
            @endforelse
        </div>

        <form wire:submit="ask" class="space-y-4 self-start rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="font-semibold">{{ __('Demander un avis') }}</h2>
            <div>
                <label for="subject" class="label">{{ __('Sur quoi ?') }}</label>
                <select id="subject" wire:model="subject" class="field">
                    <option value="">{{ __('Toute la campagne') }}</option>
                    @foreach ($this->sessions as $playSession)
                        <option value="{{ $playSession->id }}">{{ $playSession->label() }}</option>
                    @endforeach
                </select>
                @error('subject') <p class="error">{{ $message }}</p> @enderror
            </div>
            <label class="flex items-start gap-2 text-sm">
                <input type="checkbox" wire:model="anonymous" class="mt-1">
                <span>{{ __('Réponses anonymes') }} <span class="block text-xs text-stone-500">{{ __('Vous verrez les notes et les commentaires, pas qui les a écrits. Les joueurs le savent avant de répondre.') }}</span></span>
            </label>
            @if ($this->playerCount === 0)
                <p class="text-xs text-stone-500">{{ __('Aucun joueur dans cette campagne pour l’instant.') }}</p>
            @endif
            <button type="submit" class="btn-primary">{{ __('Envoyer aux joueurs') }}</button>
        </form>
    </div>
</div>
