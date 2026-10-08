<x-layouts.guest :title="__('Double authentification')">
    {{-- Sans JavaScript (les pages invité ne chargent pas Alpine) : le code de secours est dans un volet. --}}
    <p class="mb-4 text-sm text-stone-600">{{ __('Saisissez le code à 6 chiffres affiché par votre application d’authentification.') }}</p>

    <form method="POST" action="{{ route('two-factor.login.store') }}" class="space-y-4">
        @csrf
        <div>
            <label for="code" class="label">{{ __('Code') }}</label>
            <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" @unless ($errors->has('recovery_code')) autofocus @endunless class="field">
        </div>
        @error('code') <p class="error">{{ $message }}</p> @enderror

        <details class="text-sm" @if ($errors->has('recovery_code')) open @endif>
            <summary class="link cursor-pointer">{{ __('Utiliser un code de secours') }}</summary>
            <div class="mt-3">
                <p class="mb-2 text-stone-600">{{ __('Saisissez l’un de vos codes de secours. Chaque code ne sert qu’une fois.') }}</p>
                <label for="recovery_code" class="label">{{ __('Code de secours') }}</label>
                <input id="recovery_code" name="recovery_code" type="text" autocomplete="off" @if ($errors->has('recovery_code')) autofocus @endif class="field">
                @error('recovery_code') <p class="error">{{ $message }}</p> @enderror
            </div>
        </details>

        <button type="submit" class="btn-primary w-full">{{ __('Se connecter') }}</button>
    </form>
</x-layouts.guest>
