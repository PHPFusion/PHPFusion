<?php

defined('IN_FUSION') || exit;

$variants = [
    [ 'Standard · Optional', FALSE, FALSE ],
    [ 'Standard · Required', FALSE, TRUE ],
    [ 'Floating · Optional', TRUE, FALSE ],
    [ 'Floating · Required', TRUE, TRUE ],
];
$examples = [
    'text'     => [ 'Text', 'form_text', '', [ 'placeholder' => 'Enter a name' ] ],
    'select'   => [
        'Select', 'form_select', '', [
            'placeholder' => 'Choose a subject', 'search_threshold' => 0,
            'options'     => [ 'math' => 'Mathematics', 'science' => 'Science', 'english' => 'English', 'history' => 'History', 'art' => 'Art', 'music' => 'Music' ],
        ]
    ],
    'date'     => [ 'Date picker', 'form_datepicker', '', [ 'placeholder' => 'Choose a date' ] ],
    'textarea' => [ 'Basic textarea', 'form_textarea', '', [ 'placeholder' => 'Write a note', 'height' => '160px' ] ],
    'tiptap'   => [ 'Tiptap textarea', 'form_textarea', '', [ 'placeholder' => 'Write and format a note', 'tiptap' => TRUE ] ],
    'checkbox' => [ 'Checkbox', 'form_checkbox', '0', [
        'inner_text' => 'This description belongs to the checkbox.',
    ] ],
    'color'    => [ 'Colour picker', 'form_colorpicker', '#607D8B', [] ],
    'range'    => [ 'Range', 'form_range', '50', [ 'min' => 1, 'max' => 100 ] ],
];
$checkbox_modes = [
    [ 'Box · Ship To', 'home', [
        'type' => 'radio', 'box_options' => TRUE, 'box_direction' => 'row',
        'options' => [ 'home' => ['Home', 'Free'], 'studio' => 'Studio' ],
        'inner_text' => [ 'home' => 'Morgan Lee<br>1428 Valencia Street<br>San Francisco, CA 94110', 'studio' => 'Morgan Lee<br>88 Bryant Street, Suite 4B<br>San Francisco, CA 94105' ],
    ] ],
    [ 'Box · Delivery Speed', 'express', [
        'type' => 'box', 'box_direction' => 'column',
        'options' => [ 'standard' => ['Standard', 'Free'], 'express' => ['Express', '$12.00'], 'pickup' => ['Pickup', 'Free'] ],
        'inner_text' => [ 'standard' => 'Carbon-neutral delivery · Arrives May 17–19', 'express' => 'Priority packed today · Arrives May 14', 'pickup' => 'Hold at partner counter · Ready tomorrow' ],
    ] ],
    [ 'Toggle', '1', [
        'type' => 'toggle',
        'inner_text' => 'Enable or disable this setting.',
    ] ],
    [ 'Toggle · Label reverse', '1', [
        'type' => 'toggle',
        'label_reverse' => TRUE,
        'inner_text' => 'Enable or disable this setting.',
    ] ],
    [ 'Multiple radio', 'email', [
        'type' => 'radio',
        'options' => [ 'email' => 'Email', 'sms' => 'SMS', 'push' => 'Push notification' ],
        'inner_text' => [ 'email' => 'Send updates by email.', 'sms' => 'Send updates by text message.', 'push' => 'Send updates to the app.' ],
    ] ],
    [ 'Multiple radio · Label reverse', 'sms', [
        'type' => 'radio', 'label_reverse' => TRUE,
        'options' => [ 'email' => 'Email', 'sms' => 'SMS', 'push' => 'Push notification' ],
        'inner_text' => [ 'email' => 'Send updates by email.', 'sms' => 'Send updates by text message.', 'push' => 'Send updates to the app.' ],
    ] ],
    [ 'Multiple checkbox', [ 'email', 'push' ], [
        'type' => 'checkbox', 'multiple' => TRUE,
        'options' => [ 'email' => 'Email', 'sms' => 'SMS', 'push' => 'Push notification' ],
        'inner_text' => [ 'email' => 'Send updates by email.', 'sms' => 'Send updates by text message.', 'push' => 'Send updates to the app.' ],
    ] ],
    [ 'Multiple checkbox · Label reverse', [ 'sms' ], [
        'type' => 'checkbox', 'multiple' => TRUE, 'label_reverse' => TRUE,
        'options' => [ 'email' => 'Email', 'sms' => 'SMS', 'push' => 'Push notification' ],
        'inner_text' => [ 'email' => 'Send updates by email.', 'sms' => 'Send updates by text message.', 'push' => 'Send updates to the app.' ],
    ] ],
    [ 'Box options', [ 'attendance', 'assessment' ], [
        'type' => 'box', 'multiple' => TRUE, 'box_direction' => 'row',
        'options' => [ 'attendance' => 'Attendance', 'assessment' => 'Assessment', 'billing' => 'Billing' ],
        'inner_text' => [ 'attendance' => 'Track daily attendance.', 'assessment' => 'Manage learner assessments.', 'billing' => 'Manage fees and payments.' ],
    ] ],
    [ 'Box options · Label reverse', [ 'billing' ], [
        'type' => 'box', 'multiple' => TRUE, 'box_direction' => 'row', 'label_reverse' => TRUE,
        'options' => [ 'attendance' => 'Attendance', 'assessment' => 'Assessment', 'billing' => 'Billing' ],
        'inner_text' => [ 'attendance' => 'Track daily attendance.', 'assessment' => 'Manage learner assessments.', 'billing' => 'Manage fees and payments.' ],
    ] ],
    [ 'Box options · Column', [ 'assessment' ], [
        'type' => 'box', 'multiple' => TRUE, 'box_direction' => 'column',
        'options' => [ 'attendance' => 'Attendance', 'assessment' => 'Assessment', 'billing' => 'Billing' ],
        'inner_text' => [ 'attendance' => 'Track daily attendance.', 'assessment' => 'Manage learner assessments.', 'billing' => 'Manage fees and payments.' ],
    ] ],
];

