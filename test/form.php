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

require_once THEMES.'templates/footer.php';