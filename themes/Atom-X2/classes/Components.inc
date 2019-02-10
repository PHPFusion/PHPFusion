<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Atom-X2/classes/Components.inc
| Author: PHP-Fusion Inc
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace AtomX2Theme;

use \PHPFusion\Panels;

class Components extends Core {
    public static function HidePanels() {
        Panels::getInstance(TRUE)->hide_panel('LEFT');
        Panels::getInstance(TRUE)->hide_panel('RIGHT');
        Panels::getInstance(TRUE)->hide_panel('U_CENTER');
        Panels::getInstance(TRUE)->hide_panel('L_CENTER');
        Panels::getInstance(TRUE)->hide_panel('AU_CENTER');
        Panels::getInstance(TRUE)->hide_panel('BL_CENTER');
    }

    public function UserInfoBar() {
        if (iMEMBER) {
            $this->locale += fusion_get_locale('', LOCALE.LOCALESET.'user_fields.php');
            echo '<ul class="user-info-bar hidden-xs m-b-0">';
                echo '<li>'.display_avatar($this->userdata, '40px', '', FALSE, 'img-rounded m-t-10 m-l-20 m-r-10').'</li>';

                echo '<li style="margin-left: 50px;"><ul class="info-bar-dropdown list-style-none"><li class="dropdown">';
                    echo '<a class="dropdown-toggle" data-toggle="dropdown" href="#">';
                        echo '<h4>'.$this->userdata['user_name'].' <span class="caret"></span></h4>';
                        echo '<small>'.getuserlevel($this->userdata['user_level']).'</small>';
                    echo '</a>';
                    echo '<ul class="dropdown-menu" style="min-width: 320px;"><li class="p-15">';
                        echo '<strong>'.$this->locale['AX9_004'].timer($this->userdata['user_joined']).'</strong>';
                        echo '<div class="row">';
                            echo '<div class="col-xs-6"><small>';
                                echo '<b>'.$this->locale['u066'].':</b> '.showdate('shortdate', $this->userdata['user_joined']).'<br/>';
                                echo '<b>'.$this->locale['u067'].':</b> '.($this->userdata['user_lastvisit'] ? showdate('shortdate', $this->userdata['user_lastvisit']) : $this->locale['u042']).'<br/>';
                                if (column_exists('users', 'user_location')) echo '<b>'.fusion_get_locale('uf_location', LOCALE.LOCALESET.'user_fields/user_location.php').':</b> '.(!empty($this->userdata['user_location']) ? $this->userdata['user_location'] : $this->locale['user_na']).'<br/>';
                            echo '</small></div>';
                            echo '<div class="col-xs-6"><small>';
                                echo '<b>'.fusion_get_locale('uf_comments-stat', LOCALE.LOCALESET.'user_fields/user_comments-stat.php').':</b> '.number_format(dbcount("(comment_id)", DB_COMMENTS, "comment_name='".$this->userdata['user_id']."'")).'<br/>';
                                if (column_exists('users', 'user_posts')) echo '<b>'.fusion_get_locale('uf_forum-stat', LOCALE.LOCALESET.'user_fields/user_forum-stat.php').':</b> '.number_format($this->userdata['user_posts']).'<br/>';
                                echo '<b>'.$this->locale['u049'].':</b> '.$this->userdata['user_ip'].'<br/>';
                            echo '</small></div>';
                        echo '</div>';
                    echo '</li></ul>';
                echo '</li></ul></li>'; // .info-bar-dropdown

                echo '<li class="user-icon"><a href="'.BASEDIR.'profile.php?lookup='.$this->userdata['user_id'].'"><i class="fa icon fa-user"></i></a></li>';

                $msg_count = dbcount(
                    "('message_id')",
                    DB_MESSAGES, "message_to=:my_id AND message_read=:unread AND message_folder=:inbox",
                    [':inbox' => 0, ':my_id' => $this->userdata['user_id'], ':unread' => 0]
                );

                $messages_count = '';
                if ($msg_count > 0) {
                    $messages_count = '<span class="msg-count label label-danger m-l-5">'.$msg_count.'</span>';
                }

                echo '<li><ul class="info-bar-dropdown list-style-none"><li class="dropdown">';
                    echo '<a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa icon fa-envelope"></i>'.$messages_count.' <span class="caret"></span></a>';
                    echo '<ul class="dropdown-menu" style="width: 280px;padding-top: 0;">';
                        echo '<li class="mailbox">';
                            echo '<strong><a href="'.BASEDIR.'messages.php">'.$this->locale['AX9_005'].'</a></strong>';
                        echo '</li>';
                        echo '<li><ul>';
                            $result = dbquery("SELECT *
                                FROM ".DB_MESSAGES."
                                WHERE message_to='".$this->userdata['user_id']."' AND message_user='".$this->userdata['user_id']."' AND message_read='0' AND message_folder='0'
                                ORDER BY message_datestamp DESC LIMIT 0, 5
                            ");

                            if (dbrows($result) > 0) {
                                $i = 0;
                                while ($maildata = dbarray($result)) {
                                    echo '<li style="padding: 3px 10px; '.($i > 0 ? 'border-top: 1px dashed rgba(0,0,0,0.1);' : '').'"><a href="'.BASEDIR.'messages.php?folder=inbox&amp;msg_read='.$maildata['message_id'].'">'.$maildata['message_subject'].'</a><small class="pull-right">'.timer($maildata['message_datestamp']).'</small></li>';
                                    $i++;
                                }
                            } else {
                                echo '<li class="text-center p-10">'.$this->locale['AX9_006'].'</li>';
                                echo '<li class="text-center p-10" style="border-top: 1px dashed rgba(0,0,0,0.1);"><a href="'.BASEDIR.'messages.php">'.$this->locale['AX9_007'].'</a></li>';
                            }
                        echo '</ul></li>';
                    echo '</ul>';
                echo '</li></ul></li>'; // .info-bar-dropdown

                echo '<li><ul class="info-bar-dropdown list-style-none"><li class="dropdown">';
                    echo '<a class="dropdown-toggle" style="border-right: 1px solid rgba(0,0,0,0.23);" data-toggle="dropdown" href="#"><i class="fa icon fa-users"></i> <span class="caret"></span></a>';
                    echo '<ul class="dropdown-menu" style="width: 250px;">';
                        echo '<li class="dropdown-header"><b>'.$this->locale['u057'].'</b></li>';

                        $user_groups = strpos($this->userdata['user_groups'], ".") == 0 ? substr($this->userdata['user_groups'], 1) : $this->userdata['user_groups'];
                        $user_groups = explode('.', $user_groups);
                        if (!empty($user_groups['0'])) {
                            for ($i = 0; $i < count($user_groups); $i++) {
                                echo '<li><a href="'.BASEDIR.'profile.php?group_id='.$user_groups[$i].'">'.getgroupname($user_groups[$i]).': <small class="text-lighter">'.getgroupname($user_groups[$i], TRUE).'</small></a></li>';
                            }
                        } else {
                            echo '<li class="text-center">'.$this->locale['AX9_008'].'</li>';
                        }
                    echo '</ul>';
                echo '</li></ul></li>'; // .info-bar-dropdown
            echo '</ul>';
        }
    }
}
