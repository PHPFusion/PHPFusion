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

## JSON fields

Use `form_json()` when structured JSON belongs in a normal form:

```php
echo form_json('traits_json', 'Traits', $row['traits_json'], [
    'description' => 'Store reusable communication traits.',
    'root_type' => 'auto', // auto, object, or array
    'class' => 'mb-3',
]);
```

The submitted value remains a hidden textarea registered with Defender. The
component progressively adds a compact item/property summary and a modal for
adding, updating, removing, reviewing, and applying structured values. Both
object roots and array roots are supported; server code remains responsible
for domain-specific validation after decoding the JSON. The Parent selector
targets any existing object or array path. A comma-separated value becomes an
array of strings; a value without commas remains a string.
