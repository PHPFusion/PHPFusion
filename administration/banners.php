<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: banners.php
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
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
pageAccess('SB');

class BannersAdministration {
    private static $settings = [];
    private static $instance = NULL;
    protected static $locale = [];
    protected static $action = NULL;
    protected static $section = '';
    private $allowed_section = ['banners_form', 'banners_list'];

    public function __construct() {
        self::$locale = fusion_get_locale("", LOCALE.LOCALESET."admin/banners.php");
        self::$settings = fusion_get_settings();
        self::$action = get('action') ? get('action') : '';
        self::$section = get('section') ? get('section') : '';

        add_breadcrumb(['link' => ADMIN.'banners.php'.fusion_get_aidlink(), 'title' => self::$locale['BN_000']]);
    }

    public static function getInstance() {
        if (empty(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function display_admin() {
        opentable(self::$locale['BN_000']);

        self::$section = self::$section && in_array(self::$section, $this->allowed_section) ? self::$section : 'banners_list';
        $edit = self::$action == 'edit' ? TRUE : FALSE;

        $master_tab_title['title'][] = self::$locale['BN_000'];
        $master_tab_title['id'][] = 'banners_list';
        $master_tab_title['icon'][] = "";

        if ($edit) {
            $master_tab_title['title'][] = self::$locale['edit'];
            $master_tab_title['id'][] = 'banners_form';
            $master_tab_title['icon'][] = "";
        }

        echo opentab($master_tab_title, self::$section, 'banners_list', TRUE, 'nav-tabs m-b-20', '', ['ref', 'section', 'action', 'banner_id']);
        switch (self::$section) {
            case "banners_form":
                if ($edit) {
                    $this->bannerForm();
                }
                break;
            default:
                $this->listBanner();
                break;
        }
        echo closetab();
        closetable();
    }

    public function listBanner() {
        $aidlink = fusion_get_aidlink();

        if (post('save_banners')) {
            $settings_main = [
                'sitebanner1' => sanitizer('sitebanner1', '', 'sitebanner1'),
                'sitebanner2' => sanitizer('sitebanner2', '', 'sitebanner2')
            ];

            if (fusion_safe()) {
                foreach ($settings_main as $settings_key => $settings_value) {
                    dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:value WHERE settings_name=:name", [':value' => $settings_value, ':name' => $settings_key]);
                    add_notice('success', self::$locale['BN_012']);
                }

                redirect(clean_request('', ['section=banners_list', 'aid'], TRUE));
            }
        }

        if (self::$action == 'delete') {
            if (empty(get('banner_id')) or empty(self::$settings[get('banner_id')])) {
                \Defender::stop();
                add_notice('danger', self::$locale['BN_014']);
                redirect(clean_request('', ['section=banners_list', 'aid'], TRUE));
            }

            $settings_main = [
                'sitebanner1' => get('banner_id') == 'sitebanner1' ? '' : self::$settings['sitebanner1'],
                'sitebanner2' => get('banner_id') == 'sitebanner2' ? '' : self::$settings['sitebanner2'],
            ];

            if (fusion_safe()) {
                foreach ($settings_main as $settings_key => $settings_value) {
                    dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:value WHERE settings_name=:name", [':value' => $settings_value, ':name' => $settings_key]);
                }
                add_notice('warning', self::$locale['BN_013']);
                redirect(clean_request('', ['section=banners_list', 'aid'], TRUE));
            }
        }

        echo openform('bannersfrm', 'post');
        for ($i = 1; $i < 3; $i++) {
            $banner = "<div class='pull-right btn-group'>";
            $banner .= "<a class='btn btn-default btn-sm' href='".FUSION_SELF.$aidlink."&amp;ref=banners_form&amp;section=banners_form&amp;action=edit&amp;banner_id=sitebanner".$i."'><i class='fa fa-edit fa-fw'></i> ".self::$locale['edit']."</a>";
            $banner .= "<a class='btn btn-danger btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=banners_list&amp;action=delete&amp;banner_id=sitebanner".$i."' onclick=\"return confirm('".self::$locale['BN_015']."');\"><i class='fa fa-trash fa-fw'></i> ".self::$locale['delete']."</a>";
            $banner .= "</div>\n";

            openside(self::$locale['sitebanner'.$i].$banner);
            if (!empty(self::$settings['sitebanner'.$i])) {
                if (self::$settings['allow_php_exe']) {
                    eval("?>".stripslashes(self::$settings['sitebanner'.$i])."<?php ");
                } else {
                    echo stripslashes(self::$settings['sitebanner'.$i]);
                }
            }

            closeside();
        }
        echo closeform();
    }

    public function bannerForm() {
        openside('');
        echo openform('banner_form', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;section=banners_list&amp;action=edit", ['enctype' => TRUE]);
        echo form_textarea(get('banner_id'), self::$locale[get('banner_id')], stripslashes(self::$settings[get('banner_id')]), [
            'preview'   => TRUE,
            'type'      => fusion_get_settings('tinymce_enabled') ? 'tinymce' : 'html',
            'tinymce'   => fusion_get_settings('tinymce_enabled') && iADMIN ? 'advanced' : 'simple',
            'autosize'  => TRUE,
            'form_name' => 'banner_form',
            'wordcount' => TRUE,
            'inline'    => FALSE
        ]);
        echo form_button('save_banners', self::$locale['save'], self::$locale['save'], ['class' => 'btn-success']);
        echo closeform();
        closeside();
    }
}

BannersAdministration::getInstance()->display_admin();

require_once THEMES.'templates/footer.php';
