<x-layouts.guest :title="__('Mot de passe oublié')">
    @if (session('status'))
        <p class="mb-4 rounded-md bg-codex-soft px-3 py-2 text-sm text-codex">{{ session('status') }}</p>
    @endif

    <p class="mb-4 text-sm text-stone-600">{{ __('Indiquez votre adresse e-mail : vous recevrez un lien pour choisir un nouveau mot de passe.') }}</p>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="label">{{ __('Adresse e-mail') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="field">
            @error('email') <p class="error">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="btn-primary w-full">{{ __('Envoyer le lien') }}</button>
    </form>

    <p class="mt-6 text-center text-sm">
        <a href="{{ route('login') }}" class="link">{{ __('Retour à la connexion') }}</a>
    </p>
</x-layouts.guest>
