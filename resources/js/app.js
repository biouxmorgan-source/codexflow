// Autocomplétion des liens internes : taper « [[ » dans un champ propose les fiches existantes.
// Le choix insère [[Nom|id]], que l'affichage transforme en lien cliquable.
document.addEventListener('alpine:init', () => {
    window.Alpine.data('entityLinkInput', () => ({
        open: false,
        items: [],
        active: 0,
        start: null,
        query: '',

        onInput() {
            const input = this.$refs.input;
            const before = input.value.slice(0, input.selectionStart);
            const match = before.match(/\[\[([^\[\]|\n]{0,60})$/);

            if (!match) {
                this.close();
                return;
            }

            this.start = input.selectionStart - match[1].length;
            this.query = match[1];
            this.search(this.query);
        },

        async search(query) {
            const items = await this.$wire.suggestEntities(query);

            if (query !== this.query) {
                return;
            }

            this.items = items;
            this.active = 0;
            this.open = items.length > 0;
        },

        onKeydown(event) {
            if (!this.open) {
                return;
            }

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                this.active = (this.active + 1) % this.items.length;
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                this.active = (this.active - 1 + this.items.length) % this.items.length;
            } else if (event.key === 'Enter' || event.key === 'Tab') {
                event.preventDefault();
                this.choose(this.items[this.active]);
            } else if (event.key === 'Escape') {
                this.close();
            }
        },

        choose(item) {
            const input = this.$refs.input;
            const after = input.value.slice(input.selectionStart).replace(/^[^\[\]|\n]*\]\]/, '');
            const link = `${item.name.replace(/[\[\]|]/g, '')}|${item.id}]]`;

            input.value = input.value.slice(0, this.start) + link + after;
            const caret = this.start + link.length;
            input.setSelectionRange(caret, caret);
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.focus();
            this.close();
        },

        close() {
            this.open = false;
            this.items = [];
        },
    }));

    // Choix d'une fiche par son nom : renseigne l'identifiant dans la propriété Livewire donnée.
    window.Alpine.data('entityPicker', (model) => ({
        open: false,
        items: [],
        active: 0,
        query: '',

        init() {
            this.$wire.$watch(model, (value) => {
                if (value === null) {
                    this.query = '';
                }
            });
        },

        async search() {
            const query = this.query;
            this.$wire.set(model, null, false);
            const items = await this.$wire.suggestEntities(query);

            if (query !== this.query) {
                return;
            }

            this.items = items;
            this.active = 0;
            this.open = items.length > 0;
        },

        onKeydown(event) {
            if (!this.open) {
                return;
            }

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                this.active = (this.active + 1) % this.items.length;
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                this.active = (this.active - 1 + this.items.length) % this.items.length;
            } else if (event.key === 'Enter' || event.key === 'Tab') {
                event.preventDefault();
                this.choose(this.items[this.active]);
            } else if (event.key === 'Escape') {
                this.close();
            }
        },

        choose(item) {
            this.query = item.name;
            this.$wire.set(model, item.id, false);
            this.close();
        },

        close() {
            this.open = false;
            this.items = [];
        },
    }));
});

import './echo';
