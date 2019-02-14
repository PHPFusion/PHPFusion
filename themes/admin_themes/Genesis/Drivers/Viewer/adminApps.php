<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: adminApps.php
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

namespace Genesis\Viewer;

class adminApps {

    private $result = [];

    public function display_apps_result() {

        if (($this->result['status'] == 200 && !empty($this->result['data'])) && isset($_GET['mode'])) {

            if ($_GET['mode'] == "json") {
                header('Content-Type: application/json');

                echo json_encode($this->result);

            } else if ($_GET['mode'] == "html") {

                $locale = fusion_get_locale();
                ?>

                <li>
                    <h4>Search Results</h4>
                </li>
                <?php
                foreach ($this->result['data'] as $data) {

                    $title = $data['admin_title'];

                    if (stristr($data['admin_link'], '/infusions/')) {
                        $link = fusion_get_settings('siteurl').'infusions/'.$data['admin_link'];
                    } else {
                        $link = fusion_get_settings('siteurl').'administration/'.$data['admin_link'];
                    }

                    $link = $link.fusion_get_aidlink();

                    $app_icon_url = strtr(
                        get_image("ac_".$data['admin_rights']), [
                            INFUSIONS => fusion_get_settings('siteurl').'infusions/',
                            ADMIN     => fusion_get_settings('siteurl').'administration/'
                        ]
                    );

                    if ($data['admin_page'] !== 5) {
                        $title = isset($locale[$data['admin_rights']]) ? $locale[$data['admin_rights']] : $title;
                    }

                    if (checkrights($data['admin_rights'])) :
                        ?>
                        <li>
                            <a class="apps-lists" href="<?php echo $link ?>">
                                <div class="app_icon">
                                    <img class="img-responsive" alt="<?php echo $title ?>"
                                         src="<?php echo $app_icon_url ?>"/>
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
                    echo "<li><h4>Search Results Error</h4></li>\n";
                    echo "<li class=\"app_search_error\"><span>".$this->result['message']."<br/><br/>
                    <a class=\"pointer m-t-20\" onclick=\"clearSearch()\">Clear Search</a>
                    </span></li>\n";

                } else if ($_GET['mode'] == "json") {
                    header('Content-Type: application/json');

                    echo json_encode($this->result);
                }
            }
        }
    }

    /**
     * @param array $result
     */
    public
    function setResult($result) {
        $this->result = $result;
    }
}
