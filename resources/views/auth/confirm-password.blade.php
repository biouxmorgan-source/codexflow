<x-layouts.guest :title="__('Confirmez votre mot de passe')">
    <p class="mb-4 text-sm text-stone-600">{{ __('Cette partie est protégée : saisissez à nouveau votre mot de passe pour continuer.') }}</p>

    <form method="POST" action="{{ route('password.confirm.store') }}" class="space-y-4">
        @csrf
        <div>
            <label for="password" class="label">{{ __('Mot de passe') }}</label>
            <input id="password" name="password" type="password" required autofocus autocomplete="current-password" class="field">
            @error('password') <p class="error">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="btn-primary w-full">{{ __('Confirmer') }}</button>
    </form>
</x-layouts.guest>
