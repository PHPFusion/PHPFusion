<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: themes/templates/global/home.php
| Author: Chubatyj Vitalij (Rizado)
| Co-Author: Takács Ákos (Rimelek)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * Show home modules info
 */
if (!function_exists('display_home')) {
    function display_home($info) {
        $locale = fusion_get_locale('', INFUSIONS."home_panel/locale/".LANGUAGE.".php");
        if (!empty($info)) {
            echo "<div class='row'>";
            foreach ($info as $db_id => $content) {
                if (!empty($content)) {
                    $limit = 3;
                    foreach ($content['data'] as $data) {
                        echo "<div class='col-xs-12 col-sm-3 content clearfix'>";
                        opentable($content['blockTitle']);
                        if (isset($data['image'])) {
                            echo "<img class='img-responsive' src='".$data['image']."'/><br/>";
                        }
                        echo "<h3><a href='".$data['url']."'>".$data['title']."</a></h3>";
                        echo "<div class='small m-b-10'>".$data['meta']."</div>";
                        echo "<div class='overflow-hide'>".fusion_first_words($data['content'], 100)."</div>";
                        closetable();
                        echo "</div>";
                        $limit--;
                        if ($limit === 0) {
                            break;
                        }
                    }

                } else {
                    echo $content['norecord'];
                }
            }
            echo "</div>";
        } else {
            opentable($locale['home_0100']);
            echo $locale['home_0101'];
            closetable();
        }
    }
}