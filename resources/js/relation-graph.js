// Graphe des relations : placement par forces (sans bibliothèque), fiches déplaçables,
// molette pour zoomer, glisser le fond pour se déplacer. Un clic sur une fiche la met au centre.
// Les libellés qui se chevauchent sont déplacés ou masqués ; survoler une fiche les fait réapparaître.
const SVG = 'http://www.w3.org/2000/svg';

function el(name, attributes = {}, parent = null) {
    const node = document.createElementNS(SVG, name);
    Object.entries(attributes).forEach(([key, value]) => node.setAttribute(key, value));
    parent?.appendChild(node);
    return node;
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('relationGraph', () => ({
        nodes: [],
        edges: [],
        focus: null,
        labels: false,
        drag: null,
        view: { x: 0, y: 0, w: 1000, h: 600 },
        declutterFrame: null,

        init() {
            const data = JSON.parse(this.$refs.data.textContent);
            this.focus = data.focus;
            this.labels = data.focus !== null || data.nodes.length <= 25;
            this.nodes = data.nodes.map((node) => ({ ...node, color: data.colors[node.type_id] ?? '#78716c' }));
            const byId = new Map(this.nodes.map((node) => [node.id, node]));
            this.edges = data.edges.map((edge) => ({ ...edge, source: byId.get(edge.from), target: byId.get(edge.to) }));

            this.layout();
            this.draw();
            this.fit();
        },

        layout() {
            const count = this.nodes.length;
            const radius = 60 + count * 12;
            this.nodes.forEach((node, i) => {
                const angle = (2 * Math.PI * i) / Math.max(1, count);
                node.x = node.id === this.focus ? 0 : Math.cos(angle) * radius;
                node.y = node.id === this.focus ? 0 : Math.sin(angle) * radius;
                node.vx = 0;
                node.vy = 0;
            });

            const spring = 150;
            for (let step = 0; step < 300; step++) {
                const heat = 1 - step / 300;
                for (let i = 0; i < count; i++) {
                    const a = this.nodes[i];
                    for (let j = i + 1; j < count; j++) {
                        const b = this.nodes[j];
                        let dx = a.x - b.x;
                        let dy = a.y - b.y;
                        let d2 = dx * dx + dy * dy;
                        if (d2 < 0.01) {
                            dx = Math.random() - 0.5;
                            dy = Math.random() - 0.5;
                            d2 = 0.5;
                        }
                        const force = 16000 / d2;
                        const d = Math.sqrt(d2);
                        a.vx += (dx / d) * force;
                        a.vy += (dy / d) * force;
                        b.vx -= (dx / d) * force;
                        b.vy -= (dy / d) * force;
                    }
                }
                this.edges.forEach(({ source, target }) => {
                    const dx = target.x - source.x;
                    const dy = target.y - source.y;
                    const d = Math.max(1, Math.sqrt(dx * dx + dy * dy));
                    const force = (d - spring) * 0.05;
                    source.vx += (dx / d) * force;
                    source.vy += (dy / d) * force;
                    target.vx -= (dx / d) * force;
                    target.vy -= (dy / d) * force;
                });
                this.nodes.forEach((node) => {
                    node.vx -= node.x * 0.01;
                    node.vy -= node.y * 0.01;
                    if (node.id === this.focus) {
                        node.x = 0;
                        node.y = 0;
                    } else {
                        const speed = Math.sqrt(node.vx * node.vx + node.vy * node.vy);
                        const limit = 30 * heat + 1;
                        const scale = speed > limit ? limit / speed : 1;
                        node.x += node.vx * scale;
                        node.y += node.vy * scale;
                    }
                    node.vx *= 0.5;
                    node.vy *= 0.5;
                });
            }
        },

        draw() {
            const svg = this.$refs.svg;
            svg.querySelectorAll('[data-layer]').forEach((layer) => layer.remove());
            const edgeLayer = el('g', { 'data-layer': 'edges' }, svg);
            const nodeLayer = el('g', { 'data-layer': 'nodes' }, svg);

            this.edges.forEach((edge) => {
                edge.group = el('g', { class: 'graph-edge' }, edgeLayer);
                edge.line = el('line', { stroke: edge.gm ? '#b45309' : '#a8a29e', 'stroke-width': 1.5, 'marker-end': 'url(#graph-arrow)', 'stroke-dasharray': edge.gm ? '6 4' : '' }, edge.group);
                edge.text = el('text', { 'text-anchor': 'middle', 'font-size': 11, class: 'fill-stone-600', 'paint-order': 'stroke', stroke: 'var(--color-white)', 'stroke-width': 3, 'stroke-linejoin': 'round' }, edge.group);
                edge.text.textContent = edge.label;
                edge.text.style.display = this.labels ? '' : 'none';
            });

            this.nodes.forEach((node) => {
                const radius = node.id === this.focus ? 16 : 11;
                node.group = el('g', { class: 'cursor-pointer', 'data-node': node.id, tabindex: 0, role: 'button', 'aria-label': node.name }, nodeLayer);
                el('circle', { r: radius, fill: node.color, stroke: node.id === this.focus ? 'var(--color-ink)' : 'var(--color-white)', 'stroke-width': node.id === this.focus ? 3 : 2 }, node.group);
                node.radius = radius;
                const text = el('text', { y: radius + 14, 'text-anchor': 'middle', 'font-size': 13, 'font-weight': node.id === this.focus ? 700 : 500, class: 'fill-stone-800', 'paint-order': 'stroke', stroke: 'var(--color-white)', 'stroke-width': 4, 'stroke-linejoin': 'round' }, node.group);
                text.textContent = node.name;
                node.text = text;
                el('title', {}, node.group).textContent = `${node.name} · ${node.type}`;

                node.group.addEventListener('pointerenter', () => this.highlight(node, true));
                node.group.addEventListener('pointerleave', () => this.highlight(node, false));
                node.group.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        this.$wire.focusOn(node.id);
                    }
                });
            });

            this.place();
        },

        place() {
            this.nodes.forEach((node) => node.group.setAttribute('transform', `translate(${node.x} ${node.y})`));
            this.edges.forEach(({ source, target, line, text }) => {
                const dx = target.x - source.x;
                const dy = target.y - source.y;
                const d = Math.max(1, Math.sqrt(dx * dx + dy * dy));
                const end = (target.id === this.focus ? 16 : 11) + 3;
                line.setAttribute('x1', source.x);
                line.setAttribute('y1', source.y);
                line.setAttribute('x2', target.x - (dx / d) * end);
                line.setAttribute('y2', target.y - (dy / d) * end);
                text.setAttribute('x', (source.x + target.x) / 2);
                text.setAttribute('y', (source.y + target.y) / 2 - 4);
            });
            this.declutter();
        },

        // Noms des fiches : sous le cercle, sinon au-dessus, sinon masqués. Libellés des liens :
        // masqués s'ils recouvrent un nom ou un autre libellé. Recalculé au plus une fois par image.
        declutter() {
            if (this.declutterFrame) {
                return;
            }
            this.declutterFrame = requestAnimationFrame(() => {
                this.declutterFrame = null;
                const kept = [];
                const overlaps = (box) => kept.some((other) => box.x < other.x + other.width && other.x < box.x + box.width && box.y < other.y + other.height && other.y < box.y + box.height);
                const boxOf = (text, dx = 0, dy = 0) => {
                    const box = text.getBBox();
                    return { x: box.x + dx - 2, y: box.y + dy - 1, width: box.width + 4, height: box.height + 2 };
                };

                // Les cercles comptent comme occupés ; la fiche centrale passe en premier.
                this.nodes.forEach((node) => kept.push({ x: node.x - node.radius, y: node.y - node.radius, width: node.radius * 2, height: node.radius * 2 }));
                [...this.nodes].sort((a, b) => (b.id === this.focus) - (a.id === this.focus)).forEach((node) => {
                    node.text.style.display = '';
                    node.labelHidden = false;
                    for (const y of [node.radius + 14, -node.radius - 6]) {
                        node.text.setAttribute('y', y);
                        const box = boxOf(node.text, node.x, node.y);
                        if (!overlaps(box)) {
                            kept.push(box);
                            return;
                        }
                    }
                    node.text.setAttribute('y', node.radius + 14);
                    node.labelHidden = node.id !== this.focus;
                    node.text.style.display = node.labelHidden ? 'none' : '';
                });

                this.edges.forEach((edge) => {
                    edge.labelHidden = false;
                    if (!this.labels || !edge.label) {
                        return;
                    }
                    edge.text.style.display = '';
                    const box = boxOf(edge.text);
                    edge.labelHidden = overlaps(box);
                    edge.text.style.display = edge.labelHidden ? 'none' : '';
                    if (!edge.labelHidden) {
                        kept.push(box);
                    }
                });
            });
        },

        highlight(node, on) {
            const near = new Set([node]);
            this.edges.forEach((edge) => {
                const touches = edge.source === node || edge.target === node;
                edge.group.style.opacity = on && !touches ? 0.15 : 1;
                if (touches) {
                    near.add(edge.source).add(edge.target);
                }
                const shown = this.labels && !edge.labelHidden;
                edge.text.style.display = shown || (on && touches) ? '' : 'none';
            });
            // Les noms masqués faute de place reviennent pour la fiche survolée et ses voisines.
            this.nodes.forEach((other) => {
                if (other.labelHidden) {
                    other.text.style.display = on && near.has(other) ? '' : 'none';
                }
            });
        },

        fit() {
            if (this.nodes.length === 0) {
                return;
            }
            const xs = this.nodes.map((node) => node.x);
            const ys = this.nodes.map((node) => node.y);
            // Un petit réseau n'est pas agrandi au point d'avoir des noms énormes.
            const pad = 80;
            const w = Math.max(800, Math.max(...xs) - Math.min(...xs) + 2 * pad);
            const h = Math.max(500, Math.max(...ys) - Math.min(...ys) + 2 * pad);
            this.setView((Math.min(...xs) + Math.max(...xs) - w) / 2, (Math.min(...ys) + Math.max(...ys) - h) / 2, w, h);
        },

        setView(x, y, w, h) {
            this.view = { x, y, w, h };
            this.$refs.svg.setAttribute('viewBox', `${x} ${y} ${w} ${h}`);
        },

        point(event) {
            const matrix = this.$refs.svg.getScreenCTM().inverse();
            const p = new DOMPoint(event.clientX, event.clientY).matrixTransform(matrix);
            return { x: p.x, y: p.y };
        },

        down(event) {
            const group = event.target.closest('[data-node]');
            const p = this.point(event);
            const node = group ? this.nodes.find((n) => n.id === Number(group.dataset.node)) : null;
            this.drag = { node, start: p, client: { x: event.clientX, y: event.clientY }, moved: false, view: { ...this.view } };
            this.$refs.svg.setPointerCapture(event.pointerId);
        },

        move(event) {
            if (!this.drag) {
                return;
            }
            if (Math.abs(event.clientX - this.drag.client.x) + Math.abs(event.clientY - this.drag.client.y) > 4) {
                this.drag.moved = true;
            }
            if (this.drag.node) {
                const p = this.point(event);
                this.drag.node.x = p.x;
                this.drag.node.y = p.y;
                this.place();
            } else {
                const rect = this.$refs.svg.getBoundingClientRect();
                const ratio = Math.max(this.drag.view.w / rect.width, this.drag.view.h / rect.height);
                const { x, y, w, h } = this.drag.view;
                this.setView(x - (event.clientX - this.drag.client.x) * ratio, y - (event.clientY - this.drag.client.y) * ratio, w, h);
            }
        },

        up() {
            if (this.drag?.node && !this.drag.moved && this.drag.node.id !== this.focus) {
                this.$wire.focusOn(this.drag.node.id);
            }
            this.drag = null;
        },

        wheel(event) {
            this.zoomBy(event.deltaY < 0 ? 1.2 : 1 / 1.2, this.point(event));
        },

        zoomBy(factor, center = null) {
            const { x, y, w, h } = this.view;
            const c = center ?? { x: x + w / 2, y: y + h / 2 };
            const nw = Math.min(20000, Math.max(100, w / factor));
            const nh = (nw / w) * h;
            this.setView(c.x - ((c.x - x) * nw) / w, c.y - ((c.y - y) * nh) / h, nw, nh);
        },
    }));
});
