<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: theme.controller.php
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
namespace Inspire;

class Controller {

    protected static $instance = NULL;

    public function __construct($license_id) {
        Helper::set_static_variables();
    }

    public static function Instance($license_id) {
        if (self::$instance === NULL) {
            self::$instance = new static($license_id);
        }

        return self::$instance;
    }

    public function do_login_panel() {
        if ($this->license_auth()) {
            $viewer = new Viewer();
            $viewer->loginPanel();
        }
    }

    private function license_auth() {
        return TRUE;
    }

    public function do_admin_panel() {
        if ($this->license_auth()) {
            $viewer = new Viewer();
            $viewer->adminPanel();
        }
    }

    public function do_admin_dashboard() {
        if ($this->license_auth()) {
            $dashboard = new Dashboard();
            if (isset($_GET['os']) or (isset($_GET['pagenum']) && $_GET['pagenum']) > 0) {
                $dashboard->adminIcons();
            } else {
                $dashboard->adminDashboard();
            }
        }
    }

}
