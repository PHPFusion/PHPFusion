<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: login/login_admin.php
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
require_once __DIR__.'/../../maincore.php';
require_once THEMES.'templates/admin_header.php';
$locale = fusion_get_locale('', LOGIN_LOCALESET.'login.php');
add_breadcrumb(['link' => INFUSIONS.'login/login_admin.php', 'title' => $locale['login_002']]);
add_to_title($locale['login_002']);

$login = new \PHPFusion\Infusions\Login\Login();
$files = $login->cache_files();
$driver_type = [];
$user_field_name = '';
$user_field_desc = '';

if (isset($_GET['action']) && $_GET['action'] == 'configure' && isset($_GET['driver'])) {
    if ($driver = $login->cache_driver($_GET['driver'])) {
        $title = $driver['login_name'];
        $locale_file = LOGIN_LOCALESET.$title.'.php';
        $login_file = INFUSIONS.'login/user_fields/'.$title.'_include_var.php';
        if (file_exists($locale_file) && file_exists($login_file)) {
            include($locale_file);
            include($login_file);
            // Automatic
            $page_title = str_replace('{USER_FIELD}', $user_field_name, $locale['login_111']);
            \PHPFusion\OutputHandler::addToTitle($locale['global_201'].$locale['global_200'].$page_title);
            \PHPFusion\BreadCrumbs::getInstance('default')->addBreadCrumb([
                'link'  => FUSION_REQUEST,
                'title' => $page_title,
            ]);
            $settings_found = FALSE;
            $settings_method = '';
            opentable($locale['login_002']);
            if (!empty($user_field_settings)) {
                $settings_found = FALSE;
                // This is the class calling method
                if (is_array($user_field_settings) && count($user_field_settings) > 1) {
                    $settings_class = $user_field_settings[0];
                    $settings_method = $user_field_settings[1];
                    $settings = new $settings_class();
                    // Call the authentication method
                    if (is_callable($user_field_settings)) {
                        $settings->$settings_method();
                        $settings_found = TRUE;
                    }
                } else if (is_callable($settings_method)) {
                    // Call the function calling method
                    $settings = $settings_method();
                    $settings_found = TRUE;
                }
            }
            if ($settings_found === FALSE) {
                echo "<div class='alert alert-warning strong'>".$locale['login_123']."</div>";
            }
            echo "<p><a class='strong' href='".clean_request('', ['action', 'driver'], FALSE)."'><i class='fas fa-caret-left m-r-10'></i>".$locale['login_124']."</a></p>";
        } else {
            addNotice("danger", $locale['login_125']);
        }
    } else {
        addNotice("danger", $locale['login_126']);
    }
} else {
    opentable($locale['login_002']);
    echo "<table class='table'>\n";
    echo "<thead>\n";
    echo "<tr>\n";
    echo "<th class='min'></th>";
    echo "<th>".$locale['login_100']."</th>";
    echo "<th>".$locale['login_101']."</th>";
    echo "<th>".$locale['login_102']."</th>";
    echo "<th>".$locale['login_103']."</th>";
    echo "<th>".$locale['login_104']."</th>";
    echo "</tr>\n";
    echo "</thead>\n<tbody>\n";
    foreach ($files as $file_name) {
        $title = $login->filename_to_title($file_name);
        $locale_file = LOGIN_LOCALESET.$title.'.php';
        $login_file = INFUSIONS.'login/user_fields/'.$file_name;
        if (file_exists($locale_file) && file_exists($login_file)) {
            include($locale_file);
            include($login_file);
        }
        $field_status = $login->check_driver_status($title) ? $locale['login_105'] : $locale['login_106'];
        $button = "<a class='btn btn-default' href='".clean_request('action=install&driver='.$title, ['action', 'driver'], FALSE)."'>".$locale['login_107']."</a>";
        if ($login->check_driver_status($title)) {
            $button = "<a class='btn btn-default active' onclick=\"if (!confirm('".$locale['login_122']."')) return false;\" href='".clean_request('action=uninstall&driver='.$title, ['action', 'driver'], FALSE)."'>".$locale['login_108']."</a>";
        }
        $field_config_status = $login->check_driver_config($title) ? $locale['login_109'] : $locale['login_110'];
        if (!empty($user_field_settings)) {
            $user_field_name = "<a href='".clean_request("action=configure&driver=".$title, ['action', 'driver'], FALSE)."'>$user_field_name</a>";
        }
        echo "<tr>\n";
        echo "<td>$button</td>\n";
        echo "<td><strong>$user_field_name</strong><br/>$user_field_desc</td>\n";
        echo "<td>$user_field_auth_type</td>\n";
        echo "<td>$user_field_api_version</td>\n";
        echo "<td>$field_status</td>\n";
        echo "<td>$field_config_status</td>";
        echo "</tr>\n";
        $driver_type[$title] = [
            'login_title' => $user_field_name,
            'login_name'  => $title,
            'login_type'  => $user_field_auth_type,
        ];
        unset($user_field_settings);
    }
    echo "</tbody>\n</table>\n";
}

closetable();

if (isset($_GET['action']) && isset($_GET['driver'])) {
    if ($_GET['action'] == 'install') {
        if (!dbcount("(login_name)", DB_LOGIN, "login_name=:driver_name", [":driver_name" => stripinput($_GET['driver'])]) && isset($driver_type[$_GET['driver']])) {
            // install the driver
            $driver_data = [
                'login_title'    => $driver_type[$_GET['driver']]['login_title'],
                'login_name'     => stripinput($_GET['driver']),
                'login_type'     => $driver_type[$_GET['driver']]['login_type'],
                'login_status'   => 1,
                'login_settings' => ''
            ];
            dbquery_insert(DB_LOGIN, $driver_data, 'save');
            addNotice('success', str_replace('{DRIVER_NAME}', $driver_data['login_title'], $locale['login_120']));
            redirect(clean_request('', ['action', 'driver'], FALSE));
        }
    }
    if ($_GET['action'] == 'uninstall') {
        if (dbcount("(login_name)", DB_LOGIN, "login_name=:driver_name", [":driver_name" => stripinput($_GET['driver'])]) && isset($driver_type[$_GET['driver']])) {
            // install the driver
            $driver_data = [
                'login_title'    => $driver_type[$_GET['driver']]['login_title'],
                'login_name'     => stripinput($_GET['driver']),
                'login_type'     => $driver_type[$_GET['driver']]['login_type'],
                'login_status'   => 1,
                'login_settings' => ''
            ];
            dbquery_insert(DB_LOGIN, $driver_data, 'delete');
            addNotice('success', str_replace('{DRIVER_NAME}', $driver_data['login_title'], $locale['login_121']));
            redirect(clean_request('', ['action', 'driver'], FALSE));
        }
    }
}

require_once THEMES.'templates/footer.php';
