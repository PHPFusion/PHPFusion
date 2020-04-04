<?php
require_once __DIR__.'/../../maincore.php';

$input_name = post('input_name');
$input_value = post('input_value');
$default_value = post('default_value');
$multilang = post('input_multilang') ? true : false;
$defender = \Defender::getInstance();
try {
    // pageHash problem.
    $input_value = $defender->formSanitizer($input_value, $default_value, $input_name, $multilang); // fails to return the value. also fails to return error text
    //print_p('inputvalue for '.$input_name.' is '.$input_value); // ok, field also recorded value.
    //print_p($defender->get_current_field_session($input_name)); // ok field session exists
    //print_p($defender->get_current_field_session());
    if (fusion_safe()) {
        echo json_encode(array('input_name' => $input_name, 'input_value'=>$input_value, 'error' => FALSE, 'value' => $input_value));
    } else {
        echo json_encode(array('input_name' => $input_name, 'input_value'=>$input_value,  'error' => TRUE, 'error_message' => $defender::getErrorText($input_name)));
    }
} catch (Exception $e) {
    echo json_encode(array('input_name' => $input_name, 'input_value'=>$input_value, 'error' => TRUE, 'error_message' => $e->getMessage()));
}

die();
