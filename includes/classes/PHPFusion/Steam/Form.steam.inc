<?php
namespace PHPFusion\Steam;

class Form {

    private $steam; // pass the steam here.

    /**
     * Blocks constructor.
     *
     * @param \PHPFusion\Steam $steam
     */
    public function __construct(\PHPFusion\Steam $steam) {
        $this->steam = $steam;
        require_once $this->steam->getBoilerPath().DIRECTORY_SEPARATOR.'form.php';
    }

    public function input($input_name, $label, $input_value, $options) {
        return \Form::form_input($input_name, $label, $input_value, $options);
    }

    public function checkbox($input_name, $label, $input_value, $options) {
        return \Form::form_checkbox($input_name, $label, $input_value, $options);
    }


}
