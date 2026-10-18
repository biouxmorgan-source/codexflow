@props(['screen', 'layout', 'arranging' => false])
{{--
    « Organiser l'écran » : le bouton qui fait apparaître les poignées des blocs, puis, une fois ouvert,
    les blocs masqués à réafficher et le retour à la disposition par défaut. La disposition est propre
    à l'utilisateur (App\Support\ScreenLayout) et purement visuelle.
--}}
@php($labels = \App\Support\ScreenLayout::labels($screen))
<button type="button" wire:click="toggleArranging" aria-pressed="{{ $arranging ? 'true' : 'false' }}" {{ $attributes->class(['btn-secondary', 'border-codex text-codex' => $arranging]) }}>
    {{ $arranging ? __('Terminé') : __('Organiser l’écran') }}
</button>
@if ($arranging)
    <div class="basis-full rounded-xl border border-codex/30 bg-codex-soft/50 p-3 text-sm" role="region" aria-label="{{ __('Organiser l’écran') }}">
        <p class="text-stone-700">{{ __('Glissez un bloc par sa poignée ⠿ (ou utilisez les flèches) pour le déplacer, y compris d’une colonne à l’autre. « Masquer » le retire de votre écran seulement : rien n’est effacé, et les joueurs comme les autres MJ gardent leur propre écran.') }}</p>
        <div class="mt-2 flex flex-wrap items-center gap-2">
            @if ($layout['hidden'] !== [])
                <span class="text-stone-500">{{ __('Blocs masqués :') }}</span>
                @foreach ($layout['hidden'] as $block)
                    <button type="button" wire:key="show-block-{{ $block }}" wire:click="showBlock('{{ $block }}')" class="rounded-md border border-stone-300 bg-white px-2 py-0.5 text-xs text-stone-700 hover:border-codex hover:text-codex">{{ __('Afficher « :name »', ['name' => $labels[$block] ?? $block]) }}</button>
                @endforeach
            @endif
            @if ($layout['custom'])
                <button type="button" wire:click="resetLayout" wire:confirm="{{ __('Revenir à la disposition par défaut de cet écran ?') }}" class="ml-auto text-xs font-medium text-stone-600 hover:text-codex hover:underline">{{ __('Disposition par défaut') }}</button>
            @endif
        </div>
    </div>
@endif
