// Lecteur PDF (pdf.js) : le même rendu partout, tablette et téléphone compris, là où
// l'aperçu intégré du navigateur manque ou diffère. pdf.js n'est chargé que sur les pages
// qui affichent un PDF, dans sa version « legacy » (compatible avec les tablettes et navigateurs
// moins récents). Mode « scroll » : toutes les pages, rendues quand elles approchent ;
// mode « screen » (écran de table) : une page à la fois, ajustée à l'écran, flèches pour tourner.
document.addEventListener('alpine:init', () => {
    window.Alpine.data('pdfViewer', (url, mode = 'scroll') => {
        // Hors des données Alpine : les objets pdf.js ne doivent pas devenir réactifs.
        let pdf;
        let loading;
        let observer;

        return {
            status: 'loading',
            page: 1,
            pages: 0,
            zoom: 1,

            async init() {
                try {
                    const [pdfjs, worker] = await Promise.all([
                        import('pdfjs-dist/legacy/build/pdf.mjs'),
                        import('pdfjs-dist/legacy/build/pdf.worker.min.mjs?url'),
                    ]);
                    pdfjs.GlobalWorkerOptions.workerSrc = worker.default;
                    loading = pdfjs.getDocument({ url, isEvalSupported: false, enableScripting: false, withCredentials: true });
                    pdf = await loading.promise;
                    this.pages = pdf.numPages;
                    this.status = 'ready';
                    await this.$nextTick();
                    mode === 'screen' ? await this.renderScreen() : await this.layoutScroll();
                } catch (error) {
                    console.error(error);
                    this.status = 'error';
                }
            },

            async layoutScroll() {
                const pages = this.$refs.pages;
                pages.replaceChildren();
                observer?.disconnect();
                observer = new IntersectionObserver((entries) => {
                    for (const entry of entries) {
                        if (entry.isIntersecting && !entry.target.dataset.rendered) {
                            entry.target.dataset.rendered = '1';
                            this.renderInto(entry.target, Number(entry.target.dataset.page));
                        }
                    }
                }, { root: this.$refs.scroller, rootMargin: '400px 0px' });

                const first = await pdf.getPage(1);
                const ratio = first.getViewport({ scale: 1 }).height / first.getViewport({ scale: 1 }).width;

                for (let number = 1; number <= this.pages; number++) {
                    const holder = document.createElement('div');
                    holder.dataset.page = number;
                    holder.className = 'mx-auto mb-3 bg-white shadow-sm';
                    holder.style.width = `${this.zoom * 100}%`;
                    holder.style.aspectRatio = `1 / ${ratio}`;
                    pages.append(holder);
                    observer.observe(holder);
                }
            },

            async renderInto(holder, number) {
                const page = await pdf.getPage(number);
                const base = page.getViewport({ scale: 1 });
                const scale = (holder.clientWidth / base.width) * (window.devicePixelRatio || 1);
                const viewport = page.getViewport({ scale });
                const canvas = document.createElement('canvas');
                canvas.width = Math.floor(viewport.width);
                canvas.height = Math.floor(viewport.height);
                canvas.className = 'block h-auto w-full';
                canvas.setAttribute('aria-label', `${number} / ${this.pages}`);
                holder.style.aspectRatio = `${base.width} / ${base.height}`;
                await page.render({ canvas, viewport }).promise;
                holder.replaceChildren(canvas);
            },

            async renderScreen() {
                const page = await pdf.getPage(this.page);
                const box = this.$refs.scroller.getBoundingClientRect();
                const base = page.getViewport({ scale: 1 });
                const fit = Math.min(box.width / base.width, box.height / base.height);
                const viewport = page.getViewport({ scale: fit * (window.devicePixelRatio || 1) });
                const canvas = document.createElement('canvas');
                canvas.width = Math.floor(viewport.width);
                canvas.height = Math.floor(viewport.height);
                canvas.style.width = `${Math.floor(base.width * fit)}px`;
                canvas.style.height = `${Math.floor(base.height * fit)}px`;
                await page.render({ canvas, viewport }).promise;
                this.$refs.pages.replaceChildren(canvas);
            },

            go(delta) {
                const next = Math.min(this.pages, Math.max(1, this.page + delta));
                if (next !== this.page) {
                    this.page = next;
                    this.renderScreen();
                }
            },

            setZoom(delta) {
                this.zoom = Math.min(3, Math.max(0.5, Math.round((this.zoom + delta) * 4) / 4));
                this.layoutScroll();
            },

            destroy() {
                observer?.disconnect();
                // Le document se libère par sa tâche de chargement (PDFDocumentProxy n'a pas de destroy).
                loading?.destroy();
            },
        };
    });
});
