<div class="max-w-2xl">
    <h1 class="text-2xl font-semibold">Préférences</h1>
    <p class="mt-1 mb-6 text-sm text-stone-600">Réglages de votre compte, sur tous vos appareils.</p>

    @if (session('status'))
        <p class="mb-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-800" role="status">{{ session('status') }}</p>
    @endif

    <form wire:submit="save" class="space-y-6 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
        <fieldset>
            <legend class="label">Thème</legend>
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
            <legend class="label">Couleur d'accent</legend>
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
            <legend class="label">Taille du texte</legend>
            <div class="flex flex-wrap gap-2">
                @foreach ($choices['size'] as $value => $label)
                    <label @class(['flex items-center gap-2 rounded-lg border px-3 py-2 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-codex', 'border-codex bg-codex-soft font-medium' => $size === $value, 'border-stone-300' => $size !== $value])>
                        <input type="radio" wire:model.live="size" value="{{ $value }}" class="sr-only">
                        <span @class(['text-sm' => $value === 'normal', 'text-base' => $value === 'large', 'text-lg' => $value === 'xlarge'])>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <button type="submit" class="btn-primary">Enregistrer</button>
    </form>

</div>
