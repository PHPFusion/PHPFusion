<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: serverinfo.php
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
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
pageaccess('PI');

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/serverinfo.php');

add_breadcrumb(['link' => ADMIN.'serverinfo.php'.fusion_get_aidlink(), 'title' => $locale['SRV_400']]);

$allowed_sections = ['general', 'phpsettings', 'folderpermission', 'details'];
$sections = in_array(get('section'), $allowed_sections) ? get('section') : 'general';

$tabs['title'][] = $locale['SRV_401'];
$tabs['id'][] = 'general';
$tabs['icon'][] = "";
$tabs['title'][] = $locale['SRV_440'];
$tabs['id'][] = 'folderpermission';
$tabs['icon'][] = "";
$tabs['title'][] = $locale['SRV_450'];
$tabs['id'][] = 'details';
$tabs['icon'][] = "";

opentable($locale['SRV_400']);
echo opentab($tabs, $sections, 'serverinfotabs', TRUE, 'nav-tabs');
switch ($sections) {
    case 'folderpermission':
        folderpermission();
        break;
    case 'details':
        details();
        break;
    default:
        general();
        break;
}
echo closetab();
closetable();

function general() {
    $locale = fusion_get_locale();
    $settings = fusion_get_settings();

    echo '<div class="row">';
    echo '<div class="col-xs-12 col-sm-6">';
    openside('');
    echo '<div><span class="strong">'.$locale['SRV_402'].'</span> <span class="pull-right-lg">'.php_uname().'</span></div>';
    echo '<hr class="m-t-5 m-b-5">';
    echo '<div><span class="strong">'.$locale['SRV_403'].'</span> <span class="pull-right-lg">'.server('SERVER_SOFTWARE').'</span></div>';
    echo '<hr class="m-t-5 m-b-5">';
    echo '<div><span class="strong">'.$locale['SRV_404'].'</span> <span class="pull-right-lg">'.phpversion().'</span></div>';
    echo '<hr class="m-t-5 m-b-5">';
    echo '<div><span class="strong">'.$locale['SRV_405'].'</span> <span class="pull-right-lg">'.php_sapi_name().'</span></div>';
    echo '<hr class="m-t-5 m-b-5">';
    echo '<div><span class="strong">'.$locale['SRV_406'].'</span> <span class="pull-right-lg">'.dbconnection()->getServerVersion().'</span></div>';
    echo '<hr class="m-t-5 m-b-5">';
    echo '<div><span class="strong">'.$locale['SRV_406a'].'</span> <span class="pull-right-lg">'.str_replace('\\PHPFusion\\Database\Driver\\', '', \PHPFusion\Database\DatabaseFactory::getDriverClass()).'</span></div>';
    echo '<hr class="m-t-5 m-b-5">';
    echo '<div><span class="strong">'.$locale['SRV_407'].'</span> <span class="pull-right-lg">'.$settings['version'].'</span></div>';
    echo '<hr class="m-t-5 m-b-5">';
    echo '<div><span class="strong">'.$locale['SRV_408'].'</span> <span class="pull-right-lg">'.DB_PREFIX.'</span></div>';
    echo '<hr class="m-t-5 m-b-5">';
    echo '<div><span class="strong">'.$locale['SRV_409'].'</span> <span class="pull-right-lg">'.COOKIE_PREFIX.'</span></div>';
    echo '<hr class="m-t-5 m-b-5">';
    echo '<div><span class="strong">'.$locale['SRV_410'].'</span> <span class="pull-right-lg">'.server('HTTP_USER_AGENT').'</span></div>';
    echo '<hr class="m-t-5 m-b-5">';
    if (LANGUAGE !== 'English') {
        echo '<div>'.$locale['SRV_411'].'</div>';
    }
    closeside();
    echo '</div>';

    echo '<div class="col-xs-12 col-sm-6">';
    openside('');
    // Check GD version
    $gd_ver = $locale['na'];
    if (function_exists('gd_info')) {
        $gd_ver = gd_info();
        preg_match('/[0-9]+.[0-9]+/', $gd_ver['GD Version'], $gd_ver);
    }

    echo '<div><span class="strong">'.$locale['SRV_423'].'</span> <span class="pull-right-lg">'.(ini_get('safe_mode') ? $locale['yes'] : $locale['no']).'</span></div>';
    echo '<hr class="m-t-5 m-b-5">';
    echo '<div><span class="strong">'.$locale['SRV_424'].'</span> <span class="pull-right-lg">'.(ini_get('register_globals') ? $locale['yes'] : $locale['no']).'</span></div>';
    echo '<hr class="m-t-5 m-b-5">';
    echo '<div><span class="strong">'.$locale['SRV_425'].' GD</span> <span class="pull-right-lg">'.(extension_loaded('gd') ? $locale['yes'].' ('.$locale['SRV_431'].' '.$gd_ver[0].')' : $locale['no']).'</span></div>';
    echo '<hr class="m-t-5 m-b-5">';
    echo '<div><span class="strong">'.$locale['SRV_425'].' zlib</span> <span class="pull-right-lg">'.(extension_loaded('zlib') ? $locale['yes'] : $locale['no']).'</span></div>';
    echo '<hr class="m-t-5 m-b-5">';
    echo '<div><span class="strong">'.$locale['SRV_425'].' magic_quotes_gpc</span> <span class="pull-right-lg">'.(extension_loaded('magic_quotes_gpc') ? $locale['yes'] : $locale['no']).'</span></div>';
    echo '<hr class="m-t-5 m-b-5">';
    echo '<div><span class="strong">'.$locale['SRV_426'].'</span> <span class="pull-right-lg">'.(ini_get('file_uploads') ? $locale['yes'].' ('.ini_get('upload_max_filesize').')' : $locale['no']).'</span></div>';
    echo '<hr class="m-t-5 m-b-5">';
    echo '<div><span class="strong">'.$locale['SRV_428'].'</span> <span class="pull-right-lg">'.(ini_get('display_errors') ? $locale['yes'] : $locale['no']).'</span></div>';
    echo '<hr class="m-t-5 m-b-5">';
    echo '<div><span class="strong">'.$locale['SRV_429'].'</span> <span class="pull-right-lg">'.(ini_get('disable_functions') ? str_replace(',', ', ', ini_get('disable_functions')) : $locale['SRV_430']).'</span></div>';
    closeside();
    echo '</div>';
    echo '</div>';
}

