<?php
require_once __DIR__ . '/../maincore.php';
require_once THEMES . 'templates/header.php';

// Text inputs
echo form_text( 'text_input', 'Label', '', [
    'required' => TRUE
] );

echo form_text( 'text_input_2', 'Label 2', '', [
    'required'      => TRUE,
    'inline'        => FALSE,
    'prepend'       => TRUE,
    'prepend_value' => 'xxx',
    'append'        => TRUE,
    'append_value'  => 'yyy',
] );

// Dropdowns
echo form_select( 'dropdown_1', 'Dropdown 1', '', [
    'required' => TRUE,
    'options'  => [
        1 => 'Option 3',
        2 => 'Option 2',
        3 => 'Option 3',
        4 => 'Option 4',
        5 => 'Option 5',
    ]
] );

echo form_user_select( 'dropdown_2', 'Dropdown 2', '', [
    'required' => TRUE,
] );

echo form_hidden( 'hidden', 'Label', '' );

// Datepicker
echo form_datepicker( 'datepicker', 'Datepicker', '' );

echo form_colorpicker( 'colorpicker', 'Colorpicker', '' );

// Checkboxes
echo form_checkbox( 'checkbox_inline', 'Checkbox Inline', '1', ['type' => 'toggle'] );

echo form_checkbox( 'checkbox_inline', 'Checkbox Inline', '' );

echo form_checkbox( 'checkbox_options', 'Checkbox Options', '1', ['options' => [1 => 'Option 1', 2 => 'Option 2', 3 => 'Option 3']] );

echo form_checkbox( 'checkbox_inline_options', 'Checkbox Inline Options', '2', ['options' => [1 => 'Option 1', 2 => 'Option 2', 3 => 'Option 3'], 'inline' => TRUE] );

echo form_checkbox( 'checkbox_radio_options', 'Checkbox Radio Options', '', ['options' => [1 => 'Option 1', 2 => 'Option 2', 3 => 'Option 3'], 'type' => 'radio'] );

echo form_checkbox( 'checkbox_inline_radio_options', 'Checkbox Radio Options', '', ['options' => [1 => 'Option 1', 2 => 'Option 2', 3 => 'Option 3'], 'type' => 'radio', 'inline' => TRUE] );

// Textarea
echo form_textarea( 'textarea', 'TinyMCE Textarea', '', ['type' => 'tinymce', 'placeholder' => 'Type something here...'] );

echo '<form name="test_1">';
echo form_textarea( 'textarea2', 'BBCode Textarea', '', ['type' => 'bbcode', 'placeholder' => 'Type something here...', 'form_name' => 'test_1'] );

echo form_textarea( 'textarea3', 'HTML Textarea', '', ['type' => 'html', 'placeholder' => 'Type something here...', 'form_name' => 'test_1'] );
echo '</form>';

echo form_textarea( 'textarea4', 'Textarea', '', ['placeholder' => 'Type something here...'] );

// Button groups
echo form_btngroup( 'button_grp', 'Button group', '2', [
    'btn_class' => 'btn-default',
    'options' => [
        1 => 'One',
        2 => 'Two',
        3 => 'Three'

    ]
] );

echo form_btngroup( 'button_grp_2', 'Button group two', '2', [
    'btn_class' => 'btn-primary',
    'options'   => [
        1 => 'One',
        2 => 'Two',
        3 => 'Three'

    ]
] );


echo form_button( 'button', 'Button', 'button' );
echo form_button( 'button_1', 'Button', 'button', ['class' => 'btn-primary'] );
echo form_button( 'button_2', 'Button', 'button', ['class' => 'btn-success'] );


require_once THEMES . 'templates/footer.php';