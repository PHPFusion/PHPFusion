<?php
/*------------------------------------------------------
| Artemis Administration Interface
| The Artemis Project - 2014 - 2016 (c)
| Theme Framework & Networking Data Model Development
+-------------------------------------------------------
| Closed Source Development
|-------------------------------------------------------
| Filename: Artemis Package
| Author: Frederick MC Chan - PHP-Fusion Derivative
| Author: Lee Boon Seng Eric - Joomla OS Derivative
| InDesign: George Beh YC - CSS
| For : Guildsquare LLC, enVision LLC
+-------------------------------------------------------
| Artemis Package - Single Domain Licence
| Source code must be encrypted before uploaded to any
| server.
+-------------------------------------------------------
| The Artemis Project - 2014 - 2016 (c)
+-------------------------------------------------------*/
namespace Artemis;

use Artemis\Model\resource;
use Artemis\Viewer\adminDashboard;

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
            new Viewer\adminDashboard();

            $pagenum = (int)filter_input(INPUT_GET, 'pagenum');

            if ((isset($pagenum) && $pagenum) > 0) {
                $html = adminDashboard::do_admin_icons();
            } else {
                $html = adminDashboard::do_dashboard();
            }
        }

        echo $html;
    }

}