$showcase_tabs = [ 'title' => [], 'id' => [] ];
foreach ($examples as $key => [$title]) {
    $showcase_tabs['title'][] = $title;
    $showcase_tabs['id'][] = 'dynamics-showcase-' . $key;
}
$showcase_tab_active = tab_active($showcase_tabs, 0);
$theme_controls = '<div class="dynamics-showcase__themes" role="group" aria-label="Preview theme">'
    . '<button type="button" class="btn btn-outline-secondary" data-showcase-theme="light">Light</button>'
    . '<button type="button" class="btn btn-outline-secondary" data-showcase-theme="dark">Dark</button>'
    . '</div>';

opentable(
    'Dynamics UI',
    'Shared Magazine controls. Compare normal, hover, focus, active and open states. Example values are not saved.',
    $theme_controls,
    'dynamics-showcase'
);
?>
<main>
    <p class="text-muted">Use Tab to move between fields and help buttons; use Escape to dismiss popups. Required fields are marked with an asterisk. Sliders always have a value; their required marker reflects server-side validation.</p>
    <?php
    echo opentab(
        $showcase_tabs,
        $showcase_tab_active,
        'dynamics-showcase-tabs',
        FALSE,
        'nav-tabs',
        remember: true,
        wrapper_class: 'dynamics-showcase__tabs',
        wrapper_body_class: 'dynamics-showcase__tab-content'
    );
