{{--
    Fiche hors ligne (offline-sheet.js) : ce que le joueur change sans réseau attend sur l'appareil,
    puis part au retour de la connexion. Les données et les textes sont relus par le script.
--}}
<script type="application/json" id="offline-sheet-data">@json($offline)</script>
@php($offlineText = [
    'yes' => __('Oui'),
    'no' => __('Non'),
    'nothing' => __('Synchronisé : rien à changer, votre fiche était déjà à jour.'),
    'applied' => __('Synchronisé : :names mis à jour.'),
    'note' => __(':count note ajoutée.'),
    'notes' => __(':count notes ajoutées.'),
    'conflicts' => __('Le MJ a modifié entre-temps :names : sa version est gardée, vérifiez votre fiche.'),
    'rejected' => __('Non enregistré, car vous ne pouvez plus le modifier : :names.'),
    'loginAgain' => __('Reconnectez-vous pour envoyer vos modifications : elles restent sur cet appareil en attendant.'),
    'retry' => __('L’envoi n’a pas abouti. Vos modifications restent sur cet appareil ; réessayez dans un instant.'),
    'refused' => __('Vos modifications hors ligne n’ont pas pu être enregistrées.'),
])
<script type="application/json" id="offline-sheet-text">@json($offlineText)</script>

<section x-show="offline || hasPending || summary || error" x-cloak aria-live="polite" class="mb-6 rounded-xl border border-flow/40 bg-flow/5 p-4 text-sm" data-offline-ok>
    <p x-show="summary" x-text="summary" class="font-medium text-ink"></p>
    <p x-show="error" x-text="error" class="font-medium text-red-700"></p>

    <div x-show="offline">
        <h2 class="font-semibold text-ink">{{ __('Mode hors ligne') }}</h2>
        <p class="text-stone-700">{{ __('Vous pouvez régler vos compteurs, modifier les champs que le MJ vous laisse et écrire des notes. Tout est gardé sur cet appareil et envoyé dès le retour de la connexion, sans effacer ce que le MJ a changé entre-temps.') }}</p>
    </div>

    <div x-show="hasPending" class="mt-3">
        <h3 class="font-semibold text-ink">{{ __('En attente d’envoi') }}</h3>
        <ul class="mt-1 list-disc pl-5 text-stone-700">
            <template x-for="line in pendingLines" :key="line">
                <li x-text="line"></li>
            </template>
            <template x-for="(note, index) in pendingNotes" :key="note.written_at + index">
                <li>
                    <span>{{ __('Note') }} :</span>
                    <span x-text="note.body.length > 80 ? note.body.slice(0, 80) + '…' : note.body"></span>
                    <button type="button" x-show="index >= (outbox?.notes?.length ?? 0)" x-on:click="removeNote(index)" class="ml-2 text-red-700 hover:underline" data-offline-ok>{{ __('Retirer') }}</button>
                </li>
            </template>
        </ul>
        <button type="button" x-show="! offline" x-on:click="sync()" :disabled="syncing" class="btn-secondary mt-2" data-offline-ok>
            <span x-show="! syncing">{{ __('Envoyer maintenant') }}</span>
            <span x-show="syncing">{{ __('Envoi…') }}</span>
        </button>
    </div>

    <div x-show="offline" class="mt-4 space-y-4">
        <template x-if="(sheet.fields ?? []).length > 0">
            <div>
                <button type="button" x-show="! editingFields" x-on:click="startFields()" class="btn-secondary" data-offline-ok>{{ __('Modifier mes champs hors ligne') }}</button>
                <form x-show="editingFields" x-on:submit.prevent="saveFields()" class="space-y-3 rounded-lg border border-stone-200 bg-white p-4">
                    <template x-for="field in sheet.fields" :key="field.id">
                        <div>
                            <template x-if="field.type === 'boolean'">
                                <label class="flex min-h-11 items-center gap-2 font-medium"><input type="checkbox" x-model="draft[field.id]"> <span x-text="field.name"></span></label>
                            </template>
                            <template x-if="field.type !== 'boolean'">
                                <label class="block">
                                    <span class="label" x-text="field.name"></span>
                                    <template x-if="field.type === 'long_text'">
                                        <textarea x-model="draft[field.id]" rows="3" class="field"></textarea>
                                    </template>
                                    <template x-if="field.type === 'select'">
                                        <select x-model="draft[field.id]" class="field">
                                            <option value="">—</option>
                                            <template x-for="option in field.options" :key="option">
                                                <option :value="option" x-text="option" :selected="draft[field.id] === option"></option>
                                            </template>
                                        </select>
                                    </template>
                                    <template x-if="! ['long_text', 'select'].includes(field.type)">
                                        <input x-model="draft[field.id]" :type="field.type === 'date' ? 'date' : 'text'" class="field">
                                    </template>
                                </label>
                            </template>
                        </div>
                    </template>
                    <div class="flex gap-3">
                        <button type="submit" class="btn-primary">{{ __('Garder sur cet appareil') }}</button>
                        <button type="button" x-on:click="editingFields = false" class="btn-secondary">{{ __('Annuler') }}</button>
                    </div>
                </form>
            </div>
        </template>

        <template x-if="sheet.canNote">
            <form x-on:submit.prevent="addNote()" class="space-y-3 rounded-lg border border-stone-200 bg-white p-4">
                <label for="offline-note" class="font-semibold">{{ __('Note hors ligne') }}</label>
                <textarea id="offline-note" x-model="noteBody" rows="3" maxlength="20000" class="field" placeholder="{{ __('Ce qui s’est passé, ce que vous soupçonnez…') }}"></textarea>
                <fieldset>
                    <legend class="label">{{ __('Qui peut la lire ?') }}</legend>
                    <div class="flex flex-wrap gap-x-4 gap-y-1">
                        <template x-for="(label, value) in sheet.visibilities" :key="value">
                            <label class="flex items-center gap-2" x-show="value !== 'players' || sheet.companions.length > 0">
                                <input type="radio" x-model="noteVisibility" :value="value"> <span x-text="label"></span>
                            </label>
                        </template>
                    </div>
                </fieldset>
                <div x-show="noteVisibility === 'players'" class="flex flex-wrap gap-x-4 gap-y-1">
                    <template x-for="companion in sheet.companions" :key="companion.id">
                        <label class="flex items-center gap-2"><input type="checkbox" x-model="noteShares" :value="companion.id"> <span x-text="companion.name"></span></label>
                    </template>
                </div>
                <p x-show="noteVisibility === 'private'" class="font-medium text-flow">{{ __("Note privée : personne d'autre, pas même le MJ, ne pourra la lire.") }}</p>
                <button type="submit" class="btn-primary" :disabled="noteBody.trim() === '' || (noteVisibility === 'players' && noteShares.length === 0)">{{ __('Ajouter la note') }}</button>
            </form>
        </template>
    </div>
</section>
