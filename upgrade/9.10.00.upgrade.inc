<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: 9.10.00.upgrade.inc
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
$locale = fusion_get_locale('', LOCALE.LOCALESET.'setup.php');
$settings = fusion_get_settings();

$update_settings_tbl = [
    'version'      => '9.10.00',
    'opening_page' => 'index.php'
];

foreach ($update_settings_tbl as $key => $value) {
    if (isset($settings[$key])) {
        $inf_updatedbrow[] = DB_SETTINGS." SET settings_value='$value' WHERE settings_name='$key'";
    }
}

$insert_settings_tbl = [
    'recaptcha_score'     => '0.5',
    'update_last_checked' => time(),
    'license'             => 'agpl'
];

foreach ($insert_settings_tbl as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS." (settings_name, settings_value) VALUES ('$key', '$value')";
    }
}

$delete_settings_tbl = [
    'logoposition_xs',
    'logoposition_sm',
    'logoposition_md',
    'logoposition_lg',
    'subheaderdate',
    'bootstrap',
    'entypo',
    'fontawesome',
    'serveroffset',
    'default_timezone',
    'allow_php_exe'
];

foreach ($delete_settings_tbl as $value) {
    $inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='$value'";
}

// Rename PHP Info to Server Info
$inf_updatedbrow[] = DB_ADMIN." SET admin_image='serverinfo.png', admin_title='".$locale['setup_3021']."', admin_link='serverinfo.php' WHERE admin_link='phpinfo.php'";

$inf_deldbrow[] = DB_ADMIN." WHERE admin_link='settings_theme.php'";

$result = dbquery("SELECT user_id, user_rights, user_level FROM ".DB_USERS." WHERE user_rights !=''");
while ($data = dbarray($result)) {
    $current_rights = explode('.', $data['user_rights']);

    $remove_rights = ['S3'];
    foreach ($remove_rights as $key => $remove) {
        if (($key = array_search($remove, $current_rights)) !== FALSE) {
            unset($current_rights[$key]);
        }
    }

    $final_rights = implode('.', $current_rights);
    dbquery("UPDATE ".DB_USERS." SET user_rights='".$final_rights."' WHERE user_id='".$data['user_id']."'");
}

// Rename home.php to index.php
$inf_updatedbrow[] = DB_SITE_LINKS." SET link_url='index.php' WHERE link_url='home.php'";
$inf_updatedbrow[] = DB_PANELS." SET panel_url_list='index.php' WHERE panel_url_list='home.php'";

if (!column_exists(DB_PANELS, 'panel_php_exe', FALSE)) {
    $inf_altertable[] = DB_PANELS." ADD panel_php_exe TINYINT(0) UNSIGNED NOT NULL DEFAULT '0' AFTER panel_side";
}

$inf_altertable[] = DB_CUSTOM_PAGES." CHANGE page_access page_access VARCHAR(50) NOT NULL DEFAULT '0'";

$inf_updatedbrow[] = DB_ADMIN." SET admin_link='upgrade.php' WHERE admin_rights='U'";

/**
 * Delete all unused files
 */
$deleted_files = [
    THEMES.'templates/render_functions.php',
    LOCALE.'English/admin/banners.php',
    ADMIN.'updateuser.php',
    ADMIN.'includes/update_checker.php',
    ADMIN.'images/phpinfo.png',
    ADMIN.'phpinfo.php',
    LOCALE.'English/admin/phpinfo.php',
    INCLUDES.'elFinder/themes/manifests/',
    ADMIN.'images/theme_settings.png',
    ADMIN.'settings_theme.php',
    BASEDIR.'home.php',
    CLASSES.'PHPFusion/Installer/Batch.core.php',
    CLASSES.'PHPFusion/Installer/Console.core.php',
    CLASSES.'PHPFusion/Installer/Infusion.core.php',
    CLASSES.'PHPFusion/Installer/Install.core.php',
    CLASSES.'PHPFusion/Installer/Requirements.core.php',
    CLASSES.'PHPFusion/Installer/Lib/Core.settings.php',
    CLASSES.'PHPFusion/Installer/Lib/Core.tables.php',
    CLASSES.'PHPFusion/Installer/Steps/Complete.php',
    CLASSES.'PHPFusion/Feedback/',
    CLASSES.'PHPFusion/Geomap/',
    CLASSES.'PHPFusion/CustomPage.php',
    CLASSES.'PHPFusion/RemoteFileReader.php',
    CLASSES.'PHPFusion/Search/Search_Model.php',
    CLASSES.'PHPFusion/Search.php',
    DYNAMICS.'assets/colorpick/css/',
    DYNAMICS.'assets/colorpick/js/',
    DYNAMICS.'assets/datepicker/css/',
    DYNAMICS.'assets/datepicker/js/',
    INCLUDES.'jscripts/tinymce/',
    INCLUDES.'classes/PHPMailer/language/',
    INCLUDES.'dynamics/assets/datepicker/locale/',
    INCLUDES.'dynamics/assets/password/lang/',
    INCLUDES.'dynamics/assets/fileinput/js/locales/',
    THEMES.'templates/switcher.php',
    THEMES.'templates/tinymce.css',
    IMAGES.'imagelist.js',
    INCLUDES.'buildlist.php',
    INCLUDES.'geomap/callingcodes.inc.php',
    INCLUDES.'geomap/form_geomap.json.php',
    THEMES.'Magazine/classes/',
    THEMES.'Magazine/theme_autoloader.php',
    INFUSIONS.'forum/classes/Functions.php',
    INFUSIONS.'forum/classes/Admin.php',
    INFUSIONS.'forum/classes/post/ranks.php',
    IMAGES.'error/',
    INCLUDES.'multi_attachment.js',
    INFUSIONS.'blog/classes/Functions.php',
    INFUSIONS.'downloads/classes/Functions.php',
    INFUSIONS.'news/classes/admin/controllers/news_preview.php',
    INFUSIONS.'news/classes/news/news_preview.php',
];

foreach ($deleted_files as $file) {
    if (is_file($file)) {
        @unlink($file);
    } else {
        if (is_dir($file)) {
            rrmdir($file);
        }
    }
}
