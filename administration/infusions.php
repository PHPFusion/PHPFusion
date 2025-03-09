<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: infusions.php
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
use PHPFusion\Installer\Infusions;

require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
pageaccess('I');

$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/infusions.php");
$settings = fusion_get_settings();

add_breadcrumb(['link' => ADMIN.'infusions.php'.fusion_get_aidlink(), 'title' => $locale['INF_400']]);

add_to_jquery("$('.defuse').bind('click', function() {return confirm('".$locale['INF_412']."');});");
if ($folder = post("infuse")) {
    Infusions::getInstance()->infuse($folder);
    cdreset('installed_infusions');
    cdreset('adminpages');
    cdreset('infsettings');
    redirect(FUSION_REQUEST);
} else if ($folder = post("defuse")) {
    Infusions::getInstance()->defuse($folder);
    cdreset('installed_infusions');
    cdreset('adminpages');
    cdreset('infsettings');
    redirect(FUSION_REQUEST);
}

opentable($locale['INF_400']);
echo "<div class='text-right m-b-20'><a href='https://phpfusion.com/infusions/marketplace/' title='".$locale['INF_422']."' target='_blank'>".$locale['INF_422']."</a></div>";

$infs = [];
$temp = makefilelist(INFUSIONS, ".|..|index.php", TRUE, "folders");
foreach ($temp as $folders) {
    $inf = Infusions::loadInfusion($folders);
    if (!empty($inf)) {
        $infs[$folders] = $inf;
    }
}

if ($infs) {
    echo "<div class='list-group'>\n";
    echo "<div class='list-group-item hidden-xs'>\n";
    echo "<div class='row'>\n";
    echo "<div class='col-sm-3 col-md-2 col-lg-2'><strong>".$locale['INF_419']."</strong></div>\n";
    echo "<div class='col-sm-7 col-md-5 col-lg-3'><strong>".$locale['INF_400']."</strong></div>\n";
    echo "<div class='col-sm-2 col-md-2 col-lg-2'><strong>".$locale['INF_418']."</strong></div>\n";
    echo "<div class='hidden-sm col-md-1 col-lg-1'><strong>".$locale['rights']."</strong></div>\n";
    echo "<div class='hidden-sm col-md-2 col-lg-1'><strong>".$locale['INF_420']."</strong></div>\n";
    echo "<div class='hidden-sm hidden-md col-lg-3'><strong>".$locale['INF_421']."</strong></div>\n";
    echo "</div>\n</div>\n";

    foreach ($infs as $i => $inf) {

        $adminpanel = !empty($inf['mlt_adminpanel'][LANGUAGE][0]) ? $inf['mlt_adminpanel'][LANGUAGE][0] : ($inf['adminpanel'][0] ?? [
                'panel'  => $inf['folder'],
                'rights' => $inf['rights']
            ]);

        $title = $inf['status'] > 0 ? '<a href="'.INFUSIONS.$inf['folder'].'/'.$adminpanel['panel'].fusion_get_aidlink().'">'.$inf['title'].'</a>' : $inf['title'];

        echo openform('infuseform', 'POST');
        echo "<div class='list-group-item'>\n";
        echo "<div class='row'>\n";
        echo "<div class='col-xs-4 col-sm-3 col-md-2 col-lg-2'>\n";
        if ($inf['status'] > 0) {
            if ($inf['status'] > 1) {
                echo form_button('infuse', $locale['INF_416'], $inf['folder'], ['class' => 'btn-info m-t-5 infuse', 'icon' => 'fa fa-magnet', 'input_id' => 'infuse_'.$i]);
            } else {
                echo form_button('defuse', $locale['INF_411'], $inf['folder'], ['class' => 'btn-default m-t-5 defuse', 'icon' => 'fa fa-trash', 'input_id' => 'defuse_'.$i]);
            }
        } else {
            echo form_button('infuse', $locale['INF_401'], $inf['folder'], ['class' => 'btn-primary m-t-5 infuse', 'icon' => 'fa fa-magnet', 'input_id' => 'infuse_'.$i]);
        }
        echo "</div>\n";
        echo "<div class='col-xs-8 col-sm-7 col-md-5 col-lg-3'>\n";
        echo "<div class='hidden-xs pull-left m-r-10'><img style='width:48px;' alt='".$inf['name']."' src='".$inf['image']."'/></div>\n";
        echo "<div class='overflow-hide'><strong>".$title."</strong><br/>".$inf['description']."</div>";
        echo "</div>";
        echo "<div class='hidden-xs col-sm-2 col-md-2 col-lg-2'><h5 class='m-0'>".($inf['status'] > 0 ? "<span class='label label-success'>".$locale['INF_415']."</span>" : "<span class='label label-default'>".$locale['INF_414']."</span>")."</h5></div>\n";
        echo "<div class='hidden-xs hidden-sm col-md-1 col-lg-1'><span class='badge'>".$adminpanel['rights']."</span></div>\n";
        echo "<div class='hidden-xs hidden-sm col-md-2 col-lg-1'>".(!empty($inf['version']) ? $inf['version'] : '')."</div>\n";
        echo "<div class='hidden-xs hidden-sm col-md-12 col-md-offset-2 col-lg-3 col-lg-offset-0'>".($inf['url'] ? "<a href='".$inf['url']."' target='_blank'>" : "")." ".(!empty($inf['developer']) ? $inf['developer'] : $locale['410'])." ".($inf['url'] ? "</a>" : "")." <br/>".($inf['email'] ? "<a href='mailto:".$inf['email']."'>".$locale['INF_409']."</a>" : '')."</div>\n";
        echo "</div></div>";
    }
} else {
    echo "<div class='text-center'>".$locale['INF_417']."</div>";
}

echo "</div>";

closetable();
require_once THEMES.'templates/footer.php';
