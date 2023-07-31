<?php
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/header.php';

// Text inputs
echo form_text('text_input', 'Label', '', [
    'required'=>TRUE
]);

echo form_text('text_input_2', 'Label 2', '', [
    'required'=> TRUE,
    'inline'=> FALSE,
    'prepend'=>TRUE,
    'prepend_value' => 'xxx',
    'append' => TRUE,
    'append_value' => 'yyy',
]);

// Dropdowns
echo form_select('dropdown_1', 'Dropdown 1', '', [
    'required'=>TRUE,
    'options' => [
        1 => 'Option 3',
        2 => 'Option 2',
        3 => 'Option 3',
        4 => 'Option 4',
        5 => 'Option 5',
    ]
]);

echo form_user_select('dropdown_2', 'Dropdown 2', '', [
    'required'=>TRUE,
]);

// Datepicker
echo form_datepicker('datepicker', 'Datepicker', '');


require_once THEMES.'templates/footer.php';