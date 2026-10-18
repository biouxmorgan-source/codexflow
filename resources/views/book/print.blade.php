<x-layouts.base :title="__('Livre des aventures').' · '.$campaign->name" :plain="true">
    <div class="min-h-screen bg-white text-stone-900">
    <div class="mx-auto max-w-3xl px-4 py-8 print:max-w-none print:p-0">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-3 rounded-lg bg-stone-100 px-4 py-3 text-sm print:hidden">
            <span>{{ __('Choisissez « Enregistrer au format PDF » comme imprimante pour obtenir un PDF.') }}</span>
            <button type="button" id="print-book" class="btn-primary">{{ __('Imprimer') }}</button>
        </div>
        <x-book-content :campaign="$campaign" :chapters="$chapters" :sessions="$sessions" :version="$version" :narrative="$campaign->adventure_book" :hints="false" />
    </div>
    </div>
    <script nonce="{{ Vite::cspNonce() }}">
        document.getElementById('print-book').addEventListener('click', () => window.print());
    </script>
</x-layouts.base>
