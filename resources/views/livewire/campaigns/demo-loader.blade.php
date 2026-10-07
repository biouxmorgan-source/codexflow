{{-- Charger la démonstration, dans la langue de l'interface ou une autre. --}}
<span class="inline-flex flex-wrap items-center gap-2">
    <label for="demoLocale{{ $suffix ?? '' }}" class="sr-only">{{ __('Langue de la démonstration') }}</label>
    <select id="demoLocale{{ $suffix ?? '' }}" wire:model="demoLocale" class="rounded-md border border-stone-300 bg-white py-1 pr-8 pl-2 text-sm">
        @foreach (\App\Actions\Demo\LoadDemoCampaign::locales() as $code)
            <option value="{{ $code }}">{{ \App\Support\Locale::available()[$code] }}</option>
        @endforeach
    </select>
    <button type="button" wire:click="loadDemo" wire:loading.attr="disabled" wire:target="loadDemo" class="{{ $class }}">
        <span wire:loading.remove wire:target="loadDemo">{{ __('Charger la démonstration') }}</span>
        <span wire:loading wire:target="loadDemo">{{ __('Chargement…') }}</span>
    </button>
</span>
