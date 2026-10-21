// Carte de campagne, côté MJ : un clic sur un point ouvre sa sous-carte (ou ses détails) ;
// en « Placer des points », un clic sur la carte pose un point et on glisse les points pour les déplacer.
// Molette, + et − zooment ; glisser le fond déplace la carte (deux doigts pincent sur un écran tactile).
// Positions envoyées en pixels de l'image d'origine.
const MAX_ZOOM = 8;

document.addEventListener('alpine:init', () => {
    window.Alpine.data('campaignMap', () => ({
        dragging: null,
        justDragged: false,
        z: 1,
        tx: 0,
        ty: 0,
        pan: null,
        pointers: new Map(),

        editing() {
            return this.$refs.stage.dataset.editing === '1';
        },

        // Le calque zoomé donne directement la fraction de l'image sous le pointeur.
        position(event) {
            const stage = this.$refs.stage;
            const rect = this.$refs.canvas.getBoundingClientRect();
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

        // Zoom autour d'un point de l'écran (le centre par défaut), la carte couvrant toujours le cadre.
        zoomAt(factor, clientX, clientY) {
            const rect = this.$refs.stage.getBoundingClientRect();
            const cx = (clientX ?? rect.left + rect.width / 2) - rect.left;
            const cy = (clientY ?? rect.top + rect.height / 2) - rect.top;
            const z = Math.min(MAX_ZOOM, Math.max(1, this.z * factor));
            this.tx = cx - ((cx - this.tx) * z) / this.z;
            this.ty = cy - ((cy - this.ty) * z) / this.z;
            this.z = z;
            this.clampView();
        },

        zoomBy(factor) {
            this.zoomAt(factor);
        },

        wheel(event) {
            this.zoomAt(event.deltaY < 0 ? 1.2 : 1 / 1.2, event.clientX, event.clientY);
        },

        reset() {
            this.z = 1;
            this.tx = 0;
            this.ty = 0;
        },

        clampView() {
            const rect = this.$refs.stage.getBoundingClientRect();
            this.tx = Math.min(0, Math.max(rect.width * (1 - this.z), this.tx));
            this.ty = Math.min(0, Math.max(rect.height * (1 - this.z), this.ty));
        },

        panStart(event) {
            if (event.button !== 0 || event.target.closest('[data-point]') || event.target.closest('button')) {
                return;
            }

            this.pointers.set(event.pointerId, { x: event.clientX, y: event.clientY });
            this.$refs.stage.setPointerCapture(event.pointerId);
            this.pan = { x: event.clientX, y: event.clientY, tx: this.tx, ty: this.ty, moved: false, pinch: null };
        },

        panMove(event) {
            if (!this.pan || !this.pointers.has(event.pointerId)) {
                return;
            }

            this.pointers.set(event.pointerId, { x: event.clientX, y: event.clientY });

            // Deux doigts : pincer pour zoomer.
            if (this.pointers.size === 2) {
                const [a, b] = [...this.pointers.values()];
                const distance = Math.hypot(a.x - b.x, a.y - b.y);
                if (this.pan.pinch) {
                    this.zoomAt(distance / this.pan.pinch, (a.x + b.x) / 2, (a.y + b.y) / 2);
                }
                this.pan.pinch = distance;
                this.pan.moved = true;
                return;
            }

            const dx = event.clientX - this.pan.x;
            const dy = event.clientY - this.pan.y;

            if (!this.pan.moved && Math.hypot(dx, dy) < 4) {
                return;
            }

            this.pan.moved = true;
            this.tx = this.pan.tx + dx;
            this.ty = this.pan.ty + dy;
            this.clampView();
        },

        panEnd(event) {
            this.pointers.delete(event.pointerId);

            if (this.pan?.moved) {
                // Le clic qui suit un déplacement ne pose pas de point.
                this.justDragged = true;
                setTimeout(() => (this.justDragged = false), 50);
            }

            if (this.pointers.size === 0) {
                this.pan = null;
            } else if (this.pan) {
                const [rest] = [...this.pointers.values()];
                Object.assign(this.pan, { x: rest.x, y: rest.y, tx: this.tx, ty: this.ty, pinch: null });
            }
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

        // Hors placement, un point qui mène à une sous-carte l'ouvre (et l'écran de table suit) ; sinon, ses détails.
        open(id, target) {
            if (this.justDragged) {
                return;
            }

            if (!this.editing() && target) {
                this.$wire.openMap(target);
            } else {
                this.$wire.select(id);
            }
        },
    }));
});
