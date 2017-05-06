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

        opentable($locale['faq_0000']);
        echo render_breadcrumbs();

        echo "<h3 class='m-b-20'>".$locale['faq_0001']."</h3>\n";
        if (!empty($info['faq_categories'])) {
            foreach ($info['faq_categories'] as $cat_id => $cat_data) {
                echo "<div class='m-t-10' id='cat-".$cat_id."'>\n";
                echo "<a class='text-bold' href='".INFUSIONS."faq/faq.php?cat_id=".$cat_id."' title=".$cat_data['faq_cat_name'].">".$cat_data['faq_cat_name']."</a><br/>";
                echo $cat_data['faq_cat_description'];
                echo "</div>\n";
            }
        }
        closetable();
    }
}

if (!function_exists("render_faq_item")) {
    function render_faq_item($info) {
        $locale = fusion_get_locale();

        opentable($locale['faq_0000']);
        echo render_breadcrumbs();

        if (!empty($info['faq_items'])) {
            echo "<h4 class='spacer-sm'>".$info['faq_categories'][$info['faq_get']]['faq_cat_name']."</h4>\n";
            add_to_jquery('$(".top").on("click",function(e){e.preventDefault();$("html, body").animate({scrollTop:0},100);});');

            foreach ($info['faq_items'] as $faq_id => $faq_data) {
                echo "<div id='faq-item-".$faq_id."'>";
                $faq_admin = '';
                $faq_print = "<a target='_blank' href='".BASEDIR."print.php?type=FQ&amp;item_id=".$faq_data['faq_id']."' title=".$locale['print'].">".$locale['print']."</a>\n";
                if (iADMIN && checkrights("FQ")) {
                    $faq_admin = "&middot; <a href='".INFUSIONS."faq/faq_admin.php".fusion_get_aidlink()."&amp;section=faq&amp;ref=faq_form&amp;action=edit&amp;cat_id=".$faq_data['faq_cat_id']."&amp;faq_id=".$faq_data['faq_id']."' title='".$locale['edit']."'>".$locale['edit']."</a>";
                    $faq_admin .= " &middot; <a href='".INFUSIONS."faq/faq_admin.php".fusion_get_aidlink()."&amp;section=faq&amp;ref=faq_form&amp;action=delete&amp;faq_id=".$faq_data['faq_id']."' title='".$locale['delete']."'>".$locale['delete']."</a>";
                }

                echo "<a data-toggle='collapse' href='#".$faq_id."' aria-expanded='false' aria-controls='".$faq_id."'>";
                    echo "<h4 class='display-inline-block'>".$faq_data['faq_question']."</h4>";
                echo "<a class='pull-right top' href='#' title=".$locale['faq_0002']."><i class='fa fa-arrow-up'></i></a>\n";
                echo "</a>";

                echo "<div class='collapse' id='".$faq_id."'>";
                    echo $faq_data['faq_answer'];

                    echo "<div>";
                    echo $faq_print;
                    echo $faq_admin;
                    echo "</div>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<div class='well text-center'>".$locale['faq_0112']."</div>";
        }
        closetable();
    }
}
