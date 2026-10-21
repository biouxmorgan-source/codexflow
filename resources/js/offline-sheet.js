// Fiche du personnage hors ligne : le joueur règle ses compteurs, modifie les champs que le MJ
// lui laisse et écrit des notes sans réseau. Tout est gardé sur l'appareil (localStorage), puis
// envoyé au retour de la connexion (OfflineSync côté serveur, fusion champ par champ).
// Les compteurs partent en écarts (−3, +1) : un changement du MJ entre-temps n'est jamais écrasé.

const read = (key) => {
    try {
        return JSON.parse(window.localStorage.getItem(key) ?? 'null');
    } catch {
        return null;
    }
};

const write = (key, value) => {
    try {
        if (value === null) {
            window.localStorage.removeItem(key);
        } else {
            window.localStorage.setItem(key, JSON.stringify(value));
        }
    } catch {
        // Stockage refusé (navigation privée) : les changements restent le temps de la page.
    }
};

const empty = () => ({ counters: {}, fields: {}, notes: [] });

const isEmpty = (changes) => !changes
    || (Object.keys(changes.counters ?? {}).length === 0 && Object.keys(changes.fields ?? {}).length === 0 && (changes.notes ?? []).length === 0);

const uuid = () => window.crypto?.randomUUID?.() ?? `${Date.now()}-${Math.random().toString(36).slice(2)}`;

const formatNumber = (number) => String(number).replace('.', ',');

const cachedCopy = () => document.querySelector('meta[name="sagawyn-offline"]') !== null;

