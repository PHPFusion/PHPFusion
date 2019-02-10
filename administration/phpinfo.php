<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: phpinfo.php
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
require_once __DIR__.'/../maincore.php';
pageAccess('PI');

use PHPFusion\BreadCrumbs;

require_once THEMES.'templates/admin_header.php';
$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/phpinfo.php");
$aidlink = fusion_get_aidlink();
BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'phpinfo.php'.fusion_get_aidlink(), 'title' => $locale['400']]);

$allowed_section = ['general', 'phpsettings', 'folderpermission', 'details'];
$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'general';

$master_tab_title['title'][] = $locale['401'];
$master_tab_title['id'][] = 'general';
$master_tab_title['icon'][] = "";
$master_tab_title['title'][] = $locale['420'];
$master_tab_title['id'][] = 'phpsettings';
$master_tab_title['icon'][] = "";
$master_tab_title['title'][] = $locale['440'];
$master_tab_title['id'][] = 'folderpermission';
$master_tab_title['icon'][] = "";
$master_tab_title['title'][] = $locale['450'];
$master_tab_title['id'][] = 'details';
$master_tab_title['icon'][] = "";

if (isset($_GET['section'])) {
    switch ($_GET['section']) {
        case "phpsettings":
            BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $locale['420']]);
            break;
        case "folderpermission":
            BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $locale['440']]);
            break;
        case "details":
            BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $locale['450']]);
            break;
        default:
            break;
    }
}

opentable($locale['400']);
echo opentab($master_tab_title, $_GET['section'], 'general', TRUE, 'nav-tabs m-b-15');
switch ($_GET['section']) {
    case "phpsettings":
        phpsettings();
        break;
    case "folderpermission":
        folderpermission();
        break;
    case "details":
        details();
        break;
    default:
        general();
        break;
}
echo closetab();
closetable();
//General info
function general() {
    $locale = fusion_get_locale('', LOCALE.LOCALESET."admin/phpinfo.php");
    $settings = fusion_get_settings();
    $phpinfo = "<div class='table-responsive'><table class='table table-hover table-striped' id='folders'>\n";
    $phpinfo .= "<tr>\n<td style='width:20%'>".$locale['402']."</td><td class='text-right'>".php_uname()."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:20%'>".$locale['403']."</td><td class='text-right'>".$_SERVER['SERVER_SOFTWARE']."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:20%'>".$locale['404']."</td><td class='text-right'>".phpversion()."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:20%'>".$locale['405']."</td><td class='text-right'>".php_sapi_name()."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:20%'>".$locale['406']."</td><td class='text-right'>".dbconnection()->getServerVersion()."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:20%'>".$locale['406a']."</td><td class='text-right'>".str_replace('\\PHPFusion\\Database\Driver\\', '', \PHPFusion\Database\DatabaseFactory::getDriverClass())."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:20%'>".$locale['407']."</td><td class='text-right'>".$settings['version']."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:20%'>".$locale['408']."</td><td class='text-right'>".DB_PREFIX."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:20%'>".$locale['409']."</td><td class='text-right'>".COOKIE_PREFIX."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:20%'>".$locale['410']."</td><td class='text-right'>".stripinput($_SERVER['HTTP_USER_AGENT'])."</td></tr>\n";
    $phpinfo .= "</table>\n</div>";
    echo $phpinfo;
}

function phpsettings() {

    $locale = fusion_get_locale('', LOCALE.LOCALESET."admin/phpinfo.php");
    //Check GD version
    if (function_exists('gd_info')) {
        $gd_ver = gd_info();
        preg_match('/[0-9]+.[0-9]+/', $gd_ver['GD Version'], $gd_ver);
    } else {
        $gd_ver = '';
    }
    $phpinfo = "<div class='table-responsive'><table class='table table-hover table-striped' id='folders'>\n";
    $phpinfo .= "<tr>\n<td style='width:50%'>".$locale['423']."</td><td class='text-right'>".(ini_get('safe_mode') ? $locale['yes'] : $locale['no'])."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:50%'>".$locale['424']."</td><td class='text-right'>".(ini_get('register_globals') ? $locale['yes'] : $locale['no'])."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:50%'>".$locale['425']." GD (".$locale['431'].")</td><td class='text-right'>".(extension_loaded('gd') ? $locale['yes']." (".$gd_ver[0].")" : $locale['no'])."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:50%'>".$locale['425']." zlib</td><td class='text-right'>".(extension_loaded('zlib') ? $locale['yes'] : $locale['no'])."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:50%'>".$locale['425']." Magic_quotes_gpc</td><td class='text-right'>".(ini_get('magic_quotes_gpc') ? $locale['yes'] : $locale['no'])."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:50%'>".$locale['426']."</td><td class='text-right'>".(ini_get('file_uploads') ? $locale['yes']." (".ini_get('upload_max_filesize')."B)" : $locale['no'])."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:50%'>".$locale['428']."</td><td class='text-right'>".(ini_get('display_errors') ? $locale['yes'] : $locale['no'])."</td></tr>\n";
    $phpinfo .= "<tr>\n<td style='width:50%'>".$locale['429']."</td><td class='text-right'>".(ini_get('disable_functions') ? str_replace(',', ', ', ini_get('disable_functions')) : $locale['430'])."</td></tr>\n";
    $phpinfo .= "</table>\n</div>";
    echo $phpinfo;
}

