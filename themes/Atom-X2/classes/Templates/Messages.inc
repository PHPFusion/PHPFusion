<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Atom-X2/classes/Templates/Messages.inc
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
namespace AtomX2Theme\Templates;

use AtomX2Theme\Core;
use AtomX2Theme\Components;

class Messages extends Core {
    public static function display_inbox($info) {
        $locale = fusion_get_locale();
        $locale += self::GetInstance()->SetLocale();

        Components::HidePanels();

        $menu = [
            'inbox'   => ['link' => BASEDIR.'messages.php?folder=inbox', 'title' => $locale['402'], 'icon' => 'inbox'],
            'outbox'  => ['link' => BASEDIR.'messages.php?folder=outbox', 'title' => $locale['AX9_045'], 'icon' => 'send-o'],
            'archive' => ['link' => BASEDIR.'messages.php?folder=archive', 'title' => $locale['AX9_046'], 'icon' => 'archive'],
            'new'     => ['link' => BASEDIR.'messages.php?msg_send=new', 'title' => $locale['401'], 'icon' => 'envelope'],
            'options' => ['link' => BASEDIR.'messages.php?folder=options', 'title' => $locale['425'], 'icon' => 'cog']
        ];

        ob_start();
        echo '<ul>';
            foreach ($menu as $key => $folder) {
                $active = (isset($_GET['folder']) && $_GET['folder'] == $key) || (isset($_GET['msg_send']) && $_GET['msg_send'] == $key) ? ' class="active"' : '';
                echo '<li'.$active.'><a href="'.$folder['link'].'"><span class="visible-xs"><i class="fa fa-'.$folder['icon'].'"></i></span><span class="hidden-xs">'.$folder['title'].'</span></a></li>';
            }
        echo '</ul>';
        $header = ob_get_contents();
        ob_end_clean();

        self::SetParam('section_header', $header);
        self::SetParam('section_header_class', 'messages');

        echo '<div class="row">';
            if (!isset($_GET['msg_send'])) {
                echo '<div class="col-xs-12 col-sm-3">';
                    echo '<a class="btn btn-primary btn-block" href="'.$info['button']['new']['link'].'"><i class="fa fa-plus"></i> '.$locale['401'].'</a>';

                    $i = 0;
                    echo '<ul class="msg-nav list-style-none m-t-20">';
                        foreach ($info['folders'] as $key => $folder) {
                            $active = isset($_GET['folder']) && ($_GET['folder'] == $key) ? ' class="active text-dark"' : '';

                            echo '<li'.$active.'><a href="'.$folder['link'].'">';
                                echo $folder['title'];

                                if ($i < count($info['folders']) - 1) {
                                    $total_key = $key.'_total';
                                    echo '<span class="pull-right">('.$info[$total_key].')</span>';
                                }
                            echo '</a></li>';
                            $i++;
                        }
                    echo '</ul>';
                echo '</div>';
            }

            $grid = !isset($_GET['msg_send']) ? 9 : 12;
            echo '<div class="col-xs-12 col-sm-'.$grid.'">';

                if (!isset($_GET['msg_send']) && (!empty($info['actions_form']) || isset($_GET['msg_read']))) {
                    echo '<div class="m-b-20">';
                        if (isset($_GET['msg_read'])) {
                            echo '<a class="btn btn-default m-r-10" href="'.$info['button']['back']['link'].'" title="'.$info['button']['back']['title'].'"><i class="fa fa-long-arrow-left"></i></a>';
                        }

                        echo !empty($info['actions_form']) ? '<div class="display-inline-block">'.$info['actions_form'].'</div>' : '';
                        echo !empty($info['pagenav']) ? '<div class="display-inline-block pull-right">'.$info['pagenav'].'</div>' : '';
                    echo '</div>';
                }

                switch ($_GET['folder']) {
                    case 'options':
                        echo $info['options_form'];
                        break;
                    case 'inbox':
                        self::Inbox($info);
                        break;
                    default:
                        self::Inbox($info);
                }

            echo '</div>';
        echo '</div>';
    }