//    echo openform('dynamics_showcase', 'get', '', [ 'on_submit' => 'return false;' ]);
    foreach ( $examples as $key => [$title, $helper, $value, $specific] ) {
        $tab_id = 'dynamics-showcase-' . $key;
        echo opentabbody('dynamics-showcase-tabs', $tab_id, $showcase_tab_active);
        echo "<section class='dynamics-showcase__section' aria-labelledby='heading-{$key}'>";
        echo "<h2 class='fs-2' id='heading-{$key}'>{$title}</h2><div class='dynamics-showcase__grid'>";
        $control_variants = $key === 'color' ? [
            ['All formats · Optional', FALSE, FALSE],
            ['HEX only · Required', FALSE, TRUE],
            ['RGB / CSS · Optional', FALSE, FALSE],
            ['HSL only · Required', FALSE, TRUE],
            ['RGB only · Optional', FALSE, FALSE],
            ['CSS only · Optional', FALSE, FALSE],
        ] : $variants;
        foreach ( $control_variants as $index => [$variant, $floating, $required] ) {
            $name = 'demo_' . $key . '_' . $index;
            $options = $specific + [
                    'input_id'       => $name,
                    'floating_label' => $floating,
                    'required'       => $required,
                    'tip'            => 'Help for ' . $title . '. This popup is available on hover, keyboard focus and tap.',
                    'ext_tip'        => 'Example only. This extended help also has a popup.',
                    'class'          => 'mb-0',
                ];
            if ($key === 'color') {
                $options['inner_text'] = [
                    'Choose a color using any supported format.',
                    'Choose a color with a hexadecimal value.',
                    'Switch between RGB and CSS color values.',
                    'Adjust hue, saturation, lightness and opacity.',
                    'Choose a color using red, green and blue.',
                    'Choose a color with a CSS color value.',
                ][$index];
                $options['formats'] = ['ALL', 'HEX', ['RGB', 'CSS'], 'HSL', 'RGB', 'CSS'][$index];
                $options['format'] = ['HEX', 'HEX', 'RGB', 'HSL', 'RGB', 'CSS'][$index];
            }
            echo "<div class='dynamics-showcase__example'><h3 class='dynamics-showcase__variant'>{$variant}</h3>";
            echo $helper($name, $title, $value, $options);
            echo '</div>';
        }
        if ($key === 'text') {
            require DYNAMICS.'showcase/text-examples.php';
        }
        if ($key === 'textarea') {
            foreach (['markdown' => 'Markdown', 'html' => 'HTML'] as $format => $format_title) {
                foreach ([['Normal', FALSE, FALSE], ['Floating label', TRUE, FALSE], ['Inline', FALSE, TRUE]] as $layout_index => [$layout, $floating, $inline]) {
                    $name = 'demo_textarea_tiptap_' . $format . '_' . $layout_index;
                    $variant = 'Tiptap · ' . $format_title . ' · ' . $layout;
                    $content = $format === 'html'
                        ? '<h2>Lesson notes</h2><p>Review <strong>fractions</strong> before the next class.</p><ul><li>Complete the practice sheet.</li><li>Bring your workbook.</li></ul>'
                        : "## Lesson notes\n\nReview **fractions** before the next class.\n\n- Complete the practice sheet.\n- Bring your workbook.";
                    echo "<div class='dynamics-showcase__example'><h3 class='dynamics-showcase__variant'>{$variant}</h3>";
                    echo form_textarea($name, 'Lesson notes', $content, [
                        'input_id' => $name,
                        'tiptap' => TRUE,
                        'tiptap_format' => $format,
                        'floating_label' => $floating,
                        'inline' => $inline,
                        'placeholder' => 'Write and format lesson notes',
                        'height' => '200px',
                        'tip' => 'Use the Tiptap toolbar to format your notes.',
                        'ext_tip' => 'This editor returns ' . $format_title . '. Showcase values are not saved.',
                        'class' => 'mb-0',
                    ]);
                    echo '</div>';
                }
            }
        }
        if ($key === 'select') {
            foreach (['tags' => 'Tags', 'multiple' => 'Multiple options'] as $mode => $mode_title) {
                foreach ([FALSE, TRUE] as $floating) {
                    foreach ([FALSE, TRUE] as $inline) {
                        $name = 'demo_select_' . $mode . '_' . (int)$floating . '_' . (int)$inline;
                        $variant = $mode_title . ' · ' . ($floating ? 'Floating' : 'Normal') . ' · ' . ($inline ? 'Inline' : 'Non-inline');
                        $options = [
                            'input_id' => $name,
                            'multiple' => TRUE,
                            'tags' => $mode === 'tags',
                            'floating_label' => $floating,
                            'inline' => $inline,
                            'options' => $examples['select'][3]['options'],
                            'placeholder' => $mode === 'tags' ? 'Choose or add subjects' : 'Choose subjects',
                            'search_threshold' => 0,
                            'tip' => 'Help for ' . $variant . '.',
                            'ext_tip' => $mode === 'tags' ? 'Select existing subjects or type a new tag.' : 'Select more than one subject from the available options.',
                            'class' => 'mb-0',
                        ];
                        $selected = $mode === 'tags' ? ['math', 'Robotics'] : ['math', 'science'];
                        echo "<div class='dynamics-showcase__example'><h3 class='dynamics-showcase__variant'>{$variant}</h3>";
                        echo form_select($name, $mode_title, $selected, $options);
                        echo '</div>';
                    }
                }
            }
        }
        if ($key === 'checkbox') {
            foreach ($checkbox_modes as $index => [$variant, $value, $specific]) {
                $name = 'demo_checkbox_mode_' . $index;
                $options = $specific + [
                    'input_id' => $name,
                    'tip'      => 'Help for ' . $variant . '.',
                    'ext_tip'  => 'Choose the notification or module options that apply.',
                    'class'    => 'mb-0',
                ];
                echo "<div class='dynamics-showcase__example'><h3 class='dynamics-showcase__variant'>{$variant}</h3>";
                echo form_checkbox($name, $variant, $value, $options);
                echo '</div>';
            }
        }
        echo '</div></section>';
        echo closetabbody();
    }
//    echo closeform();
    echo closetab();
    ?>
</main>
<?php closetable(); ?>
