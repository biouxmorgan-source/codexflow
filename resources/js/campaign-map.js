// Carte de campagne, côté MJ : un clic sur un point ouvre sa sous-carte (ou ses détails) ;
// en « Placer des points », un clic sur la carte pose un point et on glisse les points pour les déplacer.
// Positions envoyées en pixels de l'image d'origine.
document.addEventListener('alpine:init', () => {
    window.Alpine.data('campaignMap', () => ({
        dragging: null,
        justDragged: false,

        editing() {
            return this.$refs.stage.dataset.editing === '1';
        },

        position(event) {
            const stage = this.$refs.stage;
            const rect = stage.getBoundingClientRect();
            const clamp = (v) => Math.min(1, Math.max(0, v));
            const fx = clamp((event.clientX - rect.left) / rect.width);
            const fy = clamp((event.clientY - rect.top) / rect.height);

            return {
                fx,
                fy,
                x: Math.round(fx * Number(stage.dataset.width) * 100) / 100,
                y: Math.round(fy * Number(stage.dataset.height) * 100) / 100,
            };
        },

        add(event) {
            if (!this.editing() || this.justDragged || event.target.closest('[data-point]')) {
                return;
            }

            const p = this.position(event);
            this.$wire.addPoint(p.x, p.y);
        },

        grab(event, id) {
            if (!this.editing() || event.button !== 0) {
                return;
            }

            event.currentTarget.setPointerCapture(event.pointerId);
            this.dragging = { id, el: event.currentTarget, sx: event.clientX, sy: event.clientY, moved: false };
        },

        drag(event) {
            const d = this.dragging;

            if (!d) {
                return;
            }

            if (!d.moved && Math.hypot(event.clientX - d.sx, event.clientY - d.sy) < 4) {
                return;
            }

            // Le point est ancré par sa pointe : on le pose là où se trouve le pointeur.
            const p = this.position(event);
            d.moved = true;
            d.el.style.left = `${p.fx * 100}%`;
            d.el.style.top = `${p.fy * 100}%`;
            d.x = p.x;
            d.y = p.y;
        },

        drop() {
            const d = this.dragging;
            this.dragging = null;

            if (d?.moved) {
                // Le clic qui suit le relâchement ne doit ni ouvrir le point ni en poser un autre.
                this.justDragged = true;
                setTimeout(() => (this.justDragged = false), 50);
                this.$wire.movePoint(d.id, d.x, d.y);
            }
        },

        open(id, url) {
            if (this.justDragged) {
                return;
            }

            if (!this.editing() && url) {
                window.Livewire.navigate(url);
            } else {
                this.$wire.select(id);
            }
        },
    }));
});
