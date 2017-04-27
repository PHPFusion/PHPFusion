<?php
/*------------------------------------------------------
| Genesis Administration Interface
| The Genesis Project - 2014 - 2016 (c)
| Theme Framework & Networking Data Model Development
+-------------------------------------------------------
| Closed Source Development
|-------------------------------------------------------
| Filename: Genesis Package
| Author: Frederick MC Chan - PHP-Fusion Derivative
| Author: Lee Boon Seng Eric - Joomla OS Derivative
| InDesign: George Beh YC - CSS
| For : Guildsquare LLC, enVision LLC
+-------------------------------------------------------
| Genesis Package - Single Domain Licence
| Source code must be encrypted before uploaded to any
| server.
+-------------------------------------------------------
| The Genesis Project - 2014 - 2016 (c)
+-------------------------------------------------------*/

namespace Artemis\Subcontroller;

use Artemis\Model\resource;

use Artemis\Viewer\adminApps;

class get_apps extends resource {

    private $result_message = array(
                                200 => "OK",
                                100 => "Failed system validation",
                                101 => "Failed system security measures",
                                102 => "System apps failed to load",
                                103 => "Search string is too short",
                                104 => "There are no results found",
                                );

    private $result = array(
                          "data" => array(),
                          "count" => 0,
                          "status" => 200,
                          "message" => ""
                        );

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