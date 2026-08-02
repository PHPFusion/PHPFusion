---
name: jupiter-admin-design
description: Source-grounded design contract for the PHPFusion Jupiter administration theme.
scope: themes/admin_themes/Jupiter
stack: PHP 8.1+, PHPFusion Tailwind framework, Jupiter LESS
status: canonical
---

# Jupiter admin design system

This document is the canonical visual contract for the Jupiter administration theme. It is based on a live inspection of Jupiter's public product surfaces and the six supplied PHPFusion/Jupiter administration reference screenshots on 29 July 2026:

- `https://jup.ag/terminal/alphascan`
- `https://jup.ag/portfolio/.../airdrop`
- `https://jup.ag/send`
- `https://jup.ag/onboard`

The supplied screenshots override any earlier generic recommendation in this document. The implementation borrows the measured product language—density, contrast, component geometry, and interaction hierarchy—not Jupiter's logo, illustrations, copy, or brand identity.

## 1. Direction

The interface is a dark, data-first operating surface. It should feel calm at rest and bright only where action or state matters. It extends Jupiter's product culture without copying its brand: near-black space, disciplined density, crisp blue-slate hairlines, cool blue-black controls, and scarce acid-lime interaction.

- Canvas is nearly black with a subtle cool blue bias. Warm brown, olive, and yellow-green neutrals are prohibited.
- Navigation and cards are separated with tone and one-pixel borders, not large shadows.
- Controls are compact: 38–40px inputs, 32–34px desktop buttons, and 40px isolated mobile actions.
- Primary action is acid lime. Never use it decoratively across large areas.
- Success is mint-teal, information is sky blue, warning is yellow, and danger is hot pink.
- Text uses deliberate functional roles: high, middle, muted, faint, and header-description/icon copy.
- Cards are quiet and flat on a cool blue-black surface. Nested rows and bounded controls use the next surface level; list-like hover uses a dedicated darker row treatment; raised controls and overlays remain visually distinct.
- Pills represent state or filters. Tabs represent destinations or views.

## 2. Foundations

### Color tokens

The source pages use OKLCH colors. The values below are the approved Elite EMS adaptation: cool blue-black surfaces and blue-slate structure keep the interface neutral while acid lime remains reserved for interaction.

| Token | Value | Use |
|---|---:|---|
| `canvas` | `#090D10` | Body and primary application canvas |
| `nav` | `#0B1117` | Sidebar |
| `topbar` | `#0B1015` | Fixed page header |
| `card` | `#0B1117` | Top-level cards and bordered page sections |
| `card-nested` | `#0D151E` | Card or panel nested inside another card |
| `surface` | `#0D151E` | Inputs, dropdowns, command palette |
| `surface-raised` | `#151E28` | Raised controls, neutral badges, and quiet empty states |
| `row-hover` | `#10171F` | Hovered dropdown, list, and table rows |
| `surface-strong` | `#19242E` | Emphasized panels and pressed filled controls |
| `border` | `#151E28` | Default divider and card border |
| `border-strong` | `#22303D` | Focusable controls and overlays |
| `text` | `#F1F5F9` | Headings and high-emphasis values |
| `text-mid` | `#CAD5E2` | Body and control labels |
| `text-muted` | `#90A1B9` | Supporting text and descriptions inside rows |
| `text-faint` | `#67778E` | Metadata, placeholders, disabled labels |
| `description` | `#6F85A6` | Card-header descriptions and leading menu icons |
| `primary` | `#C7F284` | Primary actions and selected indicators |
| `primary-hover` | `#D7F8A5` | Primary hover |
| `primary-active` | `#AED66D` | Primary pressed |
| `secondary` | `#A8B4C4` | Secondary actions |
| `secondary-hover` | `#CAD5E2` | Secondary hover |
| `default` | `#19242E` | Default filled control |
| `default-hover` | `#253443` | Default hover |
| `muted` | `#111A23` | Quiet control background |
| `muted-hover` | `#19242E` | Quiet control hover |
| `info` | `#38BDF8` | Informational state |
| `info-hover` | `#67D1FA` | Info hover |
| `success` | `#3CE3AB` | Complete, active, positive |
| `success-hover` | `#67EBC0` | Success hover |
| `warning` | `#F2C94C` | Attention and pending |
| `warning-hover` | `#F6D873` | Warning hover |
| `danger` | `#F23674` | Destructive and failed |
| `danger-hover` | `#F65F91` | Danger hover |

Status colors must always be paired with text, an icon, or a label. Color alone must not carry meaning.

### Color application

