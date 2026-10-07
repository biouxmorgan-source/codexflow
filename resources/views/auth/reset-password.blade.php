<x-layouts.guest :title="__('Nouveau mot de passe')">
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div>
            <label for="email" class="label">{{ __('Adresse e-mail') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autocomplete="username" class="field">
            @error('email') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="label">{{ __('Nouveau mot de passe') }}</label>
            <input id="password" name="password" type="password" required autofocus autocomplete="new-password" class="field">
            @error('password') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password_confirmation" class="label">{{ __('Confirmer le mot de passe') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="field">
        </div>
        <button type="submit" class="btn-primary w-full">{{ __('Enregistrer') }}</button>
    </form>
</x-layouts.guest>
