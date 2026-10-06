<x-layouts.guest title="Créer un compte">
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <div>
            <label for="name" class="label">Nom ou pseudo</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="nickname" class="field">
            @error('name') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="email" class="label">Adresse e-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" class="field">
            @error('email') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="label">Mot de passe</label>
            <input id="password" name="password" type="password" required autocomplete="new-password" class="field">
            @error('password') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password_confirmation" class="label">Confirmer le mot de passe</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="field">
        </div>
        <button type="submit" class="btn-primary w-full">Créer mon compte</button>
    </form>

    <p class="mt-6 text-center text-sm">
        Déjà inscrit ? <a href="{{ route('login') }}" class="text-codex hover:underline">Se connecter</a>
    </p>
</x-layouts.guest>
