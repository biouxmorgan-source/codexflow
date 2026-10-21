// Plateau de dés : le lancer se compose ici (nombre, type de dé, lecture, options), puis part au
// serveur qui tire les dés. Le dernier lancer composé est retenu pour la campagne, sur cet appareil.
document.addEventListener('alpine:init', () => {
    const MAX_DICE = 30;
    const MAX_KINDS = 6;
    const blank = () => ({
        pool: [{ count: 1, sides: 20 }],
        reading: 'raw',
        threshold: '',
        count_high: true,
        keep: 1,
        keep_high: true,
        explode: 'none',
        modifier: 0,
        hide_threshold: false,
        // Valeurs de la fiche du personnage qui lance : ajoutées au bonus, ou prises comme seuil.
        bonus_from: [],
        threshold_from: null,
    });
    const MAX_SOURCES = 6;
    // Un bonus nommé (fixed) ou une valeur de la fiche (field) ; ce qui a été lu au lancer (got) reste au serveur.
    const source = (item) => item.fixed !== undefined
        ? { fixed: Number(item.fixed), name: item.name ?? '' }
        : { field: Number(item.field), take: item.take ?? 'value', name: item.name ?? '' };

    window.Alpine.data('diceBuilder', (storageKey, takes = {}) => ({
        ...blank(),
        takes,
        sourceField: '',
        sourceTake: 'value',
        fixedName: '',
        fixedValue: '',
        custom: '',
        visibility: 'table',
        label: '',
        targets: [],
        presetName: '',
        presetScope: 'mine',
        // Lancer enregistré chargé dans le plateau : { id, scope, label }, pour le mettre à jour.
        loaded: null,

        init() {
            try {
                const saved = JSON.parse(localStorage.getItem(storageKey) || 'null');
                if (saved) this.load(saved);
            } catch (e) {
                // Stockage indisponible (navigation privée) : on part du lancer par défaut.
            }
            this.$watch('formula', (value) => {
                try {
                    localStorage.setItem(storageKey, JSON.stringify(value));
                } catch (e) {}
            });
        },

        load(formula, label = null) {
            const { count, sides, ...rest } = formula;
            Object.assign(this, blank(), rest);
            // Un lancer enregistré avant le mélange des dés n'a qu'un type : count + sides.
            this.pool = (formula.pool ?? [{ count: count ?? 1, sides: sides ?? 6 }]).map((kind) => ({ count: Number(kind.count), sides: Number(kind.sides) }));
            this.threshold = formula.threshold ?? '';
            this.custom = '';
            if (label !== null) this.label = label;
        },

        loadPreset(formula, label, scope, id) {
            this.load(formula, label);
            this.presetName = label;
            this.presetScope = scope;
            this.loaded = { id, scope, label };
        },

        get count() {
            return this.pool.reduce((total, kind) => total + kind.count, 0);
        },

        get sides() {
            return this.pool[0]?.sides ?? 6;
        },

        get notation() {
            return this.pool.map((kind) => kind.count + 'd' + kind.sides).join(' + ');
        },

        get formula() {
            return {
                pool: this.pool.map((kind) => ({ count: kind.count, sides: kind.sides })),
                reading: this.reading,
                threshold: this.threshold === '' || this.threshold_from ? null : Number(this.threshold),
                count_high: this.count_high,
                keep: Number(this.keep),
                keep_high: this.keep_high,
                explode: this.explode,
                modifier: Number(this.modifier) || 0,
                hide_threshold: this.hide_threshold && !this.threshold_from,
                bonus_from: this.bonus_from.map(source),
                threshold_from: this.threshold_from ? source(this.threshold_from) : null,
            };
        },

        takeLabel(item) {
            if (item.fixed !== undefined) return (item.name ? item.name + ' ' : '') + (item.fixed >= 0 ? '+' : '') + item.fixed;
            const label = this.takes[item.take] ?? ':name';
            return '+ ' + label.replace(':name', item.name || '?');
        },

        // Le nom vient de l'option choisie : la liste des champs n'est écrite que dans le plateau ouvert.
        addSource(name) {
            if (this.sourceField === '' || this.bonus_from.length >= MAX_SOURCES) return;
            this.bonus_from.push({ field: Number(this.sourceField), take: this.sourceTake, name });
            this.sourceField = '';
            this.sourceTake = 'value';
        },

        // Une valeur touchée sur une feuille de personnage : comme seuil (on lance sous la valeur) ou comme bonus.
        // Sur la fiche de son propre personnage, la valeur reste liée au champ et se relit à chaque lancer.
        useValue(detail) {
            const linked = detail.own && detail.field;
            const value = Math.trunc(Number(detail.value) || 0);
            if (detail.as === 'threshold') {
                if (!this.needsThreshold) this.setReading('below');
                this.threshold_from = linked ? { field: Number(detail.field), take: 'value', name: detail.name } : null;
                this.threshold = linked ? '' : String(value);
            } else if (this.bonus_from.length < MAX_SOURCES && !this.bonus_from.some((item) => linked ? item.field === Number(detail.field) && item.take === 'value' : item.fixed === value && item.name === detail.name)) {
                if (!this.hasModifier) this.setReading('sum');
                this.bonus_from.push(linked ? { field: Number(detail.field), take: 'value', name: detail.name } : { fixed: value, name: detail.name });
            }
            this.loaded = null;
            this.$wire.openFor(detail.character ?? null);
        },

        addFixed() {
            const value = parseInt(this.fixedValue, 10);
            if (!value || this.bonus_from.length >= MAX_SOURCES) return;
            this.bonus_from.push({ fixed: Math.max(-999, Math.min(999, value)), name: this.fixedName.trim() });
            this.fixedName = '';
            this.fixedValue = '';
        },

        removeSource(index) {
            this.bonus_from.splice(index, 1);
        },

        setThresholdFrom(id, take, name) {
            this.threshold_from = id === '' ? null : { field: Number(id), take, name };
        },

        // Toucher un type de dé en ajoute un ; plusieurs types se mélangent dans le même lancer.
        add(sides) {
            const kind = this.pool.find((item) => item.sides === sides);
            if (this.count >= MAX_DICE) return;
            if (kind) {
                kind.count++;
            } else if (this.pool.length < MAX_KINDS) {
                this.pool.push({ count: 1, sides });
            }
        },

        addCustom() {
            const value = parseInt(this.custom, 10);
            if (value >= 2 && value <= 1000) this.add(value);
            this.custom = '';
        },

        bump(index, delta) {
            const kind = this.pool[index];
            if (delta > 0 && this.count >= MAX_DICE) return;
            kind.count = Math.max(1, kind.count + delta);
            this.keep = Math.min(this.keep, this.count);
        },

        removeKind(index) {
            if (this.pool.length > 1) this.pool.splice(index, 1);
            this.keep = Math.min(this.keep, this.count);
        },

        get needsThreshold() {
            return ['above', 'below', 'count'].includes(this.reading);
        },

        get hasModifier() {
            return ['sum', 'above', 'below', 'keep'].includes(this.reading);
        },

        get canExplode() {
            return !['raw', 'digits'].includes(this.reading);
        },

        get canExplodeDown() {
            return !['raw', 'count', 'digits'].includes(this.reading);
        },

        setReading(reading) {
            // « Seuil sur le total » : au seuil ou plus (above) ou au seuil ou moins (below).
            if (reading === 'total') reading = ['above', 'below'].includes(this.reading) ? this.reading : 'above';
            this.reading = reading;
            if (!this.canExplodeDown && ['down', 'both'].includes(this.explode)) this.explode = this.reading === 'count' && this.explode === 'both' ? 'up' : 'none';
            if (!this.canExplode) this.explode = 'none';
        },

        toggleTarget(id) {
            this.targets = this.targets.includes(id) ? this.targets.filter((t) => t !== id) : [...this.targets, id];
        },
    }));

    // Pendant l'animation, la face du dé défile au hasard avant de s'arrêter sur sa vraie valeur.
    window.Alpine.data('dieFace', (value, sides) => ({
        shown: value,

        init() {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            let ticks = 0;
            const timer = setInterval(() => {
                this.shown = 1 + Math.floor(Math.random() * sides);
                if (++ticks >= 8) {
                    clearInterval(timer);
                    this.shown = value;
                }
            }, 70);
        },
    }));
});
