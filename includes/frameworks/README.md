# UI frameworks

The complete source-grounded architecture, lifecycle, extension, debugging, and validation reference is:

```text
documentation/phpfusion/ui-framework-engine.md
```

The Tailwind community class and interaction adapter has its focused reference at `documentation/framework-bridge/README.md`. This package README is only a quick orientation; use the master document for implementation work.

Themes select one UI framework with a constant:

```php
const UI_FRAMEWORK = 'tailwind';
```

`themes/templates/layout.php` and `themes/templates/admin_layout.php` discover the matching manifest in
`includes/frameworks/{framework}/framework_db.php`, load only that framework, and execute its header and footer hooks.
This selection boundary can later read a database value without changing component callers.

Render shared components through the framework-neutral API:

```php
echo fusion_render_framework_component('notices', $notices);
echo fusion_render_framework_component('modal', $modal_options);
```

The compatible legacy form is:

```php
echo fusion_get_template('tabs', $tab_options);
```

A framework manifest supplies a stable `$framework_key` and lists its bootstrap file in `$framework_files`.
Its bootstrap file registers assets on `fusion_framework_header` and
`fusion_framework_footer`, then registers semantic component translators with
`fusion_register_framework_components()`.

## Bootstrap and Tabler

Tabler is a project-owned Bootstrap presentation variant. A theme selects the framework and variant without
loading any framework assets itself:

```php
const UI_FRAMEWORK = 'bootstrap';
const UI_FRAMEWORK_VERSION = '5';
const UI_FRAMEWORK_VARIANT = 'tabler';
```

The composite runtime lives in `includes/frameworks/bootstrap/tabler` and owns Tabler CSS and JavaScript,
the Tabler icon webfont, the SVG registry, and the Bootstrap compatibility adapter. Since Tabler bundles
Bootstrap and Popper, the variant does not also load the standalone Bootstrap bundle.

## Tailwind

The Tailwind implementation is project-owned, uses the `tw-` prefix and `--ui-*` semantic roles, and has no
Bootstrap or Tabler dependency.

Community markup is adapted in the browser by `tailwind/tailwind.js`. The bridge preserves Bootstrap/Tabler,
Bulma, Foundation, and custom source classes while adding canonical `tw-*` aliases through DOM APIs. It does not
run a regular expression over the server response. `framework_css()` remains an optional server-side fast path,
not a template-authoring requirement. See `documentation/framework-bridge/README.md` for the adapter and
interaction contract.

```powershell
npm.cmd run build:tailwind:framework
```

Jupiter adds its own theme-level Tailwind build while using the shared Tailwind framework for assets,
component translation, and interaction behavior.
