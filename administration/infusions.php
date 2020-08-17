<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: infusions.php
| Author: Core Development Team (coredevs@phpfusion.com)
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

class Infusions_Admin {
    private $infuse_post = '';
    private $defuse_post = '';

    public function __construct() {
        pageAccess('I');
        $this->infuse_post = post('infuse');
        $this->defuse_post = post('defuse');
    }

    private function doInfuse() {
        if (($folder = $this->infuse_post)) {
            \PHPFusion\Installer\Infusion_Core::getInstance()->infuse($folder);

        } else if ($folder = $this->defuse_post) {
            \PHPFusion\Installer\Infusion_Core::getInstance()->defuse($folder);

        }
    }

    private function cacheInf() {
        $infs = [];
        $temp = makefilelist(INFUSIONS, ".|..|index.php", TRUE, "folders");
        foreach ($temp as $folders) {
            $inf = \PHPFusion\Installer\Infusion_Core::load_infusion($folders);
            if (!empty($inf)) {
                $infs[$folders] = $inf;
            }
            unset($inf);
        }
        return (array)$infs;
    }

    public function displayAdmin() {
        $locale = fusion_get_locale('', LOCALE.LOCALESET."admin/infusions.php");
        add_breadcrumb(['link' => ADMIN.'infusions.php'.fusion_get_aidlink(), 'title' => $locale['400']]);
        add_to_jquery("$('.defuse').bind('click', function() {return confirm('".$locale['412']."');});");
        $infs = $this->cacheInf();
        $this->doInfuse();
        opentable($locale['400']);
        echo "<div class='clearfix spacer-xs'>";
        echo "<div class='pull-right text-smaller'>\n";
        echo format_word(count($infs), $locale['fmt_infusion']);
        echo "</div>\n";
        //@todo: Add file filter
        // echo "<div class='pull-left'>";
        // echo "<a href=''>All</a> | ";
        // echo "<a href=''>Infused</a> | "; //             $status = $locale['415'];
        // echo "<a href=''>Inactive</a> | "; //   $status = $locale['414'];
        // echo "<a href=''>Update Available</a>";
        // echo '</div>';
        echo '</div>';


        if (!$this->infuse_post && !$this->defuse_post) {
            $content = "";
            if ($infs) {
                $content .= "<div class='table-responsive'><table class='table'>\n";
                $content .= "<thead><tr><th>".$locale['400a']."</th><th class='hidden-xs hidden-sm hidden-md'>".$locale['425']."</th></tr>\n</thead>\n";
                $content .= "<tbody>";
                foreach ($infs as $i => $inf) {

                    $row_class = '';

                    $button = form_button('infuse', $locale['401'], $inf['folder'], ['class' => 'btn btn-link btn-xs infuse', 'input_id' => 'infuse_'.$i]);

                    if ($inf['status'] > 0) {
                        $row_class = 'info';
                        $button = '';
                        if ($inf['status'] > 1) {
                            $row_class = 'warning';
                            $button = form_button('infuse', $locale['416'], $inf['folder'], ['class' => 'btn btn-link btn-xs infuse p-l-0', 'input_id' => 'infuse_'.$i]);
                        }
                        $button .= form_button('defuse', $locale['411'], $inf['folder'], ['class' => 'btn btn-link btn-xs defuse p-l-0', 'input_id' => 'defuse_'.$i]);

                        if (!empty($inf['mlt_adminpanel'][LANGUAGE])) {
                            foreach ($inf['mlt_adminpanel'][LANGUAGE] as $inf_prop) {
                                if (!empty($inf_prop['mlt_adminpanel'][LANGUAGE])) {
                                    $button .= '<a class="btn btn-link btn-xs p-l-0" href="'.INFUSIONS.$inf['folder'].'/'.$inf_prop['panel'].'">'.$inf_prop['title'].'</a>';
                                }
                            }
                        } else if (!empty($inf['adminpanel'])) {
                            foreach ($inf['adminpanel'] as $inf_prop) {
                                if (!empty($inf_prop['panel'])) {
                                    $button .= '<a class="btn btn-link btn-xs p-l-0" href="'.INFUSIONS.$inf['folder'].'/'.$inf_prop['panel'].'">'.$inf_prop['title'].'</a>';
                                }
                            }
                        }
                    }

                    $description = $inf['description']."<br/>\n";
                    $description .= "<span class='m-r-5'>".$locale['420']." ".($inf['version'] ? $inf['version'] : '')."</span>";
                    $description .= ($inf['url'] ? "|<a href='".$inf['url']."' target='_blank'>" : "")."
                    <span class='m-l-5 m-r-5'>".($inf['developer'] ? $inf['developer'] : $locale['410'])."</span>
                     ".($inf['url'] ? "</a>" : "")."
                     ".($inf['email'] ? "|<a class='m-l-5' href='mailto:".$inf['email']."'>".$locale['409']."</a>" : '');

                    $content .= "<tr class='$row_class'><td class='p-l-20' style='min-width:350px;'>\n";
                    $content .= "<div class='pull-left m-r-20 overflow-hide'><img style='width:30px;' alt='".$inf['name']."' src='".$inf['image']."'/></div>\n";
                    $content .= "<div class='clearfix'>\n";
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

        echo "<div class='alert alert-info'>\n";
        echo str_replace(['[LINK]', '[/LINK]'], ["<a class='text-underline' href='https://www.phpfusion.com/infusions/marketplace/'' target='_blank'>", "</a>"], $locale['422']);
        echo "</div>\n";
        closetable();

    }

}

$inf_admin = new Infusions_Admin();
$inf_admin->displayAdmin();

require_once THEMES.'templates/footer.php';
