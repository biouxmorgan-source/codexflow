@php($version = \App\Support\ContentDeclarations::current())
<fieldset {{ $attributes->merge(['class' => 'rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950']) }}>
    <legend class="sr-only">{{ __('Déclaration de droits') }}</legend>
    <label class="flex items-start gap-3">
        <input type="checkbox" wire:model="rightsDeclared" class="mt-1 shrink-0" required>
        <span class="space-y-2">
            @foreach (\App\Support\ContentDeclarations::text('import', $version) as $paragraph)
                <span class="block">{{ __($paragraph) }}</span>
            @endforeach
        </span>
    </label>
    @error('rightsDeclared') <p class="error mt-2">{{ $message }}</p> @enderror
    <p class="mt-2 text-xs text-amber-900/80">
        {{ __('Déclaration version :version, enregistrée avec la date de l’import.', ['version' => $version]) }}
        <a href="{{ route('privacy') }}#contenus-utilisateurs" class="underline">{{ __('Conditions d’utilisation : contenus utilisateurs et propriété intellectuelle') }}</a>
    </p>
</fieldset>