- Page canvas uses `canvas`; the sidebar and top-level cards use `nav`/`card`.
- Inputs, dropdowns, nested module rows, and grouped switch controls use `surface` or `card-nested`.
- Dropdown options, list items, table rows, and compact settings rows use `row-hover` (`#10171F`) for hover, keyboard focus, and routine selection.
- `surface-raised` is not a row-hover substitute. Reserve it for raised controls, neutral badges, and quiet empty states.
- Titles and important values use `text`; routine labels use `text-mid`; row descriptions use `text-muted`; metadata and placeholders use `text-faint`.
- `description` is limited to card-header supporting copy and leading menu icons. It must not replace `text-muted` inside data or settings rows.
- Primary lime appears only for primary actions, focus, and important selected indicators. Status colors remain semantic.

### Typography

- Primary family: `"Geist", ui-sans-serif, system-ui, sans-serif`.
- Monospace family: `"Geist Mono", ui-monospace, SFMono-Regular, monospace`.
- Topbar page title: 14px, weight 600.
- In-content section title: 18–20px, weight 600.
- Card title: 14px, weight 600.
- Body and controls: 14px; dense table and navigation rows may use 13px.
- Supporting text: 12px.
- Badges and compact metadata: 10–11px, weight 500–600.
- IDs, code, key caps, API-style values, and dense numerical telemetry use Geist Mono.
- Numeric tabular data: enable `font-variant-numeric: tabular-nums`.

### Shape and spacing

| Element | Height | Radius | Horizontal padding |
|---|---:|---:|---:|
| Compact icon/button | 32px | 8px | 8–10px |
| Standard button | 34px | 8px | 12–14px |
| Input/select | 38–40px | 8px | 10–12px |
| Badge | 16–20px | full pill | 6–8px |
| Tab | 36–40px | 0 | 12px |
| Card | auto | 12px | 20px |
| Dropdown/popover | auto | 10px | 6px outer |
| Modal | auto | 14px | 20px |

Use the 4px spacing grid. Normal component gaps are 8px or 12px; section gaps are 16px or 24px.

### Borders and elevation

- Default border: 1px solid `border`.
- Interactive border: 1px solid `border-strong`.
- Focus ring: 3px `rgba(199, 242, 132, .16)` plus a `primary` border.
- Dropdown: `0 16px 44px rgba(0, 0, 0, .38)`.
- Modal: `0 28px 80px rgba(0, 0, 0, .56)` over a dark blurred backdrop.
- Avoid gradients, glass blur, and luminous outer glows.

## 3. Component contract

### Buttons

All buttons are inline-flex, centered, gap 6px, 13px/600, and have a visible focus ring. Disabled buttons use 45% opacity and do not shift on hover.

| Variant | Background | Text | Border |
|---|---|---|---|
| Primary | `primary` | `#101710` | `primary` |
| Secondary | `transparent` | `secondary` | `border-strong` |
| Default | `default` | `text-mid` | `border-strong` |
| Muted | `muted` | `text-muted` | `border` |
| Info | 12% `info` | `info` | 30% `info` |
| Success | 12% `success` | `success` | 30% `success` |
| Warning | 12% `warning` | `warning` | 30% `warning` |
| Danger | 12% `danger` | `danger` | 30% `danger` |

Legacy button tokens are aliased by the shared framework to `tw-btn-*` components and must resolve to these roles rather than a stock framework palette.

Links inherit `text-mid`, shift to `primary` on hover or keyboard focus, and never render an underline on the anchor or any wrapped child element. Informational state styling is independent of link styling.

### Search and form controls

- Global command search is a 220px-by-32px input-style topbar trigger with a leading search icon, descriptive label, and trailing `/` key cap. Clicking it, pressing `/` outside an editable control, or pressing `Ctrl/Cmd + K` opens the centered command surface shown in the supplied search reference.
- Standard form controls are 38–40px tall, radius 8px, use the input surface, and use `border-strong`.
- Placeholder text uses `text-faint`.
- Hover adds a quiet raised background.
- Focus changes the border to `primary` and shows the focus ring.
- Search icons sit 10–12px from the edge; keyboard shortcuts are right aligned in a small neutral key cap.
- Validation uses an icon and message below the field. Do not rely on border color alone.

### Switches

- The compact switch track is 36px by 20px with a 16px thumb and one-pixel border.
- Unchecked uses `surface-raised` with `border-strong`; its thumb uses `text` so it reads as white against the dark rail.
- Checked uses `text` for the rail and border, then inverts the thumb to `canvas`. Checked state does not use primary lime.
- Keyboard focus retains the standard primary focus ring, keeping focus distinct from checked state.
- Disabled switches preserve the same inverse state treatment at 50% opacity and use a not-allowed cursor.

### Dropdowns and popovers

