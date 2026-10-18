/**
 * « Organiser l'écran » : glisser-déposer des blocs ([data-block]) entre les colonnes
 * ([data-screen-column]) d'un écran, à la souris comme au doigt, ou avec les flèches.
 * Le DOM est réordonné tout de suite, puis l'ordre part au serveur (saveLayout) :
 * le rendu qui revient correspond déjà à ce qui est affiché.
 */
document.addEventListener('alpine:init', () => {
    window.Alpine.data('screenArranger', () => ({
        dragging: null,

        start(event) {
            const block = event.target.closest('[data-block]');
            if (! block || ! this.$root.contains(block) || event.button > 0) return;
            event.preventDefault();
            this.dragging = block;
            block.classList.add('opacity-60', 'ring-2', 'ring-codex');

            const move = (e) => this.over(e);
            const up = () => {
                window.removeEventListener('pointermove', move);
                window.removeEventListener('pointerup', up);
                window.removeEventListener('pointercancel', up);
                this.drop();
            };
            window.addEventListener('pointermove', move);
            window.addEventListener('pointerup', up);
            window.addEventListener('pointercancel', up);
        },

        over(event) {
            if (! this.dragging) return;
            // Près du haut ou du bas de la fenêtre, la page défile pour atteindre les blocs cachés.
            if (event.clientY < 60) window.scrollBy(0, -14);
            else if (event.clientY > window.innerHeight - 60) window.scrollBy(0, 14);

            const element = document.elementFromPoint(event.clientX, event.clientY);
            const column = element?.closest('[data-screen-column]');
            if (! column || ! this.$root.contains(column)) return;

            const target = element.closest('[data-block]');
            if (target && target !== this.dragging && target.parentElement === column) {
                const rect = target.getBoundingClientRect();
                target[event.clientY > rect.top + rect.height / 2 ? 'after' : 'before'](this.dragging);
            } else if (! target) {
                const last = this.blocksOf(column).filter((b) => b !== this.dragging).at(-1);
                if (! last || event.clientY > last.getBoundingClientRect().bottom) column.append(this.dragging);
            }
        },

        drop() {
            if (! this.dragging) return;
            this.dragging.classList.remove('opacity-60', 'ring-2', 'ring-codex');
            this.dragging = null;
            this.save();
        },

        /** Flèches : un cran plus haut ou plus bas, en passant d'une colonne à la suivante. */
        move(button, step) {
            const block = button.closest('[data-block]');
            const columns = this.columns();
            const column = block.parentElement;
            const index = columns.indexOf(column);
            const sibling = step < 0 ? block.previousElementSibling : block.nextElementSibling;

            if (sibling?.matches('[data-block]')) {
                sibling[step < 0 ? 'before' : 'after'](block);
            } else if (columns[index + step]) {
                step < 0 ? columns[index + step].append(block) : columns[index + step].prepend(block);
            } else {
                return;
            }
            button.focus();
            block.scrollIntoView({ block: 'nearest' });
            this.save();
        },

        columns() {
            return [...this.$root.querySelectorAll('[data-screen-column]')];
        },

        blocksOf(column) {
            return [...column.children].filter((child) => child.matches('[data-block]'));
        },

        save() {
            const layout = {};
            this.columns().forEach((column) => {
                layout[column.dataset.screenColumn] = this.blocksOf(column).map((block) => block.dataset.block);
            });
            this.$wire.saveLayout(layout);
        },
    }));
});
