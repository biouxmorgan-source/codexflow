<x-layouts.guest title="Connexion">
    @if (session('status'))
        <p class="mb-4 rounded-md bg-codex-soft px-3 py-2 text-sm text-codex">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="label">Adresse e-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="field">
            @error('email') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="label">Mot de passe</label>
            <input id="password" name="password" type="password" required autocomplete="current-password" class="field">
            @error('password') <p class="error">{{ $message }}</p> @enderror
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember" class="rounded border-stone-300"> Se souvenir de moi
        </label>
        <button type="submit" class="btn-primary w-full">Se connecter</button>
    </form>

    <div class="mt-6 flex justify-between text-sm">
        <a href="{{ route('password.request') }}" class="text-codex hover:underline">Mot de passe oublié ?</a>
        <a href="{{ route('register') }}" class="text-codex hover:underline">Créer un compte</a>
    </div>
</x-layouts.guest>