    private static function Inbox($info) {
        $locale = fusion_get_locale();

        if (isset($_GET['msg_read']) && isset($info['items'][$_GET['msg_read']])) {
            $data = $info['items'][$_GET['msg_read']];

            echo '<h4>'.$data['message']['message_header'].'</h4>';
            echo '<div class="m-b-20">';
                echo display_avatar($data, '40px', '', FALSE, 'img-rounded pull-left m-t-5 m-r-10');
                echo profile_link($data['user_id'], $data['user_name'], $data['user_status'],'display-block');
                echo '<span>'.showdate(!empty($locale['date_day']) ? $locale['date_day'] : 'shortdate', $data['message_datestamp']).', '.timer($data['message_datestamp']).'</span>';
            echo '</div>';

            echo nl2br(parse_textarea($data['message']['message_text'], TRUE, TRUE, FALSE));
            echo '<hr/>';
            echo $info['reply_form'];
        } else if (isset($_GET['msg_send'])) {
            echo $info['reply_form'];
        } else {
            if (!empty($info['items'])) {
                $unread = [];
                $read = [];

                foreach ($info['items'] as $message_id => $messageData) {
                    if ($messageData['message_read']) {
                        $read[$message_id] = $messageData;
                    } else {
                        $unread[$message_id] = $messageData;
                    }
                }

                echo '<h4><a data-target="#unread_inbox" class="pointer text-dark" data-toggle="collapse">'.$locale['446'].' <span class="caret"></span></a></h4>';
                echo '<div id="unread_inbox" class="collapse in">';
                    if (!empty($unread)) {
                        echo '<div class="table-responsive"><table id="unread_tbl" class="table table-hover table-striped">';
                            foreach ($unread as $id => $messageData) {
                                echo '<tr>';
                                    echo '<td class="col-xs-1">'.form_checkbox('pmID', '', '', [
                                        'input_id' => 'pmID-'.$id,
                                        'value'    => $id,
                                        'class'    => 'm-b-0'
                                    ]).'</td>';
                                    echo '<td class="col-xs-2"><b>'.$messageData['contact_user']['user_name'].'</b></td>';
                                    echo '<td class="col-xs-7"><a href="'.$messageData['message']['link'].'"><b>'.$messageData['message']['name'].'</b></a></td>';
                                    echo '<td class="col-xs-2">'.showdate(!empty($locale['date_day']) ? $locale['date_day'] : 'shortdate', $messageData['message_datestamp']).'</td>';
                                echo '</tr>';
                            }
                        echo '</table></div>';
                    } else {
                        echo '<div class="well text-center">'.$locale['471'].'</div>';
                    }
                echo '</div>';

                echo '<h4><a data-target="#read_inbox" class="pointer text-dark" data-toggle="collapse">'.$locale['447'].' <span class="caret"></span></a></h4>';
                echo '<div id="read_inbox" class="collapse in">';
                    if (!empty($read)) {
                        echo '<div class="table-responsive"><table id="read_tbl" class="table table-hover table-striped">';
                            foreach ($read as $id => $messageData) {
                                echo '<tr>';
                                    echo '<td class="col-xs-1">'.form_checkbox('pmID', '', '', [
                                        'input_id' => 'pmID-'.$id,
                                        'value'    => $id,
                                        'class'    => 'm-b-0'
                                    ]).'</td>';
                                    echo '<td class="col-xs-2">'.$messageData['contact_user']['user_name'].'</td>';
                                    echo '<td class="col-xs-7"><a href="'.$messageData['message']['link'].'">'.$messageData['message']['name'].'</a></td>';
                                    echo '<td class="col-xs-2">'.showdate(!empty($locale['date_day']) ? $locale['date_day'] : 'shortdate', $messageData['message_datestamp']).'</td>';
                                echo '</tr>';
                            }
                        echo '</table></div>';
                    }
                echo '</div>';
            } else {
                echo '<div class="well text-center">'.$info['no_item'].'</div>';
            }
        }
    }
}
