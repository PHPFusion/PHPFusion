<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: faq.tpl.php
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
defined('IN_FUSION') || exit;

if (!function_exists('display_main_faq')) {
    function display_main_faq($info) {
        opentable($info['faq_tablename']);
        echo render_breadcrumbs();
        echo '<div class="faq-index">';

        if (!empty($info['faq_categories'])) {
            foreach ($info['faq_categories'] as $cat_data) {
                echo '<div id="'.$cat_data['faq_cat_id'].'" class="m-t-10 faq-index-item">
                    <div class="spacer-xs">
                        <a href="'.$cat_data['faq_cat_link'].'">'.$cat_data['faq_cat_name'].'</a>
                        <div>'.$cat_data['faq_cat_description'].'</div>
                    </div>
                </div>';
            }
        } else {
            echo '<div class="well text-center m-t-15">'.fusion_get_locale('faq_0112a').'</div>';
        }

        echo '</div>';
        closetable();
    }
}

if (!function_exists('render_faq_item')) {
    function render_faq_item($info) {
        opentable($info['faq_tablename']);
        echo render_breadcrumbs();
        echo '<div class="faq-item">';
        echo '<h2 class="spacer-sm">'.$info['faq_get_name'].'</h2>';

        if (!empty($info['faq_items'])) {
            add_to_jquery('$(".top").on("click",function(e){e.preventDefault();$("html, body").animate({scrollTop:0},100);});');
            echo opencollapse('faq');
            foreach ($info['faq_items'] as $faq_data) {
                echo opencollapsebody($faq_data['faq_question'], 'faq'.$faq_data['faq_id'], 'faq');
                echo $faq_data['faq_answer'];
                echo '<div>';
                    echo "<a href='".$faq_data['print']['link']."' target='_blank' title='".$faq_data['print']['title']."'><i class='fa fa-fw fa-print'></i></a>";
                    echo !empty($faq_data['edit']['link']) ? "<a href='".$faq_data['edit']['link']."' title='".$faq_data['edit']['title']."'><i class='fa fa-fw fa-pencil m-l-10'></i></a>" : '';
                    echo !empty($faq_data['delete']['link']) ? "<a href='".$faq_data['delete']['link']."' title='".$faq_data['delete']['title']."'><i class='fa fa-fw fa-trash m-l-10'></i></a>" : '';
                echo '</div>';
                echo closecollapsebody();
            }
            echo closecollapse();
        } else {
            echo '<div class="well text-center m-t-15">'.fusion_get_locale('faq_0112').'</div>';
        }

        echo '</div>';
        closetable();
    }
}

if (!function_exists('display_faq_submissions')) {
    function display_faq_submissions($info) {
        opentable($info['faq_tablename']);
        if (!empty($info['item'])) {
            echo '<div class="well spacer-xs">'.$info['item']['guidelines'].'</div>';
            echo $info['item']['openform'];
            echo $info['item']['faq_question'];
            echo $info['item']['faq_answer'];
            echo $info['item']['faq_cat_id'];
            echo $info['item']['faq_language'];
            echo $info['item']['faq_submit'];
            echo closeform();
        }

        if (!empty($info['confirm'])) {
            echo '<div class="well text-center">
                <p class="strong">'.$info['confirm']['title'].'</p>
                <p class="strong">'.$info['confirm']['submit_link'].'</p>
                <p class="strong">'.$info['confirm']['index_link'].'</p>
            </div>';
        }

        if (!empty($info['no_submissions'])) {
            echo '<div class="well text-center">'.$info['no_submissions'].'</div>';
        }

        closetable();
    }
}
