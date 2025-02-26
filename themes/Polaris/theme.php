<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Polaris/theme.php
| Author: Meangczac (Chan), Core Development Team
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

require_once INCLUDES.'theme_functions_include.php';

require_once __DIR__ . '/classes/Polaris.autoloader.php';
require_once __DIR__ . '/classes/PolarisThemeFactory.class.php';
require_once __DIR__ . '/classes/Polaris.class.php';

fusion_load_script(THEME.'polaris.js');


/**
 * Theme layout
 *
 * @return void
 */
function render_page()
{
    $theme = Polaris::getInstance();
    $theme->setLayoutClass();
    $theme->setSiteLinksOptions();

    Polaris::getInstance()->renderPage();
}


/*function polaris_uip()
{
    $locale = fusion_get_locale('', POLARIS_LOCALE);
    $settings = fusion_get_settings();
    $userdata = fusion_get_userdata();
    $languages = fusion_get_enabled_languages();

    if (iMEMBER) {
        $name = $locale['MG_001'] . $userdata['user_name'];
    } else {
        $name = $locale['login'] . ($settings['enable_registration'] ? '/' . $locale['register'] : '');
    }

    ob_start();
    echo '<ul class="nav navbar-nav navbar-right secondary m-r-0">';
    if (count($languages) > 1) {
        echo '<li class="dropdown language-switcher">';
        echo '<a id="ddlangs" href="#" class="dropdown-toggle pointer" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="' . LANGUAGE . '">';
        echo '<i class="fa fa-globe"></i> ';
        echo '<img src="' . BASEDIR . 'locale/' . LANGUAGE . '/' . LANGUAGE . '-s.png" alt="' . translate_lang_names(LANGUAGE) . '"/>';
        echo '<span class="caret"></span>';
        echo '</a>';

        echo '<ul class="dropdown-menu" aria-labelledby="ddlangs">';
        foreach ($languages as $language_folder => $language_name) {
            echo '<li><a class="display-block" href="' . clean_request('lang=' . $language_folder, ['lang'], FALSE) . '">';
            echo '<img class="m-r-5" src="' . BASEDIR . 'locale/' . $language_folder . '/' . $language_folder . '-s.png" alt="' . $language_folder . '"/> ';
            echo $language_name;
            echo '</a></li>';
        }
        echo '</ul>';
        echo '</li>';
    }

    echo '<li id="user-info" class="dropdown">';
    echo '<a href="#" id="user-menu" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . $name . ' <span class="caret"></span></a>';

    if (iMEMBER) {
        echo '<ul class="dropdown-menu" aria-labelledby="user-menu" style="min-width: 180px;">';
        echo '<li><a href="' . BASEDIR . 'profile.php?lookup=' . $userdata['user_id'] . '"><i class="m-r-5 fa fa-fw fa-user-circle-o"></i>' . $locale['view'] . ' ' . $locale['profile'] . '</a></li>';
        echo '<li><a href="' . BASEDIR . 'messages.php"><i class="m-r-5 fa fa-fw fa-envelope-o"></i> ' . $locale['message'] . '</a></li>';
        echo '<li><a href="' . BASEDIR . 'edit_profile.php"><i class="m-r-5 fa fa-fw fa-pencil"></i> ' . $locale['UM080'] . '</a></li>';
        echo iADMIN ? '<li role="separator" class="divider"></li>' : '';
        echo iADMIN ? '<li><a href="' . ADMIN . 'index.php' . fusion_get_aidlink() . '&pagenum=0"><i class="m-r-5 fa fa-fw fa-dashboard"></i> ' . $locale['global_123'] . '</a></li>' : '';
        echo '<li role="separator" class="divider"></li>';

        if (session_get('login_as')) {
            echo '<li><a href="' . BASEDIR . 'index.php?logoff=' . $userdata['user_id'] . '"><i class="m-r-5 fa fa-fw fa-sign-out"></i> ' . $locale['UM103'] . '</a></li>';
        }
        echo '<li><a href="' . BASEDIR . 'index.php?logout=yes"><i class="m-r-5 fa fa-fw fa-sign-out"></i> ' . $locale['logout'] . '</a></li>';
        echo '</ul>';
    } else {
        echo '<ul class="dropdown-menu login-menu" aria-labelledby="user-menu">';
        echo '<li>';
        $action_url = FUSION_SELF . (FUSION_QUERY ? '?' . FUSION_QUERY : '');
        if (isset($_GET['redirect']) && strstr($_GET['redirect'], '/')) {
            $action_url = cleanurl(urldecode($_GET['redirect']));
        }

        echo openform('loginform', 'post', $action_url, ['form_id' => 'login-form']);
        switch ($settings['login_method']) {
            case 2:
                $placeholder = $locale['global_101c'];
                break;
            case 1:
                $placeholder = $locale['global_101b'];
                break;
            default:
                $placeholder = $locale['global_101a'];
        }

        echo form_text('user_name', '', '', ['placeholder' => $placeholder, 'required' => TRUE, 'input_id' => 'username']);
        echo form_text('user_pass', '', '', ['placeholder' => $locale['global_102'], 'type' => 'password', 'required' => TRUE, 'input_id' => 'userpassword']);
        echo form_checkbox('remember_me', $locale['global_103'], '', ['value' => 'y', 'class' => 'm-0', 'reverse_label' => TRUE, 'input_id' => 'rememberme']);
        echo form_button('login', $locale['global_104'], '', ['class' => 'btn-primary btn-sm m-b-5', 'input_id' => 'loginbtn']);
        echo closeform();
        echo '</li>';
        echo '<li>' . str_replace(['[LINK]', '[/LINK]'], ['<a href="' . BASEDIR . 'lostpassword.php">', '</a>'], $locale['global_106']) . '</li>';
        if ($settings['enable_registration']) echo '<li><a href="' . BASEDIR . 'register.php">' . $locale['register'] . '</a></li>';
        echo '</ul>';
    }
    echo '</li>';
    echo '</ul>';

    $html = ob_get_contents();
    ob_end_clean();
    return $html;
}*/

if (!defined('ADMIN_PANEL')) {
    function opentable($title = FALSE, $class = '')
    {
        Polaris::getInstance()->opentable($title, $class);
    }

    function closetable()
    {
        Polaris::getInstance()->closeComponents(2);
    }

    function openside($title = FALSE, $class = '')
    {
        Polaris::getInstance()->openside($title, $class);
    }

    function closeside()
    {
        Polaris::getInstance()->closeComponents(1);
    }
}
