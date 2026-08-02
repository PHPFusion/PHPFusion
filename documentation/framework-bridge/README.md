# PHPFusion Community Framework Bridge

This document is the focused contract for community class adaptation and Tailwind-side interactions. For framework selection, manifest discovery, boot timing, asset hooks, semantic component dispatch, Bootstrap/Tabler variants, AJAX behavior, extension procedures, and engine-wide debugging, read `documentation/phpfusion/ui-framework-engine.md` first.

PHPFusion accepts community markup written for Bootstrap/Tabler, Bulma, Foundation, or a project-owned vocabulary without requiring template authors to rewrite every class or wrap markup in `framework_css()`.

The Tailwind framework runtime preserves the original markup and progressively adds canonical `tw-*` component aliases in the browser. It has no Bootstrap, Bulma, Foundation, jQuery, or other runtime dependency.

## Architecture

```text
Community HTML
    Bootstrap/Tabler | Bulma | Foundation | custom adapter
                              |
                              v
    includes/frameworks/tailwind/tailwind.js
        DOM class adapter + delegated interactions
                              |
                              v
    canonical tw-* component and state classes
                              |
                              v
    includes/frameworks/tailwind/tailwind.css
        structural components using semantic --ui-* roles
                              |
                              v
    active theme CSS overrides presentation
```

The bridge uses the parsed DOM and `classList`. It does not run a regular expression across the complete server response and does not replace `innerHTML`.

## Author contract

Write normal semantic markup in the vocabulary appropriate to the theme or extension:

```html
<button class="btn btn-primary">Bootstrap</button>
<button class="button is-primary">Bulma</button>
<button class="button primary">Foundation</button>
```

When Tailwind is active, the bridge retains those source classes and adds the canonical aliases:

```html
<button class="btn btn-primary tw-btn tw-btn-primary">Bootstrap</button>
```

Do not wrap every class in `framework_css()`. The helper remains available as an optional server-side fast path for core-owned renderers and for useful no-JavaScript presentation, but it is not a community authoring requirement.

Unknown classes are never removed. A custom vocabulary continues to use its own CSS until its author registers an adapter.

## Built-in dialects

The built-in bridge covers the common component intersection rather than attempting to reproduce every framework plugin.

| Family | Bootstrap/Tabler | Bulma | Foundation | Canonical result |
|---|---|---|---|---|
| Grid | `row`, `col-md-6` | `columns`, `column is-half` | `grid-x`, `cell medium-6` | `tw-row`, responsive `tw-col-span-*` |
| Surface | `card` | `card`, `box` | `card` | `tw-card` |
| Button | `btn btn-primary` | `button is-primary` | `button primary` | `tw-btn tw-btn-primary` |
| Badge | `badge` | `tag` | `label` | `tw-badge` |
| Feedback | `alert` | `notification` | `callout` | `tw-alert` |
| Dialog | `modal` | `modal` | `reveal` | `tw-modal` contract |
| Menu | `dropdown-menu` | `dropdown-menu` | `dropdown-pane` | `tw-dropdown-menu` |

Also covered are page shells, containers, cards, button variants and groups, badges, Tabler light backgrounds, avatars, forms, tables, status dots, lists, pagination, tabs, collapses, offcanvas panels, common display/flex/text utilities, spacing utilities, and responsive columns.

### Bootstrap and Tabler utilities

The DOM bridge and optional PHP map use the same utility resolver. Source order is preserved, so this markup:

```html
<div class="ms-auto d-flex gap-2">...</div>
```

receives `tw-ms-auto tw-flex tw-gap-2` without removing `ms-auto d-flex gap-2`.

Supported utility families include:

- display, flex direction/wrap/grow/shrink, alignment, justification, order, and float;
- logical margin and padding, `auto` margins, negative margins, `gap`, `row-gap`, and `column-gap`;
- responsive `sm`, `md`, `lg`, `xl`, and `xxl` forms such as `ms-md-auto`, `d-lg-flex`, and `gap-xl-4`;
- width/height percentages, viewport sizing, overflow, visibility, position, borders, radius, shadows, typography, object fit, pointer events, and selection;
- Tabler `w-0` through `w-6`, `h-0` through `h-6`, `space-x-*`, `space-y-*`, `divide-x-*`, `divide-y-*`, and semantic surface backgrounds.

