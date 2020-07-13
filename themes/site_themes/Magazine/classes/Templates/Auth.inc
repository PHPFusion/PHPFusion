<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Magazine/classes/Templates/Auth.inc
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
namespace Magazine\Templates;

use Magazine\Core;
use \PHPFusion\Panels;

class Auth extends Core {
    public static function loginForm($info) {
        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();
        $settings = fusion_get_settings();

        Panels::getInstance(TRUE)->hide_panel('RIGHT');
        Panels::getInstance(TRUE)->hide_panel('LEFT');
        Panels::getInstance(TRUE)->hide_panel('AU_CENTER');
        Panels::getInstance(TRUE)->hide_panel('U_CENTER');
        Panels::getInstance(TRUE)->hide_panel('L_CENTER');
        Panels::getInstance(TRUE)->hide_panel('BL_CENTER');
        self::setParam('notices', FALSE);

        $twig = twig_init(THEME.'twig');

        $links = [];
        if (iMEMBER) {
            $adminlink = [];
            if (iADMIN) {
                $adminlink = ['link' => ADMIN.'index.php'.fusion_get_aidlink(), 'title' => $locale['global_123']];
            }

            $msg_count = dbcount("(message_id)", DB_MESSAGES, "message_to='".$userdata['user_id']."' AND message_read='0' AND message_folder='0'");

            $links = [
                ['link' => BASEDIR.'edit_profile.php', 'title' => $locale['global_120']],
                ['link' => BASEDIR.'messages.php', 'title' => $locale['global_121'].' - '.sprintf($locale['global_125'], $msg_count)],
                ['link' => BASEDIR.'members.php', 'title' => $locale['global_122']],
                $adminlink,
                ['link' => BASEDIR.'index.php?logout=yes', 'title' => $locale['global_124']],
                ['link' => BASEDIR.$settings['opening_page'], 'title' => $locale['home']]
            ];
        }

        $context = [
            'locale'   => $locale,
            'userdata' => $userdata,
            'settings' => $settings,
            'notices'  => render_notices(get_notices(['all', FUSION_SELF])),
            'info'     => $info,
            'links'    => $links
        ];

        echo $twig->render('bootstrap3/auth/login.twig', $context);
    }

    public static function registerForm($info) {
        $locale = fusion_get_locale();

        Panels::getInstance(TRUE)->hide_panel('RIGHT');
        Panels::getInstance(TRUE)->hide_panel('LEFT');
        Panels::getInstance(TRUE)->hide_panel('AU_CENTER');
        Panels::getInstance(TRUE)->hide_panel('U_CENTER');
        Panels::getInstance(TRUE)->hide_panel('L_CENTER');
        Panels::getInstance(TRUE)->hide_panel('BL_CENTER');
        self::setParam('notices', FALSE);

        $twig = twig_init(THEME.'twig');

        $open = NULL;
        $close = NULL;

        if (isset($info['section']) && count($info['section']) > 1) {
            $tab_title = [];
            foreach ($info['section'] as $page_section) {
                $tab_title['title'][$page_section['id']] = $page_section['name'];
                $tab_title['id'][$page_section['id']] = $page_section['id'];
                $tab_title['icon'][$page_section['id']] = '';
            }
            $open = opentab($tab_title, get('section'), 'user-profile-form', TRUE);
            $close = closetab();
        }

        $ufs = NULL;
        if (!empty($info['user_field'])) {
            foreach ($info['user_field'] as $field => $fieldData) {
                echo !empty($fieldData['title']) ? $fieldData['title'] : '';
                if (!empty($fieldData['fields']) && is_array($fieldData['fields'])) {
                    foreach ($fieldData['fields'] as $cField => $cFieldData) {
                        $ufs .=  !empty($cFieldData) ? $cFieldData : '';
                    }
                }
            }
        }

        $context = [
            'locale'    => $locale,
            'notices'   => render_notices(get_notices(['all', FUSION_SELF])),
            'info'      => $info,
            'open_tab'  => $open,
            'close_tab' => $close,
            'no_uf'     => empty($info['user_name']) && empty($info['user_field']) ? TRUE : FALSE,
            'ufs'       => $ufs
        ];

        echo $twig->render('bootstrap3/auth/register.twig', $context);
    }

    public static function lostPassword($content) {
        $locale = fusion_get_locale();

        Panels::getInstance(TRUE)->hide_panel('RIGHT');
        Panels::getInstance(TRUE)->hide_panel('LEFT');
        Panels::getInstance(TRUE)->hide_panel('AU_CENTER');
        Panels::getInstance(TRUE)->hide_panel('U_CENTER');
        Panels::getInstance(TRUE)->hide_panel('L_CENTER');
        Panels::getInstance(TRUE)->hide_panel('BL_CENTER');
        self::setParam('notices', FALSE);

        $twig = twig_init(THEME.'twig');

        $context = [
            'locale'  => $locale,
            'notices' => render_notices(get_notices(['all', FUSION_SELF])),
            'content' => $content
        ];

        echo $twig->render('bootstrap3/auth/lostpassword.twig', $context);
    }

    public static function fusionGateway($info) {
        global $locale;

        Panels::getInstance(TRUE)->hide_panel('RIGHT');
        Panels::getInstance(TRUE)->hide_panel('LEFT');
        Panels::getInstance(TRUE)->hide_panel('AU_CENTER');
        Panels::getInstance(TRUE)->hide_panel('U_CENTER');
        Panels::getInstance(TRUE)->hide_panel('L_CENTER');
        Panels::getInstance(TRUE)->hide_panel('BL_CENTER');

        $twig = twig_init(THEME.'twig');

        $context = [
            'session' => ['validated' => session_get('validated')],
            'locale'  => $locale,
            'info'    => $info
        ];

        echo $twig->render('bootstrap3/auth/gateway.twig', $context);
    }
}
