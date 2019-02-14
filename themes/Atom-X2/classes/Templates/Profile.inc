<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Atom-X2/classes/Templates/Profile.inc
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
use \PHPFusion\Locale;

class Profile extends Core {
    public static function DisplayProfile($info) {
        global $userFields;

        $locale = fusion_get_locale();
        $locale += self::GetInstance()->SetLocale();
        $locale += fusion_get_locale('', LOCALE.LOCALESET.'user_fields.php');
        $userdata = fusion_get_userdata();
        $user_data = $userFields->getUserData();
        $settings = fusion_get_settings();

        Components::HidePanels();

        ob_start();
        echo '<div style="padding-bottom: 30px;"><div class="row">';
            echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">';
                echo '<h3 class="m-t-5 strong"><span>'.$locale['AX9_011'].'</span>'.$user_data['user_name'].'</h3>';

                echo '<div class="row">';
                    echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">';
                        echo '<span>'.$locale['u066'].': '.showdate('longdate', $user_data['user_joined']).'</span>';
                        echo '<br/><span>'.$locale['u067'].': '.($user_data['user_lastvisit'] ? timer($user_data['user_lastvisit']) : $locale['u042']).'</span>';
                    echo '</div>';
                    echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">';
                        echo '<span>'.(iADMIN || $user_data['user_hide_email'] == 1) ? '<a href="mailto:'.$user_data['user_email'].'">'.$user_data['user_email'].'</a>' : $locale['AX9_012'].'</span>';
                    echo '</div>';
                echo '</div>';
            echo '</div>';

            echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">';
                if (iMEMBER && $userdata['user_id'] != $user_data['user_id']) {
                    echo '<div class="btn-group pull-right">';
                        echo '<a class="btn btn-primary" href="'.BASEDIR.'messages.php?msg_send='.$user_data['user_id'].'">'.$locale['u043'].'</a>';

                    if (checkrights('M') && fusion_get_userdata('user_level') <= USER_LEVEL_ADMIN && $user_data['user_id'] != 1) {
                        echo '<a class="btn btn-primary" href="'.ADMIN.'members.php'.fusion_get_aidlink().'&amp;ref=log&amp;lookup='.$user_data['user_id'].'">'.$locale['u054'].'</a>';
                    }
                    echo '</div>';
                }

                if (!empty($info['user_admin'])) {
                    $link = $info['user_admin'];
                    echo '<div class="btn-group pull-right m-t-5">';
                        echo '<a class="btn btn-default" href="'.$link['user_edit_link'].'">'.$link['user_edit_title'].'</a>';
                        echo '<a class="btn btn-default" href="'.$link['user_ban_link'].'">'.$link['user_ban_title'].'</a>';
                        echo '<a class="btn btn-default" href="'.$link['user_suspend_link'].'">'.$link['user_suspend_title'].'</a>';
                        echo '<a class="btn btn-danger" href="'.$link['user_delete_link'].'">'.$link['user_delete_title'].'</a>';
                    echo '</div>';
                }
            echo '</div>';
        echo '</div></div>';
        $header = ob_get_contents();
        ob_end_clean();

        self::SetParam('section_header', $header);

        $custom_avatar = $settings['siteurl'].'themes/Atom-X2/images/noavatar50.png';
        $forum = function_exists('infusion_exists') ? infusion_exists('forum') : db_exists(DB_PREFIX.'forums');

        echo '<div class="row">';
            echo '<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">';

            if (!empty($info['section'])) {
                $tab_title = [];

                foreach ($info['section'] as $page_section) {
                    $tab_title['title'][$page_section['id']] = $page_section['name'];
                    $tab_title['id'][$page_section['id']] = $page_section['id'];
                    $tab_title['icon'][$page_section['id']] = $page_section['icon'];
                }

                $tab_active = tab_active($tab_title, $_GET['section']);

                echo '<div class="profile-main">';
                echo opentab($tab_title, $_GET['section'], 'profile_tab', TRUE, '', 'section', ['section']);
                echo opentabbody($tab_title['title'][$_GET['section']], $tab_title['id'][$_GET['section']], $tab_active, TRUE);

                if ($tab_title['id'][$_GET['section']] == $tab_title['id'][1]) {
                    echo '<div class="panel panel-default">';
                        echo '<div class="panel-heading" style="background: #222;color: #fff;">'.strtoupper($locale['AX9_013']).'</div>';
                        echo '<div class="panel-body">';

                        echo '<div class="row">';
                            echo '<div class="col-xs-1 col-sm-1 col-md-1 col-lg-1">';
                                echo display_avatar($user_data, '50px', '', TRUE, 'img-rounded', $custom_avatar);
                            echo '</div>';

                            echo '<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">';
                                if (!empty($info['core_field'])) {
                                    foreach ($info['core_field'] as $field_id => $data) {
                                        switch ($field_id) {
                                            case 'profile_user_group':
                                                if (!empty($data['value']) && is_array($data['value'])) {
                                                    foreach ($data['value'] as $groups) {
                                                        $user_groups[] = $groups;
                                                    }
                                                }
                                                break;
                                            case 'profile_user_joined':
                                                $user_level['user_joined'] = $data['value'];
                                                break;
                                            case 'profile_user_visit':
                                                $user_level['user_lastvisit'] = $data['value'];
                                                break;
                                            default:
                                                if (!empty($data['value'])) {
                                                    echo '<div id="'.$field_id.'" class="row">';
                                                        echo '<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4"><strong>'.$data['title'].'</strong></div>';
                                                        echo '<div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">'.$data['value'].'</div>';
                                                    echo '</div>';
                                                }
                                        }
                                    }
                                }

                                echo '<div class="btn-group btn-group-sm m-t-10">';
                                    if (isset($_GET['lookup']) && $userdata['user_id'] == $_GET['lookup']) {
                                        echo '<a class="btn btn-primary" href="'.BASEDIR.'edit_profile.php">'.$locale['AX9_014'].'</a>';
                                    }

                                    echo '<button type="button" id="show-uf" class="btn btn-primary">'.$locale['AX9_015'].'</button>';

                                    add_to_jquery('
                                        $("#extra-info").hide();
                                        $("#show-uf").click(function () {
                                            $("#extra-info").slideToggle(200);
                                            $(this).text($(this).text() == "'.$locale['AX9_015'].'" ? "'.$locale['AX9_016'].'" : "'.$locale['AX9_015'].'");
                                        });
                                    ');
                                echo '</div>';
                            echo '</div>';

                            echo '<div class="col-xs-5 col-sm-5 col-md-5 col-lg-5">';
                                echo '<label class="pull-left"><strong>'.$locale['u057'].'</strong></label>';
                                echo '<div class="pull-right">';
                                    if (!empty($user_groups) && is_array($user_groups)) {
                                        $i = 0;
                                        foreach ($user_groups as $id => $group) {
                                            echo $i > 0 ? ', ' : '';
                                            echo '<a href="'.$group['group_url'].'">'.$group['group_name'].'</a>';
                                            $i++;
                                        }
                                    } else {
                                        echo !empty($locale['u117']) ? $locale['u117'] : $locale['na'];
                                    }
                                echo '</div>';

                                if (!empty($info['group_admin'])) {
                                    $group = $info['group_admin'];
                                    echo $group['ug_openform'];
                                    echo '<div class="strong">'.$group['ug_title'].'</div>';
                                    echo '<div class="spacer-xs">'.$group['ug_dropdown_input'].'</div>';
                                    echo '<div>'.$group['ug_button'].'</div>';
                                    echo $group['ug_closeform'];
                                }
                            echo '</div>';
                        echo '</div>'; // .row
                        echo '</div>';
                    echo '</div>';

                    if (!empty($info['admin'])) {
                        echo '<div class="m-t-20">'.$info['admin'].'</div>';
                    }
                }

                if (!empty($info['user_field'])) {
                    echo '<div class="row profile-fields equal-height" id="extra-info">';
                    foreach ($info['user_field'] as $field_cat_id => $category_data) {
                        if (!empty($category_data['fields'])) {
                            echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">';
                                echo '<div class="panel panel-default" style="height: 100%;">';
                                    echo '<div class="panel-heading" style="background: #222;color: #fff;">'.$category_data['title'].'</div>';

                                    echo '<div class="panel-body">';
                                    if (isset($category_data['fields'])) {
                                        foreach ($category_data['fields'] as $field_id => $field_data) {
                                            if ($field_data['value']) {
                                                echo '<div id="field-'.$field_id.'" class="row">';
                                                    echo '<label class="col-xs-12 col-sm-4 col-md-4 col-lg-4"><strong>'.(!empty($field_data['icon']) ? $field_data['icon'] : '').' '.$field_data['title'].'</strong></label>';
                                                    echo '<div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">'.$field_data['value'].'</div>';
                                                echo '</div>';
                                            } else {
                                                echo '<div>'.$locale['AX9_017'].'</div>';
                                            }
                                        }
                                    }
                                    echo '</div>';
                                echo '</div>';
                            echo '</div>';
                        }
                    }
                    echo '</div>';
                } else {
                    echo '<div class="text-center well">'.$locale['uf_108'].'</div>';
                }

                if ($tab_title['id'][$_GET['section']] == $tab_title['id'][1]) {
                    echo '<div>';
                    echo '<h4 style="font-weight: bold;">'.$locale['AX9_018'].'</h4>';

                    if (iMEMBER) {
                        $timeline = [];
                        $_timeline = [];

                        if ($forum) {
                            $forum_activities = dbquery("SELECT p.*, t.thread_subject
                                FROM ".DB_FORUM_POSTS." p
                                LEFT JOIN ".DB_FORUM_THREADS." t ON t.thread_id = p.thread_id
                                LEFT JOIN ".DB_FORUMS." f ON f.forum_id = p.forum_id
                                WHERE p.post_author='".$user_data['user_id']."' AND ".groupaccess('forum_access')."
                                ORDER BY p.post_id DESC, p.post_edittime DESC LIMIT 10
                            ");

                            while ($fdata = dbarray($forum_activities)) {
                                $item = '<label class="label label-primary">'.$locale['AX9_026'].'</label> '.$locale['in'].' <a href="'.FORUM.'viewthread.php?thread_id='.$fdata['thread_id'].'&pid='.$fdata['post_id'].'#post_'.$fdata['post_id'].'"><b>'.$fdata['thread_subject'].'</b></a>';
                                $time = $fdata['post_datestamp'];
                                $message = '<img src="'.THEME.'images/icons/forum.png" alt="Forum" style="max-width: 22px;"/> '.display_avatar($user_data, '22px', '', FALSE, 'img-rounded', $custom_avatar).' <a class="label label-default" href="'.BASEDIR.'profile.php?lookup='.$user_data['user_id'].'">'.$user_data['user_name'].'</a>'.$locale['AX9_027'].$item;
                                $timeline[$time][] = $message;
                            }
                        }

                        krsort($timeline);
                        function timeline_format($time) {
                            $locale = Core::GetInstance()->SetLocale();

                            if ($time >= strtotime('today 00:00')) {
                                return date('G:i', $time);
                            } elseif ($time >= strtotime('yesterday 00:00')) {
                                return $locale['AX9_021'];
                            } elseif ($time >= strtotime('-6 day 00:00')) {
                                return $locale['AX9_022'];
                            } elseif ($time >= strtotime('-13 day 00:00')) {
                                return $locale['AX9_023'];
                            } elseif ($time >= strtotime('-20 day 00:00')) {
                                return $locale['AX9_024'];
                            } elseif ($time >= strtotime('-29 day 00:00')) {
                                return $locale['AX9_025'];
                            } else {
                                return date('M j, Y', $time);
                            }
                        }

                        foreach ($timeline as $timestamp => $item) {
                            $format = timeline_format($timestamp);

                            if ($format == $locale['AX9_021']) {
                                $_timeline['1'][$format][] = $item;
                            } else if ($format == $locale['AX9_022']) {
                                $_timeline['3'][$format][] = $item;
                            } else if ($format == $locale['AX9_023']) {
                                $_timeline['4'][$format][] = $item;
                            } else if ($format == $locale['AX9_024']) {
                                $_timeline['5'][$format][] = $item;
                            } else if ($format == $locale['AX9_025']) {
                                $_timeline['6'][$format][] = $item;
                            } else {
                                $_timeline['2'][$format][] = $item;
                            }
                        }

                        if (!empty($_timeline)) {
                            foreach ($_timeline as $timeline_cats) {
                                foreach ($timeline_cats as $time => $t_item) {
                                    echo '<h6 style="font-weight: bold;">'.$time.'</h6>';

                                    $i = 0;
                                    foreach ($t_item as $ts) {
                                        foreach ($ts as $item) {
                                            echo '<div style="padding: 10px 0;'.($i > 0 ? ' border-top: 1px solid #eee;' :'').'">'.$item.'</div>';
                                            $i++;
                                        }
                                    }
                                }
                            }
                        } else {
                            echo '<div class="well text-center">'.str_replace('[USER]', $user_data['user_name'], $locale['AX9_020']).'</div>';
                        }
                    } else {
                        echo '<div class="well text-center">'.$locale['AX9_019'].'</div>';
                    }
                    echo '</div>';
                }

                echo closetabbody();
                echo closetab();
                echo '</div>';
            }

            echo '</div>';

            // Right Side
            echo '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">';
                echo '<div id="point-of-interest" class="panel panel-default"><div class="panel-body">';
                    echo '<p><span class="m-r-10"><img src="'.THEME.'images/icons/account.png" alt="Account"/></span><b>'.$locale['AX9_028'].'</b></p>';

                    echo '<ul class="profile-side-ul">';
                        echo '<li><span class="m-r-10"><img src="'.THEME.'images/icons/messages.png" alt="Messages"/></span><a href="'.BASEDIR.'messages.php">'.$locale['message'].'</a></li>';
                        echo '<li><span class="m-r-10"><img src="'.THEME.'images/icons/account.png" alt="Account"/></span><a href="'.BASEDIR.'edit_profile.php">'.$locale['AX9_014'].'</a></li>';
                        if ($forum) echo '<li><span class="m-r-10"><img src="'.THEME.'images/icons/forum.png" alt="Forum"/></span><a href="'.FORUM.'index.php">'.$locale['AX9_029'].'</a></li>';
                        $news = function_exists('infusion_exists') ? infusion_exists('news') : db_exists(DB_PREFIX.'news');
                        if ($news) echo '<li><span class="m-r-10"><img src="'.THEME.'images/icons/news.png" alt="News"/></span><a href="'.INFUSIONS.'news/news.php">'.$locale['AX9_030'].'</a></li>';
                        echo '<li><span class="m-r-10"><img src="'.THEME.'images/icons/mail.png" alt="Mail"/></span><a href="mailto:'.$settings['siteemail'].'">'.$settings['siteemail'].'</a></li>';
                    echo '</ul>';
                echo '</div></div>'; // #point-of-interest

                $guests = 0;
                $members = [];

                function color($user_level) {
                    switch ($user_level) {
                        case -103:
                            $class = 'superadmin';
                            break;
                        case -102:
                            $class = 'admin';
                            break;
                        case -101:
                            $class = 'member';
                            break;
                        default:
                            $class = '';
                    }

                    return $class.'-color';
                }

                $result = dbquery("SELECT o.*, u.user_id, u.user_name, u.user_status, u.user_level
                    FROM ".DB_ONLINE." o
                    LEFT JOIN ".DB_USERS." u ON o.online_user=u.user_id
                ");

                while ($data = dbarray($result)) {
                    if ($data['online_user'] == 0) {
                        $guests++;
                    } else {
                        $members[$data['user_id']] = [
                            $data['user_id'],
                            $data['user_name'],
                            $data['user_status'],
                            $data['user_level']
                        ];
                    }
                }

                $totalonline = Locale::format_word($guests + count($members), $locale['fmt_user']);
                $members_online = Locale::format_word(count($members), $locale['fmt_member']);
                $guests = Locale::format_word($guests, $locale['fmt_guest']);

                echo '<div class="panel panel-default">';
                    echo '<div class="panel-heading">';
                        echo '<div class="m-b-10"><span class="m-r-10"><img src="'.THEME.'images/icons/info.png" alt="Info"/></span><b>'.$locale['AX9_031'].'</b></div>';
                        echo str_replace(['[TOTAL_ONLINE]', '[MEMBERS]', '[GUESTS]'], [$totalonline, $members_online, $guests], $locale['AX9_032']);
                    echo '</div>';

                    echo '<div class="panel-body">';
                        echo '<div><i class="fa fa-fw fa-user"></i> <b>'.$locale['AX9_033'].'</b></div>';

                        if (isset($settings['online_maxcount']) && isset($settings['online_maxtime'])) {
                            echo '<div class="m-b-5">'.str_replace(
                                    ['[MAX_ONLINE]', '[MAX_ONLINE_TIME]'],
                                    ['<b>'.$settings['online_maxcount'].'</b>', '<b>'.showdate('longdate', $settings['online_maxtime']).'</b>'],
                                    $locale['AX9_034']
                                ).'</div>';
                        }

                        if (count($members)) {
                            echo implode(', ', array_map(function ($member) {
                                return profile_link($member[0], $member[1], $member[2], color($member[3]));
                            }, $members));
                        }
                    echo '</div>';

                    echo '<div class="panel-footer">';
                        echo '<div><i class="fa fa-fw fa-users"></i> <b>'.$locale['AX9_035'].'</b></div>';
                        // New members
                        $data = dbarray(dbquery("SELECT user_id, user_name, user_status
                            FROM ".DB_USERS." WHERE user_status='0' ORDER BY user_joined DESC LIMIT 0,1
                        "));
                        echo '<div>'.$locale['AX9_036'].profile_link($data['user_id'], $data['user_name'], $data['user_status'], 'member-color').'</div>';

                        $today = dbcount("(user_id)", DB_USERS, "user_status<='1' AND user_joined > ".(time() - 86400 + (mktime(24, 0, 0) - time())));
                        $days_7 = dbcount("(user_id)", DB_USERS, "user_status<='1' AND user_joined > ".(time() - 604800));
                        $days_30 = dbcount("(user_id)", DB_USERS, "user_status<='1' AND user_joined > ".(time() - 2592000));

                        echo '<ul class="profile-side-ul m-t-10">';
                            echo '<li><i class="fa fa-calendar"></i> '.$locale['AX9_037'].'<div class="pull-right">'.Locale::format_word($today, $locale['AX9_040']).'</div></li>';
                            echo '<li><i class="fa fa-calendar"></i> '.$locale['AX9_038'].'<div class="pull-right">'.Locale::format_word($days_7, $locale['AX9_040']).'</div></li>';
                            echo '<li><i class="fa fa-calendar"></i> '.$locale['AX9_039'].'<div class="pull-right">'.Locale::format_word($days_30, $locale['AX9_040']).'</div></li>';
                        echo '</ul>';
                        echo '<hr/>';

                        $visited = number_format(dbcount("(user_id)", DB_USERS, "user_status<='1' AND user_lastvisit > UNIX_TIMESTAMP(CURDATE())"));
                        $total_members = number_format(dbcount("(user_id)", DB_USERS, "user_status<='1'"), 0);
                        echo '<div><b>'.$locale['AX9_041'].'</b><span class="pull-right">'.$visited.'/'.$total_members.'</span></div>';

                        $i = 0;
                        $result = dbquery("SELECT user_id, user_name, user_level, user_status, user_lastvisit
                            FROM ".DB_USERS."
                            WHERE user_lastvisit > UNIX_TIMESTAMP(CURDATE()) AND user_status = '0'
                            ORDER BY user_lastvisit DESC
                        ");

                        if (dbrows($result) != 0) {
                            while ($data = dbarray($result)) {
                                echo $i > 0 ? ', ' : '';
                                echo profile_link($data['user_id'], $data['user_name'], $data['user_status'], color($data['user_level']));
                                $i++;
                            }
                        }

                        if (column_exists('users', 'user_birthdate')) {
                            echo '<hr/>';
                            echo '<div><b>'.$locale['AX9_042'].'</b></div>';

                            $result = dbquery("SELECT user_id, user_name, user_level, user_status, user_birthdate
                                FROM ".DB_USERS."
                                WHERE user_birthdate LIKE '____-".date('m').'-'.date('d')."' AND user_status = '0'
                            ");

                            $birthdays = number_format(dbrows($result));

                            if ($birthdays) {
                                $i = 1;
                                echo str_replace('[MEMBERS]', $birthdays, $locale['AX9_044']);
                                while ($data = dbarray($result)) {
                                    $birthdate = explode('-', $data['user_birthdate']);
                                    $year = date('Y') - $birthdate[0];

                                    echo profile_link($data['user_id'], $data['user_name'], $data['user_status']).' ('.$year.')';
                                    echo $i != $birthdays ? ', ' : '';
                                    $i++;
                                }
                            } else {
                                echo $locale['AX9_043'];
                            }
                        }
                    echo '</div>';
                echo '</div>';
            echo '</div>';

        echo '</div>';
    }

    public static function EditProfile() {
        self::SetParam('greybody', 'TRUE');
        Components::HidePanels();

        echo '<div class="panel panel-default">';
        echo '<div class="panel-heading"><h4><b>'.fusion_get_locale('u102').'</b></h4></div>';
        echo '<div class="panel-body">';
        echo '{%tab_header%}';
        echo '{%open_form%}';
        echo '{%user_name_field%}';
        echo '{%user_email_field%}';
        echo '{%user_hide_email_field%}';
        echo '{%user_reputation_field%}';
        echo '{%user_avatar_field%}';
        echo '{%user_password_field%}';
        echo '{%user_admin_password_field%}';
        echo '{%custom_fields%}';
        echo '{%captcha_fields%}';
        echo '{%eula%}';
        echo '{%post_button%}';
        echo '{%close_form%}';
        echo '{%tab_footer%}';
        echo '</div>';
        echo '</div>';
    }
}
