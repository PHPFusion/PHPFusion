<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: infusions.php
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
require_once THEMES.'templates/admin_header.php';
pageAccess('I');
$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/infusions.php");
$settings = fusion_get_settings();

add_to_jquery("$('.defuse').bind('click', function() {return confirm('".$locale['412']."');});");
\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'infusions.php'.fusion_get_aidlink(), 'title' => $locale['400']]);

if (($folder = filter_input(INPUT_POST, 'infuse'))) {
    \PHPFusion\Installer\Infusion_Core::getInstance()->infuse($folder);
    redirect(FUSION_REQUEST);
} else if ($folder = filter_input(INPUT_POST, 'defuse')) {
    \PHPFusion\Installer\Infusion_Core::getInstance()->defuse($folder);
    redirect(FUSION_REQUEST);
}

opentable($locale['400']);
echo "<div class='text-right'>\n";
echo "<a href='https://www.php-fusion.co.uk/infusions/marketplace/' title='".$locale['422']."' target='_blank'>".$locale['422']."</a>\n";
echo "</div>\n";

$infs = [];
$temp = makefilelist(INFUSIONS, ".|..|index.php", TRUE, "folders");
foreach ($temp as $folders) {
    $inf = \PHPFusion\Installer\Infusion_Core::load_infusion($folders);
    if (!empty($inf)) {
        $infs[$folders] = $inf;
    }
}

//if (!isset($_POST['infuse']) && !isset($_POST['infusion']) && !isset($_GET['defuse'])) {
    $content = "";
    if ($infs) {
        $content .= "<div class='list-group'>\n";
        $content .= "<div class='list-group-item hidden-xs'>\n";
        $content .= "<div class='row'>\n";
        $content .= "<div class='col-xs-2 col-sm-4 col-md-2'><strong>".$locale['419']."</strong></div>\n";
        $content .= "<div class='col-xs-6 col-sm-6 col-md-5 col-lg-4'><strong>".$locale['400']."</strong></div>\n";
        $content .= "<div class='col-xs-2 col-sm-2 col-md-2'><strong>".$locale['418']."</strong></div>\n";
        $content .= "<div class='hidden-xs hidden-sm col-md-2 col-lg-1'><strong>".$locale['420']."</strong></div>\n";
        $content .= "<div class='hidden-xs hidden-sm hidden-md col-lg-3 col-lg-offset-0 col-lg-2'><strong>".$locale['421']."</strong></div>\n";
        $content .= "</div>\n</div>\n";

        foreach ($infs as $i => $inf) {

            $content .= openform('infuseform', 'post', FUSION_SELF.fusion_get_aidlink());
            $content .= "<div class='list-group-item'>\n";
            $content .= "<div class='row'>\n";
            $content .= "<div class='col-xs-2 col-sm-4 col-md-2'>\n";
            if ($inf['status'] > 0) {
                if ($inf['status'] > 1) {
                    $content .= form_button('infuse', $locale['416'], $inf['folder'], ['class' => 'btn-info m-t-5 infuse', 'icon' => 'fa fa-magnet', 'input_id' => 'infuse_'.$i]);
                } else {
                    $content .= form_button('defuse', $locale['411'], $inf['folder'], ['class' => 'btn-default m-t-5 defuse', 'icon' => 'fa fa-trash', 'input_id' => 'defuse_'.$i]);
                }
            } else {
                $content .= form_button('infuse', $locale['401'], $inf['folder'], ['class' => 'btn-primary m-t-5 infuse', 'icon' => 'fa fa-magnet', 'input_id' => 'infuse_'.$i]);
            }
            $content .= "</div>\n";
            $content .= "<div class='col-xs-6 col-sm-6 col-md-5 col-lg-4'>\n";
            $content .= "<div class='pull-left m-r-10'><img style='width:48px;' alt='".$inf['name']."' src='".$inf['image']."'/></div>\n";
            $content .= "<div class='overflow-hide'><strong>".$inf['title']."</strong><br/>".$inf['description']."</div>\n</div>\n";
            $content .= "<div class='col-xs-2 col-sm-2 col-md-2'><h5 class='m-0'>".($inf['status'] > 0 ? "<span class='label label-success'>".$locale['415']."</span>" : "<span class='label label-default'>".$locale['414']."</span>")."</h5></div>\n";
            $content .= "<div class='hidden-xs hidden-sm col-md-2 col-lg-1'>".($inf['version'] ? $inf['version'] : '')."</div>\n";
            $content .= "<div class='col-xs-10 col-xs-offset-2 col-sm-10 col-sm-offset-2 col-md-8 col-md-offset-2 col-lg-3 col-lg-offset-0'>".($inf['url'] ? "<a href='".$inf['url']."' target='_blank'>" : "")." ".($inf['developer'] ? $inf['developer'] : $locale['410'])." ".($inf['url'] ? "</a>" : "")." <br/>".($inf['email'] ? "<a href='mailto:".$inf['email']."'>".$locale['409']."</a>" : '')."</div>\n";

            $content .= "</div>\n</div>\n";
        }
    } else {
        $content .= "<div class='text-center'>".$locale['417']."</div>\n";
    }

    $content .= "</div>\n";
    echo $content;
//}
closetable();
require_once THEMES.'templates/footer.php';
