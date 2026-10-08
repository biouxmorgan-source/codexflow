<section class="mt-8 space-y-6 rounded-xl border border-stone-200 bg-white p-6 shadow-sm" aria-labelledby="profile-title">
    <div>
        <h2 id="profile-title" class="text-lg font-semibold">{{ __('Mon compte') }}</h2>
        <p class="mt-1 text-sm text-stone-600">{{ __('Votre nom est visible des autres membres de vos campagnes. Changer d’adresse e-mail ou de mot de passe demande votre mot de passe actuel.') }}</p>
    </div>

    @if (session('profile-status'))
        <p class="rounded-lg bg-green-50 px-4 py-2 text-sm text-green-800" role="status">{{ session('profile-status') }}</p>
    @endif

    <form wire:submit="saveProfile" class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="profile-name" class="label">{{ __('Nom') }}</label>
            <input id="profile-name" type="text" wire:model="name" autocomplete="name" class="field" maxlength="255">
            @error('name') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="profile-email" class="label">{{ __('Adresse e-mail') }}</label>
            <input id="profile-email" type="email" wire:model="email" autocomplete="email" class="field" maxlength="255">
            @error('email') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div class="sm:col-span-2">
            <label for="profile-current" class="label">{{ __('Mot de passe actuel') }} <span class="font-normal text-stone-500">{{ __('(pour changer d’adresse ou de mot de passe)') }}</span></label>
            <input id="profile-current" type="password" wire:model="currentPassword" autocomplete="current-password" class="field sm:w-1/2">
            @error('currentPassword') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div class="sm:col-span-2">
            <button type="submit" class="btn-primary">{{ __('Enregistrer') }}</button>
        </div>
    </form>

    <form wire:submit="savePassword" class="grid gap-4 border-t border-stone-200 pt-6 sm:grid-cols-2">
        <div>
            <label for="profile-password" class="label">{{ __('Nouveau mot de passe') }}</label>
            <input id="profile-password" type="password" wire:model="password" autocomplete="new-password" class="field">
            <p class="mt-1 text-xs text-stone-500">{{ __('Au moins 10 caractères, avec des lettres et des chiffres.') }}</p>
            @error('password') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="profile-password-confirmation" class="label">{{ __('Confirmez le nouveau mot de passe') }}</label>
            <input id="profile-password-confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" class="field">
        </div>
        <div class="sm:col-span-2">
            <button type="submit" class="btn-secondary">{{ __('Changer le mot de passe') }}</button>
        </div>
    </form>
</section>
