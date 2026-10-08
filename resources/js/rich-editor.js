// Éditeur riche des textes longs : gras, italique, intertitres, listes, citations et liens
// [[…]] vers les fiches. Le texte reste enregistré en Markdown, lisible tel quel. Tiptap n'est
// chargé que sur les pages qui ont un tel champ ; d'ici là, la zone de texte reste utilisable.
document.addEventListener('alpine:init', () => {
    window.Alpine.data('richEditor', (model) => {
        // Hors des données Alpine : l'éditeur ne doit pas être rendu réactif.
        let editor;

        return {
            ready: false,
            marks: {},

            async init() {
                const { createRichEditor } = await import('./tiptap');
                const source = this.$refs.input;

                editor = createRichEditor({
                    element: this.$refs.editor,
                    content: source.value,
                    label: document.querySelector(`label[for="${source.id}"]`)?.textContent.trim(),
                    search: (query) => this.$wire.suggestEntities(query),
                    onUpdate: () => {
                        source.value = editor.getMarkdown();
                        source.dispatchEvent(new Event('input', { bubbles: true }));
                    },
                    onTransaction: () => this.refresh(),
                });

                // Texte vidé ou remplacé par Livewire (enregistrement, annulation) : l'éditeur suit.
                this.$wire.$watch(model, (value) => {
                    if ((value ?? '') !== editor.getMarkdown()) {
                        editor.commands.setContent(value ?? '', { contentType: 'markdown', emitUpdate: false });
                    }
                });

                this.ready = true;
                this.refresh();
            },

            refresh() {
                this.marks = {
                    bold: editor.isActive('bold'),
                    italic: editor.isActive('italic'),
                    heading: editor.isActive('heading', { level: 2 }),
                    bulletList: editor.isActive('bulletList'),
                    orderedList: editor.isActive('orderedList'),
                    blockquote: editor.isActive('blockquote'),
                };
            },

            run(command) {
                const chain = editor.chain().focus();
                ({
                    bold: () => chain.toggleBold(),
                    italic: () => chain.toggleItalic(),
                    heading: () => chain.toggleHeading({ level: 2 }),
                    bulletList: () => chain.toggleBulletList(),
                    orderedList: () => chain.toggleOrderedList(),
                    blockquote: () => chain.toggleBlockquote(),
                    link: () => chain.insertContent('[['),
                })[command]().run();
            },

            destroy() {
                editor?.destroy();
            },
        };
    });
});
