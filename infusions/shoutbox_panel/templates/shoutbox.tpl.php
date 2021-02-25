<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: shoutbox.tpl.php
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
defined('IN_FUSION') || exit;

if (!function_exists('render_shoutbox')) {
    function render_shoutbox($info) {
        $locale = fusion_get_locale();

        openside($locale['SB_title']);
            echo $info['form'];

            if (!empty($info['items'])) {
                echo '<div class="shoutbox-items m-t-10">';
                foreach ($info['items'] as $item) {
                    echo '<div class="shoutbox-item clearfix m-b-10">';
                        echo '<div class="shoutboxavatar pull-left m-r-5 m-t-5">';
                            echo display_avatar($item, '30px', '', TRUE, 'img-rounded');
                        echo '</div>';

                        if (!empty($item['edit_link']) && !empty($item['delete_link'])) {
                            echo '<div class="pull-right btn-group btn-group-xs">';
                                echo '<a class="btn btn-default" href="'.$item['edit_link'].'" title="'.$item['edit_title'].'"><i class="fa fa-edit"></i></a>';
                                echo '<a class="btn btn-default" href="'.$item['delete_link'].'" title="'.$item['delete_title'].'"><i class="fa fa-trash"></i></a>';
                            echo '</div>';
                        }

                        $online = !empty($item['user_lastvisit']) ? '<span style="color: #5CB85C; font-size: 10px;"><i class="m-l-5 m-r-5 fa fa-'.($item['user_lastvisit'] >= time() - 300 ? 'circle' : 'circle-thin').'"></i></span>' : '';

                        echo '<div class="clearfix">';
                            echo '<strong class="display-block">'.(!empty($item['user_name']) ? $item['profile_link'].$online : '<span class="m-r-5">'.$item['shout_name'].'</span>').'</strong>';
                            echo timer($item['shout_datestamp']);
                        echo '</div>';

                        echo '<div class="shoutbox-message word-break">'.$item['message'].'</div>';
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<div class="text-center m-t-10">'.$locale['SB_no_msgs'].'</div>';
            }

            if (!empty($info['archive'])) {
                echo '<div class="text-center m-t-20"><a class="btn btn-default btn-xs" href="'.$info['archive']['link'].'">'.$info['archive']['title'].'</a></div>';
            }
        closeside();
    }
}
