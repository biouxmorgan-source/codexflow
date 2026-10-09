<x-layouts.guest :title="__('Créer un compte')">
    @include('auth._invitation')
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <div>
            <label for="name" class="label">{{ __('Nom ou pseudo') }}</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="nickname" class="field">
            @error('name') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="email" class="label">{{ __('Adresse e-mail') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email', request('email')) }}" required autocomplete="username" class="field">
            @error('email') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="label">{{ __('Mot de passe') }}</label>
            <input id="password" name="password" type="password" required autocomplete="new-password" class="field">
            <p class="mt-1 text-xs text-stone-500">{{ __('Au moins 10 caractères, avec des lettres et des chiffres.') }}</p>
            @error('password') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password_confirmation" class="label">{{ __('Confirmer le mot de passe') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="field">
        </div>
        <button type="submit" class="btn-primary w-full">{{ __('Créer mon compte') }}</button>
    </form>

    <p class="mt-6 text-center text-sm">
        {{ __('Déjà inscrit ?') }} <a href="{{ route('login') }}" class="link">{{ __('Se connecter') }}</a>
    </p>
    <p class="mt-6 text-center text-xs text-stone-500">
        <a href="{{ route('bugs.create', ['page' => '/'.request()->path()]) }}" class="hover:underline">{{ __('Un problème pour vous connecter ou vous inscrire ? Signalez-le.') }}</a>
    </p>
</x-layouts.guest>
