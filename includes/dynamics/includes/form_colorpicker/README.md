# Dynamics Color Picker

Dependency-free replacement for the Dynamics jscolor integration. Uses the existing
`form_colorpicker()` API and owns its PHP, LESS/CSS and JavaScript in this folder.
The previous `assets/colorpick/jscolor.min.js` asset is no longer loaded by this helper.

```php
echo form_colorpicker('accent', 'Accent', '#71717B', [
    'formats' => 'ALL', // default; also 'HEX', 'RGB', 'CSS', 'HSL', or ['HEX', 'RGB']
    'format' => 'HEX',  // initial format, defaults to the first allowed format if restricted
    'required' => TRUE,
    'inner_text' => 'Choose the accent color for this surface.',
]);
```

- The label and its optional `inner_text` form the left column; the neutral trigger
  sits at the far end of the same flex row. `ext_tip` appears on a separate row below.
  Label size and colors follow `form_text`. `floating_label`, `inline`,
  `width` and `inner_width` no longer alter the layout.
- The trigger displays the swatch, active format and complete value. One allowed
  format hides the format selector inside the popup. Format restrictions control
  output choices; pasting any supported color converts it to the selected format.
- HEX submits `#RRGGBB` or `#RRGGBBAA`; RGB submits `rgb(r, g, b)` or
  `rgba(r, g, b, a)`; CSS submits `rgb(r g b / a)`; HSL submits
  `hsl(h s% l% / a)`. Opaque colors omit alpha. CSS input accepts browser-supported
  concrete colors (including names) and converts them to sRGB; context-dependent
  `var()`, `currentColor` and inheritance keywords are not supported.
- The text editor displays the color without alpha; the adjacent percentage
  controls opacity. Pasting a value with explicit alpha updates both controls.
- Existing field name/id, posted values, Defender registration, required,
  disabled, help and error options are retained. Empty values stay empty until edited.
- Keyboard: Enter/Space opens the trigger; palette arrows adjust saturation and
  brightness (Shift uses larger steps); native range keys adjust hue and opacity;
  Escape closes and returns focus. Outside click and focus leaving dismiss the popup.
- EyeDropper uses the browser's native screen sampler. Its button is disabled with
  an explanatory tooltip when the API or secure context is unavailable; cancellation
  leaves the color unchanged. No screen-capture dependency or permission fallback.
- Newly inserted fragments initialize automatically. Form reset restores the initial
  value. The original input emits bubbling `input` and `change` events.

```js
const root = document.querySelector('[data-colorpicker]');
DynamicsColorPicker.setValue(root, 'rgba(113, 113, 123, 0.42)');
DynamicsColorPicker.getValue(root);
DynamicsColorPicker.init(fragment); // optional, MutationObserver normally handles this
```

Rebuild with `lessc.cmd --clean-css includes/dynamics/includes/form_colorpicker/component.less includes/dynamics/includes/form_colorpicker/component.css`.
Run `node tests/colorpicker_contract.cjs` and `php tests/colorpicker_contract.php`.
The PHP contract also serves a database-free browser fixture via PHP's local dev server.

Visual anatomy references (no registry source copied or installed):
- https://www.shadcn.io/components/color-picker
- https://www.shadcnblocks.com/component/color-picker/color-picker-alpha-1
- https://developer.mozilla.org/en-US/docs/Web/API/EyeDropper
