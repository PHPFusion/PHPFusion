# Dynamics form components

Each form component is isolated in its own directory:

```text
form_text/
  model.php
  template.php (optional)
  component.js
```

- `model.php` owns the existing PHP function, option normalization, validation,
  data preparation, and server-side behavior.
- `template.php` is an optional presentation boundary for components that
  still translate assembled markup. Components that call `framework_css()`
  while rendering their classes can return their HTML directly.
- `component.js` is reserved for component-scoped progressive enhancement.
  Existing parameterized scripts remain in the model until they can be moved
  without duplicating server rules or changing the public function contract.

Register new components in `dynamics_component_manifest()` in
`includes/dynamics.php`. Do not add loose PHP component entry files back to
this directory.
