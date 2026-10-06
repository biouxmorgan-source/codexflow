<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="hover:text-codex" wire:navigate>Mes campagnes</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="hover:text-codex" wire:navigate>{{ $campaign->name }}</a>
        › <a href="{{ route('rules.index', $campaign) }}" class="hover:text-codex" wire:navigate>Règles</a>
    </nav>

    <h1 class="mb-6 text-2xl font-semibold">{{ $rule ? 'Modifier '.$rule->title : 'Nouvelle règle' }}</h1>

    <form wire:submit="save" class="space-y-6">
        <div class="grid gap-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="title" class="label">Titre</label>
                <input id="title" type="text" wire:model="title" class="field" autofocus placeholder="Voyage : jet de condition">
                @error('title') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="category" class="label">Catégorie <span class="font-normal text-stone-500">(facultatif)</span></label>
                <input id="category" type="text" wire:model="category" list="category-suggestions" class="field" placeholder="Voyage, combat, magie…">
                <datalist id="category-suggestions">
                    @foreach ($this->categories as $existing)
                        <option value="{{ $existing }}">
                    @endforeach
                </datalist>
                @error('category') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="source" class="label">Source <span class="font-normal text-stone-500">(facultatif)</span></label>
                <input id="source" type="text" wire:model="source" class="field" placeholder="Livre de base p. 112">
                @error('source') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="origin" class="label">Origine</label>
                <select id="origin" wire:model="origin" class="field">
                    @foreach (\App\Enums\RuleOrigin::cases() as $ruleOrigin)
                        <option value="{{ $ruleOrigin->value }}">{{ $ruleOrigin->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="label">Statut</label>
                <select id="status" wire:model="status" class="field">
                    @foreach (\App\Enums\RuleStatus::cases() as $ruleStatus)
                        <option value="{{ $ruleStatus->value }}">{{ $ruleStatus->label() }}</option>
                    @endforeach
                </select>
            </div>
            <fieldset>
                <legend class="label">Rattachement</legend>
                <label class="flex items-center gap-2 text-sm"><input type="radio" wire:model="scope" value="game"> Au jeu {{ $campaign->gameSystem->name }} (toutes ses campagnes)</label>
                <label class="flex items-center gap-2 text-sm"><input type="radio" wire:model="scope" value="campaign"> À cette campagne seulement</label>
            </fieldset>
            <fieldset>
                <legend class="label">Visibilité</legend>
                <label class="flex items-center gap-2 text-sm"><input type="radio" wire:model="zone" value="public"> Consultable par les joueurs</label>
                <label class="flex items-center gap-2 text-sm"><input type="radio" wire:model="zone" value="gm"> MJ seulement</label>
            </fieldset>
            <div class="md:col-span-2">
                <x-tags-input :existing="$this->existingTags" />
            </div>
        </div>

        <section class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <div>
                <label for="summary" class="label">Résumé</label>
                <textarea id="summary" wire:model="summary" rows="2" class="field" placeholder="Ce qu'il faut retenir en une phrase."></textarea>
                @error('summary') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="procedure" class="label">Procédure</label>
                <x-link-textarea id="procedure" model="procedure" rows="8" placeholder="1. Chaque voyageur lance Force + Esprit…" />
                @error('procedure') <p class="error">{{ $message }}</p> @enderror
            </div>
        </section>

        <section class="rounded-xl border border-flow/30 bg-white p-6 shadow-sm">
            <label for="gmNotes" class="label text-flow">Notes MJ <span class="font-normal text-stone-500">(jamais visibles des joueurs)</span></label>
            <textarea id="gmNotes" wire:model="gmNotes" rows="4" class="field" placeholder="Pourquoi cette variante, ce qu'on a observé en test…"></textarea>
            @error('gmNotes') <p class="error">{{ $message }}</p> @enderror
        </section>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">Enregistrer</button>
            <a href="{{ $rule ? route('rules.show', [$campaign, $rule]) : route('rules.index', $campaign) }}" class="btn-secondary" wire:navigate>Annuler</a>
        </div>
    </form>
</div>
