<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Artemis Interface
| The Artemis Project - 2014 - 2016 (c)
| Network Data Model Development
| Filename: Artemis_ACP/acp_request.php
| Author: Guidlsquare , enVision Sdn Bhd
| Copyright patent 0517721 IPO
| Author's all rights reserved.
+--------------------------------------------------------+
| Released under PHP-Fusion EPAL
+--------------------------------------------------------*/

namespace Artemis\Viewer;

class adminApps {

    private $result = array();


    public function display_apps_result() {

        global $aidlink;

        // need the url
        $uri = pathinfo($_GET['url']);

        $count = substr($_GET['url'], -1) == "/" ? substr_count($uri['dirname'], "/") : substr_count($uri['dirname'], "/") - 1;

        $prefix_ = str_repeat("../", $count);

        $infusions_count = substr($_GET['url'], -1) == "/" ? substr_count($uri['dirname'], "/") : substr_count($uri['dirname'], "/") - 2;

        $infusions_prefix_ = str_repeat("../", $infusions_count);

        if (($this->result['status'] == 200 && !empty($this->result['data'])) && isset($_GET['mode'])) {

            if ($_GET['mode'] == "json") {

                echo json_encode($this->result);

            } elseif ($_GET['mode'] == "html") {

                foreach ($this->result['data'] as $data) {

                    $title = $data['admin_title'];

                    if (stristr($data['admin_link'], '/infusions/')) {
                        $link = $infusions_prefix_.$data['admin_link'];
                    } else {
                        $link = $prefix_."administration/".$data['admin_link'];
                    }
                    $link = $link.$aidlink;

                    $app_icon_url = str_replace('../', '', get_image("ac_".$data['admin_rights']));

                    $app_icon_url = $prefix_.$app_icon_url;

                    if ($data['admin_page'] !== 5) {
                        $title = isset($locale[$data['admin_rights']]) ? $locale[$data['admin_rights']] : $title;
                    }

                    if (checkrights($data['admin_rights'])) :
                        ?>
                        <li>
                            <a href="<?php echo $link ?>">
                                <div class="app_icon">
                                    <img class="img-responsive" alt="<?php echo $title ?>" src="<?php echo $app_icon_url ?>"/>
                                </div>
                                <div class="apps">
                                    <h4><?php echo $title ?></h4>
                                </div>
                            </a>
                        </li>
                        <?php
                    endif;
                }

            } else {

                echo "<li class=\"app_search_error\"><span>API Error - Mode is not of a valid type</span></li>";

            }

        } else {

            if (!isset($_GET['mode'])) {
                echo "<li class=\"app_search_error\"><span>API Error - Please specify a mode of return</span></li>";

            } else {

                if ($_GET['mode'] == "html") {

                    echo "<li class=\"app_search_error\"><span>".$this->result['message']."</span></li>\n";

                } elseif ($_GET['mode'] == "json") {

                    echo json_encode($this->result);

                }
            }
        }
    }

    /**
     * @param array $result
     */
    public function setResult($result) {
        $this->result = $result;
    }


}