<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

if (!function_exists("display_main_faq")) {
    /**
     * Page Template
     * @param $info
     */
    function display_main_faq($info) {
        $locale = fusion_get_locale();

        echo "<span id='content'></span>\n";

        opentable($locale['faqs_0000']);
        echo render_breadcrumbs();
        \PHPFusion\Panels::addPanel('faq_header', 'Header Panel', 'aaa', \PHPFusion\Panels::PANEL_AU_CENTER, iGUEST, 1);
		  //<!-- Diplay Categories -->
        echo "<h4>".$locale['faqs_0001']."</h4>\n";
        if (!empty($info['faq_categories'])) {
            echo "<div class='row spacer-sm'>\n";
            foreach ($info['faq_categories'] as $cat_id => $cat_data) {
                echo "<div class='col-xs-12 col-sm-4 col-md-4 col-lg-4 p-0'>\n";
                echo "<a href='".INFUSIONS."faq/faq.php?cat_id=".$cat_id."' title=".$cat_data['faq_cat_name'].">".$cat_data['faq_cat_name']."</a><br/>";
                echo $cat_data['faq_cat_description'];
                echo "</div>\n";
            }
            echo "</div>\n";
        }

        if (!empty($info['faq_items'])) {
            echo "<h4 class='spacer-sm'>".$info['faq_categories'][$info['faq_get']]['faq_cat_name']."</h4>\n";
            echo opencollapse('faq_collapse');
            foreach ($info['faq_items'] as $faq_id => $faq_data) {
                $faq_admin = '';
                $faq_print = "<a href='".BASEDIR."print.php?type=FQ&amp;item_id=".$faq_data['faq_id']."' title=".$locale['print'].">".$locale['print']."</a>\n";
                if (iADMIN && checkrights("FQ")) {
                    $faq_admin = "&middot; <a href='".INFUSIONS."faq/faq_admin.php".fusion_get_aidlink()."&amp;section=faq&amp;ref=faq_form&amp;action=edit&amp;cat_id=".$faq_data['faq_cat_id']."&amp;faq_id=".$faq_data['faq_id']."' title='".$locale['edit']."'>".$locale['edit']."</a>";
                }
                echo opencollapsebody("<h4 class='display-inline-block'>".$faq_data['faq_question']."</h4>", $faq_id, 'faq_collapse', FALSE, '');
                echo "<div class='p-20'>\n";
                echo "<div class='pull-right'><a class='pull-right' href='".FUSION_REQUEST."#content' title=".$locale['faqs_0002']."><i class='fa fa-arrow-up'></i></a>\n</div>\n";
                echo $faq_data['faq_answer'];
                echo "<div class='spacer-xs m-b-0'>\n";
                echo $faq_print;
                echo $faq_admin;
                echo "</div>\n";
                echo "</div>\n";
                echo closecollapsebody();
            }
            echo closecollapse();
        } else {
            echo "<div class='well text-center'>".$locale['faqs_0010']."</div>";
        }
        closetable();
    }
}