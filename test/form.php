<?php
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/header.php';

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

require_once THEMES.'templates/footer.php';