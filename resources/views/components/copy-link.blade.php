@props(['url', 'label'])
{{-- Un lien à copier, avec son libellé. --}}
<div x-data="{ copied: false }">
    <p class="mb-1 text-xs font-medium text-stone-600">{{ $label }}</p>
    <div class="flex items-center gap-2">
        <input type="text" readonly value="{{ $url }}" x-ref="link" x-on:focus="$el.select()" class="field min-w-0 flex-1 py-1 font-mono text-xs" aria-label="{{ $label }}">
        <button type="button" class="btn-secondary shrink-0 py-1 text-xs" x-on:click="navigator.clipboard.writeText($refs.link.value).then(() => { copied = true; setTimeout(() => copied = false, 2000) })">
            <span x-show="! copied">{{ __('Copier le lien') }}</span>
            <span x-show="copied" x-cloak>{{ __('Copié !') }}</span>
        </button>
    </div>
</div>
