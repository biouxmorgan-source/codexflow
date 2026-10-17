@props(['url', 'label', 'qr' => false])
{{-- Un lien à copier, avec son libellé ; avec $qr, un QR code à montrer pour le scanner au téléphone. --}}
<div x-data="{ copied: false, qr: false }">
    <p class="mb-1 text-xs font-medium text-stone-600">{{ $label }}</p>
    <div class="flex items-center gap-2">
        <input type="text" readonly value="{{ $url }}" x-ref="link" x-on:focus="$el.select()" class="field min-w-0 flex-1 py-1 font-mono text-xs" aria-label="{{ $label }}">
        <button type="button" class="btn-secondary shrink-0 py-1 text-xs" x-on:click="navigator.clipboard.writeText($refs.link.value).then(() => { copied = true; setTimeout(() => copied = false, 2000) })">
            <span x-show="! copied">{{ __('Copier le lien') }}</span>
            <span x-show="copied" x-cloak>{{ __('Copié !') }}</span>
        </button>
        @if ($qr)
            <button type="button" class="btn-secondary shrink-0 py-1 text-xs" x-on:click="qr = ! qr" :aria-expanded="qr">{{ __('QR code') }}</button>
        @endif
    </div>
    @if ($qr)
        <div x-show="qr" x-cloak class="mt-2 inline-block rounded-lg bg-white p-2 shadow-sm ring-1 ring-stone-200" role="img" aria-label="{{ __('QR code de :label', ['label' => $label]) }}">
            {!! \App\Support\QrCode::svg($url) !!}
        </div>
    @endif
</div>
