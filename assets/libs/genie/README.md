# GenieUI

GenieUI is the core PHPFusion decorator for adding AI assistance to a textarea.
The textarea remains a normal Dynamics field; Genie owns the surrounding controls,
loading status, request, suggestions, and selection behaviour.

The complete implementation is owned by this directory:

- `genie.php` — PHP `genie_ui()` wrapper and HTML template
- `src/` — JavaScript and LESS source
- `dist/` — generated browser assets

Dynamics loads `genie.php` in component order; there is no second GenieUI
implementation under `includes/dynamics/`.

```php
echo genie_ui(form_textarea('notes', 'Notes', '', [
    'tiptap' => TRUE,
    'tiptap_format' => 'markdown',
]), [
    'namespace' => 'example',
    'task_key' => 'recommendation',
    'data' => ['record_id' => $record_id],
]);
```

The wrapper owns its CSRF token, loading state, request, suggestion panel, error
display, and selection-to-textarea binding. The application supplies only its
canonical task identity and the minimum record identifiers needed by its trusted
executor. Named callbacks remain available only for non-standard interactions;
normal textarea improvement does not require them. GenieUI never evaluates
JavaScript strings.

Source files live in `src/`. Rebuild committed assets with:

```powershell
npm.cmd run build:genie
```
