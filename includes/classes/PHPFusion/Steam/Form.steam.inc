<?php
namespace PHPFusion\Steam;

use PHPFusion\Steam;

class Form {

    /**
     * Blocks constructor.
     *
     * @param \PHPFusion\Steam $steam
     */
    public function __construct() {
        $steam = Steam::getInstance();
        require_once $steam->getBoilerPath().DIRECTORY_SEPARATOR.'form.php';
    }

    public function input($input_name, $label, $input_value, $options) {
        return \Form::form_input($input_name, $label, $input_value, $options);
    }

    public function checkbox($input_name, $label, $input_value, $options) {
        return \Form::form_checkbox($input_name, $label, $input_value, $options);
    }


}
