// Carte de la table, côté MJ : glisser la carte ou un jeton, zoomer à la molette, tracer la règle.
// Le cadrage envoyé au serveur est celui de l'écran de table.
document.addEventListener('alpine:init', () => {
    window.Alpine.data('mapEditor', () => ({
        mode: 'move',
        drag: null,
        ruler: false,
        saveTimer: null,

        svg() {
            return this.$refs.stage.querySelector('svg[data-width]');
        },

        size() {
            const svg = this.svg();
            return { width: Number(svg.dataset.width), height: Number(svg.dataset.height), grid: Number(svg.dataset.grid) };
        },

        viewBox() {
            const [x, y, w, h] = this.svg().getAttribute('viewBox').split(' ').map(Number);
            return { x, y, w, h };
        },

        setViewBox(x, y, w, h) {
            const value = `${x} ${y} ${w} ${h}`;
            this.svg().setAttribute('viewBox', value);
            this.$refs.overlay.setAttribute('viewBox', value);
        },

        point(event) {
            const svg = this.svg();
            const p = svg.createSVGPoint();
            p.x = event.clientX;
            p.y = event.clientY;
            return p.matrixTransform(svg.getScreenCTM().inverse());
        },

        // Unités de carte par pixel d'écran (le SVG garde ses proportions).
        unitsPerPixel() {
            const rect = this.svg().getBoundingClientRect();
            const v = this.viewBox();
            return Math.max(v.w / rect.width, v.h / rect.height);
        },

        down(event) {
            if (event.button !== 0) {
                return;
            }

            const token = event.target.closest('[data-token]');
            const p = this.point(event);
            this.$refs.stage.setPointerCapture(event.pointerId);

            if (this.mode === 'ruler') {
                this.drag = { type: 'ruler', x1: p.x, y1: p.y, x2: p.x, y2: p.y };
                this.drawRuler();
                this.ruler = true;
            } else if (token) {
                const [tx, ty] = token.getAttribute('transform').match(/-?[\d.]+/g).map(Number);
                this.drag = { type: 'token', el: token, id: Number(token.dataset.token), dx: p.x - tx, dy: p.y - ty, x: tx, y: ty, moved: false };
            } else {
                this.drag = { type: 'pan', cx: event.clientX, cy: event.clientY, view: this.viewBox(), upp: this.unitsPerPixel() };
            }
        },

        move(event) {
            if (!this.drag) {
                return;
            }

            if (this.drag.type === 'pan') {
                const { view, upp } = this.drag;
                this.setViewBox(view.x - (event.clientX - this.drag.cx) * upp, view.y - (event.clientY - this.drag.cy) * upp, view.w, view.h);
            } else if (this.drag.type === 'token') {
                const p = this.point(event);
                this.drag.x = Math.round((p.x - this.drag.dx) * 100) / 100;
                this.drag.y = Math.round((p.y - this.drag.dy) * 100) / 100;
                this.drag.moved = true;
                this.drag.el.setAttribute('transform', `translate(${this.drag.x} ${this.drag.y})`);
            } else {
                const p = this.point(event);
                this.drag.x2 = p.x;
                this.drag.y2 = p.y;
                this.drawRuler();
            }
        },

        up() {
            const drag = this.drag;
            this.drag = null;

            if (!drag) {
                return;
            }

            if (drag.type === 'pan') {
                this.saveView();
            } else if (drag.type === 'token' && drag.moved) {
                this.$wire.moveToken(drag.id, drag.x, drag.y);
            } else if (drag.type === 'ruler') {
                this.ruler = false;
                if (Math.hypot(drag.x2 - drag.x1, drag.y2 - drag.y1) > this.size().grid / 4) {
                    this.$wire.setRuler(drag.x1, drag.y1, drag.x2, drag.y2);
                }
            }
        },

        wheel(event) {
            this.zoomBy(event.deltaY < 0 ? 1.15 : 1 / 1.15, this.point(event));
        },

        // Zoom autour d'un point (le pointeur, ou le centre de la vue).
        zoomBy(factor, around = null) {
            const { width } = this.size();
            const v = this.viewBox();
            const zoom = Math.min(8, Math.max(1, (width / v.w) * factor));
            const w = width / zoom;
            const h = v.h * (w / v.w);
            const p = around ?? { x: v.x + v.w / 2, y: v.y + v.h / 2 };
            this.setViewBox(p.x - (p.x - v.x) * (w / v.w), p.y - (p.y - v.y) * (h / v.h), w, h);

            clearTimeout(this.saveTimer);
            this.saveTimer = setTimeout(() => this.saveView(), 350);
        },

        saveView() {
            const { width } = this.size();
            const v = this.viewBox();
            this.$wire.setView(v.x + v.w / 2, v.y + v.h / 2, width / v.w);
        },

        drawRuler() {
            const { grid } = this.size();
            const d = this.drag;
            const line = this.$refs.rulerLine;
            const text = this.$refs.rulerText;

            line.setAttribute('x1', d.x1);
            line.setAttribute('y1', d.y1);
            line.setAttribute('x2', d.x2);
            line.setAttribute('y2', d.y2);
            line.setAttribute('stroke-width', Math.max(2, grid / 12));
            line.setAttribute('stroke-dasharray', `${grid / 4} ${grid / 6}`);
            text.setAttribute('x', d.x2);
            text.setAttribute('y', d.y2 - Math.max(10, grid * 0.35));
            text.setAttribute('font-size', Math.max(14, grid * 0.5));
            text.setAttribute('stroke-width', Math.max(3, grid / 10));
            text.textContent = this.distance(Math.hypot(d.x2 - d.x1, d.y2 - d.y1) / grid);
        },

        distance(cells) {
            const data = this.$root.dataset;
            const format = (n) => new Intl.NumberFormat(document.documentElement.lang || undefined, { maximumFractionDigits: 1 }).format(Math.round(n * 10) / 10);

            return data.scale
                ? `${format(cells * Number(data.scale))} ${data.unit}`
                : `${format(cells)} ${cells < 2 ? data.case : data.cases}`;
        },
    }));
});