Spacing numbers are translated by value, not copied blindly. The shared Bootstrap/Tabler values are `0 → 0`, `1 → .25rem`, `2 → .5rem`, `3 → 1rem`, and `4 → 1.5rem`. Values `5 → 2rem` and `6 → 2.5rem` follow the bundled Tabler scale. Thus `gap-3` becomes `tw-gap-4`, while `gap-2` remains `tw-gap-2`. Bootstrap and Tabler breakpoints are used: 576, 768, 992, 1200, and 1400 pixels.

## Custom adapters

Register project vocabulary with the dependency-free public API:

```js
window.FusionUI.registerAdapter('community-theme', {
    classes: {
        'action': 'tw-btn',
        'action-main': 'tw-btn tw-btn-primary',
        'content-box': 'tw-card',
    },
    resolve(element, tokens, add) {
        if (tokens.has('span-half')) {
            add('tw-col-span-12 md:tw-col-span-6');
        }
    },
});
```

`classes` handles direct token mappings. Use `resolve` only for contextual or compound contracts. Preserve the source class and add one or more statically buildable `tw-*` aliases.

If an adapter registers after the initial page scan, the bridge refreshes the current document automatically. `window.FusionUI.adapt(root)` can explicitly refresh a document fragment.

## Dynamic markup and performance

The initial pass visits elements with classes and native selects. A batched `MutationObserver` then processes only added subtrees and changed class attributes.

Each processed element stores its source-class signature and the aliases owned by the bridge. Mutations caused only by adding `tw-*` classes are skipped, and stale bridge-owned aliases are removed when the source class list changes.

Performance depends on DOM elements and class tokens, not source-code line count. Do not replace this with full-response PHP regex processing, `innerHTML` rewriting, or repeated whole-document rescans after AJAX updates.

## Interaction contract

The same vanilla script provides delegated behavior for:

- Bootstrap 5 `data-bs-*` attributes;
- Bootstrap 3/4 `data-*` attributes;
- common Foundation `data-open`, `data-close`, and `data-toggle` targets;
- PHPFusion `data-fusion-*` and `data-tailwind-*` components.

It supports dropdowns, modals, collapses, tabs/pills, offcanvas panels, alerts, toasts, Escape dismissal, modal focus containment and focus return, ARIA expansion state, and Bootstrap-compatible custom events. A limited `window.bootstrap` facade remains available for existing programmatic calls.

The bridge does not emulate arbitrary third-party plugins such as framework-specific carousels, datepickers, tooltips, or commercial components. Those require a PHPFusion component or a purpose-built adapter.

## Source ownership

| Responsibility | Source |
|---|---|
| Browser class and interaction bridge | `includes/frameworks/tailwind/tailwind.js` |
| Canonical Tailwind component source | `includes/frameworks/tailwind/tailwind.input.css` |
| Generated framework bundle | `includes/frameworks/tailwind/tailwind.css` |
| Tailwind content and safelist configuration | `includes/frameworks/tailwind/tailwind.config.js` |
| Optional PHP translation map | `includes/frameworks/tailwind/tailwind_css.php` |
| Framework loader | `includes/frameworks/tailwind/tailwind_framework.php` |
| Contract checks | `tests/framework_css_contract.php` |

Never edit `tailwind.css` directly.

## Adding or changing compatibility

Use this procedure whenever rendered markup contains a Bootstrap or Tabler class that the Tailwind bridge missed.

### 1. Confirm the source contract

Copy the smallest real markup example and inspect the source framework's computed declaration. Do not infer behavior from the class name alone.

Authoritative local references are:

- Bootstrap utility definitions: `node_modules/bootstrap/scss/_utilities.scss`;
- compiled Bootstrap behavior: `includes/frameworks/bootstrap/v5/css/bootstrap.min.css`;
- compiled Tabler behavior and Tabler-only extensions: `includes/frameworks/bootstrap/tabler/tabler.min.css`.

Check whether the class is responsive, stateful, directional, negative, or part of a numeric scale. Bootstrap and Tabler can assign a different value than Tailwind to the same numeric suffix.

### 2. Choose the mapping type

