<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2013 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: get_apps.php
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

namespace Genesis\Subcontroller;

use Genesis\Model\resource;

use Genesis\Viewer\adminApps;

class get_apps extends resource {

    private $result_message = [
        200 => "OK",
        100 => "Failed system validation",
        101 => "Failed system security measures",
        102 => "System apps failed to load",
        103 => "Search string is too short",
        104 => "There are no results found",
    ];

    private $result = [
        "data"    => [],
        "count"   => 0,
        "status"  => 200,
        "message" => ""
    ];

    public function __construct() {

        parent::__construct();

        if ($this->authorize_aid()) {

            if (\defender::safe()) {

                $this->search_apps();

                $this->result['message'] = $this->result_message[$this->result['status']];

                if (!empty($this->result)) {

                    $app_html = new adminApps();

                    $app_html->setResult($this->result);

                    $app_html->display_apps_result();

                }

            } else {
                $this->result['status'] = 101;
            }
        } else {
            $this->result['status'] = 100;
        }
    }

    private function authorize_aid() {
        if (isset($_GET['aid']) && iAUTH == $_GET['aid']) {
            return TRUE;
        }
        return FALSE;
    }

    private function search_apps() {

        $search_string = $_GET['appString'];

        if (strlen($search_string) >= 2) {

            $available_apps = parent::getAdminPages();

            $apps = flatten_array($available_apps);

            $result_rows = 0;

            if (!empty($apps)) {

                foreach ($apps as $appData) {
                    if (
                        stristr($appData['admin_title'], $search_string) == TRUE ||
                        stristr($appData['admin_link'], $search_string) == TRUE
                    ) {
                        $this->result['data'][] = $appData;
                        $result_rows++;
                    }
                }

            } else {
                $this->result['status'] = 102;
            }

            if ($result_rows > 0) {

                $this->result['count'] = $result_rows;
                $this->result['status'] = 200;

            } else {
                $this->result['status'] = 104;
            }

        } else {
            $this->result['status'] = 103;
        }

    }

}
