// Les textes longs (liens [[…]] compris) passent par l'éditeur riche : voir rich-editor.js.
document.addEventListener('alpine:init', () => {
    // Champ d'une ligne où « [[ » propose les fiches (note rapide de séance) : le choix devient [[Nom|id]].
    window.Alpine.data('linkInput', () => ({
        open: false,
        items: [],
        active: 0,
        start: -1,

        async search() {
            const input = this.$refs.input;
            const before = input.value.slice(0, input.selectionStart);
            const match = before.match(/\[\[([^\[\]|\n]{0,60})$/);

            if (!match) {
                return this.close();
            }

            this.start = before.length - match[0].length;
            const query = match[1];
            const items = await this.$wire.suggestEntities(query);

            if (input.value.slice(0, input.selectionStart) !== before) {
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
                event.stopPropagation();
                this.close();
            }
        },

        choose(item) {
            const input = this.$refs.input;
            const link = `[[${item.name.replace(/[\[\]|]/g, '')}|${item.id}]] `;
            input.value = input.value.slice(0, this.start) + link + input.value.slice(input.selectionStart);
            const caret = this.start + link.length;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.focus();
            input.setSelectionRange(caret, caret);
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

import './rich-editor';
import './echo';
import './map-editor';
import './relation-graph';
import './pwa';
