# Dynamics Markdown Filter

Canonical client-side Markdown rendering for Dynamics controls and asynchronous previews.

- Uses `markdown-it` with raw HTML disabled.
- Preserves Tiptap Markdown fallback tags only when they are exact, paired tags: `u`, `sub`, `sup`, and `mark`.
- Exposes `window.DynamicsMarkdownFilter.render(markdown)` and `renderInline(markdown)`.

Build from the project root:

```powershell
npm.cmd run build:dynamics-markdown
```
