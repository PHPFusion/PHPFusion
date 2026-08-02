# Tiptap library

The project-owned Tiptap integration is consolidated here:

- `src/tiptap.js` is the editable source.
- `src/tiptap.less` owns editor and bubble-menu appearance without a theme palette.
- `dist/tiptap.js` is the readable browser bundle.
- `dist/tiptap.min.js` is the production bundle loaded by Dynamics.
- `dist/tiptap.css` is the compiled component stylesheet loaded by Dynamics.

Build both browser bundles from the repository root:

```powershell
npm run build:tiptap
```

Themes may assign the documented `--tiptap-*` color roles, but component layout
and appearance stay in `src/tiptap.less`. Do not edit files under `dist/` directly.
