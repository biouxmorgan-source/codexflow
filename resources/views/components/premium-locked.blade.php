@props(['title'])
{{-- Fonction Premium que la formule du propriétaire ne comprend pas (ou plus) : signalée, jamais cachée. --}}
<section {{ $attributes->merge(['class' => 'rounded-xl border border-dashed border-stone-300 bg-stone-50 p-6 text-stone-500']) }}>
    <h2 class="mb-1 flex items-center gap-1 font-semibold">{{ $title }} <x-premium /></h2>
    <p class="text-sm">{{ __('Fonction Premium, non comprise dans la formule du propriétaire de la campagne. Rien n’est effacé : tout revient dès le passage à Premium.') }}</p>
</section>
