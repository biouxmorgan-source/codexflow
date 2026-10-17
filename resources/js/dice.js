// Plateau de dés : le lancer se compose ici (nombre, type de dé, lecture, options), puis part au
// serveur qui tire les dés. Le dernier lancer composé est retenu pour la campagne, sur cet appareil.
document.addEventListener('alpine:init', () => {
    const blank = {
        count: 1,
        sides: 20,
        reading: 'sum',
        threshold: '',
        count_high: true,
        keep: 1,
        keep_high: true,
        explode: 'none',
        modifier: 0,
        hide_threshold: false,
    };

    window.Alpine.data('diceBuilder', (storageKey) => ({
        ...blank,
        custom: '',
        visibility: 'table',
        label: '',
        targets: [],
        presetName: '',

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
            Object.assign(this, blank, formula);
            this.threshold = formula.threshold ?? '';
            this.custom = [4, 6, 8, 10, 12, 20, 100].includes(Number(this.sides)) ? '' : String(this.sides);
            if (label !== null) this.label = label;
        },

        get formula() {
            return {
                count: Number(this.count),
                sides: Number(this.sides),
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

        pick(sides) {
            this.sides = sides;
            this.custom = '';
        },

        customSides() {
            const value = parseInt(this.custom, 10);
            if (value >= 2 && value <= 1000) this.sides = value;
        },

        bump(delta) {
            this.count = Math.max(1, Math.min(30, Number(this.count) + delta));
            this.keep = Math.min(this.keep, this.count);
        },

        get needsThreshold() {
            return ['above', 'below', 'count'].includes(this.reading);
        },

        get hasModifier() {
            return ['sum', 'above', 'below', 'keep'].includes(this.reading);
        },

        get canExplode() {
            return this.reading !== 'digits';
        },

        get canExplodeDown() {
            return !['count', 'digits'].includes(this.reading);
        },

        setReading(reading) {
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
