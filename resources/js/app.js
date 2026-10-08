// Les textes longs (liens [[…]] compris) passent par l'éditeur riche : voir rich-editor.js.
document.addEventListener('alpine:init', () => {
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
