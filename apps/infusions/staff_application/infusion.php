<?php
defined('IN_FUSION') || exit;

$locale = fusion_get_locale('', STF_LOCALE);

// Infusion general information
$inf_title = $locale['stf_001'];
$inf_description = $locale['stf_001'];
$inf_version = "1.2";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "info@php-fusion.co.uk";
$inf_weburl = "https://www.php-fusion.co.uk";
$inf_folder = "staff_application";
$inf_image = 'team.png';

$inf_newtable[1] = DB_STF_APPLICATIONS." (
stf_id SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
stf_user_id MEDIUMINT(8) UNSIGNED NOT NULL,
stf_real_name VARCHAR(30) NOT NULL DEFAULT '',
stf_main_name VARCHAR(30) NOT NULL DEFAULT '',
stf_main_name_id MEDIUMINT(8) UNSIGNED NOT NULL,
stf_email VARCHAR(100) NOT NULL,
stf_type TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL, 
stf_ip VARCHAR(20) NOT NULL DEFAULT '0.0.0.0',
stf_status TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL,
stf_admin MEDIUMINT(8) UNSIGNED NOT NULL,
stf_text TEXT NOT NULL,
stf_datestamp INT(10) UNSIGNED DEFAULT '0' NOT NULL,
stf_approver_comment TEXT NOT NULL,
PRIMARY KEY (stf_id)
) TYPE=MyISAM;";

$inf_droptable[1] = DB_STF_APPLICATIONS;

$inf_adminpanel[1] = [
    "title"  => $locale['stf_009'],
    "image"  => "image.gif",
    "panel"  => "application_admin.php",
    "rights" => "STFF"
];

$inf_sitelink[1] = [
    "title"      => $locale['stf_015'],
    "url"        => "application.php",
    "visibility" => "0"
];
