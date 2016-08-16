<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: settings_banners.php
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
require_once "../maincore.php";
pageAccess('SB');
require_once THEMES."templates/admin_header.php";
require_once INCLUDES."html_buttons_include.php";
include LOCALE.LOCALESET."admin/settings.php";
$settings = fusion_get_settings();
add_breadcrumb(array('link' => ADMIN.'banners.php'.$aidlink, 'title' => $locale['850']));

$message = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case '1':
            $message = $locale['901'];;
            $status = 'danger';
            $icon = "<i class='fa fa-alert fa-lg fa-fw'></i>";
            break;
        default:
            $message = $locale['900'];;
            $status = 'success';
            $icon = "<i class='fa fa-check-square-o fa-lg fa-fw'></i>";
    }
    if ($message) {
        addNotice($status, $icon.$message);
    }
}

if (isset($_POST['save_banners'])) {
    $error = 0;
    $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".addslash($_POST['sitebanner1'])."' WHERE settings_name='sitebanner1'");
    if (!$result) {
        $error = 1;
    }
    $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".addslash($_POST['sitebanner2'])."' WHERE settings_name='sitebanner2'");
    if (!$result) {
        $error = 1;
    }
    redirect(FUSION_SELF.$aidlink."&error=".$error, TRUE);

}
if (isset($_POST['preview_banners'])) {
    $sitebanner1 = "";
    $sitebanner2 = "";
    $sitebanner1 = stripslash($_POST['sitebanner1']);
    $sitebanner2 = stripslash($_POST['sitebanner2']);
} else {
    $sitebanner1 = stripslashes($settings['sitebanner1']);
    $sitebanner2 = stripslashes($settings['sitebanner2']);
}
opentable($locale['850']);
echo openform("banner_form", "post", FUSION_REQUEST);
echo form_textarea('sitebanner1', $locale['851'], $sitebanner1, array(
                                    "type" => "html",
                                    "form_name" => "banner_form",
                                    "inline" => FALSE,
                                )
);
if (isset($_POST['preview_banners']) && $sitebanner1) {
    eval("?><div class='list-group-item'>".$sitebanner1."</div><?php ");
}
echo form_textarea('sitebanner2', $locale['852'], $sitebanner2, array(
    "type" => "html",
    "form_name" => "banner_form",
    "inline" => FALSE,
));
if (isset($_POST['preview_banners']) && $sitebanner2) {
    eval("?><div class='list-group-item'>".$sitebanner2."</div><?php ");
}
echo form_button('preview_banners', $locale['855'], $locale['855'], array('class' => 'btn-default m-r-10'));
echo form_button('save_banners', $locale['854'], $locale['854'], array('class' => 'btn-success m-r-10'));
echo closeform();
closetable();

require_once THEMES."templates/footer.php";