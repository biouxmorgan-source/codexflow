<div>
    <h1 class="text-2xl font-semibold">{{ __('Administration') }}</h1>
    <p class="mt-1 mb-4 text-sm text-stone-600">{{ __('Les cahiers de recette de chaque version, pour garder la trace des tests et des notes.') }}</p>
    <x-admin-nav />

    @if ($this->recettes->isEmpty())
        <p class="rounded-xl border border-dashed border-stone-300 p-6 text-center text-stone-500">{{ __('Aucun cahier de recette pour l’instant.') }}</p>
    @else
        <ul class="mb-6 flex flex-wrap gap-2">
            @foreach ($this->recettes as $item)
                <li wire:key="recette-{{ $item->id }}">
                    <button type="button" wire:click="$set('recetteId', {{ $item->id }})" @class(['rounded-lg border px-3 py-2 text-left text-sm', 'border-codex bg-codex-soft' => $item->id === $recetteId, 'border-stone-200 bg-white hover:border-codex' => $item->id !== $recetteId])>
                        <span class="block font-medium">{{ __('Recette v:version', ['version' => $item->version]) }}</span>
                        <span class="text-xs text-stone-500">
                            {{ __('Testée le :date · :count lignes', ['date' => $item->tested_on->isoFormat('L'), 'count' => $item->items_count]) }}
                            @if ($item->score !== null)
                                · {{ \Illuminate\Support\Number::format($item->score, 1, locale: app()->getLocale()) }}/100
                            @endif
                        </span>
                    </button>
                </li>
            @endforeach
        </ul>
    @endif

    @if ($recette = $this->recette)
        <section class="mb-6 rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-baseline justify-between gap-3">
                <h2 class="text-xl font-semibold">{{ __('Recette v:version', ['version' => $recette->version]) }}</h2>
                @if ($recette->score !== null)
                    <p class="text-sm">
                        <span class="text-2xl font-semibold tabular-nums">{{ \Illuminate\Support\Number::format($recette->score, 1, locale: app()->getLocale()) }}</span>/100
                        @if ($recette->score_before !== null)
                            <span class="text-stone-500">({{ __('avant corrections : :score', ['score' => \Illuminate\Support\Number::format($recette->score_before, 1, locale: app()->getLocale())]) }})</span>
                        @endif
                    </p>
                @endif
            </div>
            @if ($recette->summary)
                <details class="mt-3" open>
                    <summary class="cursor-pointer text-sm font-medium text-codex">{{ __('Synthèse') }}</summary>
                    <div class="recette-summary mt-3 space-y-3 text-sm text-stone-700 [&_ul]:list-disc [&_ul]:space-y-1 [&_ul]:pl-5 [&_h3]:mt-4 [&_h3]:font-semibold [&_h3]:text-ink [&_table]:w-full [&_table]:border-collapse [&_td]:border [&_td]:border-stone-200 [&_td]:px-2 [&_td]:py-1 [&_th]:border [&_th]:border-stone-200 [&_th]:bg-stone-50 [&_th]:px-2 [&_th]:py-1 [&_th]:text-left">
                        {!! \Illuminate\Support\Str::markdown($recette->summary, ['html_input' => 'escape', 'allow_unsafe_links' => false]) !!}
                    </div>
                </details>
            @endif
        </section>

        <div class="mb-4 flex flex-wrap items-end gap-3">
            <div class="min-w-56 flex-1">
                <label for="recette-search" class="label">{{ __('Rechercher une fonctionnalité') }}</label>
                <input id="recette-search" type="search" wire:model.live.debounce.300ms="search" class="field" autocomplete="off">
            </div>
            <label class="flex items-center gap-2 pb-2 text-sm">
                <input type="checkbox" wire:model.live="belowTarget"> {{ __('Seulement les notes sous 100') }}
            </label>
        </div>

        @forelse ($this->sections as $section => $items)
            <section class="mb-6" wire:key="section-{{ md5($section) }}">
                <h3 class="mb-2 font-semibold">{{ $section }}</h3>
                <div class="relative overflow-x-auto rounded-xl border border-stone-200 bg-white shadow-sm">
                    <table class="w-full min-w-[64rem] text-left text-sm">
                        <thead class="border-b border-stone-200 bg-stone-50 text-xs text-stone-600">
                            <tr>
                                <th class="w-48 px-3 py-2">{{ __('Fonctionnalité') }}</th>
                                <th class="px-3 py-2">{{ __('Résultat attendu') }}</th>
                                <th class="px-3 py-2">{{ __('Tests réalisés') }}</th>
                                <th class="px-3 py-2">{{ __('Écarts constatés ou bug') }}</th>
                                <th class="w-32 px-3 py-2">{{ __('Résultat obtenu') }}</th>
                                <th class="w-24 px-3 py-2 text-right">{{ __('Note /100') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 align-top">
                            @foreach ($items as $item)
                                <tr wire:key="item-{{ $item->id }}">
                                    <td class="px-3 py-2 font-medium">{{ $item->feature }}</td>
                                    <td class="px-3 py-2">{{ $item->expected }}</td>
                                    <td class="px-3 py-2 text-stone-600">{!! \Illuminate\Support\Str::inlineMarkdown($item->tests, ['html_input' => 'escape', 'allow_unsafe_links' => false]) !!}</td>
                                    <td class="px-3 py-2">{!! \Illuminate\Support\Str::inlineMarkdown($item->gaps, ['html_input' => 'escape', 'allow_unsafe_links' => false]) !!}</td>
                                    <td class="px-3 py-2">{{ $item->result }}</td>
                                    <td class="px-3 py-2 text-right whitespace-nowrap tabular-nums">
                                        <span @class(['font-semibold', 'text-green-800' => $item->score === 100, 'text-amber-800' => $item->score !== null && $item->score < 100 && $item->score >= 85, 'text-red-700' => $item->score !== null && $item->score < 85])>{{ $item->score ?? 'n/a' }}</span>
                                        @if ($item->score_before !== null)
                                            <span class="block text-xs text-stone-500">{{ __('avant : :score', ['score' => $item->score_before]) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @empty
            <p class="text-sm text-stone-500">{{ __('Aucune ligne ne correspond.') }}</p>
        @endforelse
    @endif
</div>
