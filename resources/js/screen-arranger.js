/**
 * « Organiser l'écran » : glisser-déposer des blocs ([data-block]) d'un écran ([data-screen-grid]), à la souris
 * comme au doigt, ou avec les flèches, et choix de leur largeur (1, 2 ou 3 colonnes).
 *
 * Chaque bloc porte sa colonne de départ (data-column) et sa largeur (data-span). Sur un grand écran, il est
 * posé juste sous les blocs qui le précèdent dans l'ordre et occupent l'une de ses colonnes : les colonnes
 * restent tassées, et un bloc large passe sous tout ce qui est au-dessus de lui. Sur un petit écran, les blocs
 * s'empilent dans l'ordre. Le DOM est réordonné tout de suite, puis la disposition part au serveur (saveLayout) :
 * le rendu qui revient correspond déjà à ce qui est affiché.
 */
const WIDE = '(min-width: 64rem)';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('screenArranger', () => ({
        dragging: null,
        placed: new Map(),

        init() {
            this.media = window.matchMedia(WIDE);
            this.relayout = () => this.schedule();
            this.media.addEventListener('change', this.relayout);
            window.addEventListener('resize', this.relayout);
            this.sizes = new ResizeObserver(this.relayout);
            // Livewire réécrit les blocs (et retire les styles posés ici) : on recale après chaque passage.
            this.changes = new MutationObserver((mutations) => {
                const touched = (m) => {
                    if (m.target === this.$root) return m.type === 'childList' || m.attributeName === 'style' || ('placed' in this.$root.dataset) !== this.wide();
                    return m.type === 'attributes' && m.target.parentElement === this.$root && this.placed.get(m.target) !== m.target.getAttribute('style');
                };
                if (mutations.some(touched)) this.schedule();
            });
            this.changes.observe(this.$root, { childList: true, attributes: true, attributeFilter: ['style', 'data-placed'], subtree: true });
            this.layout();
        },

        destroy() {
            this.media.removeEventListener('change', this.relayout);
            window.removeEventListener('resize', this.relayout);
            this.sizes.disconnect();
            this.changes.disconnect();
        },

        schedule() {
            if (this.frame) return;
            this.frame = requestAnimationFrame(() => {
                this.frame = null;
                this.layout();
            });
        },

        columnCount() {
            return Math.max(1, parseInt(getComputedStyle(this.$root).getPropertyValue('--screen-columns'), 10) || 3);
        },

        wide() {
            return this.media.matches && this.columnCount() > 1;
        },

        /** Colonne de départ et largeur du bloc, ramenées dans la grille. */
        place(block, columns = this.columnCount()) {
            const span = Math.min(columns, Math.max(1, parseInt(block.dataset.span, 10) || 1));
            const column = Math.min(columns - span + 1, Math.max(1, parseInt(block.dataset.column, 10) || 1));
            return { column, span };
        },

        layout() {
            const blocks = this.blocks();
            blocks.forEach((block) => this.sizes.observe(block));

            if (! this.wide()) {
                delete this.$root.dataset.placed;
                blocks.forEach((block) => this.setStyle(block, null));
                return;
            }

            this.$root.dataset.placed = '';
            const columns = this.columnCount();
            const gap = parseFloat(getComputedStyle(this.$root).columnGap) || 0;
            const places = blocks.map((block) => this.place(block, columns));
            // D'abord la largeur (elle change la hauteur), puis la hauteur de chacun.
            blocks.forEach((block, i) => {
                block.style.gridColumn = `${places[i].column} / span ${places[i].span}`;
            });
            const heights = blocks.map((block) => Math.ceil(block.getBoundingClientRect().height));
            const bottoms = Array(columns).fill(0);

            blocks.forEach((block, i) => {
                const { column, span } = places[i];
                const covered = bottoms.slice(column - 1, column - 1 + span);
                const top = Math.max(...covered);
                this.setStyle(block, `grid-column: ${column} / span ${span}; grid-row: ${top + 1} / span ${Math.max(1, heights[i])};`);
                if (heights[i] > 0) {
                    for (let c = column - 1; c < column - 1 + span; c++) bottoms[c] = top + heights[i] + gap;
                }
            });
        },

        setStyle(block, style) {
            if (style === null) block.removeAttribute('style');
            else if (block.getAttribute('style') !== style) block.setAttribute('style', style);
            this.placed.set(block, block.getAttribute('style'));
        },

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

            const rect = this.$root.getBoundingClientRect();
            if (event.clientX < rect.left - 40 || event.clientX > rect.right + 40) return;
            const before = this.signature();

            if (this.wide()) {
                // La colonne sous le pointeur devient celle du bloc (sa largeur ne change pas).
                const columns = this.columnCount();
                const pointer = Math.min(columns, Math.max(1, Math.floor(((event.clientX - rect.left) / rect.width) * columns) + 1));
                const { span } = this.place(this.dragging, columns);
                this.dragging.dataset.column = Math.min(pointer, columns - span + 1);
            }

            const element = document.elementFromPoint(event.clientX, event.clientY);
            const target = element?.closest('[data-block]');
            if (target && target !== this.dragging && target.parentElement === this.$root) {
                const box = target.getBoundingClientRect();
                target[event.clientY > box.top + box.height / 2 ? 'after' : 'before'](this.dragging);
            } else if (! target || target.parentElement !== this.$root) {
                // Dans le vide d'une colonne : après le dernier bloc de ces colonnes qui commence plus haut.
                const others = this.neighbours(this.dragging);
                const above = others.filter((b) => b.getBoundingClientRect().top < event.clientY).at(-1);
                if (above) above.after(this.dragging);
                else if (others[0]) others[0].before(this.dragging);
            }

            if (this.signature() !== before) this.layout();
        },

        drop() {
            if (! this.dragging) return;
            this.dragging.classList.remove('opacity-60', 'ring-2', 'ring-codex');
            this.dragging = null;
            this.save();
        },

        /** Flèches ↑ ↓ : un cran plus haut ou plus bas, parmi les blocs qui partagent une colonne avec lui. */
        move(button, step) {
            const block = button.closest('[data-block]');
            const others = this.neighbours(block);
            const blocks = this.blocks();
            const index = blocks.indexOf(block);
            const target = step < 0
                ? others.filter((b) => blocks.indexOf(b) < index).at(-1)
                : others.find((b) => blocks.indexOf(b) > index);
            if (! target) return;
            target[step < 0 ? 'before' : 'after'](block);
            this.changed(button, block);
        },

        /** Flèches ← → : une colonne plus à gauche ou plus à droite. */
        shift(button, step) {
            const block = button.closest('[data-block]');
            const { column } = this.place(block);
            const next = this.place({ dataset: { column: column + step, span: block.dataset.span } });
            if (next.column === column) return;
            block.dataset.column = next.column;
            this.changed(button, block);
        },

        /** Largeur choisie dans la liste : 1, 2 ou 3 colonnes. */
        resize(select, span) {
            const block = select.closest('[data-block]');
            block.dataset.span = span;
            block.dataset.column = this.place(block).column;
            this.changed(select, block);
        },

        changed(control, block) {
            this.layout();
            control.focus();
            block.scrollIntoView({ block: 'nearest' });
            this.save();
        },

        /** Les autres blocs qui partagent au moins une colonne avec celui-ci, dans l'ordre (tous sur petit écran). */
        neighbours(block) {
            const others = this.blocks().filter((b) => b !== block);
            if (! this.wide()) return others;
            const mine = this.place(block);
            return others.filter((b) => {
                const theirs = this.place(b);
                return theirs.column < mine.column + mine.span && mine.column < theirs.column + theirs.span;
            });
        },

        blocks() {
            return [...this.$root.children].filter((child) => child.matches('[data-block]'));
        },

        signature() {
            return this.blocks().map((b) => `${b.dataset.block}:${b.dataset.column}:${b.dataset.span}`).join(',');
        },

        /**
         * Une seule sauvegarde à la fois, et toujours la dernière disposition : deux clics rapides ne peuvent pas
         * arriver au serveur dans le désordre.
         */
        async save() {
            this.pending = this.blocks().map((block) => ({ id: block.dataset.block, ...this.place(block) }));
            if (this.saving) return;
            this.saving = true;
            while (this.pending) {
                const layout = this.pending;
                this.pending = null;
                try {
                    await this.$wire.saveLayout(layout);
                } catch (e) {
                    // Requête perdue : la prochaine manipulation renverra la disposition complète.
                }
            }
            this.saving = false;
        },
    }));
});
