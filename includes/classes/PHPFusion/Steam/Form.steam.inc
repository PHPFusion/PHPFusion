<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: Form.steam.inc
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
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
        require_once $steam->getBoilerPath().DIRECTORY_SEPARATOR."form.php";
    }

    public function input($input_name, $label, $input_value, $options) {
        return \Form::form_input($input_name, $label, $input_value, $options);
    }

    public function checkbox($input_name, $label, $input_value, $options) {
        return \Form::form_checkbox($input_name, $label, $input_value, $options);
    }


}