function folderpermission() {
    $locale = fusion_get_locale('', LOCALE.LOCALESET."admin/phpinfo.php");
    $status = '';
    $folders = [
        //path => have to be writeable or not
        'administration/db_backups/' => TRUE,
        'images/'                    => TRUE,
        'images/imagelist.js'        => TRUE,
        'images/avatars/'            => TRUE,
        'images/smiley/'             => TRUE,
        'robots.txt'                 => TRUE,
        'config.php'                 => FALSE
    ];

    $infusions = \PHPFusion\Admins::getInstance()->getFolderPermissions();
    foreach ($infusions as $value) {
        $folders += $value;
    }

    add_to_head("<style type='text/css'>.passed {color:green;} .failed {color:red; text-transform: uppercase; font-weight:bold;}</style>\n");
    //Check file/folder writeable
    $i = 0;
    foreach ($folders as $folder => $writeable) {
        $status .= "<tr>\n<td style='width:50%'><i class='fa fa-folder fa-fw'></i> ".$folder."</td><td class='text-right'>";
        if (is_writable(BASEDIR.$folder) == TRUE) {
            $status .= "<span class='".($writeable == TRUE ? "passed" : "failed")."'>".$locale['441']."</span>";
        } else {
            $status .= "<span class='".($writeable == TRUE ? "failed" : "passed")."'>".$locale['442']."</span>";
        }
        $status .= " (".substr(sprintf('%o', fileperms(BASEDIR.$folder)), -4).")</td></tr>\n";
        $i++;
    }
    $phpinfo = "<div class='table-responsive'><table class='table table-hover table-striped table-responsive tab' id='folders'>\n";
    $phpinfo .= $status;
    $phpinfo .= "</table>\n</div>";
    echo $phpinfo;
}

function details() {
    $locale = fusion_get_locale('', LOCALE.LOCALESET."admin/phpinfo.php");
    if (!stristr(ini_get('disable_functions'), "phpinfo")) {
        //Generating new phpinfo style, compatible with PHP-Fusion styles
        ob_start();
        phpinfo();
        $phpinfo = ob_get_contents();
        ob_end_clean();
        $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
        $phpinfo = preg_replace('%<h1.*>.*</h1>%', "<h3 class='tbl2'>$2</h3>", $phpinfo);
        $phpinfo = preg_replace('%<h2><a name="(.*)">(.*)</a></h2>%', "<h4 class='phpinfo forum-caption'>$2</h4>", $phpinfo);
        $phpinfo = preg_replace('%<h2>(.*)</h2>%', "<div class='forum-caption'>$1</div>", $phpinfo);
        $phpinfo = preg_replace('%<th colspan="2">(.*)</th>%', "<th colspan='2'><h5>$1</h5></th>", $phpinfo);
        $phpinfo = str_replace('<table>', '<table class="table table-responsive table-hover table-striped">', $phpinfo);
        $phpinfo = str_replace("<h3 class='tbl2'></h3>", '', $phpinfo);
        $phpinfo = str_replace('class="h"', "class='tbl2 center'", $phpinfo);
        $phpinfo = str_replace('class="e"', "class='tbl2'", $phpinfo);
        $phpinfo = str_replace('class="v"', "class='tbl1'", $phpinfo);
    } else {
        $phpinfo = "<div class='admin-message'>".$locale['451']."</div>\n";
    }
    echo $phpinfo;
}

require_once THEMES.'templates/footer.php';
