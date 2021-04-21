<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: upgrade.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../maincore.php";
$current_version = '8.00.90';
if (!checkrights("U") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
    redirect("../index.php");
}

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."setup.php";
if (file_exists(LOCALE.LOCALESET."admin/upgrade.php")) {
    include LOCALE.LOCALESET."admin/upgrade.php";
} else {
    include LOCALE."English/admin/upgrade.php";
}


if (!function_exists('rrmdir')) {
    /**
     * Remove folder and all files/subdirectories
     *
     * @param string $dir
     */
    function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (filetype($dir.'/'.$object) == 'dir') {
                        rrmdir($dir.'/'.$object);
                    } else {
                        unlink($dir.'/'.$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
}

echo "<div style='text-align:center' class='text-center'><br />\n";

opentable($locale['400']);
echo "<div style='text-align:center'>\n";

if (isset($_GET['upgrade_ok'])) {
    echo "<div class='alert alert-success'>".$locale['502']."</div>\n";
}

echo "<form name='upgradeform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
if ($settings['version'] < $current_version) {
    if (!isset($_POST['stage'])) {
        echo "<div class='well'>\n";
        echo sprintf($locale['500'], $locale['504'])."<br />\n".$locale['501']."\n";
        echo "</div>\n";
        echo "<input type='hidden' name='stage' value='2'>\n";
        echo "<input type='submit' name='upgrade' value='".$locale['400']."' class='button'><br /><br />\n";
    } else if (isset($_POST['upgrade']) && isset($_POST['stage']) && $_POST['stage'] == 2) {
        // Set a new version
        $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$current_version."' WHERE settings_name='version'");

        $insert_settings_tbl = [
            // 8.00.22
            'number_delimiter'    => '.',
            'thousands_separator' => ',',
            // 8.00.30
            'gateway_method'      => '1',
            'allow_php_exe'       => '0',
            'update_checker'      => '1',
        ];

        foreach ($insert_settings_tbl as $key => $value) {
            if (!isset($settings[$key])) {
                $result = dbquery("INSERT INTO ".DB_PREFIX."settings (settings_name, settings_value) VALUES ('$key', '$value')");
            }
        }

        $result = dbquery("INSERT INTO ".DB_PREFIX."email_templates (template_key, template_format, template_active, template_name, template_subject, template_content, template_sender_name, template_sender_email, template_language) VALUES ('ACTIVATION', 'html', '0', '".$locale['T304']."', '".$locale['T305']."', '".$locale['T306']."', '".$settings['siteusername']."', '".$settings['siteemail']."', '".$settings['locale']."')");

        rrmdir(INCLUDES.'filemanager');
        redirect(FUSION_SELF.$aidlink."&amp;upgrade_ok");
    }

} else {
    echo $locale['401']."<br /><br />\n";
}

echo "</form>\n</div>\n";
closetable();
echo '</div>';

require_once THEMES."templates/footer.php";
