<?php
require_once __DIR__."/../../maincore.php";

$input_name = post("input_name");
$input_value = post("input_value");
//$default_value = post("default_value"); // we did not post this.
$multilang = post("input_multilang") ? TRUE : FALSE;
$defender = \Defender::getInstance();
try {

    if (empty($defender->get_current_field_session($input_name))) {
        $config = array(
            "input_name"     => $input_name,
            "title"          => trim($input_name, "[]"),
            "id"             => $input_name,
            "type"           => "text",
            "required"       => FALSE,
            "safemode"       => FALSE,
            "regex"          => "",
            "callback_check" => "",
            "delimiter"      => ",",
            "min_length"     => 0,
            "max_length"     => 200,
            "censor_words"   => "",
        );
        $defender::add_field_session($config);
    }
    // pageHash problem.
    $input_value = form_sanitizer($input_value, "", $input_name, $multilang); // fails to return the value. also fails to return error text

    //print_p("inputvalue for ".$input_name." is ".$input_value); // ok, field also recorded value.
    //print_p(); // ok field session exists
    //print_p($defender->get_current_field_session());

    if (fusion_safe()) {
        echo json_encode(array("input_name" => $input_name, "input_value" => $input_value, "error" => FALSE, "error_message" => "", "config" => $defender->get_current_field_session($input_name)));
    } else {
        echo json_encode(array("input_name" => $input_name, "input_value" => $input_value, "error" => TRUE, "error_message" => $defender::getErrorText($input_name), "config" => $defender->get_current_field_session($input_name)));
    }
} catch (Exception $e) {
    echo json_encode(array("input_name" => $input_name, "input_value" => $input_value, "error" => TRUE, "error_message" => $e->getMessage()));
}

die();
