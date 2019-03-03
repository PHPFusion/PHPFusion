<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: global/register.php
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
defined('IN_FUSION') || exit;

if (!function_exists("display_registerform")) {
    function display_registerform($info) {
        global $locale;
        opentable($locale['u101']);
        // page navigation
        $open = "";
        $close = "";
        $tab_title = [];
        if (isset($info['section']) && count($info['section']) > 1) {
            foreach ($info['section'] as $page_section) {
                $tab_title['title'][$page_section['id']] = $page_section['name'];
                $tab_title['id'][$page_section['id']] = $page_section['id'];
                $tab_title['icon'][$page_section['id']] = '';
            }
            $open = opentab($tab_title, $_GET['section'], 'user-profile-form', TRUE);
            $close = closetab();
        }
        echo $open;
        if (empty($info['user_name']) && empty($info['user_field'])) {
            global $locale;
            echo "<div class='well text-center'>\n";
            echo $locale['uf_108'];
            echo "</div>\n";
        } else {
            echo "<!--editprofile_pre_idx-->";
            echo "<div id='register_form' class='row m-t-20'>\n";
            echo "<div class='col-xs-12 col-sm-12'>\n";
            if (!empty($info['openform'])) {
                echo $info['openform'];
            }
            if (!empty($info['user_name'])) {
                echo $info['user_name'];
            }
            if (!empty($info['user_email'])) {
                echo $info['user_email'];
            }
            if (!empty($info['user_hide_email'])) {
                echo $info['user_hide_email'];
            }
            if (!empty($info['user_avatar'])) {
                echo $info['user_avatar'];
            }
            if (!empty($info['user_password'])) {
                echo $info['user_password'];
            }
            if (!empty($info['user_admin_password']) && iADMIN) {
                echo $info['user_admin_password'];
            }

            if (!empty($info['user_field'])) {
                foreach ($info['user_field'] as $fieldData) {
                    if (!empty($fieldData['title'])) {
                        echo $fieldData['title'];
                    }
                    if (!empty($fieldData['fields']) && is_array($fieldData['fields'])) {
                        foreach ($fieldData['fields'] as $cFieldData) {
                            if (!empty($cFieldData)) {
                                echo $cFieldData;
                            }
                        }
                    }
                }
            }

            if (!empty($info['validate'])) {
                echo $info['validate'];
            }
            if (!empty($info['terms'])) {
                echo $info['terms'];
            }
            if (!empty($info['button'])) {
                echo $info['button'];
            }
            if (!empty($info['closeform'])) {
                echo $info['closeform'];
            }
            echo "</div>\n</div>\n";
            echo "<!--editprofile_sub_idx-->";
        }
        echo $close;
        closetable();
    }
}
