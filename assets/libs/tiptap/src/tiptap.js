// import { Editor } from 'https://esm.sh/@tiptap/core';
// import StarterKit from 'https://esm.sh/@tiptap/starter-kit';
// import Placeholder from 'https://esm.sh/@tiptap/extension-placeholder';
// import BubbleMenu from 'https://esm.sh/@tiptap/extension-bubble-menu';
// // import TurndownService from 'https://esm.sh/turndown';
// import Highlight from 'https://esm.sh/@tiptap/extension-highlight';
// import Typography from 'https://esm.sh/@tiptap/extension-typography';
// import TaskList from 'https://esm.sh/@tiptap/extension-task-list';
// import TaskItem from 'https://esm.sh/@tiptap/extension-task-item';
// import Subscript from 'https://esm.sh/@tiptap/extension-subscript'; // 1. Added Subscript
// import Superscript from 'https://esm.sh/@tiptap/extension-superscript'; // 2. Added Superscript
// import { Markdown } from 'https://esm.sh/tiptap-markdown';

import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import BubbleMenu from '@tiptap/extension-bubble-menu';
import Highlight from '@tiptap/extension-highlight';
import Typography from '@tiptap/extension-typography';
import TaskList from '@tiptap/extension-task-list';
import TaskItem from '@tiptap/extension-task-item';
import Underline from '@tiptap/extension-underline';
import Subscript from '@tiptap/extension-subscript';
import Superscript from '@tiptap/extension-superscript';
import Mention from '@tiptap/extension-mention';
import { PluginKey } from '@tiptap/pm/state';
import { Markdown } from 'tiptap-markdown';

/**
 * Build a Mention extension wired to the shared dropdown (window.MentionMenu,
 * defined in includes/jscripts/mention.js) and the project's @-mention endpoint.
 *
 * `cfg` mirrors the mention.js config: { url, idCol, titleCol, trigger, qParam, minChars }.
 * Picks insert an atomic chip node, are mirrored into the `{fieldId}_mentions`
 * hidden input, and serialise to clean "@Name" markdown for getMarkdown().
 */
function buildMentionExtension(cfg, fieldId) {
    const trigger = cfg.trigger || '@';
    // Own plugin key so a user-dismissed popup can cleanly end the suggestion
    // (focus is in our search box, so the editor never sees the Escape key).
    const pluginKey = new PluginKey('phpFusionMention_' + fieldId);

    const recordMention = (id, title) => {
        const reg = document.getElementById(fieldId + '_mentions');
        if (!reg) return;
        let arr = [];
        try { arr = JSON.parse(reg.value || '[]'); } catch (e) { arr = []; }
        if (!arr.some((m) => String(m.id) === String(id))) {
            arr.push({ id: id, title: title });
            reg.value = JSON.stringify(arr);
        }
    };

    // Renderer that delegates to the shared dropdown (search box + list + hover +
    // dark theme). We drive filtering from the popup's own search input, so the
    // editor never has to hold a half-typed query.
    const renderer = () => {
        let menu = null;
        let command = null;
        let editor = null;

        const reposition = (props) => {
            if (menu && props && props.clientRect && props.clientRect()) {
                window.MentionMenu.placeFixed(menu.el, props.clientRect());
            }
        };
        const teardown = () => { if (menu) { menu.destroy(); menu = null; } };

        return {
            onStart(props) {
                command = props.command;
                editor = props.editor;
                if (!window.MentionMenu) {
                    console.warn('[tiptap mention] window.MentionMenu unavailable — is includes/jscripts/mention.js loaded?');
                    return;
                }
                menu = window.MentionMenu.create({
                    config: cfg,
                    placeholder: 'Search…',
                    emptyText: 'No matches',
                    ignoreEl: editor.view.dom,
                    onPick: (item) => {
                        const cmd = command;
                        recordMention(item.id, item.title);
                        teardown();
                        if (cmd) cmd({ id: item.id, label: item.title }); // inserts the chip + exits
                    },
                    onClose: () => {
                        // Escape / outside click: tear down + end the ProseMirror suggestion.
                        teardown();
                        if (editor) editor.view.dispatch(editor.view.state.tr.setMeta(pluginKey, { exit: true }));
                    }
                });
                reposition(props);
                menu.seed(props.query || '');
                menu.focus();
            },
            onUpdate(props) {
                command = props.command;
                editor = props.editor;
                reposition(props);
                // If focus is still in the editor (search box not focused), keep the
                // popup in sync with whatever was typed after the trigger.
                if (menu && !menu.hasFocus()) menu.seed(props.query || '');
            },
            onKeyDown(props) {
                if (props.event.key === 'Escape') { if (menu) menu.close(); return true; }
                return menu ? menu.onKeyDown(props.event) : false;
            },
            onExit() {
                teardown();
            }
        };
    };

    return Mention.extend({
        // Provide a markdown serializer so tiptap-markdown's getMarkdown() emits a
        // clean "@Name" instead of falling back to raw HTML for this custom node.
        addStorage() {
            const parent = (this.parent && this.parent()) || {};
            return {
                ...parent,
                markdown: {
                    serialize(state, node) {
                        state.write((node.attrs.mentionSuggestionChar || trigger) + (node.attrs.label ?? node.attrs.id ?? ''));
                    },
                    parse: {}
                }
            };
        }
    }).configure({
        HTMLAttributes: { class: 'mention-chip' },
        deleteTriggerWithBackspace: true,
        suggestion: {
            char: trigger,
            pluginKey: pluginKey,
            allowSpaces: false,
            // The dropdown fetches its own results, so suggestion.items stays empty.
            items: () => [],
            render: renderer
        }
    });
}

