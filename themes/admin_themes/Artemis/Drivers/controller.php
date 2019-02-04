<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: controller.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace Artemis;

use Artemis\Model\resource;

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
        $html = '';
        if ($this->license_auth()) {
            resource::set_static_variables();
            $dashboard = new Viewer\adminDashboard();

            $pagenum = (int)filter_input(INPUT_GET, 'pagenum');

            if ((isset($pagenum) && $pagenum) > 0) {
                $html = $dashboard->do_admin_icons();
            } else {
                $html = $dashboard->do_dashboard();
            }
        }

        echo $html;
    }

}
