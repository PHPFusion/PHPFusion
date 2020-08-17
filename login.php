<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: login.php
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
require_once __DIR__.'/maincore.php';
require_once THEMES.'templates/header.php';
require_once THEMES."templates/global/login.php";
$locale = fusion_get_locale();
add_to_title($locale['global_200'].$locale['global_100']);
add_to_meta("keywords", $locale['global_100']);
$settings = fusion_get_settings();
$info = [];

if (!iMEMBER) {
    // do login
    if (isset($_GET['error']) && isnum($_GET['error'])) {
        if (isset($_GET['redirect']) && strpos(urldecode($_GET['redirect']), "/") === 0) {
            $action_url = cleanurl(urldecode($_GET['redirect']));
        }
        switch ($_GET['error']) {
            case 1:
                add_notice("danger", $locale['error_input_username']);
                break;
            case 2:
                add_notice("danger", $locale['global_192']);
                break;
            case 3:
                if (isset($_COOKIE[COOKIE_PREFIX."user"])) {
                    redirect($action_url);
                } else {
                    add_notice("danger", $locale['global_193']);
                }
                break;
            case 4:
                if (isset($_GET['status']) && isnum($_GET['status'])) {
                    $id = (filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT) ?: "0");

                    switch ($_GET['status']) {
                        case 1:
                            $data = dbarray(dbquery("SELECT suspend_reason
                                FROM ".DB_SUSPENDS."
                                WHERE suspended_user=:suser
                                ORDER BY suspend_date DESC  LIMIT 1", [':suser' => $id]));
                            add_notice("danger", $locale['global_406']." ".$data['suspend_reason']);
                            break;
                        case 2:
                            add_notice("danger", $locale['global_195']);
                            break;
                        case 3:
                            $data = dbarray(dbquery("SELECT u.user_actiontime, s.suspend_reason
                                FROM ".DB_SUSPENDS." s
                                LEFT JOIN ".DB_USERS." u ON u.user_id=s.suspended_user
                                WHERE s.suspended_user=:suser
                                ORDER BY s.suspend_date DESC LIMIT 1", [':suser' => $id]));
                            add_notice("danger", $locale['global_407'].showdate('shortdate', $data['user_actiontime']).$locale['global_408']." - ".$data['suspend_reason']);
                            break;
                        case 4:
                            add_notice("danger", $locale['global_409']);
                            break;
                        case 5:
                            add_notice("danger", $locale['global_411']);
                            break;
                        case 6:
                            add_notice("danger", $locale['global_412']);
                            break;
                    }
                }
                break;
                break;
            case 5:
                add_notice('danger', $locale['error_input_password']);
        }
    }

    switch ($settings['login_method']) {
        case "2" :
            $placeholder = $locale['global_101c'];
            break;
        case "1" :
            $placeholder = $locale['global_101b'];
            break;
        default:
            $placeholder = $locale['global_101a'];
    }

    $login_connectors = [];
    $login_hooks = fusion_filter_hook('fusion_login_connectors');
    if (!empty($login_hooks)) {
        foreach($login_hooks as $buttons) {
            $login_connectors[] = $buttons;
        }
    }

    $post_url = $settings['opening_page'];
    if ($site_referer = server('HTTP_REFERER')) {
        // check if last referrer page is from own site.
        if (stristr($site_referer, $settings['siteurl'])) {
            // path change to relative path
            $post_url = strtr($site_referer, [$settings['siteurl'] => BASEDIR]);
        }
        unset($site_referer);
    }

    $info = [
        'open_form'            => openform('loginpageform', 'POST', $post_url),
        'user_name'            => form_text('user_name', $placeholder, '', ['placeholder' => $placeholder]),
        'user_pass'            => form_text('user_pass', $locale['global_102'], '', ['placeholder' => $locale['global_102'], 'type' => 'password']),
        'remember_me'          => form_checkbox('remember_me', $locale['global_103'], '', ['reverse_label' => TRUE, 'ext_tip' => $locale['UM067']]),
        'login_button'         => form_button('login', $locale['UM064'], $locale['UM064'], ['class' => 'btn-primary btn-login']),
        'signup_button'        => "<a class='btn btn-default btn-register' href='".BASEDIR."register.php'>".$locale['global_109']."</a>\n",
        'registration_link'    => (fusion_get_settings('enable_registration')) ? strtr($locale['global_105'], ['[LINK]' => "<a href='".BASEDIR."register.php'>\n", '[/LINK]' => "</a>\n"]) : '',
        'forgot_password_link' => strtr($locale['global_106'], ['[LINK]' => "<a href='".BASEDIR."lostpassword.php'>\n", '[/LINK]' => "</a>\n",]),
        'close_form'           => closeform(),
        'connect_buttons'      => $login_connectors,
        'form_action'          => $post_url,
    ];
}

display_loginform($info);
require_once THEMES.'templates/footer.php';
