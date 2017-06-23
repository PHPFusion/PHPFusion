<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Material/classes/Components.inc
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
namespace Material;

/**
 * Class Components
 * @package Material
 */
class Components {
    private static $messages = [];

    public static function ThemeSettings() {
        echo '<aside id="theme-settings" class="hidden-xs">';
            echo '<a href="#" title="'.self::SetLocale('002').'" data-action="theme-settings" class="btn-theme-settings cogs-animation">';
                echo '<i class="fa fa-cog fa-spin"></i>';
                echo '<i class="fa fa-cog fa-spin fa-spin-reverse"></i>';
                echo '<i class="fa fa-cog fa-spin"></i>';
            echo '</a>';

            echo '<div class="settings-box">';
                echo '<h4>'.self::SetLocale('002').'</h4>';

                echo '<ul class="settings-menu">';
                    $theme_settings = [
                        ['name' => 'hide-sidebar',      'title' => self::SetLocale('003')],
                        ['name' => 'sidebar-sm',        'title' => self::SetLocale('004')],
                        ['name' => 'fixedmenu',         'title' => self::SetLocale('005'), 'toggle' => 'on'],
                        ['name' => 'fixedsidebar',      'title' => self::SetLocale('006'), 'toggle' => 'on'],
                        ['name' => 'fixedfootererrors', 'title' => self::SetLocale('007'), 'toggle' => 'on'],
                        ['name' => 'fullscreen',        'title' => self::SetLocale('008')]
                    ];

                    foreach ($theme_settings as $setting) {
                        echo '<li><a href="#" data-action="'.$setting['name'].'" id="'.$setting['name'].'">'.$setting['title'].'<div class="btn-toggle pull-right '.(!empty($setting['toggle']) ? $setting['toggle'] : '').'"></div></a></li>';
                    }
                echo '</ul>';
            echo '</div>';
        echo '</aside>';
    }

    public static function MessagesBox() {
        $messages = self::GetMessages();

        echo '<aside class="messages-box hidden-xs">';
            echo '<div class="button-group">';
                echo '<a href="#" id="messages-box-close">'.fusion_get_locale('close').'</a>';
                echo '<a href="'.BASEDIR.'messages.php?msg_send=new" class="new-message">'.self::SetLocale('011').'</a>';
            echo '</div>';
            echo '<h3 class="title">'.self::SetLocale('009').'</h3>';

            if (!empty($messages)) {
                echo '<ul>';
                    foreach ($messages as $message) {
                        echo '<li>';
                            echo '<div class="message-block">';
                                echo display_avatar($message['user'], '40px', '', FALSE, 'avatar m-r-5');
                                echo '<div class="block">';
                                    echo '<span class="title">'.$message['user']['user_name'].' <small>'.$message['datestamp'].'</small></span>';
                                    echo '<br /><small>'.trim_text($message['title'], 20).'</small>';
                                    echo '<a href="'.BASEDIR.'messages.php?folder=inbox&amp;msg_read='.$message['link'].'" class="read-message">'.self::SetLocale('010').'</a>';
                                echo '</div>';
                            echo '</div>';
                        echo '</li>';
                    }
                echo '</ul>';
            } else {
                echo '<div class="no-messages">';
                    echo '<i class="fa fa-envelope icon"></i><br />';
                    echo self::SetLocale('012');
                echo '</div>';
            }
        echo '</aside>';
    }

    public static function SetLocale($lc = NULL) {
        $locale = [];

        if (file_exists(MATERIAL."locale/".LANGUAGE.".php")) {
            include MATERIAL."locale/".LANGUAGE.".php";
        } else {
            include MATERIAL."locale/English.php";
        }

        return $locale['material_'.$lc];
    }

    public static function Messages() {
        $userdata = fusion_get_userdata();

        $result = dbquery("
            SELECT message_id, message_subject, message_from user_id, u.user_name, u.user_status, u.user_avatar, message_datestamp
            FROM ".DB_MESSAGES."
            INNER JOIN ".DB_USERS." u ON u.user_id=message_from
            WHERE message_to='".$userdata['user_id']."' AND message_user='".$userdata['user_id']."' AND message_read='0' AND message_folder='0'
            GROUP BY message_id
        ");

        if (dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_user='".$userdata['user_id']."' AND message_read='0' AND message_folder='0'")) {
            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    self::$messages[] = [
                        'link'      => $data['message_id'],
                        'title'     => $data['message_subject'],
                        'user'      => [
                            'user_id'     => $data['user_id'],
                            'user_name'   => $data['user_name'],
                            'user_status' => $data['user_status'],
                            'user_avatar' => $data['user_avatar']
                        ],
                        'datestamp' => timer($data['message_datestamp'])
                    ];
                }
            }
        }

        return self::$messages;
    }

    public static function GetMessages() {
        return self::$messages;
    }

    public static function IsMobile() {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER['HTTP_USER_AGENT']);
    }


    public static function OpenSide($title = FALSE, $class = NULL) {
        echo '<div class="panel panel-default openside '.$class.'">';
        echo $title ? '<div class="panel-heading">'.$title.'</div>' : '';
        echo '<div class="panel-body">';
    }

    public static function CloseSide($title = FALSE) {
        echo '</div>';
        echo $title ? '<div class="panel-footer">'.$title.'</div>' : '';
        echo '</div>';
    }

    public static function OpenTable($title = FALSE, $class = NULL) {
        echo '<div class="panel opentable '.$class.'">';
        echo $title ? '<header><h3>'.$title.'</h3></header>' : '';
        echo '<div class="panel-body">';
    }

    public static function CloseTable() {
        echo '</div>';
        echo '</div>';
    }
}
