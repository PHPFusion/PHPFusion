<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: shoutbox.tpl.php
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

if (!function_exists('render_shoutbox')) {
    function render_shoutbox($info) {
        $locale = fusion_get_locale();

        openside($info['title']);
            echo $info['form'];

            if (!empty($info['items'])) {
                echo '<div class="shoutbox-items m-t-10">';
                foreach ($info['items'] as $item) {
                    echo '<div class="shoutbox-item clearfix m-b-20" id="shout'.$item['shout_id'].'">';
                        echo '<div class="shoutboxavatar pull-left m-r-5 m-t-5">';
                            echo display_avatar($item, '20px', '', !empty($item['user_id']), 'img-rounded');

                            if (!empty($item['user_lastvisit'])) {
                                echo '<span style="font-size:7px;position:absolute;margin-left:-5px;margin-top:-3px;">';
                                    echo '<i class="fas fa-circle text-'.($item['user_lastvisit'] >= time() - 300 ? 'success' : 'danger').'"></i>';
                                echo '</span>';
                            }
                        echo '</div>';

                        if (!user_blacklisted($item['user_id'], TRUE)) {
                            echo '<div class="pull-right btn-group btn-group-xs">';
                                if (!empty($item['reply_link'])) {
                                    echo '<a class="btn btn-default" href="'.$item['reply_link'].'" title="'.$item['reply_title'].'"><i class="fas fa-reply"></i></a>';
                                }

                                if (!empty($item['edit_link']) && !empty($item['delete_link'])) {
                                    echo '<a class="btn btn-default" href="'.$item['edit_link'].'" title="'.$item['edit_title'].'"><i class="fas fa-edit"></i></a>';
                                    echo '<a class="btn btn-default" href="'.$item['delete_link'].'" title="'.$item['delete_title'].'"><i class="fas fa-trash"></i></a>';
                                }
                            echo '</div>';
                        }

                        echo '<div class="clearfix">';
                            echo '<strong class="display-block">'.(!empty($item['user_id']) ? $item['profile_link'] : $item['shout_name']).'</strong>';
                            echo timer($item['shout_datestamp']);
                            echo '<span class="m-l-5">#'.$item['shout_id'].'</span>';
                        echo '</div>';

                        if (user_blacklisted($item['user_id'], TRUE)) { // this is here on purpose, theme devs can customize this
                            echo '<div class="shoutbox-message blocked">'.$locale['SB_blocked_user'].'</div>';
                        } else {
                            echo '<div class="shoutbox-message" style="hyphens:auto;overflow-wrap:break-word;">'.$item['message'].'</div>';
                        }
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<div class="text-center m-t-10">'.$locale['SB_no_msgs'].'</div>';
            }

            echo !empty($info['pagenav']) ? '<div class="text-center m-t-10">'.$info['pagenav'].'</div>' : '';

            if (empty($info['is_archive']) && !empty($info['archive'])) {
                echo '<div class="text-center m-t-20"><a class="btn btn-default btn-xs" href="'.$info['archive']['link'].'">'.$info['archive']['title'].'</a></div>';
            }
        closeside();
    }
}
