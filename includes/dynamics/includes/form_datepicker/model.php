<?php

/**
 * Escape a value for datepicker HTML attributes.
 */
function form_datepicker_escape_attribute($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Only expose callable JavaScript paths, never arbitrary inline code.
 */
function form_datepicker_callback_name($value): string
{
    $value = trim((string)$value);

    return preg_match('/^[A-Za-z_$][A-Za-z0-9_$]*(?:\.[A-Za-z_$][A-Za-z0-9_$]*)*$/', $value) ? $value : '';
}

/**
 * Normalize one incoming date or time without turning invalid input into 1970.
 */
function form_datepicker_normalize_value($value, string $format): string
{
    if ($value === NULL || $value === '') {
        return '';
    }

    if (is_numeric($value)) {
        return date($format, (int)$value);
    }

    $value = trim((string)$value);
    $date = \DateTime::createFromFormat('!'.$format, $value);
    if ($date instanceof \DateTime) {
        return $date->format($format);
    }

    $timestamp = strtotime($value);

    return $timestamp === FALSE ? $value : date($format, $timestamp);
}

/**
 * Normalize the initial scalar or range value rendered into the text input.
 */
function form_datepicker_normalize_input($value, array $options): string
{
    if (!$options['range']) {
        return form_datepicker_normalize_value($value, $options['date_format_php']);
    }

    $values = is_array($value)
        ? array_values($value)
        : preg_split('/\s*'.preg_quote((string)$options['range_separator'], '/').'\s*/', trim((string)$value), 2);
    $normalized = [];
    foreach ((array)$values as $part) {
        $part = form_datepicker_normalize_value($part, $options['date_format_php']);
        if ($part !== '') {
            $normalized[] = $part;
        }
    }

    return implode((string)$options['range_separator'], array_slice($normalized, 0, 2));
}

/**
 * Build the progressive Flatpickr controller configuration.
 */
function form_datepicker_config(array $options, string $label, string $input_value, array $date_filter, array $weekends): array
{
    $strings = array_merge([
        'open' => 'Open calendar',
        'previousMonth' => 'Previous month',
        'nextMonth' => 'Next month',
        'backToCalendar' => 'Back to calendar',
        'chooseTime' => 'Choose time',
        'done' => 'Done',
    ], is_array($options['strings']) ? $options['strings'] : []);

    return [
        'type' => (string)$options['type'],
        'initialValue' => $input_value,
        'dateFormat' => (string)$options['date_format_js'],
        'displayFormat' => (string)$options['display_format'],
        'displayPlaceholder' => (string)$options['display_placeholder'],
        'range' => (bool)$options['range'],
        'rangeSeparator' => (string)$options['range_separator'],
        'enableTime' => (bool)$options['showTime'],
        'timeOnly' => $options['type'] === 'time',
        'time24hr' => TRUE,
        'enableSeconds' => (bool)$options['enable_seconds'],
        'minuteIncrement' => max(1, (int)$options['minute_increment']),
        'allowInput' => (bool)$options['allow_input'],
        'floatingLabel' => (bool)$options['floating_label'],
        'label' => html_entity_decode(strip_tags($label), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        'required' => (bool)$options['required'],
        'locale' => (string)$options['locale'],
        'weekStart' => min(6, max(0, (int)$options['week_start'])),
        'disableDates' => $options['include_filtered_dates'] ? [] : $date_filter,
        'enableDates' => $options['include_filtered_dates'] ? $date_filter : [],
        'disabledWeekdays' => $weekends,
        'joinFromId' => ltrim((string)$options['join_from_id'], '#'),
        'joinToId' => ltrim((string)$options['join_to_id'], '#'),
        'joinExclusive' => (bool)$options['join_exclusive'],
        'callbacks' => [
            'open' => form_datepicker_callback_name($options['on_open']),
            'close' => form_datepicker_callback_name($options['on_close']),
            'change' => form_datepicker_callback_name($options['on_change']),
        ],
        'strings' => $strings,
    ];
}

function form_datepicker_config_attribute(array $config): string
{
    $json = json_encode(
        $config,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );

    return form_datepicker_escape_attribute($json ?: '{}');
}

function load_form_datepicker_assets(string $locale): void
{
    static $loaded = FALSE;
    static $loaded_locales = [];

    if (!$loaded) {
        fusion_load_script(DYNAMICS.'assets/flatpickr/flatpickr.min.css', 'css');
        fusion_load_script(DYNAMICS.'includes/form_datepicker/component.css?v='.filemtime(__DIR__.'/component.css'), 'css');
        fusion_load_script(DYNAMICS.'assets/flatpickr/flatpickr.min.js');
        $loaded = TRUE;
    }

    if ($locale !== 'default' && preg_match('/^[a-z0-9_-]+$/i', $locale) && empty($loaded_locales[$locale])) {
        fusion_load_script(DYNAMICS.'assets/flatpickr/l10n/'.$locale.'.min.js');
        $loaded_locales[$locale] = TRUE;
    }

    fusion_load_script(DYNAMICS.'includes/form_datepicker/component.js');
}

/**
 * Render a PHPFusion date, time, timestamp, datetime, or date-range picker.
 *
 * The text input remains the submitted value source. Flatpickr progressively
 * adds the calendar, 24-hour time view, range selection, paired constraints,
 * callbacks, and the public FusionDatepicker JavaScript API.
 *
 * Important options:
 * - type: date|time|timestamp|datetime
 * - showTime: date followed by the animated 24-hour time view
 * - range: select two dates without closing between selections
 * - floating_label: render the label inside the control
 * - join_from_id / join_to_id: constrain paired picker boundaries
 * - join_exclusive: disable the linked boundary date as well as earlier/later dates
 * - on_open / on_close / on_change: dotted global JavaScript callback names
 *
 * @return string
 */
function form_datepicker($input_name, $label = '', $input_value = '', array $options = [])
{
    $locale = fusion_get_locale();
    $input_value = clean_input_value($input_value);
    if (check_post($input_name)) {
        $input_value = post($input_name);
    }

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace('_', ' ', $input_name)));
    $input_name = stripinput($input_name);
    $default_options = [
        'input_id' => $input_name,
        'required' => FALSE,
        'placeholder' => '',
        'deactivate' => FALSE,
        'width' => '',
        'inner_width' => '',
        'label_class' => '',
        'class' => '',
        'inline' => FALSE,
        'floating_label' => FALSE,
        'error_text' => $locale['error_input_default'],
        'ext_tip' => '',
        'date_format_js' => 'Y-m-d',
        'date_format_php' => 'Y-m-d',
        'display_format' => '',
        'display_placeholder' => '',
        'delimiter' => '-',
        'range' => FALSE,
        'range_separator' => ' to ',
        'fieldicon_off' => FALSE,
        'filtered_dates' => [],
        'include_filtered_dates' => FALSE,
        'weekend' => [],
        'disable_weekend' => FALSE,
        'type' => 'date',
        'tip' => '',
        'showTime' => FALSE,
        'enable_seconds' => FALSE,
        'minute_increment' => 5,
        'allow_input' => TRUE,
        'week_start' => fusion_get_settings('week_start'),
        'locale' => $locale['datepicker'] ?? 'default',
        'join_to_id' => '',
        'join_from_id' => '',
        'join_exclusive' => TRUE,
        'on_open' => '',
        'on_close' => '',
        'on_change' => '',
        'strings' => [],
        'debug' => '',
        'stacked' => '',
        'js_check' => FALSE,
    ];
    $options += $default_options;
    if (!empty($options['ext_tip'])) {
        $options['ext_tip'] = dynamics_field_help($options['ext_tip'], TRUE);
    }

    if (!in_array($options['type'], ['date', 'time', 'timestamp', 'datetime'], TRUE)) {
        $options['type'] = 'date';
    }
    if ($options['range']) {
        $options['type'] = 'date';
        $options['showTime'] = FALSE;
    }
    if ($options['type'] === 'date') {
        $options['date_format_php'] = 'Y-m-d';
        $options['date_format_js'] = 'Y-m-d';
    } elseif ($options['type'] === 'time') {
        $options['date_format_php'] = $options['enable_seconds'] ? 'H:i:s' : 'H:i';
        $options['date_format_js'] = $options['date_format_php'];
        $options['showTime'] = TRUE;
    } elseif ($options['type'] === 'datetime') {
        $options['date_format_php'] = $options['enable_seconds'] ? 'Y-m-d H:i:s' : 'Y-m-d H:i';
        $options['date_format_js'] = $options['date_format_php'];
        $options['showTime'] = TRUE;
    } elseif ($options['showTime'] && $options['date_format_php'] === $default_options['date_format_php']) {
        $options['date_format_php'] = $options['enable_seconds'] ? 'Y-m-d H:i:s' : 'Y-m-d H:i';
        $options['date_format_js'] = $options['date_format_php'];
    }

    if ($options['display_format'] === '') {
        if ($options['type'] === 'time') {
            $options['display_format'] = $options['enable_seconds'] ? 'H:i:S' : 'H:i';
        } elseif ($options['showTime']) {
            $options['display_format'] = $options['enable_seconds'] ? 'd / m / Y H:i:S' : 'd / m / Y H:i';
        } else {
            $options['display_format'] = 'd / m / Y';
        }
    }
    if ($options['display_placeholder'] === '') {
        $options['display_placeholder'] = strtr($options['display_format'], [
            'Y' => 'yyyy',
            'y' => 'yy',
            'd' => 'dd',
            'j' => 'dd',
            'm' => 'mm',
            'n' => 'mm',
            'H' => 'hh',
            'G' => 'hh',
            'h' => 'hh',
            'i' => 'mm',
            'S' => 'ss',
            's' => 'ss',
        ]);
    }

    $options['input_id'] = trim((string)$options['input_id'], '[]');
    $input_value = form_datepicker_normalize_input($input_value, $options);
    $date_filter = [];
    foreach (is_array($options['filtered_dates']) ? $options['filtered_dates'] : [] as $value) {
        $date_filter[] = form_datepicker_normalize_value($value, 'Y-m-d');
    }
    $date_filter = array_values(array_filter(array_unique($date_filter)));
    $weekends = $options['disable_weekend']
        ? array_values(array_map('intval', $options['weekend'] ?: [0, 6]))
        : [];

    $error_class = '';
    if (\Defender::inputHasError($input_name)) {
        $error_class = 'has-error ';
        $new_error_text = \Defender::getErrorText($input_name);
        if ($new_error_text !== '') {
            $options['error_text'] = $new_error_text;
        }
        if ($options['error_text'] !== '') {
            addnotice('danger', $options['error_text']);
        }
    }

    $config = form_datepicker_config($options, (string)$label, $input_value, $date_filter, $weekends);
    $config_attribute = !$options['deactivate']
        ? " data-dynamics-datepicker='".form_datepicker_config_attribute($config)."'"
        : '';
    $field_id = form_datepicker_escape_attribute($options['input_id']);
    $described_by = [];
    if ($options['ext_tip']) {
        $described_by[] = $field_id.'-description';
    }
    if (\Defender::inputHasError($input_name)) {
        $described_by[] = $field_id.'-help';
    }
    $described_by_attribute = $described_by ? " aria-describedby='".implode(' ', $described_by)."'" : '';
    $wrapper_class = framework_css(trim('form-group '.($options['inline'] && $label ? 'row ' : '').$error_class.$options['class']));
    $control_class = framework_css('input-group date dynamics-datepicker'.($options['floating_label'] ? ' dynamics-datepicker--floating' : ''));
    $label_class = framework_css('form-label '.$options['label_class'].($options['inline'] ? ' col-form-label col-sm-3 col-md-3 col-lg-3' : ''));
    $inline_control_class = framework_css('col-sm-9 col-md-9 col-lg-9');
    $input_class = framework_css('form-control dynamics-datepicker__input');
    $floating_label_class = framework_css('dynamics-datepicker__floating-label '.$options['label_class']);
    $toggle_class = framework_css('input-group-text dynamics-datepicker__toggle');
    $form_text_class = framework_css('form-text');
    $error_text_class = framework_css('form-text text-danger');

    $html = "<div id='{$field_id}-field' class='".form_datepicker_escape_attribute($wrapper_class)."'>";
    if ($label && !$options['floating_label']) {
        $html .= "<label class='".form_datepicker_escape_attribute($label_class)."' for='{$field_id}'>".$label;
        $html .= $options['required'] ? "<span class='required'>&nbsp;*</span>" : '';
        $html .= dynamics_field_help($options['tip']);
        $html .= '</label>';
    }
    $html .= $options['inline'] && $label ? "<div class='".form_datepicker_escape_attribute($inline_control_class)."'>" : '';
    $html .= "<div class='".form_datepicker_escape_attribute($control_class)."'".($options['width'] ? " style='width:".form_datepicker_escape_attribute($options['width'])."'" : '').'>';
    $html .= "<input type='text' name='".form_datepicker_escape_attribute($input_name)."' id='{$field_id}' value='".form_datepicker_escape_attribute($input_value)."' class='".form_datepicker_escape_attribute($input_class)."' autocomplete='off'";
    $html .= $options['inner_width'] ? " style='width:".form_datepicker_escape_attribute($options['inner_width'])."'" : '';
    $html .= $options['placeholder'] ? " placeholder='".form_datepicker_escape_attribute($options['placeholder'])."'" : '';
    $html .= $options['required'] ? " aria-required='true'" : '';
    $html .= $described_by_attribute.$config_attribute.'>';
    if ($label && $options['floating_label']) {
        $html .= "<label class='".form_datepicker_escape_attribute($floating_label_class)."' for='{$field_id}'>".$label;
        $html .= $options['required'] ? "<span class='required'>&nbsp;*</span>" : '';
        $html .= dynamics_field_help($options['tip']);
        $html .= '</label>';
    }
    if (!$options['fieldicon_off']) {
        $open_label = form_datepicker_escape_attribute($config['strings']['open']);
        $html .= "<button type='button' class='".form_datepicker_escape_attribute($toggle_class)."' aria-label='{$open_label}'".($options['deactivate'] ? ' disabled' : '').">
            <svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'>
                <path d='M8 2v4M16 2v4M3 10h18'/><rect width='18' height='18' x='3' y='4' rx='2'/>
            </svg>
        </button>";
    }
    $html .= '</div>';
    $html .= $options['ext_tip'] ? "<div id='{$field_id}-description' class='".form_datepicker_escape_attribute($form_text_class)."'>".$options['ext_tip'].'</div>' : '';
    $html .= \Defender::inputHasError($input_name)
        ? "<div id='{$field_id}-help' class='".form_datepicker_escape_attribute($error_text_class)."'>".$options['error_text'].'</div>'
        : '';
    $html .= $options['stacked'];
    $html .= $options['inline'] && $label ? '</div>' : '';
    $html .= '</div>';

    $validation_type = $options['range'] ? 'daterange' : $options['type'];
    \Defender::add_field_session([
        'input_name' => clean_input_name($input_name),
        'type' => $validation_type,
        'title' => $title,
        'id' => $options['input_id'],
        'required' => $options['required'],
        'error_text' => $options['error_text'],
        'delimiter' => $options['delimiter'],
        'range_separator' => $options['range_separator'],
        'date_format' => $options['date_format_php'],
        'safemode' => TRUE,
    ]);

    if (!$options['deactivate']) {
        load_form_datepicker_assets((string)$options['locale']);
    }

    if ($options['js_check'] && $options['required']) {
        add_to_jquery("$('#".$options['input_id']."').on('blur', function () {
            if (!this.value) {
                setFieldError('".$options['input_id']."', '".$options['error_text']."');
            } else {
                clearFieldError('".$options['input_id']."');
            }
        });");
    }

    return $html;
}
