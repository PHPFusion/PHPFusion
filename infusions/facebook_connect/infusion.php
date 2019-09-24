<?php

$locale = fusion_get_locale('', [FBC_LOCALE]);
// Do the hook for login, logout, connect and disconnect.
$inf_title = $locale['fbc_title'];
$inf_description = $locale['fbc_desc'];
$inf_version = "1.0";
$inf_developer = "PHP-Fusion Development Team";
$inf_email = "management@php-fusion.co.uk";
$inf_weburl = "php-fusion.co.uk";
$inf_folder = "facebook_connect";
$inf_image = "facebook.svg";

$inf_adminpanel[] = [
    "image"  => $inf_image,
    "page"   => 2,
    "rights" => "FBC",
    "title"  => $locale['fbc_title'],
    "panel"  => ""
];

$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('fb_app_id', '', '".$inf_folder."')";
$inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('fb_secret', '', '$inf_folder')";

$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='$inf_folder'";
