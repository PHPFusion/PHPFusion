<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2013 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: controller.php
| Author: Frederick Chan (deviance)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace Genesis;

use Genesis\Model\resource;
use Genesis\Viewer\adminDashboard;

class Controller {

    protected static $instance = NULL;

    public $login_panel = "";

    public function __construct($license_id) {
    }

    public static function Instance($license_id) {
        if (self::$instance === NULL) {
            self::$instance = new static($license_id);
        }

        return self::$instance;
    }

    public function do_login_panel() {
        if ($this->license_auth()) {
            resource::set_static_variables();
            new Viewer\loginPanel();
        }

    }

    private function license_auth() {
        return TRUE;
    }

    public function do_admin_panel() {
        if ($this->license_auth()) {
            resource::set_static_variables();
            new Viewer\adminPanel();
        }
    }

    public function do_admin_dashboard() {

        if ($this->license_auth()) {

            resource::set_static_variables();

            new Viewer\adminDashboard();

            if (isset($_GET['os']) or (isset($_GET['pagenum']) && $_GET['pagenum']) > 0) {

                adminDashboard::do_admin_icons();

            } else {
                adminDashboard::do_dashboard();
            }

        }
    }

}