window.initTiptapEditor = (textareaId, options = {}) => {
    const textarea = document.getElementById(textareaId);
    if (!textarea) return;

    if (textarea.dataset.tiptapInitialized === 'true') {
        return window.PHPFusionEditors && window.PHPFusionEditors[textareaId];
    }

    const outputFormat = options.output === 'html' ? 'html' : 'markdown';

    // const td = new TurndownService({
    //     headingStyle: 'atx',
    //     strongDelimiter: '**',
    //     emDelimiter: '_'
    // });

    const container = document.createElement('div');
    container.className = 'tiptap-editor-wrapper';
    textarea.parentNode.insertBefore(container, textarea);
    textarea.style.display = 'none';

    const menuEl = document.createElement('div');
    menuEl.className = 'tiptap-bubble-menu';
    menuEl.setAttribute('role', 'toolbar');
    menuEl.setAttribute('aria-label', 'Text formatting');
    menuEl.style.visibility = 'hidden';
    menuEl.style.opacity = '0';

    menuEl.innerHTML = `
    <div class="dropdown tiptap-dropdown-container">
        <button type="button" class="btn dropdown-toggle d-flex align-items-center gap-2 tiptap-dropdown-trigger" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="menu">
            <span class="current-label">Text</span>
        </button>
        <div class="dropdown-menu shadow-sm">
            <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-value="paragraph">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-inline" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M7 20v-16h5.5a4.5 4.5 0 1 1 0 9h-5.5" /></svg>
                <span>Text</span>
            </button>
            <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-value="heading1">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-inline" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M3 12h10M3 18V6M13 18V6M17 12a2 2 0 1 1 4 0v4M21 12v8" /></svg>
                <span>Heading 1</span>
            </button>
            <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-value="heading2">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-inline" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M3 12h10M3 18V6M13 18V6M17 12a2 2 0 1 1 4 0v4M17 20h4" /></svg>
                <span>Heading 2</span>
            </button>
            <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-value="heading3">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-inline" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M3 12h10M3 18V6M13 18V6M17 12a2 2 0 1 1 4 0v4M19 12h2M17 12a2 2 0 1 1 4 0v4a2 2 0 1 1 -4 0" /></svg>
                <span>Heading 3</span>
            </button>
            <div class="dropdown-divider"></div>
            <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-value="bulletList">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-inline" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M9 6l11 0M9 12l11 0M9 18l11 0M5 6l0 .01M5 12l0 .01M5 18l0 .01" /></svg>
                <span>Bulleted List</span>
            </button>
            <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-value="orderedList">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-inline" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M9 6l11 0M9 12l11 0M9 18l11 0M4 6h1v4M4 10h2" /></svg>
                <span>Numbered List</span>
            </button>
            <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-value="taskList">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-inline" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M9 11l3 3l8 -8" /><path d="M20 12v6a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h9" /></svg>
                <span>To-do List</span>
            </button>
            <div class="dropdown-divider"></div>
            <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-value="blockquote">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-inline" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M6 15h3l2 -2H6z" /><path d="M14 15h3l2 -2h-3z" /><path d="M5 9h5v5H5z" /><path d="M13 9h5v5h-5z" /></svg>
                <span>Blockquote</span>
            </button>
            <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-value="codeBlock">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-inline" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path d="M7 8l-4 4l4 4" /><path d="M17 8l4 4l-4 4" /><path d="M14 4l-4 16" /></svg>
                <span>Code Block</span>
            </button>
        </div>
    </div>
    <div class="vr mx-1"></div>
    <button type="button" data-command="bold" class="btn menu-btn"><svg width="24" height="24" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6 2.5C5.17157 2.5 4.5 3.17157 4.5 4V20C4.5 20.8284 5.17157 21.5 6 21.5H15C16.4587 21.5 17.8576 20.9205 18.8891 19.8891C19.9205 18.8576 20.5 17.4587 20.5 16C20.5 14.5413 19.9205 13.1424 18.8891 12.1109C18.6781 11.9 18.4518 11.7079 18.2128 11.5359C19.041 10.5492 19.5 9.29829 19.5 8C19.5 6.54131 18.9205 5.14236 17.8891 4.11091C16.8576 3.07946 15.4587 2.5 14 2.5H6ZM14 10.5C14.663 10.5 15.2989 10.2366 15.7678 9.76777C16.2366 9.29893 16.5 8.66304 16.5 8C16.5 7.33696 16.2366 6.70107 15.7678 6.23223C15.2989 5.76339 14.663 5.5 14 5.5H7.5V10.5H14ZM7.5 18.5V13.5H15C15.663 13.5 16.2989 13.7634 16.7678 14.2322C17.2366 14.7011 17.5 15.337 17.5 16C17.5 16.663 17.2366 17.2989 16.7678 17.7678C16.2989 18.2366 15.663 18.5 15 18.5H7.5Z" fill="currentColor"></path></svg></button>
    <button type="button" data-command="italic" class="btn menu-btn"><svg width="24" height="24" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M15.0222 3H19C19.5523 3 20 3.44772 20 4C20 4.55228 19.5523 5 19 5H15.693L10.443 19H14C14.5523 19 15 19.4477 15 20C15 20.5523 14.5523 21 14 21H9.02418C9.00802 21.0004 8.99181 21.0004 8.97557 21H5C4.44772 21 4 20.5523 4 20C4 19.4477 4.44772 19 5 19H8.30704L13.557 5H10C9.44772 5 9 4.55228 9 4C9 3.44772 9.44772 3 10 3H14.9782C14.9928 2.99968 15.0075 2.99967 15.0222 3Z" fill="currentColor"></path></svg></button>
    <button type="button" data-command="underline" class="btn menu-btn"><svg width="24" height="24" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M7 4C7 3.44772 6.55228 3 6 3C5.44772 3 5 3.44772 5 4V10C5 11.8565 5.7375 13.637 7.05025 14.9497C8.36301 16.2625 10.1435 17 12 17C13.8565 17 15.637 16.2625 16.9497 14.9497C18.2625 13.637 19 11.8565 19 10V4C19 3.44772 18.5523 3 18 3C17.4477 3 17 3.44772 17 4V10C17 11.3261 16.4732 12.5979 15.5355 13.5355C14.5979 14.4732 13.3261 15 12 15C10.6739 15 9.40215 14.4732 8.46447 13.5355C7.52678 12.5979 7 11.3261 7 10V4ZM4 19C3.44772 19 3 19.4477 3 20C3 20.5523 3.44772 21 4 21H20C20.5523 21 21 20.5523 21 20C21 19.4477 20.5523 19 20 19H4Z" fill="currentColor"></path></svg></button>
    <button type="button" data-command="strike" class="btn menu-btn"><svg width="24" height="24" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M9.00039 3H16.0001C16.5524 3 17.0001 3.44772 17.0001 4C17.0001 4.55229 16.5524 5 16.0001 5H9.00011C8.68006 4.99983 8.36412 5.07648 8.07983 5.22349C7.79555 5.37051 7.55069 5.5836 7.36585 5.84487C7.181 6.10614 7.06155 6.40796 7.01754 6.72497C6.97352 7.04198 7.00623 7.36492 7.11292 7.66667C7.29701 8.18737 7.02414 8.75872 6.50344 8.94281C5.98274 9.1269 5.4114 8.85403 5.2273 8.33333C5.01393 7.72984 4.94851 7.08396 5.03654 6.44994C5.12456 5.81592 5.36346 5.21229 5.73316 4.68974C6.10285 4.1672 6.59256 3.74101 7.16113 3.44698C7.72955 3.15303 8.36047 2.99975 9.00039 3Z" fill="currentColor"></path><path d="M18 13H20C20.5523 13 21 12.5523 21 12C21 11.4477 20.5523 11 20 11H4C3.44772 11 3 11.4477 3 12C3 12.5523 3.44772 13 4 13H14C14.7956 13 15.5587 13.3161 16.1213 13.8787C16.6839 14.4413 17 15.2044 17 16C17 16.7956 16.6839 17.5587 16.1213 18.1213C15.5587 18.6839 14.7956 19 14 19H6C5.44772 19 5 19.4477 5 20C5 20.5523 5.44772 21 6 21H14C15.3261 21 16.5979 20.4732 17.5355 19.5355C18.4732 18.5979 19 17.3261 19 16C19 14.9119 18.6453 13.8604 18 13Z" fill="currentColor"></path></svg></button>
    <button type="button" data-command="code" class="btn menu-btn"><svg width="24" height="24" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M15.4545 4.2983C15.6192 3.77115 15.3254 3.21028 14.7983 3.04554C14.2712 2.88081 13.7103 3.1746 13.5455 3.70175L8.54554 19.7017C8.38081 20.2289 8.6746 20.7898 9.20175 20.9545C9.72889 21.1192 10.2898 20.8254 10.4545 20.2983L15.4545 4.2983Z" fill="currentColor"></path><path d="M6.70711 7.29289C7.09763 7.68342 7.09763 8.31658 6.70711 8.70711L3.41421 12L6.70711 15.2929C7.09763 15.6834 7.09763 16.3166 6.70711 16.7071C6.31658 17.0976 5.68342 17.0976 5.29289 16.7071L1.29289 12.7071C0.902369 12.3166 0.902369 11.6834 1.29289 11.2929L5.29289 7.29289C5.68342 6.90237 6.31658 6.90237 6.70711 7.29289Z" fill="currentColor"></path><path d="M17.2929 7.29289C17.6834 6.90237 18.3166 6.90237 18.7071 7.29289L22.7071 11.2929C23.0976 11.6834 23.0976 12.3166 22.7071 12.7071L18.7071 16.7071C18.3166 17.0976 17.6834 17.0976 17.2929 16.7071C16.9024 16.3166 16.9024 15.6834 17.2929 15.2929L20.5858 12L17.2929 8.70711C16.9024 8.31658 16.9024 7.68342 17.2929 7.29289Z" fill="currentColor"></path></svg></button>
    <div class="vr mx-1"></div>
    <button type="button" data-command="subscript" class="btn menu-btn"><svg width="24" height="24" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12.7071 7.29289C13.0976 7.68342 13.0976 8.31658 12.7071 8.70711L4.70711 16.7071C4.31658 17.0976 3.68342 17.0976 3.29289 16.7071C2.90237 16.3166 2.90237 15.6834 3.29289 15.2929L11.2929 7.29289C11.6834 6.90237 12.3166 6.90237 12.7071 7.29289Z" fill="currentColor"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M3.29289 7.29289C3.68342 6.90237 4.31658 6.90237 4.70711 7.29289L12.7071 15.2929C13.0976 15.6834 13.0976 16.3166 12.7071 16.7071C12.3166 17.0976 11.6834 17.0976 11.2929 16.7071L3.29289 8.70711C2.90237 8.31658 2.90237 7.68342 3.29289 7.29289Z" fill="currentColor"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M17.405 1.40657C18.0246 1.05456 18.7463 0.92634 19.4492 1.04344C20.1521 1.16054 20.7933 1.51583 21.2652 2.0497L21.2697 2.05469L21.2696 2.05471C21.7431 2.5975 22 3.28922 22 4.00203C22 5.08579 21.3952 5.84326 20.7727 6.34289C20.1966 6.80531 19.4941 7.13675 18.9941 7.37261C18.9714 7.38332 18.9491 7.39383 18.9273 7.40415C18.4487 7.63034 18.2814 7.78152 18.1927 7.91844C18.1778 7.94155 18.1625 7.96834 18.1473 8.00003H21C21.5523 8.00003 22 8.44774 22 9.00003C22 9.55231 21.5523 10 21 10H17C16.4477 10 16 9.55231 16 9.00003C16 8.17007 16.1183 7.44255 16.5138 6.83161C16.9107 6.21854 17.4934 5.86971 18.0728 5.59591C18.6281 5.33347 19.1376 5.09075 19.5208 4.78316C19.8838 4.49179 20 4.25026 20 4.00203C20 3.77192 19.9178 3.54865 19.7646 3.37182C19.5968 3.18324 19.3696 3.05774 19.1205 3.01625C18.8705 2.97459 18.6137 3.02017 18.3933 3.14533C18.1762 3.26898 18.0191 3.45826 17.9406 3.67557C17.7531 4.19504 17.18 4.46414 16.6605 4.27662C16.141 4.0891 15.8719 3.51596 16.0594 2.99649C16.303 2.3219 16.7817 1.76125 17.4045 1.40689L17.405 1.40657Z" fill="currentColor"></path></svg></button>
    <button type="button" data-command="superscript" class="btn menu-btn"><svg width="24" height="24" class="tiptap-button-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M3.29289 7.29289C3.68342 6.90237 4.31658 6.90237 4.70711 7.29289L12.7071 15.2929C13.0976 15.6834 13.0976 16.3166 12.7071 16.7071C12.3166 17.0976 11.6834 17.0976 11.2929 16.7071L3.29289 8.70711C2.90237 8.31658 2.90237 7.68342 3.29289 7.29289Z" fill="currentColor"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M12.7071 7.29289C13.0976 7.68342 13.0976 8.31658 12.7071 8.70711L4.70711 16.7071C4.31658 17.0976 3.68342 17.0976 3.29289 16.7071C2.90237 16.3166 2.90237 15.6834 3.29289 15.2929L11.2929 7.29289C11.6834 6.90237 12.3166 6.90237 12.7071 7.29289Z" fill="currentColor"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M17.4079 14.3995C18.0284 14.0487 18.7506 13.9217 19.4536 14.0397C20.1566 14.1578 20.7977 14.5138 21.2696 15.0481L21.2779 15.0574L21.2778 15.0575C21.7439 15.5988 22 16.2903 22 17C22 18.0823 21.3962 18.8401 20.7744 19.3404C20.194 19.8073 19.4858 20.141 18.9828 20.378C18.9638 20.387 18.9451 20.3958 18.9266 20.4045C18.4473 20.6306 18.2804 20.7817 18.1922 20.918C18.1773 20.9412 18.1619 20.9681 18.1467 21H21C21.5523 21 22 21.4477 22 22C22 22.5523 21.5523 23 21 23H17C16.4477 23 16 22.5523 16 22C16 21.1708 16.1176 20.4431 16.5128 19.832C16.9096 19.2184 17.4928 18.8695 18.0734 18.5956C18.6279 18.334 19.138 18.0901 19.5207 17.7821C19.8838 17.49 20 17.2477 20 17C20 16.7718 19.9176 16.5452 19.7663 16.3672C19.5983 16.1792 19.3712 16.0539 19.1224 16.0121C18.8722 15.9701 18.6152 16.015 18.3942 16.1394C18.1794 16.2628 18.0205 16.4549 17.9422 16.675C17.7572 17.1954 17.1854 17.4673 16.665 17.2822C16.1446 17.0972 15.8728 16.5254 16.0578 16.005C16.2993 15.3259 16.7797 14.7584 17.4039 14.4018L17.4079 14.3995L17.4079 14.3995Z" fill="currentColor"></path></svg></button>
    `;

    const commandLabels = {
        bold: 'Bold',
        italic: 'Italic',
        underline: 'Underline',
        strike: 'Strikethrough',
        code: 'Inline code',
        subscript: 'Subscript',
        superscript: 'Superscript',
    };
    menuEl.querySelectorAll('[data-command]').forEach((button) => {
        const command = button.getAttribute('data-command');
        button.setAttribute('aria-label', commandLabels[command] || command);
        button.setAttribute('aria-pressed', 'false');
        button.dataset.state = 'off';
    });
    menuEl.querySelector('.dropdown-menu')?.setAttribute('role', 'menu');
    menuEl.querySelectorAll('.dropdown-item').forEach((item) => item.setAttribute('role', 'menuitem'));

    const closeLocalDropdown = () => {
        const trigger = menuEl.querySelector('.tiptap-dropdown-trigger');
        if (trigger && typeof bootstrap !== 'undefined') {
            const instance = bootstrap.Dropdown.getInstance(trigger);
            if (instance) instance.hide();
        }
    };

    const extensions = [
        StarterKit,
        Underline,
        Subscript,   // 3. Registered Extension
        Superscript, // 4. Registered Extension
        Placeholder.configure({ placeholder: textarea.placeholder || 'Start typing...' }),
        BubbleMenu.configure({
            element: menuEl,
            appendTo: () => document.body,
            shouldShow: ({ element, view, state }) => {
                const menuHasFocus = element.contains(document.activeElement);

                if (state.selection.empty || (!view.hasFocus() && !menuHasFocus)) {
                    closeLocalDropdown();
                    return false;
                }
                return true;
            },
        }),
        Highlight.configure({ multicolor: true }),
        Typography,
        TaskList,
        TaskItem.configure({ nested: true }),
        Markdown
    ];

    // @-mention chips: enabled per-field via form_textarea(['mention' => [...]]).
    // The config is delivered two ways for resilience: a JS registry populated by
    // form_textarea (immune to HTML attribute filtering / output sanitisers) and,
    // as a fallback, a data-mention attribute on the source textarea.
    let mentionConfig = null;
    try {
        if (window.PHPFusionMentionConfigs && window.PHPFusionMentionConfigs[textareaId]) {
            mentionConfig = window.PHPFusionMentionConfigs[textareaId];
        } else if (textarea.dataset.mention) {
            mentionConfig = JSON.parse(textarea.dataset.mention);
        }
    } catch (e) { mentionConfig = null; }
    if (mentionConfig && mentionConfig.url) {
        extensions.push(buildMentionExtension(mentionConfig, textareaId));
    } else if (textarea.dataset.mention || (window.PHPFusionMentionConfigs && window.PHPFusionMentionConfigs[textareaId])) {
        console.warn('[tiptap mention] config present but unusable for "' + textareaId + '"');
    }

    const editor = new Editor({
        element: container,
        extensions,
        content: textarea.value,
        editorProps: {
            attributes: {
                role: 'textbox',
                'aria-multiline': 'true',
                'aria-label': textarea.labels?.[0]?.textContent.trim() || textarea.placeholder || 'Text editor',
                'aria-required': textarea.getAttribute('aria-required') || 'false',
            },
        },
        onUpdate({ editor }) {
            // Sync Marks (includes sub/sup automatically)
            menuEl.querySelectorAll('.menu-btn').forEach(btn => {
                const command = btn.getAttribute('data-command');
                const isActive = editor.isActive(command);
                btn.dataset.state = isActive ? 'on' : 'off';
                btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });

            // Sync Dropdown Logic
            const label = menuEl.querySelector('.current-label');
            const items = menuEl.querySelectorAll('.dropdown-item');
            let activeLabel = 'Text';

            items.forEach(item => {
                const val = item.getAttribute('data-value');
                let isActive = false;
                if (val.startsWith('heading')) {
                    isActive = editor.isActive('heading', { level: parseInt(val.replace('heading', '')) });
                } else {
                    isActive = editor.isActive(val);
                }
                if (isActive) {
                    item.setAttribute('aria-current', 'true');
                } else {
                    item.removeAttribute('aria-current');
                }
                if (isActive) activeLabel = item.querySelector('span').innerText;
            });
            label.innerText = activeLabel;

            // --- FIX: Unified Variable Scoping ---
            // Make sure we use the same native element variable ("textarea") consistently
            if (textarea) {
                if (outputFormat === 'html') {
                    textarea.value = editor.getHTML();
                } else if (editor.storage && editor.storage.markdown) {
                    textarea.value = editor.storage.markdown.getMarkdown();
                } else {
                    textarea.value = editor.getHTML();
                }

                // Keep form submission and any progressive enhancement listeners in sync.
                textarea.dispatchEvent(new Event('change', { bubbles: true }));
            }
        },
    });

    const hideBubbleMenuOnExternalFocus = (event) => {
        const target = event.target;

        if (editor.isDestroyed || editor.view.dom.contains(target) || menuEl.contains(target)) {
            return;
        }

        closeLocalDropdown();
        editor.view.dispatch(editor.state.tr.setMeta('bubbleMenu', 'hide'));
    };

    document.addEventListener('focusin', hideBubbleMenuOnExternalFocus);
    editor.on('destroy', () => {
        document.removeEventListener('focusin', hideBubbleMenuOnExternalFocus);
    });

    // Dropdown Events
    menuEl.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const val = item.getAttribute('data-value');
            const chain = editor.chain().focus();

            if (val === 'paragraph') chain.setParagraph().run();
            else if (val === 'heading1') chain.toggleHeading({ level: 1 }).run();
            else if (val === 'heading2') chain.toggleHeading({ level: 2 }).run();
            else if (val === 'heading3') chain.toggleHeading({ level: 3 }).run();
            else if (val === 'bulletList') chain.toggleBulletList().run();
            else if (val === 'orderedList') chain.toggleOrderedList().run();
            else if (val === 'taskList') chain.toggleTaskList().run();
            else if (val === 'blockquote') chain.toggleBlockquote().run();
            else if (val === 'codeBlock') chain.toggleCodeBlock().run();

            closeLocalDropdown();
        });
    });

    // Mark Events (Bold, Italic, Underline, Strike, Code, Subscript, Superscript)
    menuEl.addEventListener('click', (e) => {
        const btn = e.target.closest('.menu-btn');
        if (!btn) return;
        e.preventDefault();
        const command = btn.getAttribute('data-command');
        if (command) {
            const method = `toggle${command.charAt(0).toUpperCase() + command.slice(1)}`;
            editor.chain().focus()[method]().run();
        }
    });

    window.PHPFusionEditors = window.PHPFusionEditors || {};
    window.PHPFusionEditors[textareaId] = editor;
    textarea.dataset.tiptapInitialized = 'true';

    return editor;
};
