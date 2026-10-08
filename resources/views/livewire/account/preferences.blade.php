<div class="max-w-2xl">
    <h1 class="text-2xl font-semibold">{{ __('Préférences') }}</h1>
    <p class="mt-1 mb-6 text-sm text-stone-600">{{ __('Réglages de votre compte, sur tous vos appareils.') }}</p>

    @if (session('status'))
        <p class="mb-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-800" role="status">{{ session('status') }}</p>
    @endif

    <form wire:submit="save" class="space-y-6 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
        <div>
            <label for="locale" class="label">{{ __('Langue') }}</label>
            <select id="locale" wire:model="locale" class="field max-w-xs">
                <option value="">{{ __('Comme le navigateur') }}</option>
                @foreach ($locales as $code => $name)
                    <option value="{{ $code }}" lang="{{ $code }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <fieldset>
            <legend class="label">{{ __('Thème') }}</legend>
            <div class="flex flex-wrap gap-2">
                @foreach ($choices['theme'] as $value => $label)
                    <label @class(['flex items-center gap-2 rounded-lg border px-3 py-2 text-sm has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-codex', 'border-codex bg-codex-soft font-medium' => $theme === $value, 'border-stone-300' => $theme !== $value])>
                        <input type="radio" wire:model.live="theme" value="{{ $value }}" class="sr-only">
                        <span aria-hidden="true">{{ ['system' => '🖥️', 'light' => '☀️', 'dark' => '🌙'][$value] }}</span> {{ $label }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        <fieldset>
            <legend class="label">{{ __("Couleur d'accent") }}</legend>
            <div class="flex flex-wrap gap-2">
                @foreach ($choices['accent'] as $value => $label)
                    <label @class(['flex items-center gap-2 rounded-lg border px-3 py-2 text-sm has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-codex', 'border-codex bg-codex-soft font-medium' => $accent === $value, 'border-stone-300' => $accent !== $value])>
                        <input type="radio" wire:model.live="accent" value="{{ $value }}" class="sr-only">
                        <span class="h-4 w-4 rounded-full" style="background: {{ ['codex' => '#2e5b6e', 'blue' => '#2f5d9c', 'green' => '#3d6b45', 'violet' => '#5b4a8a', 'red' => '#7a2e3a'][$value] }}" aria-hidden="true"></span>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        <fieldset>
            <legend class="label">{{ __('Taille du texte') }}</legend>
            <div class="flex flex-wrap gap-2">
                @foreach ($choices['size'] as $value => $label)
                    <label @class(['flex items-center gap-2 rounded-lg border px-3 py-2 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-codex', 'border-codex bg-codex-soft font-medium' => $size === $value, 'border-stone-300' => $size !== $value])>
                        <input type="radio" wire:model.live="size" value="{{ $value }}" class="sr-only">
                        <span @class(['text-sm' => $value === 'normal', 'text-base' => $value === 'large', 'text-lg' => $value === 'xlarge'])>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <button type="submit" class="btn-primary">{{ __('Enregistrer') }}</button>
    </form>

    <livewire:account.ai-key />

</div>
