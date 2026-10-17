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
    });

    window.Alpine.data('diceBuilder', (storageKey) => ({
        ...blank(),
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
                threshold: this.threshold === '' ? null : Number(this.threshold),
                count_high: this.count_high,
                keep: Number(this.keep),
                keep_high: this.keep_high,
                explode: this.explode,
                modifier: Number(this.modifier) || 0,
                hide_threshold: this.hide_threshold,
            };
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
