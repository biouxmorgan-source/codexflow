<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="hover:text-codex" wire:navigate>Mes campagnes</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="hover:text-codex" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <h1 class="text-2xl font-semibold">Importer depuis un fichier</h1>
    <p class="mt-1 mb-6 text-sm text-stone-600">Fichier CSV (Excel, LibreOffice, Google Sheets) ou JSON. Rien n'est créé avant que vous cliquiez sur « Importer ».</p>

    @if ($result)
        <div class="mb-6 rounded-xl border border-codex/30 bg-codex-soft p-4" role="status">
            @php($plural = fn (int $count, string $one, string $many) => $count.' '.($count > 1 ? $many : $one))
            <p class="font-medium text-codex">Import terminé : {{ collect([
                $plural($result['created'], 'créé', 'créés'),
                $plural($result['updated'], 'mis à jour', 'mis à jour'),
                ! empty($result['fields']) ? $plural($result['fields'], 'nouveau champ', 'nouveaux champs') : null,
                ! empty($result['scenarios']) ? $plural($result['scenarios'], 'nouveau scénario', 'nouveaux scénarios') : null,
            ])->filter()->implode(', ') }}.</p>
            <p class="mt-1 text-sm">
                @if ($mode === 'rules')
                    <a href="{{ route('rules.index', $campaign) }}" class="text-codex hover:underline" wire:navigate>Voir les règles</a>
                @elseif ($mode === 'scenes')
                    <a href="{{ route('scenarios.index', $campaign) }}" class="text-codex hover:underline" wire:navigate>Voir les scénarios</a>
                @else
                    <a href="{{ route('campaigns.show', $campaign) }}" class="text-codex hover:underline" wire:navigate>Voir les fiches</a>
                    · <a href="{{ route('fields.index', $campaign) }}" class="text-codex hover:underline" wire:navigate>Voir les champs du jeu</a>
                @endif
            </p>
        </div>
    @endif

    <form wire:submit="import" class="space-y-6">
        <section class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <fieldset>
                <legend class="label">Que contient le fichier ?</legend>
                <div class="flex flex-col gap-2 sm:flex-row sm:gap-6">
                    <label class="flex items-center gap-2"><input type="radio" wire:model.live="mode" value="entities"> Des fiches (une ligne par fiche)</label>
                    <label class="flex items-center gap-2"><input type="radio" wire:model.live="mode" value="fields"> Une liste de champs pour le jeu {{ $campaign->gameSystem->name }}</label>
                    <label class="flex items-center gap-2"><input type="radio" wire:model.live="mode" value="rules"> Des règles ou aides de jeu</label>
                    <label class="flex items-center gap-2"><input type="radio" wire:model.live="mode" value="scenes"> Des scénarios et leurs scènes</label>
                </div>
            </fieldset>

            <p class="text-sm text-stone-600">
                @if ($mode === 'entities')
                    Une colonne pour le nom, puis une colonne par information : type, résumé, ou vos champs (Force, Discrétion…). Les colonnes inconnues deviennent de nouveaux champs.
                @elseif ($mode === 'fields')
                    Colonnes reconnues : Nom, Groupe, Type (texte, texte long, nombre, oui/non, date, liste), Zone (publique ou MJ), Choix (séparés par |), Type de fiche.
                @elseif ($mode === 'scenes')
                    Une ligne par scène. Colonnes reconnues : Scénario, Résumé du scénario, Chapitre, Scène, Description, Statut, puis Fiches, Documents et Règles à lier (noms séparés par |). Importez d'abord les fiches et les documents pour que les liens les trouvent.
                @else
                    Une ligne par règle. Colonnes reconnues : Titre, Catégorie, Résumé, Procédure, Notes MJ, Source, Origine (référence, maison, test), Statut, Zone (publique ou MJ), Tags (séparés par des virgules).
                @endif
                <a href="{{ route('imports.example', [$campaign, ['entities' => 'fiches', 'fields' => 'champs', 'rules' => 'regles', 'scenes' => 'scenes'][$mode] ?? 'fiches']) }}" class="text-codex hover:underline">Télécharger un fichier exemple</a>
            </p>

            <div>
                <label for="file" class="label">Fichier</label>
                <input id="file" type="file" wire:model="file" accept=".csv,.txt,.json,text/csv,application/json" class="block w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-codex-soft file:px-3 file:py-2 file:font-medium file:text-codex">
                <div wire:loading wire:target="file" class="mt-1 text-sm text-stone-500">Lecture du fichier…</div>
                @error('file') <p class="error">{{ $message }}</p> @enderror
                @if (is_string($this->table))
                    <p class="error">{{ $this->table }}</p>
                @endif
            </div>
        </section>

        @if ($this->table instanceof \App\Support\Import\TabularFile && $mode === 'entities')
            <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-1 font-semibold">Colonnes</h2>
                <p class="mb-4 text-sm text-stone-600">{{ count($this->table->rows) }} ligne{{ count($this->table->rows) > 1 ? 's' : '' }} lue{{ count($this->table->rows) > 1 ? 's' : '' }}. Vérifiez à quoi correspond chaque colonne.</p>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($this->table->headers as $index => $header)
                        <div wire:key="column-{{ $index }}">
                            <label for="mapping-{{ $index }}" class="label truncate">{{ $header }}
                                <span class="font-normal text-stone-500">ex. {{ \Illuminate\Support\Str::limit($this->table->rows[0]['cells'][$index] ?? '', 30) ?: '—' }}</span>
                            </label>
                            <select id="mapping-{{ $index }}" wire:model.live="mapping.{{ $index }}" class="field">
                                @foreach (\App\Actions\Imports\ImportEntities::TARGETS as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                                <option value="new">Nouveau champ « {{ $header }} »</option>
                                @if ($this->definitions->isNotEmpty())
                                    <optgroup label="Champs du jeu">
                                        @foreach ($this->definitions as $definition)
                                            <option value="field:{{ $definition->id }}">{{ $definition->group ? $definition->group.' › ' : '' }}{{ $definition->name }}{{ $definition->entityType ? ' ('.$definition->entityType->name.')' : '' }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 grid gap-4 border-t border-stone-200 pt-4 md:grid-cols-2">
                    <div>
                        <label for="defaultTypeId" class="label">Type de fiche <span class="font-normal text-stone-500">(si la colonne Type est absente ou vide)</span></label>
                        <select id="defaultTypeId" wire:model.live="defaultTypeId" class="field">
                            @foreach ($this->types as $entityType)
                                <option value="{{ $entityType->id }}">{{ $entityType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($campaign->world)
                        <div>
                            <label for="scope" class="label">Nouvelles fiches rangées dans</label>
                            <select id="scope" wire:model="scope" class="field">
                                <option value="world">Le monde « {{ $campaign->world->name }} »</option>
                                <option value="campaign">Cette campagne seulement</option>
                            </select>
                        </div>
                    @endif
                    <label class="flex items-center gap-2 text-sm md:col-span-2">
                        <input type="checkbox" wire:model.live="updateExisting">
                        Mettre à jour les fiches qui existent déjà sous le même nom et le même type (les cases vides ne changent rien)
                    </label>
                </div>

                @if (in_array('new', $mapping, true))
                    <div class="mt-6 grid gap-4 border-t border-stone-200 pt-4 md:grid-cols-3">
                        <p class="text-sm text-stone-600 md:col-span-3">Les nouveaux champs seront ajoutés au jeu {{ $campaign->gameSystem->name }}.</p>
                        <div>
                            <label for="newGroup" class="label">Groupe</label>
                            <input id="newGroup" type="text" wire:model="newGroup" class="field" placeholder="Caractéristiques…">
                            @error('newGroup') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="newZone" class="label">Zone</label>
                            <select id="newZone" wire:model="newZone" class="field">
                                <option value="public">Zone publique</option>
                                <option value="gm">Zone MJ</option>
                            </select>
                        </div>
                        <div>
                            <label for="newTypeId" class="label">Pour les fiches</label>
                            <select id="newTypeId" wire:model.live="newTypeId" class="field">
                                <option value="">Tous les types</option>
                                @foreach ($this->types as $entityType)
                                    <option value="{{ $entityType->id }}">{{ $entityType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif
            </section>
        @endif

        @if ($this->table instanceof \App\Support\Import\TabularFile && $mode === 'rules')
            <section class="grid gap-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm md:grid-cols-2">
                <div>
                    <label for="ruleScope" class="label">Règles rattachées à</label>
                    <select id="ruleScope" wire:model.live="ruleScope" class="field">
                        <option value="game">Tout le jeu {{ $campaign->gameSystem->name }}</option>
                        <option value="campaign">Cette campagne seulement</option>
                    </select>
                </div>
                <label class="flex items-center gap-2 self-end text-sm">
                    <input type="checkbox" wire:model.live="updateExisting">
                    Mettre à jour les règles qui existent déjà sous le même titre (les cases vides ne changent rien)
                </label>
            </section>
        @endif

        @if ($this->table instanceof \App\Support\Import\TabularFile && $mode === 'scenes')
            <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model.live="updateExisting">
                    Mettre à jour les scènes qui existent déjà sous le même nom dans le même scénario (les cases vides ne changent rien)
                </label>
            </section>
        @endif

        @if ($plan = $this->plan)
            @php($errorRows = collect($plan['rows'])->filter(fn ($row) => $row['errors'] !== []))
            <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-3 font-semibold">Aperçu</h2>

                @foreach ($plan['errors'] as $error)
                    <p class="error mb-2">{{ $error }}</p>
                @endforeach

                @if (! empty($plan['new_fields']))
                    <p class="mb-3 text-sm">
                        Nouveaux champs :
                        @foreach ($plan['new_fields'] as $new)
                            <span class="mr-1 inline-block rounded-full bg-stone-100 px-2 py-0.5 text-xs">{{ $new['name'] }} · {{ $new['type']->label() }}{{ $new['reused'] ? ' (existe déjà)' : '' }}</span>
                        @endforeach
                    </p>
                @endif

                @if ($errorRows->isNotEmpty())
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                        <p class="font-medium">{{ $errorRows->count() }} ligne{{ $errorRows->count() > 1 ? 's' : '' }} en erreur, ignorée{{ $errorRows->count() > 1 ? 's' : '' }} à l'import :</p>
                        <ul class="mt-1 list-disc pl-5">
                            @foreach ($errorRows->take(50) as $row)
                                <li>Ligne {{ $row['line'] }}{{ $row['name'] !== '' ? ' ('.$row['name'].')' : '' }} : {{ implode(' ; ', $row['errors']) }}</li>
                            @endforeach
                        </ul>
                        @if ($errorRows->count() > 50)
                            <p class="mt-1">… et {{ $errorRows->count() - 50 }} autres.</p>
                        @endif
                    </div>
                @endif

                @php($warningRows = collect($plan['rows'])->filter(fn ($row) => ! empty($row['warnings']) && $row['errors'] === []))
                @if ($warningRows->isNotEmpty())
                    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                        <p class="font-medium">À vérifier (ces lignes seront importées) :</p>
                        <ul class="mt-1 list-disc pl-5">
                            @foreach ($warningRows->take(50) as $row)
                                <li>Ligne {{ $row['line'] }} ({{ $row['name'] }}) : {{ implode(' ; ', $row['warnings']) }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-stone-200 text-stone-600">
                            <tr>
                                <th class="py-2 pr-3 font-medium">Ligne</th>
                                <th class="py-2 pr-3 font-medium">Nom</th>
                                @if ($mode === 'scenes')
                                    <th class="py-2 pr-3 font-medium">Scénario</th>
                                    <th class="py-2 pr-3 font-medium">Liens</th>
                                @elseif ($mode === 'rules')
                                    <th class="py-2 pr-3 font-medium">Catégorie</th>
                                    <th class="py-2 pr-3 font-medium">Zone</th>
                                @elseif ($mode === 'fields')
                                    <th class="py-2 pr-3 font-medium">Groupe</th>
                                    <th class="py-2 pr-3 font-medium">Type</th>
                                    <th class="py-2 pr-3 font-medium">Zone</th>
                                    <th class="py-2 pr-3 font-medium">Fiches</th>
                                @else
                                    <th class="py-2 pr-3 font-medium">Type</th>
                                @endif
                                <th class="py-2 font-medium">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach (array_slice($plan['rows'], 0, 20) as $row)
                                <tr wire:key="row-{{ $row['line'] }}">
                                    <td class="py-1.5 pr-3 text-stone-500">{{ $row['line'] }}</td>
                                    <td class="py-1.5 pr-3 font-medium">{{ $row['name'] ?: '—' }}</td>
                                    @if ($mode === 'scenes')
                                        <td class="py-1.5 pr-3">{{ $row['scenario'] ?: '—' }}</td>
                                        <td class="py-1.5 pr-3">{{ $row['links'] }}</td>
                                    @elseif ($mode === 'rules')
                                        <td class="py-1.5 pr-3">{{ $row['category'] ?: '—' }}</td>
                                        <td class="py-1.5 pr-3">{{ $row['zone'] }}</td>
                                    @elseif ($mode === 'fields')
                                        <td class="py-1.5 pr-3">{{ $row['group'] ?: '—' }}</td>
                                        <td class="py-1.5 pr-3">{{ $row['type'] }}</td>
                                        <td class="py-1.5 pr-3">{{ $row['zone'] }}</td>
                                        <td class="py-1.5 pr-3">{{ $row['entity_type'] }}</td>
                                    @else
                                        <td class="py-1.5 pr-3">{{ $row['type'] }}</td>
                                    @endif
                                    <td class="py-1.5">
                                        @if ($row['errors'])
                                            <span class="text-red-700">Erreur</span>
                                        @elseif ($row['action'] === 'update')
                                            <span class="text-flow">Mise à jour</span>
                                        @else
                                            <span class="text-codex">Création</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if (count($plan['rows']) > 20)
                        <p class="mt-2 text-sm text-stone-500">… et {{ count($plan['rows']) - 20 }} autres lignes.</p>
                    @endif
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <button type="submit" class="btn-primary" @disabled($plan['valid'] === 0) wire:loading.attr="disabled" wire:target="import">
                        Importer {{ $plan['valid'] }} ligne{{ $plan['valid'] > 1 ? 's' : '' }}
                    </button>
                    <a href="{{ route('campaigns.show', $campaign) }}" class="btn-secondary" wire:navigate>Annuler</a>
                </div>
            </section>
        @endif
    </form>
</div>
