@props(['id', 'model', 'rows' => 6, 'placeholder' => null, 'links' => true])
{{--
    Texte long avec mise en forme (Tiptap) et, sauf :links="false", liens [[…]] vers les fiches : taper « [[ »
    propose les fiches. Enregistré en Markdown ; sans JavaScript, la zone de texte reste utilisable.
--}}
<div x-data="richEditor(@js($model))" class="rich-editor">
    <div class="mb-1 flex flex-wrap gap-1" role="toolbar" aria-label="{{ __('Mise en forme') }}" x-cloak x-show="ready">
        @foreach ([
            'bold' => ['B', __('Gras'), 'font-bold'],
            'italic' => ['I', __('Italique'), 'italic'],
            'heading' => ['T', __('Intertitre'), 'font-semibold'],
            'bulletList' => ['•', __('Liste à puces'), ''],
            'orderedList' => ['1.', __('Liste numérotée'), ''],
            'blockquote' => ['❝', __('Citation'), ''],
        ] as $command => [$symbol, $label, $style])
            <button type="button" x-on:mousedown.prevent x-on:click="run('{{ $command }}')" :aria-pressed="marks.{{ $command }} ? 'true' : 'false'"
                :class="marks.{{ $command }} ? 'bg-codex-soft text-codex' : 'text-stone-600 hover:bg-stone-100'"
                class="min-w-8 rounded-md px-2 py-1 text-sm {{ $style }}" title="{{ $label }}" aria-label="{{ $label }}">{{ $symbol }}</button>
        @endforeach
        @if ($links)
            <button type="button" x-on:mousedown.prevent x-on:click="run('link')" class="rounded-md px-2 py-1 text-sm text-stone-600 hover:bg-stone-100" title="{{ __('Lier une fiche') }}">[[ ]]</button>
        @endif
    </div>
    <div wire:ignore x-ref="editor"></div>
    <textarea
        id="{{ $id }}"
        x-ref="input"
        wire:model="{{ $model }}"
        rows="{{ $rows }}"
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        x-bind:class="ready && 'hidden'"
        {{ $attributes->merge(['class' => 'field']) }}
    ></textarea>
    @if ($links)
        <p class="mt-1 text-xs text-stone-500">{!! __('Tapez :keys pour lier une autre fiche.', ['keys' => '<kbd class="rounded border border-stone-300 bg-stone-50 px-1">[[</kbd>']) !!}</p>
    @endif
</div>