function folderpermission() {
    $locale = fusion_get_locale();
    $status = '';
    $folders = [
        // path => have to be writeable or not
        'administration/db_backups/' => TRUE,
        'images/'                    => TRUE,
        'images/avatars/'            => TRUE,
        'images/smiley/'             => TRUE,
        'robots.txt'                 => TRUE,
        'config.php'                 => FALSE
    ];

    $infusions = \PHPFusion\Admins::getInstance()->getFolderPermissions();
    foreach ($infusions as $value) {
        $folders += $value;
    }

    // Check file/folder writeable
    $i = 0;
    foreach ($folders as $folder => $writeable) {
        $status .= "<tr>\n<td style='width:50%'><i class='fa fa-folder fa-fw'></i> ".$folder."</td><td class='text-right'>";
        if (is_writable(BASEDIR.$folder) == TRUE) {
            $status .= "<span class='".($writeable == TRUE ? "text-success" : "text-danger text-bold text-uppercase")."'>".$locale['SRV_441']."</span>";
        } else {
            $status .= "<span class='".($writeable == TRUE ? "text-danger text-bold text-uppercase" : "text-success")."'>".$locale['SRV_442']."</span>";
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
    $locale = fusion_get_locale();
    if (!stristr(ini_get('disable_functions'), "phpinfo")) {
        // Generating new phpinfo style, compatible with PHPFusion styles
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
        $phpinfo = "<div class='admin-message'>".$locale['SRV_451']."</div>\n";
    }
    echo $phpinfo;
}

require_once THEMES.'templates/footer.php';
