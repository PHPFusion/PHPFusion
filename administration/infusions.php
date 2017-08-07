<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusions.php
| Author: PHP-Fusion Development Team
| Co-Author: Christian Damsgaard Jorgensen (PMM)
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
require_once THEMES."templates/admin_header.php";
pageAccess('I');
$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/infusions.php");
$settings = fusion_get_settings();

add_to_jquery("$('.defuse').bind('click', function() {return confirm('".$locale['412']."');});");
\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'infusions.php'.fusion_get_aidlink(), 'title' => $locale['400']]);

if (($folder = filter_input(INPUT_POST, 'infuse'))) {
    PHPFusion\Installer\Infusion_Core::getInstance()->infuse($folder);
} elseif ($folder = filter_input(INPUT_POST, 'defuse')) {
    PHPFusion\Installer\Infusion_Core::getInstance()->defuse($folder);
}

opentable($locale['400']);
echo "<div class='text-right'>\n";
echo "<a href='https://www.php-fusion.co.uk/infusions/addondb/directory.php' title='".$locale['422']."' target='_blank'>".$locale['422']."</a>\n";
echo "</div>\n";
$temp = opendir(INFUSIONS);
$infs = array();
while ($folder = readdir($temp)) {
    if (!in_array($folder, array("..", ".")) && ($inf = PHPFusion\Installer\Infusion_Core::load_infusion($folder))) {
        $infs[] = $inf;
    }
}
closedir($temp);
sort($infs);
if (!isset($_POST['infuse']) && !isset($_POST['infusion']) && !isset($_GET['defuse'])) {
    $content = "";
    if ($infs) {
        $content .= "<div class='list-group'>\n";
        $content .= "<div class='list-group-item hidden-xs'>\n";
        $content .= "<div class='row'>\n";
        $content .= "<div class='col-xs-2 col-sm-4 col-md-2'>\n<strong>".$locale['419']."</strong></div>\n";
        $content .= "<div class='col-xs-6 col-sm-6 col-md-5 col-lg-4'>\n<strong>".$locale['400']."</strong></div>\n";
        $content .= "<div class='col-xs-2 col-sm-2 col-md-2'>\n<strong>".$locale['418']."</strong></div>\n";
        $content .= "<div class='hidden-xs hidden-sm col-md-2 col-lg-1'>\n<strong>".$locale['420']."</strong></div>\n";
        $content .= "<div class='hidden-xs hidden-sm hidden-md col-lg-3 col-lg-offset-0 col-lg-2'>\n<strong>".$locale['421']."</strong></div>\n";
        $content .= "</div>\n</div>\n";


        foreach ($infs as $i => $inf) {

            $content .= openform('infuseform', 'post', FUSION_SELF.fusion_get_aidlink());
            $content .= "<div class='list-group-item'>\n";
            $content .= "<div class='row'>\n";
            $content .= "<div class='col-xs-2 col-sm-4 col-md-2'>\n";
            if ($inf['status'] > 0) {
                if ($inf['status'] > 1) {
                    $content .= form_button('infuse', $locale['416'], $inf['folder'], array('class' => 'btn-info m-t-5 infuse', 'icon' => ' fa fa-magnet', 'input_id' => 'infuse_'.$i));
                } else {
                    $content .= form_button('defuse', $locale['411'], $inf['folder'], array('class' => 'btn-default m-t-5 defuse', 'icon' => 'fa fa-trash', 'input_id' => 'defuse_'.$i));
                }
            } else {
                $content .= form_button('infuse', $locale['401'], $inf['folder'], array('class' => 'btn-primary m-t-5 infuse', 'icon' => 'fa fa-magnet', 'input_id' => 'infuse_'.$i));
            }
            $content .= "</div>\n";
            $content .= "<div class='col-xs-6 col-sm-6 col-md-5 col-lg-4'>\n";
            $content .= "<div class='pull-left m-r-10'>\n<img style='width:48px;' src='".$inf['image']."' alt='".$inf['name']."'/></div>\n";
            $content .= "<div class='overflow-hide'>\n<strong>".$inf['title']."</strong><br/>".$inf['description']."</div>\n</div>\n";
            $content .= "<div class='col-xs-2 col-sm-2 col-md-2'>".($inf['status'] > 0 ? "<h5 class='m-0'><label class='label label-success'>".$locale['415']."</label></h5>" : "<h5 class='m-0'><label class='label label-default'>".$locale['414']."</label></h5>")."</div>\n";
            $content .= "<div class='hidden-xs hidden-sm col-md-2 col-lg-1'>".($inf['version'] ? $inf['version'] : '')."</div>\n";
            $content .= "<div class='col-xs-10 col-xs-offset-2 col-sm-10 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-3 col-lg-offset-0'>".($inf['url'] ? "<a href='".$inf['url']."' target='_blank'>" : "")." ".($inf['developer'] ? $inf['developer'] : $locale['410'])." ".($inf['url'] ? "</a>" : "")." <br/>".($inf['email'] ? "<a href='mailto:".$inf['email']."'>".$locale['409']."</a>" : '')."</div>\n";

            $content .= "</div>\n</div>\n";
    }
    } else {
        $content .= "<br /><p class='text-center'>".$locale['417']."</p>\n";
    }

    $content .= "</div>\n";
    echo $content;
}
closetable();
require_once THEMES."templates/footer.php";
