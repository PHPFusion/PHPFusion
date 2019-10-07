<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2013 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: adminDashboard.php
| Author: Frederick Chan (deviance)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace Genesis\Viewer;

use Genesis\Model\resource;

/**
 * Class adminDashboard
 *
 * @package Genesis\Viewer
 */
class adminDashboard extends resource {

    public static function do_dashboard() {
        global $members, $global_submissions,  $infusions_count, $global_infusions, $submit_data, $upgrade_info;
        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();
        $userdata = fusion_get_userdata();
        $settings = fusion_get_settings();

        opentable($locale['250']);
        new \AdminDashboard();

        $panels = array(
            'registered'   => array('link' => '', 'title' => 251),
            'cancelled'    => array('link' => 'status=5', 'title' => 263),
            'unactivated'  => array('link' => 'status=2', 'title' => 252),
            'security_ban' => array('link' => 'status=4', 'title' => 253)
        );
        echo "<div class='".grid_row()."'>\n";
        echo "<div class='".grid_column_size(100,100,50,33)." responsive-admin-column'>\n";
        // lets do an internal analytics
        // members registered
        // members online
        // members
        echo "<div class='list-group'>\n";
        echo "<div class='list-group-item'><h4 class='m-0'>Users</h4></div>";
        echo "<!--Start Members-->\n";
        echo "<div class='list-group-item'>\n";
        echo "<div class='row'>\n";
        foreach ($panels as $panel => $block) {
            $link_start = ''; $link_end = '';
            if (checkrights('M')) {
                $block['link'] = empty($block['link']) ? $block['link'] : '&amp;'.$block['link'];
                $link_start = "<a class='text-sm' href='".ADMIN."members.php".$aidlink.$block['link']."'>";
                $link_end = "</a>\n";
            }
            echo "<div class='col-xs-12 col-sm-3'>\n";
            echo "<h2 class='m-0 text-light text-info'>".number_format($members[$panel])."</h2>\n";
            echo "<span class='m-t-10'>".$link_start.$locale[$block['title']].$link_end."</span>\n";
            echo "</div>\n";
        }
        echo "</div>\n";
        echo "</div>\n";
        echo "<!--End Members-->\n";
        echo "<div class='list-group-item'>\n";
        echo "<h4 class='m-0 display-inline-block'>".$locale['283']."</h4> <span class='pull-right badge'>".number_format((int)$infusions_count)."</span>";
        echo "</div>\n";
        echo "<div class='list-group-item'>\n";
        if ($infusions_count > 0) {
            echo "<div class='comment_content'>\n";
            if (!empty($global_infusions)) {
                foreach ($global_infusions as $inf_id => $inf_data) {
                    echo "<span class='badge m-b-10 m-r-5'>".$inf_data['inf_title']."</span>\n";
                }
            }
            echo "</div>\n";
            echo checkrights("I") ? "<div class='text-right'>\n<a href='".ADMIN."infusions.php".$aidlink."'>".$locale['285']."</a><i class='fas fa-angle-right text-lighter m-l-15'></i></div>\n" : '';
        } else {
            echo "<div class='text-center'>".$locale['284']."</div>\n";
        }
        echo "</div>\n";
        echo "</div>\n";
        echo "</div>\n<div class='".grid_column_size(100,100,50,33)." responsive-admin-column'>\n";
        echo "<div class='list-group'>\n<div class='list-group-item'>\n";
        echo "<h4 class='display-inline-block m-0'>".$locale['279']."</h4>\n";
        echo "<span class='pull-right badge'>".number_format($global_submissions['rows'])."</span>\n";
        echo "</div>\n";
        echo "<div class='list-group-item'>\n";
        if (count($global_submissions['data']) > 0) {
            foreach ($global_submissions['data'] as $i => $submit_date) {
                $review_link = sprintf($submit_data[$submit_date['submit_type']]['admin_link'], $submit_date['submit_id']);

                echo "<!--Start Submissions Item-->\n";
                echo "<div data-id='$i' class='submission_content clearfix p-t-10 p-b-10' ".($i > 0 ? "style='border-top:1px solid #ddd;'" : '')." >\n";
                echo "<div class='pull-left display-inline-block' style='margin-top:5px; margin-bottom:10px;'>".display_avatar($submit_date, "25px", "", FALSE, "img-rounded m-r-5")."</div>\n";
                echo "<strong>".profile_link($submit_date['user_id'], $submit_date['user_name'], $submit_date['user_status'])." </strong>\n";
                echo "<span class='text-lighter'>".$locale['273b']." <strong>".$submit_data[$submit_date['submit_type']]['submit_locale']."</strong></span><br/>\n";
                echo timer($submit_date['submit_datestamp'])."<br/>\n";
                if (!empty($review_link)) {
                    echo "<a class='btn btn-xs btn-default m-t-5' title='".$locale['286']."' href='".$review_link."'>".$locale['286']."</a>\n";
                }
                echo "</div>\n";
                echo "<!--End Submissions Item-->\n";
            }

            if (isset($global_submissions['submissions_nav'])) {
                echo "<div class='clearfix'>\n";
                echo "<span class='pull-right text-smaller'>".$global_submissions['submissions_nav']."</span>";
                echo "</div>\n";
            }
        } else {
            echo "<div class='text-center'>".$global_submissions['nodata']."</div>\n";
        }
        echo "</div>\n";
        echo "</div>\n";
        echo "</div>\n<div class='".grid_column_size(100,100,50,33)." responsive-admin-column'>\n";

        echo "</div>\n</div>\n";
        closetable();
    }

    public static function do_admin_icons() {
        global $admin_icons, $admin_images;

        $aidlink = self::get_aidlink();
        $locale = parent::get_locale();
        //add_to_head('<link href="'.THEME.'templates/css/autogrid.css" rel="stylesheet" />');
        opentable($locale['admin_apps']);
        echo "<div class='row'>\n";
        if (count($admin_icons['data']) > 0) {
            foreach ($admin_icons['data'] as $i => $data) {
                echo "<div class='display-table col-xs-6 col-sm-3 col-md-2' style='height:140px;'>\n";
                if ($admin_images) {
                    echo "<div class='panel-body align-middle text-center' style='width:100%;'>\n";
                    echo "<a href='".$data['admin_link'].$aidlink."'><img style='max-width:48px;' src='".get_image("ac_".$data['admin_rights'])."' alt='".$data['admin_title']."'/>\n</a>\n";
                    echo "<div class='overflow-hide'>\n";
                    echo "<a class='icon_title' href='".$data['admin_link'].$aidlink."'>".$data['admin_title']."</a>\n";
                    echo "</div>\n";
                    echo "</div>\n";
                } else {
                    echo "<span class='small'>".THEME_BULLET." <a href='".$data['admin_link'].$aidlink."'>".$data['admin_title']."</a></span>";
                }
                echo "</div>\n";
            }
        }
        echo "</div>\n";
        closetable();
    }

}
