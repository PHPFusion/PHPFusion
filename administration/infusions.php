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

if (($folder = post('infuse'))) {
    \PHPFusion\Installer\Infusion_Core::getInstance()->infuse($folder);
    redirect(FUSION_REQUEST);
} else if ($folder = post('defuse')) {
    \PHPFusion\Installer\Infusion_Core::getInstance()->defuse($folder);
    redirect(FUSION_REQUEST);
}

opentable($locale['400']);

$infs = [];
$temp = makefilelist(INFUSIONS, ".|..|index.php", TRUE, "folders");
foreach ($temp as $folders) {
    $inf = \PHPFusion\Installer\Infusion_Core::load_infusion($folders);
    if (!empty($inf)) {
        $infs[$folders] = $inf;
    }
    unset($inf);
}

echo "<div class='alert alert-info'>\n";
echo str_replace(['[LINK]', '[/LINK]'], ["<a class='text-underline' href='https://www.php-fusion.co.uk/infusions/marketplace/'' target='_blank'>", "</a>"], $locale['422']);
echo "</div>\n";

echo "<div class='text-right spacer-xs'>\n";
echo format_word(count($infs), $locale['fmt_infusion']);
echo "</div>\n";

if (!isset($_POST['infuse']) && !isset($_POST['infusion']) && !isset($_GET['defuse'])) {
    $content = "";
    if ($infs) {
        $content .= "<div class='table-responsive'><table class='table'>\n";
        $content .= "<thead><tr><th></th><th>".$locale['400a']."</th><th>".$locale['425']."</th></tr>\n</thead>\n";
        $content .= "<tbody>";
        foreach ($infs as $i => $inf) {
            $row_class = '';
            $status = $locale['415'];
            $label = 'success';
            if ($inf['status'] > 0) {
                $row_class = 'info';
                if ($inf['status'] > 1) {
                    $row_class = 'warning';
                    $button = form_button('infuse', $locale['416'], $inf['folder'], ['class' => 'btn btn-primary btn-xs infuse', 'input_id' => 'infuse_'.$i]);
                } else {
                    $button = form_button('defuse', $locale['411'], $inf['folder'], ['class' => 'btn btn-danger btn-xs defuse', 'input_id' => 'defuse_'.$i]);
                }
            } else {
                $status = $locale['414'];
                $label = 'info';
                $button = form_button('infuse', $locale['401'], $inf['folder'], ['class' => 'btn btn-success btn-xs infuse', 'input_id' => 'infuse_'.$i]);
            }
            $description = $inf['description']."<br/>\n";
            $description .= "<span class='m-r-5 label label-".$label."'>$status</span>|";
            $description .= "<span class='m-l-5 m-r-5'>".$locale['420']." ".($inf['version'] ? $inf['version'] : '')."</span>";
            $description .= ($inf['url'] ? "|<a href='".$inf['url']."' target='_blank'>" : "")." 
            <span class='m-l-5 m-r-5'>".($inf['developer'] ? $inf['developer'] : $locale['410'])."</span>
             ".($inf['url'] ? "</a>" : "")."
             ".($inf['email'] ? "|<a class='m-l-5' href='mailto:".$inf['email']."'>".$locale['409']."</a>" : '');

            $content .= "<tr class='$row_class'><td>\n";
            $content .= "</td>\n<td class='col-lg-4'>\n";
            $content .= "<div class='pull-left m-r-20 overflow-hide'><img style='width:48px;' alt='".$inf['name']."' src='".$inf['image']."'/></div>\n";
            $content .= "<div>\n";
            $content .= ($inf['status'] > 0 ? "<strong>" : "").$inf['title'].($inf['status'] > 0 ? "</strong>" : "")."<br/>";
            $content .= openform('infuseform', 'post', FUSION_SELF.fusion_get_aidlink());
            $content .= $button;
            $content .= closeform();
            $content .= "<div class='hidden-lg spacer-sm'>".$description."</div>\n";
            $content .= "</div>\n</td>\n";
            $content .= "<td class='hidden-xs hidden-sm hidden-md'>\n";
            $content .= $description;
            $content .= "</td>\n";
            $content .= "</tr>\n";


        }
    } else {
        $content .= "<td class='text-center'>".$locale['417']."</td>\n";
    }
    $content .= "</tbody></table></div>\n";

    echo $content;
}
closetable();
require_once THEMES.'templates/footer.php';