| Missing class shape | Implementation location |
|---|---|
| One source token always produces the same aliases, such as `text-reset` | `builtinAliases` in `tailwind.js` and the static array in `tailwind_css.php` |
| A family with values or breakpoints, such as `float-md-end` or `mt-lg-n2` | `resolveContextualAliases()` in `tailwind.js` and the corresponding generated loop/map in `tailwind_css.php` |
| A canonical class needs CSS that Tailwind cannot express safely | `tailwind.input.css`, normally in `@layer utilities` |
| A Bootstrap/Tabler component rather than a utility | `builtinAliases`, plus its canonical anatomy in `@layer components` |
| Behavior driven by `data-bs-*` or state classes | the existing delegated interaction controller in `tailwind.js` |

Do not add a regular expression over complete HTML. A small regular expression that parses one already-tokenized `classList` value inside `resolveContextualAliases()` is appropriate.

### 3. Update both translation paths

The browser adapter is the community-facing path and must work with untouched markup. Add the class to `builtinAliases` when it is a direct mapping:

```js
'text-reset': 'tw-text-inherit',
```

Add the equivalent optional server mapping:

```php
'text-reset' => 'tw-text-inherit',
```

For a patterned family, update the JavaScript resolver and PHP generator together. They must produce the same aliases in the same order. Keep the original Bootstrap/Tabler class on the element; the bridge only adds owned `tw-*` aliases.

If the two source frameworks use the same class name with different computed values, prefer the behavior of the framework bundled and used by PHPFusion, document the ambiguity, and add a contract test for the chosen value.

### 4. Make the target buildable

Tailwind only emits classes it can discover during compilation.

- A literal target such as `'tw-text-inherit'` in a scanned source normally needs no safelist entry.
- A target assembled with interpolation, such as `` `tw-${utility}-${value}` ``, must be enumerated in `tailwind.config.js`.
- Responsive and negative targets must include their complete form, such as `md:tw-float-end` or `lg:-tw-mt-2`.
- If a target name collides with a canonical component, create a distinct name. For example, `d-table` maps to `tw-d-table`, not `tw-table`, because `.tw-table` is the PHPFusion table component.
- Add custom declarations only to `tailwind.input.css`; never patch generated `tailwind.css`.

### 5. Add regression coverage

Every added class needs at least these checks:

1. Add a `framework_css()` assertion to `tests/framework_css_contract.php`.
2. Assert an important generated selector when a new Tailwind target or custom class is introduced.
3. Add representative untouched markup to `tests/fixtures/framework_bridge.html` when the browser resolver changed.
4. In a browser, verify both the aliases in `className` and the relevant computed style.
5. If the family is responsive, verify below and above its breakpoint.
6. If markup may arrive through AJAX, insert it after page load and verify the `MutationObserver` adapts it.

### 6. Rebuild and review

Run the build and validation commands below. Then inspect the generated CSS for the expected selector and review the focused diff. A change is incomplete if JavaScript works but `framework_css()` disagrees, or if the alias appears in the DOM but its compiled selector is absent.

### Missing-class checklist

- [ ] Real source declaration confirmed from local Bootstrap/Tabler assets.
- [ ] Correct direct, patterned, component, or interaction path selected.
- [ ] Source class preserved and unknown classes unaffected.
- [ ] Browser and PHP mappings kept in parity.
- [ ] Dynamic targets safelisted, including responsive and negative variants.
- [ ] Semantic `--ui-*` roles used for color and presentation.
- [ ] Source CSS edited and generated CSS rebuilt.
- [ ] Contract, compiled-selector, dynamic-DOM, and breakpoint checks passed.

## Build and validation

```powershell
npm.cmd run build:tailwind:framework
php tests/framework_css_contract.php
node --check includes/frameworks/tailwind/tailwind.js
php -l includes/frameworks/tailwind/tailwind_framework.php
php -l includes/frameworks/tailwind/tailwind_css.php
git diff --check -- includes/frameworks/tailwind tests/framework_css_contract.php documentation/framework-bridge .agents/skills/phpfusion-framework
```

Browser verification must cover keyboard operation, Escape, focus return, dynamically inserted markup, and responsive behavior at 375px, 768px, 1024px, and 1440px.