- Raised input surface with a strong border and 8px radius.
- Standard dropdown width is 320px where space permits; compact menus may use 216px. Use 6px internal padding.
- Menu items are 32px high with a 6px radius, 10px horizontal padding, and 14px/20px Geist at weight 500.
- Filter dropdowns include an immediately focused 36px search field, a visible selected-item check, and a footer with selection count and Clear action.
- Search filters visible options while typing; Clear restores the unfiltered, unselected state without closing the menu.
- Leading menu icons use a fixed 16px box and `description`; hover, focus, and selected states promote icons to `text-mid`.
- Item hover, focus, and selection use `row-hover` with `text`; routine menu selection does not introduce a primary tint.
- Separators use `border`; headings use 10px uppercase tracking and `text-faint`.
- The menu closes on Escape and outside click and returns focus to its trigger.

### Badges and pills

- Badges are 16–20px tall, full radius, 10–11px/600.
- Filled status badges use a 12% color tint, colored text, and a 25–30% border.
- Neutral badges use `surface-raised` with `text-muted`.
- Filter pills are 28–32px tall and use the button focus behavior.
- A selected filter pill uses a 10% primary background and primary border/text.

### Tabs

- Tabs sit on a one-pixel bottom divider.
- The active tab uses high text and a 2px primary underline.
- Inactive tabs use `text-muted`; hover uses `text-mid`.
- Tab labels remain on one line. The row scrolls horizontally on small screens.
- Pills are for filters or modes; underline tabs are for page destinations.

### Accordions

- Header height is at least 40px and remains a real button.
- The header uses a quiet transparent background; hover uses `surface`.
- Expanded state uses `surface` and `border-strong`.
- The chevron rotates 90 degrees with a 160ms transition.
- Body padding is 12px 14px 16px and starts beneath a border.
- `aria-expanded` and `aria-controls` are mandatory.

### Modal

- Backdrop is `rgba(3, 7, 10, .78)` with 6–8px background blur.
- Modal surface is the near-black overlay surface, border `border`, radius 14px.
- Standard width is 450px; small 380px; large 760px; extra-large 1040px.
- Header and footer are separated with one-pixel borders.
- Title is 15px/600. Close control is a 32px icon button.
- Initial focus moves into the modal; Escape closes it; focus returns to the opener.
- On mobile, leave 12px around the viewport and allow the body to scroll.

### Cards, tables, and lists

- Top-level cards use the `card` surface, a 1px `border`, 12px radius, and no default shadow. A card nested inside another card uses `card-nested`; do not create nested cards only to add decoration.
- Card titles are consistently 14px/20px at weight 600. Card descriptions are consistently 13px/20px at weight 400 using `description`.
- Compact settings/module rows use `card-nested`, a 1px `border`, 8px radius, and `row-hover` on hover or focus-within. Nest rows only when they form independently actionable records.
- A compact settings row keeps handle, icon, title, badges, muted description, grouped switch, feedback, and trailing actions on one desktop flex line. The identity area is the only growing column.
- The switch state label and track sit inside one `surface` wrapper with a `border`, 8px radius, and an 8–12px internal gap.
- Settings pages use stacked cards with 20px content padding and a separated action footer aligned right.
- Empty chart/data regions use a dashed `border` inside the card and remain at least 140px tall.
- Tables are dense: 40px header, 40–44px rows.
- Table headers are 11px/600 uppercase, with `text-faint`.
- Rows use a bottom border; hover uses `row-hover`.
- Selected rows use a primary-tinted inset edge plus `row-hover`, unless selection requires stronger emphasis.
- Sticky headers must retain an opaque background.
- Empty states are centered, concise, and offer one clear next action.

### Alerts, toasts, progress, and pagination

- Alerts and toasts use a 10–12% semantic tint with a 30% semantic border.
- Toasts use the same overlay elevation as dropdowns.
- Progress tracks are 6px high on `surface-raised`; bars use semantic colors.
- Pagination uses compact 32px square controls. Current page uses primary tint and text.

### PHPFusion Tailwind compatibility map

The shared framework retains legacy tokens only for third-party behavior and adds `tw-*` aliases to rendered markup. Jupiter styles the aliases exclusively:

- `.tw-btn`, `.tw-btn-*`, `.tw-btn-outline-*`, `.tw-btn-group`
- `.tw-badge`, `.tw-bg-*`, `.tw-text-*`
- `.tw-alert`, `.tw-toast`, `.tw-progress`
- `.tw-card`, `.tw-card-header`, `.tw-card-body`, `.tw-card-footer`
- `.tw-dropdown-menu`, `.tw-dropdown-item`, `.tw-dropdown-divider`
- `.tw-modal`, `.tw-offcanvas`, `.tw-popover`, `.tw-tooltip`
- `.tw-accordion`, `.tw-accordion-item`, `.tw-accordion-button`
- `.tw-nav-tabs`, `.tw-nav-pills`, `.tw-nav-link`, `.tw-tab-content`
- `.tw-form-control`, `.tw-form-select`, `.tw-form-check-input`, `.tw-input-group`
- `.select2-*`, date pickers, upload controls, rich-text toolbars
- `.tw-table`, `.dataTable`, `.dataTables_*`
- `.tw-pagination`, `.tw-page-link`, `.tw-list-group`
- `.tw-breadcrumb`, `.tw-avatar`

