<form wire:submit="save" class="mt-8 space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
    <div>
        <h2 class="text-lg font-semibold">{{ __('Assistant IA : votre propre clé') }}</h2>
        <p class="mt-1 text-sm text-stone-600">{{ __('Facultatif. Avec une clé d’API à votre nom, l’assistant IA analyse vos séances sans copier-coller. Les appels sont facturés par le fournisseur sur votre compte, pas par CodexFlow. Sans clé, le mode « texte à coller » reste gratuit.') }}</p>
    </div>

    @if (session('ai-status'))
        <p class="rounded-lg bg-green-50 px-4 py-2 text-sm text-green-800" role="status">{{ session('ai-status') }}</p>
    @endif

    @if ($savedKey)
        <p class="text-sm">{{ __('Clé enregistrée pour :provider, se terminant par :end.', ['provider' => $savedProvider, 'end' => $savedKey]) }}</p>
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="ai-provider" class="label">{{ __('Fournisseur') }}</label>
            <select id="ai-provider" wire:model.live="provider" class="field">
                <option value="">{{ __('Aucun') }}</option>
                @foreach ($providers as $key => $choice)
                    <option value="{{ $key }}">{{ $choice["name"] }}</option>
                @endforeach
            </select>
            @error('provider') <p class="error">{{ $message }}</p> @enderror
        </div>
        @if ($provider !== '')
            <div>
                <label for="ai-model" class="label">{{ __('Modèle') }}</label>
                <input id="ai-model" type="text" wire:model="model" list="ai-models" class="field" maxlength="100">
                <datalist id="ai-models">
                    @foreach ($providers[$provider]['models'] as $model)
                        <option value="{{ $model }}">
                    @endforeach
                </datalist>
                @error('model') <p class="error">{{ $message }}</p> @enderror
            </div>
        @endif
    </div>

    @if ($provider !== '')
        <div>
            <label for="ai-key" class="label">{{ $savedKey ? __('Nouvelle clé') : __('Clé d’API') }} @if ($savedKey)<span class="font-normal text-stone-500">{{ __('(vide pour garder la clé enregistrée)') }}</span>@endif</label>
            <input id="ai-key" type="password" wire:model="apiKey" class="field font-mono" autocomplete="off" spellcheck="false">
            @error('apiKey') <p class="error">{{ $message }}</p> @enderror
            <p class="mt-1 text-xs text-stone-500">
                {{ __('Créez-la sur') }} <a href="{{ $providers[$provider]['keys'] }}" target="_blank" rel="noopener" class="link">{{ parse_url($providers[$provider]['keys'], PHP_URL_HOST) }}</a>.
                {{ __('Elle est chiffrée sur le serveur, jamais réaffichée ni exportée, et ne sert qu’à votre compte. Fixez une limite de dépense chez le fournisseur.') }}
            </p>
        </div>
    @endif

    <div class="flex flex-wrap gap-3">
        @if ($provider !== '')
            <button type="submit" class="btn-primary">{{ __('Enregistrer la clé') }}</button>
        @endif
        @if ($savedKey)
            <button type="button" wire:click="forget" wire:confirm="{{ __('Effacer votre clé d’API ?') }}" class="btn-secondary">{{ __('Effacer la clé') }}</button>
        @endif
    </div>
</form>
