import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import BubbleMenu from '@tiptap/extension-bubble-menu'
import Highlight from '@tiptap/extension-highlight'
import Underline from '@tiptap/extension-underline'
import Link from '@tiptap/extension-link'
import Superscript from '@tiptap/extension-superscript'
import Subscript from '@tiptap/extension-subscript'
import TextAlign from '@tiptap/extension-text-align'
import Image from '@tiptap/extension-image'
import TaskList from '@tiptap/extension-task-list'
import TaskItem from '@tiptap/extension-task-item'

// Attach to window immediately
Object.assign(window, {
    TiptapEditor: Editor,
    TiptapStarterKit: StarterKit,
    TiptapBubbleMenu: BubbleMenu,
    TiptapHighlight: Highlight,
    TiptapUnderline: Underline,
    TiptapLink: Link,
    TiptapSuperscript: Superscript,
    TiptapSubscript: Subscript,
    TiptapTextAlign: TextAlign,
    TiptapImage: Image,
    TiptapTaskList: TaskList,
    TiptapTaskItem: TaskItem
});

console.log('Tiptap Bundle: Global assignments complete.');
//npx esbuild main.js --bundle --minify --format=iife --platform=browser --outfile=tiptap-core-full.js