New visual rules belong in `acp_styles.less`; do not add one-off inline colors to templates.

## 4. Admin shell

- Desktop sidebar width: 242px with a 5px outer inset, 12px radius, and a one-pixel border.
- Sidebar background: `nav`. The bordered 52px brand switcher uses `IMAGES.'phpfusion-icon.png'` beside the escaped `$settings['sitename']`; it has no secondary “Administration” label.
- The sidebar header, navigation body, resource links, and account footer use the same `nav` surface. Do not divide the sidebar into two background tones.
- Legacy `.nav-content` state must never alter the sidebar width. The main workspace starts once at `x = 247px`; no flex basis or second margin may create an empty gutter.
- The first navigation label is `Platform`; navigation item height is 32px with a 7px radius.
- The navigation block starts 24px beneath the sidebar header so the label and first route have deliberate breathing room.
- Sidebar navigation text is `text` in Geist at 13px/20px and weight 500. Its icon and caret inherit the same foreground; hover, focus, open, and active states change both to primary lime. Use Geist Mono only for identifiers, code, shortcuts, and dense numeric values.
- Navigation, resource, caret, and account icons use one 16px outline-icon box. Expand/collapse carets remain on the right edge of the parent row.
- Hover, keyboard focus, open, and active navigation states share the same raised surface and lime foreground treatment.
- PHP resolves the active admin link before adding the administration base URL and aid token. An active descendant expands and highlights its parent section on the initial render.
- Expanded subnavigation starts with a one-pixel vertical guide aligned directly below the parent icon. Child rows have no repeated icons, are 28px high, and use the same hover/active treatment.
- Active navigation uses `surface-raised`, primary icon/text, and no glow.
- Nested navigation uses a subtle vertical guide and 30px child rows.
- Bottom resources appear above a compact 50px full-width account switcher containing a 32px avatar, an 11px online indicator, the user name, localized `getuserlevel()` rank, and a trailing up/down account-menu icon.
- The sidebar footer owns one uniform 8px inset on its left, right, and bottom edges; nested wrappers add no padding.
- Canvas topbar is 60px, sticky, `topbar`, with the context selector left, current page title centered, and actions right. Every toolbar control is vertically centered.
- The content breadcrumb is rendered with PHPFusion's `render_breadcrumbs()` immediately before page content. It uses a 13px Geist row: linked ancestors are semibold `text`, the current item is `muted`, and separators stay `text-faint`.
- Desktop page content starts 40px below the toolbar, uses 64px horizontal padding, and is centered inside a 1440px maximum-width box on 1440p, 1600p, and 4K displays.
- Below the boxed maximum, content remains fluid so page modules can use the available width without horizontal overflow.
- The main, canvas, topbar, and view container share the `canvas` surface so the workspace reads as one continuous window.
- The canvas is a full-height flex column: the topbar and footer/error console do not grow, while the view container grows to keep the footer at the bottom with 24px bottom breathing room.
- Below 800px the sidebar becomes a compact horizontal/overlay surface and content keeps at least 12px side padding.

## 5. Motion and accessibility

- Hover/focus transitions: 140–180ms.
- Accordion and menu transitions: no more than 200ms.
- Do not animate layout-heavy properties on large page regions.
- Respect `prefers-reduced-motion: reduce`.
- Minimum interactive target: 32px in dense desktop UI, 44px for isolated mobile actions.
- Maintain WCAG AA contrast for body text and controls.
- Every icon-only button requires an accessible name.
- Keep a visible skip link and strong `:focus-visible` treatment.

## 6. LESS implementation

Jupiter uses PHPFusion's shared Tailwind framework for `framework_css()` translations, prefixed utilities, component behavior, and compatibility aliases. The theme itself owns only its semantic tokens, `tw-*` component customization, and responsive shell in LESS; Bootstrap-named selectors do not belong in the theme source.

Build from the repository root:

```bash
lessc --clean-css --source-map=themes/admin_themes/Jupiter/acp_styles.min.css.map themes/admin_themes/Jupiter/acp_styles.less themes/admin_themes/Jupiter/acp_styles.min.css
```

Source of truth:

- `acp_styles.less` — Jupiter tokens, component mappings, and responsive shell
- `acp_styles.min.css` — compiled theme asset loaded after the shared framework stylesheet

Never edit `acp_styles.min.css` directly.
