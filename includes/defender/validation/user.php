<?php

class User extends \Defender\Validation {

    public function verify_name() {
        $name = $this->field_name;
        if ($this->field_config['required'] && !$_POST[$name][0]) {
            $this->stop();
            self::setInputError($name.'-firstname');
        }
        if ($this->field_config['required'] && !$_POST[$name][1]) {
            $this->stop();
            self::setInputError($name.'-lastname');
        }
        if ($this->safe()) {
            $return_value = $this->verify_text();
            return $return_value;
        }
    }

    public function verify_address() {
        $name = $this->field_name;
        if ($this->field_config['required'] && !$_POST[$name][0]) {
            $this->stop();
            self::setInputError($name.'-street-1');
        }
        if ($this->field_config['required'] && !$_POST[$name][2]) {
            $this->stop();
            self::setInputError($name.'-country');
        }
        if ($this->field_config['required'] && !$_POST[$name][3]) {
            $this->stop();
            self::setInputError($name.'-region');
        }
        if ($this->field_config['required'] && !$_POST[$name][4]) {
            $this->stop();
            self::setInputError($name.'-city');
        }
        if ($this->field_config['required'] && !$_POST[$name][5]) {
            $this->stop();
            self::setInputError($name.'-postcode');
        }
        if ($this->safe()) {
            $return_value = $this->verify_text();
            return $return_value;
        }
    }

    public function verify_document() {
        $name = $this->field_name;
        if ($this->field_config['required'] && !$_POST[$name][0]) {
            $this->stop();
            self::setInputError($name.'-doc-1');
        }
        if ($this->field_config['required'] && !$_POST[$name][1]) {
            $this->stop();
            self::setInputError($name.'-doc-2');
        }
        if ($this->field_config['required'] && !$_POST[$name][2]) {
            $this->stop();
            self::setInputError($name.'-doc-3');
        }
        if ($this->field_config['required'] && !$_POST[$name][3]) {
            $this->stop();
            self::setInputError($name.'-doc-4');
        }
        if ($this->field_config['required'] && !$_POST[$name][4]) {
            $this->stop();
            self::setInputError($name.'-doc-5');
        }
        if ($this->safe()) {
            $return_value = $this->verify_text();

            return $return_value;
        }
    }

}