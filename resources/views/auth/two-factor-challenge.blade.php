<x-layouts.guest :title="__('Double authentification')">
    <div x-data="{ recovery: false }">
        <p class="mb-4 text-sm text-stone-600" x-show="! recovery">{{ __('Saisissez le code à 6 chiffres affiché par votre application d’authentification.') }}</p>
        <p class="mb-4 text-sm text-stone-600" x-show="recovery" x-cloak>{{ __('Saisissez l’un de vos codes de secours. Chaque code ne sert qu’une fois.') }}</p>

        <form method="POST" action="{{ route('two-factor.login.store') }}" class="space-y-4">
            @csrf
            <div x-show="! recovery">
                <label for="code" class="label">{{ __('Code') }}</label>
                <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" autofocus class="field" x-bind:disabled="recovery">
            </div>
            <div x-show="recovery" x-cloak>
                <label for="recovery_code" class="label">{{ __('Code de secours') }}</label>
                <input id="recovery_code" name="recovery_code" type="text" autocomplete="one-time-code" class="field" x-bind:disabled="! recovery">
            </div>
            @error('code') <p class="error">{{ $message }}</p> @enderror
            @error('recovery_code') <p class="error">{{ $message }}</p> @enderror
            <button type="submit" class="btn-primary w-full">{{ __('Se connecter') }}</button>
        </form>

        <p class="mt-6 text-center text-sm">
            <button type="button" class="link" x-show="! recovery" x-on:click="recovery = true">{{ __('Utiliser un code de secours') }}</button>
            <button type="button" class="link" x-show="recovery" x-cloak x-on:click="recovery = false">{{ __('Utiliser l’application d’authentification') }}</button>
        </p>
    </div>
</x-layouts.guest>
