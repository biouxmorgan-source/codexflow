// Cœur de l'éditeur riche (Tiptap), chargé à la demande par rich-editor.js. Textes longs : gras, italique, titres, listes, citations,
// et liens [[…]] vers les fiches. Le texte reste enregistré en Markdown, lisible tel quel :
// [[Nom|42]] pour un lien, **gras**, « - » pour une liste.
import { Editor, Node, mergeAttributes } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import { Markdown } from '@tiptap/markdown';
import Suggestion from '@tiptap/suggestion';
import { PluginKey } from '@tiptap/pm/state';

const LINK = /^\[\[([^\[\]|\n]+?)(?:\|(\d+))?\]\]/;

const EntityLink = Node.create({
    name: 'entityLink',
    group: 'inline',
    inline: true,
    atom: true,
    selectable: true,

    addOptions() {
        return { search: async () => [], suggestions: null };
    },

    addAttributes() {
        return { id: { default: null }, name: { default: '' } };
    },

    parseHTML() {
        return [{ tag: 'span[data-entity-link]', getAttrs: (el) => ({ id: el.dataset.id || null, name: el.textContent }) }];
    },

    renderHTML({ node, HTMLAttributes }) {
        return ['span', mergeAttributes(HTMLAttributes, { 'data-entity-link': '', 'data-id': node.attrs.id, class: 'entity-chip' }), node.attrs.name];
    },

    renderText({ node }) {
        return node.attrs.name;
    },

    markdownTokenizer: {
        name: 'entityLink',
        level: 'inline',
        start: (src) => src.indexOf('[['),
        tokenize(src) {
            const match = LINK.exec(src);

            if (match) {
                return { type: 'entityLink', raw: match[0], name: match[1].trim(), id: match[2] ?? null };
            }
        },
    },

    parseMarkdown(token) {
        return { type: 'entityLink', attrs: { id: token.id, name: token.name } };
    },

    renderMarkdown(node) {
        const name = (node.attrs?.name ?? '').replace(/[\[\]|\n]/g, '');

        return node.attrs?.id ? `[[${name}|${node.attrs.id}]]` : `[[${name}]]`;
    },

    addProseMirrorPlugins() {
        return [
            Suggestion({
                editor: this.editor,
                pluginKey: new PluginKey('entityLink'),
                char: '[[',
                allowSpaces: true,
                allowedPrefixes: null,
                items: ({ query }) => this.options.search(query),
                command: ({ editor, range, props }) => {
                    editor.chain().focus().insertContentAt(range, [
                        { type: 'entityLink', attrs: { id: String(props.id), name: props.name } },
                        { type: 'text', text: ' ' },
                    ]).run();
                },
                render: this.options.suggestions,
            }),
        ];
    },
});

// Liste des fiches proposées sous le curseur, au clavier comme au doigt.
function suggestionList() {
    let list;
    let state;

    const draw = () => {
        list.replaceChildren(...state.items.map((item, index) => {
            const option = document.createElement('li');
            option.setAttribute('role', 'option');
            option.setAttribute('aria-selected', index === state.active ? 'true' : 'false');
            option.className = 'flex cursor-pointer items-center justify-between gap-3 px-3 py-2' + (index === state.active ? ' bg-codex-soft' : '');
            const name = document.createElement('span');
            name.className = 'font-medium';
            name.textContent = item.name;
            const type = document.createElement('span');
            type.className = 'text-xs text-stone-500';
            type.textContent = item.type;
            option.append(name, type);
            option.addEventListener('mousedown', (event) => {
                event.preventDefault();
                state.command(item);
            });
            return option;
        }));
        list.hidden = state.items.length === 0;
    };

    const place = () => {
        const rect = state.clientRect?.();

        if (rect) {
            list.style.left = `${Math.max(8, Math.min(rect.left, window.innerWidth - list.offsetWidth - 8))}px`;
            list.style.top = `${rect.bottom + 4}px`;
        }
    };

    return {
        onStart(props) {
            list = document.createElement('ul');
            list.setAttribute('role', 'listbox');
            list.className = 'fixed z-50 max-h-64 w-72 max-w-[calc(100vw-1rem)] overflow-auto rounded-md border border-stone-200 bg-white py-1 text-sm shadow-lg';
            document.body.append(list);
            state = { ...props, active: 0 };
            draw();
            place();
        },
        onUpdate(props) {
            state = { ...props, active: 0 };
            draw();
            place();
        },
        onKeyDown({ event }) {
            if (state.items.length === 0) {
                return false;
            }

            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                const step = event.key === 'ArrowDown' ? 1 : -1;
                state.active = (state.active + step + state.items.length) % state.items.length;
                draw();
                return true;
            }

            if (event.key === 'Enter' || event.key === 'Tab') {
                state.command(state.items[state.active]);
                return true;
            }

            if (event.key === 'Escape') {
                list.hidden = true;
                return true;
            }

            return false;
        },
        onExit() {
            list?.remove();
        },
    };
}

export function createRichEditor({ element, content, label, search, onUpdate, onTransaction }) {
    return new Editor({
        element,
        extensions: [
            StarterKit.configure({ heading: { levels: [2, 3] }, code: false, codeBlock: false, horizontalRule: false, link: false, underline: false, strike: false }),
            Markdown,
            EntityLink.configure({ search, suggestions: suggestionList }),
        ],
        content,
        contentType: 'markdown',
        editorProps: {
            attributes: {
                class: 'rich field',
                role: 'textbox',
                'aria-multiline': 'true',
                ...(label ? { 'aria-label': label } : {}),
            },
        },
        onUpdate,
        onTransaction,
    });
}