document.addEventListener('alpine:init', () => {
    window.Alpine.data('offlineSheet', () => ({
        offline: false,
        // Changements pas encore envoyés, et ceux envoyés dont on attend la réponse.
        pending: empty(),
        outbox: null,
        syncing: false,
        summary: '',
        error: '',
        editingFields: false,
        draft: {},
        noteBody: '',
        noteVisibility: 'gm',
        noteShares: [],
        sheetSource: '',
        sheetCache: null,
        refreshTimer: null,

        init() {
            const stored = read(this.sheet.key) ?? {};
            this.pending = { ...empty(), ...(stored.pending ?? {}) };
            this.outbox = stored.outbox ?? null;
            this.offline = !navigator.onLine || cachedCopy();

            try {
                this.summary = window.sessionStorage.getItem(`${this.sheet.key}:summary`) ?? '';
                window.sessionStorage.removeItem(`${this.sheet.key}:summary`);
            } catch {
                // Rien à afficher.
            }

            window.addEventListener('offline', () => { this.offline = true; });
            window.addEventListener('online', () => this.reconnect());

            // Chaque changement fait en ligne remet à jour la copie gardée pour le hors ligne.
            window.Livewire?.hook?.('commit', ({ component, succeed }) => {
                if (component.id === this.$wire?.$id) {
                    succeed(() => this.refreshCopy());
                }
            });

            if (!this.offline && this.hasPending) {
                this.sync();
            }
        },

        // Données de la page (compteurs, champs, partages), relues car Livewire les met à jour.
        get sheet() {
            const source = document.getElementById('offline-sheet-data')?.textContent ?? '{}';
            if (source !== this.sheetSource) {
                this.sheetSource = source;
                this.sheetCache = JSON.parse(source);
            }

            return this.sheetCache;
        },

        get text() {
            return JSON.parse(document.getElementById('offline-sheet-text')?.textContent ?? '{}');
        },

        get hasPending() {
            return !isEmpty(this.pending) || !isEmpty(this.outbox);
        },

        get pendingNotes() {
            return [...(this.outbox?.notes ?? []), ...this.pending.notes];
        },

        // Lignes « En attente d'envoi » : compteurs (écart total), champs (nouvelle valeur).
        get pendingLines() {
            const lines = [];
            const counters = new Set([...Object.keys(this.outbox?.counters ?? {}), ...Object.keys(this.pending.counters)]);
            counters.forEach((id) => {
                const delta = this.counterDelta(id);
                if (delta !== 0) {
                    lines.push(`${this.sheet.counters?.[id]?.name ?? '?'} : ${delta > 0 ? '+' : '−'}${formatNumber(Math.abs(delta))}`);
                }
            });
            const fields = { ...(this.outbox?.fields ?? {}), ...this.pending.fields };
            Object.entries(fields).forEach(([id, change]) => {
                const field = (this.sheet.fields ?? []).find((candidate) => String(candidate.id) === id);
                const value = typeof change.value === 'boolean' ? (change.value ? this.text.yes : this.text.no) : (change.value || '—');
                lines.push(`${field?.name ?? '?'} → ${value}`);
            });

            return lines;
        },

        save() {
            write(this.sheet.key, isEmpty(this.pending) && isEmpty(this.outbox) ? null : { pending: this.pending, outbox: this.outbox });
        },

        counterDelta(id) {
            return (this.outbox?.counters?.[id] ?? 0) + (this.pending.counters[id] ?? 0);
        },

        hasCounter(id) {
            return this.counterDelta(id) !== 0;
        },

        // Valeur affichée : la dernière connue, plus ce qui n'est pas encore envoyé.
        counterText(id) {
            const counter = this.sheet.counters?.[id];
            if (!counter) {
                return '—';
            }
            const value = Math.max(0, Number(counter.value) + this.counterDelta(id));

            return formatNumber(value) + (counter.max !== null && counter.max !== undefined ? ` / ${formatNumber(counter.max)}` : '');
        },

        queueCounter(id, delta) {
            const total = (this.pending.counters[id] ?? 0) + delta;
            if (total === 0) {
                delete this.pending.counters[id];
            } else {
                this.pending.counters[id] = total;
            }
            this.pending = { ...this.pending };
            this.summary = '';
            this.save();
        },

        // Valeur actuelle d'un champ, en tenant compte de ce qui attend l'envoi.
        fieldValue(field) {
            return this.pending.fields[field.id]?.value ?? this.outbox?.fields?.[field.id]?.value ?? field.input;
        },

        startFields() {
            this.draft = Object.fromEntries((this.sheet.fields ?? []).map((field) => [field.id, this.fieldValue(field)]));
            this.editingFields = true;
        },

        saveFields() {
            (this.sheet.fields ?? []).forEach((field) => {
                const value = this.draft[field.id];
                if (value === this.fieldValue(field)) {
                    return;
                }
                // Valeur de départ : celle du serveur quand le joueur a commencé à modifier ce champ.
                const base = this.pending.fields[field.id]?.base ?? this.outbox?.fields?.[field.id]?.value ?? field.input;
                if (value === base) {
                    delete this.pending.fields[field.id];
                } else {
                    this.pending.fields[field.id] = { base, value };
                }
            });
            this.pending = { ...this.pending };
            this.editingFields = false;
            this.summary = '';
            this.save();
        },

        addNote() {
            const body = this.noteBody.trim();
            if (body === '' || (this.noteVisibility === 'players' && this.noteShares.length === 0)) {
                return;
            }
            this.pending.notes.push({
                body,
                visibility: this.noteVisibility,
                shares: this.noteVisibility === 'players' ? this.noteShares.map(Number) : [],
                written_at: new Date().toISOString(),
            });
            this.pending = { ...this.pending };
            this.noteBody = '';
            this.noteShares = [];
            this.summary = '';
            this.save();
        },

        removeNote(index) {
            // Seules les notes pas encore envoyées peuvent être retirées.
            const sent = this.outbox?.notes?.length ?? 0;
            if (index >= sent) {
                this.pending.notes.splice(index - sent, 1);
                this.pending = { ...this.pending };
                this.save();
            }
        },

        async reconnect() {
            if (!cachedCopy()) {
                this.offline = false;
            }
            if (this.hasPending) {
                await this.sync();
            }
            // Page gardée sur l'appareil : on recharge la vraie fiche, sauf saisie en cours.
            if (cachedCopy() && !this.hasPending && !this.editingFields && this.noteBody.trim() === '') {
                window.location.reload();
            }
        },

        async sync() {
            if (this.syncing) {
                return;
            }
            if (isEmpty(this.outbox)) {
                if (isEmpty(this.pending)) {
                    return;
                }
                this.outbox = { sync_id: uuid(), ...this.pending };
                this.pending = empty();
                this.save();
            }

            this.syncing = true;
            this.error = '';
            let response;
            try {
                response = await fetch(this.sheet.syncUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify(this.outbox),
                });
            } catch {
                // Toujours pas de réseau : les changements restent sur l'appareil.
                this.syncing = false;

                return;
            }
            this.syncing = false;

            if (response.status === 419 && cachedCopy()) {
                // Jeton de la page gardée périmé : la vraie fiche renverra les changements.
                window.location.reload();

                return;
            }
            if (response.status === 401 || response.status === 419) {
                this.error = this.text.loginAgain;

                return;
            }
            if (response.status === 409 || response.status === 429 || response.status >= 500) {
                this.error = this.text.retry;

                return;
            }

            const result = response.ok ? await response.json().catch(() => null) : null;
            this.outbox = null;
            this.save();

            if (!result) {
                this.error = this.text.refused;

                return;
            }

            this.summary = this.describe(result);
            this.offline = !navigator.onLine || cachedCopy();
            if (cachedCopy()) {
                try {
                    window.sessionStorage.setItem(`${this.sheet.key}:summary`, this.summary);
                } catch {
                    // Le résumé ne s'affichera pas après le rechargement.
                }
            } else {
                this.$wire.characterChanged();
            }
        },

        describe(result) {
            const names = (list) => list.join(', ');
            const parts = [];
            if (result.counters.length + result.fields.length + result.notes === 0 && result.conflicts.length + result.rejected.length === 0) {
                return this.text.nothing;
            }
            if (result.counters.length + result.fields.length > 0) {
                parts.push(this.text.applied.replace(':names', names([...new Set([...result.counters, ...result.fields])])));
            }
            if (result.notes > 0) {
                parts.push((result.notes === 1 ? this.text.note : this.text.notes).replace(':count', result.notes));
            }
            if (result.conflicts.length > 0) {
                parts.push(this.text.conflicts.replace(':names', names(result.conflicts)));
            }
            if (result.rejected.length > 0) {
                parts.push(this.text.rejected.replace(':names', names(result.rejected)));
            }

            return parts.join(' ');
        },

        // Garde sur l'appareil la fiche telle qu'elle est maintenant (le service worker la met en cache).
        refreshCopy() {
            clearTimeout(this.refreshTimer);
            this.refreshTimer = setTimeout(() => {
                fetch(window.location.href, { credentials: 'same-origin', headers: { Accept: 'text/html' } }).catch(() => {});
            }, 2000);
        },
    }));
});
