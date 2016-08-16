<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: login.php
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
require_once "maincore.php";
require_once THEMES."templates/header.php";
require_once THEMES."templates/global/login.php";
$locale = fusion_get_locale();
add_to_title($locale['global_200'].$locale['global_100']);
add_to_meta("keywords", $locale['global_100']);
$info = array();
if (!iMEMBER) {
    if (isset($_GET['error']) && isnum($_GET['error'])) {
        if (isset($_GET['redirect']) && strpos(urldecode($_GET['redirect']), "/") === 0) {
            $action_url = cleanurl(urldecode($_GET['redirect']));
        }
        switch ($_GET['error']) {
            case 1:
                addNotice("warning", $locale['global_196']);
                break;
            case 2:
                addNotice("warning", $locale['global_192']);
                break;
            case 3:
                if (isset($_COOKIE[COOKIE_PREFIX."user"])) {
                    redirect($action_url);
                } else {
                    addNotice("warning", $locale['global_193']);
                }
                break;
            case 4:
                if (isset($_GET['status']) && isnum($_GET['status'])) {
                    $id = ((isset($_GET['id']) && isnum($_GET['id'])) ? $_GET['id'] : "0");
                    switch ($_GET['status']) {
                        case 1:
                            $data = dbarray(dbquery("SELECT suspend_reason FROM ".DB_SUSPENDS."
								WHERE suspended_user='".$id."'
								ORDER BY suspend_date DESC  LIMIT 1"));
                            addNotice("danger", $locale['global_406']." ".$data['suspend_reason']);
                            break;
                        case 2:
                            addNotice("danger", $locale['global_195']);
                            break;
                        case 3:
                            $data = dbarray(dbquery("SELECT u.user_actiontime, s.suspend_reason FROM ".DB_SUSPENDS." s
								LEFT JOIN ".DB_USERS." u ON u.user_id=s.suspended_user
								WHERE s.suspended_user='".$id."'
								ORDER BY s.suspend_date DESC LIMIT 1"));
                            addNotice("danger", $locale['global_407'].showdate('shortdate',
                                                                               $data['user_actiontime']).$locale['global_408']." - ".$data['suspend_reason']);
                            break;
                        case 4:
                            addNotice("danger", $locale['global_409']);
                            break;
                        case 5:
                            addNotice("danger", $locale['global_411']);
                            break;
                        case 6:
                            addNotice("danger", $locale['global_412']);
                            break;
                    }
                }
                break;
        }
    }
    switch (fusion_get_settings("login_method")) {
        case "2" :
            $placeholder = $locale['global_101c'];
            break;
        case "1" :
            $placeholder = $locale['global_101b'];
            break;
        default:
            $placeholder = $locale['global_101a'];
    }
    $info = array(
        "open_form" => openform('loginpageform', 'POST', fusion_get_settings("opening_page")),
        "user_name" => form_text('user_name', $placeholder, isset($_POST['user_name']) ? $_POST['user_name'] : "",
                                 array('placeholder' => $placeholder)),
        "user_pass" => form_text('user_pass', $locale['global_102'], "", array('placeholder' => $locale['global_102'], 'type' => 'password')),
        "remember_me" => form_checkbox("remember_me", $locale['global_103'], ""),
        "login_button" => form_button('login', $locale['global_104'], $locale['global_104'], array('class' => 'btn-primary btn-block m-b-20')),
        "registration_link" => (fusion_get_settings("enable_registration")) ? str_replace(array(
                                                                                              "[LINK]", "[/LINK]"
                                                                                          ), array("<a href='".BASEDIR."register.php'>", "</a>"),
                                                                                          $locale['global_105']) : "",
        "forgot_password_link" => str_replace(array("[LINK]", "[/LINK]"), array("<a href='".BASEDIR."lostpassword.php'>", "</a>"),
                                              $locale['global_106']),
        "close_form" => closeform()
    );
}
display_loginform($info);
require_once THEMES."templates/footer.php";