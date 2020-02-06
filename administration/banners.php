<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: banners.php
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
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
pageAccess('SB');

class BannersAdministration {
    private static $settings = [];
    private static $instance = NULL;
    private static $locale = [];

    public function __construct() {
        self::$locale = fusion_get_locale("", LOCALE.LOCALESET."admin/banners.php");
        self::$settings = fusion_get_settings();
        $_GET['action'] = isset($_GET['action']) ? $_GET['action'] : '';

        switch ($_GET['action']) {
            case 'delete':
                if (empty($_GET['banner_id'])) {
                    \defender::stop();
                    addNotice('danger', self::$locale['BN_014']);
                    redirect(clean_request('', ['section=banners_list', 'aid'], TRUE));
                }
                break;
            default:
                break;
        }

        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'banners.php'.fusion_get_aidlink(), 'title' => self::$locale['BN_000']]);
    }

    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
            self::$instance->set_bannerdb();
        }

        return self::$instance;
    }

    private function set_bannerdb() {
        if (isset($_POST['upload_banner'])) {
            if (\defender::getInstance()->safe()) {
                if (!empty($_FILES['banner_image']) && is_uploaded_file($_FILES['banner_image']['tmp_name'])) {
                    $upload = form_sanitizer($_FILES['banner_image'], '', 'banner_image');
                    if ($upload['error'] == 0) {
                        addNotice('success', self::$locale['BN_010']);
                        redirect(clean_request('', ['section=banners_list', 'aid'], TRUE));
                    }
                } else {
                    addNotice('danger', self::$locale['BN_011']);
                    redirect(clean_request('', ['section=banners_list', 'aid'], TRUE));
                }
            }
        }

        if (isset($_POST['save_banners'])) {
            $settings_main = [
                'sitebanner1' => isset($_POST['sitebanner1']) ? descript(addslashes($_POST['sitebanner1'])) : self::$settings['sitebanner1'],
                'sitebanner2' => isset($_POST['sitebanner2']) ? descript(addslashes($_POST['sitebanner2'])) : self::$settings['sitebanner2'],
            ];

            if (\defender::safe()) {
                foreach ($settings_main as $settings_key => $settings_value) {
                    dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:value WHERE settings_name=:name", [':value' => $settings_value, ':name' => $settings_key]);
                    addNotice('success', self::$locale['BN_012']);
                }

                redirect(clean_request('', ['section=banners_list', 'aid'], TRUE));
            }
        }

        if (isset($_GET['action']) && $_GET['action'] == 'delete') {
            $settings_main = [
                'sitebanner1' => isset($_GET['banner_id']) && $_GET['banner_id'] == 'sitebanner1' ? '' : self::$settings['sitebanner1'],
                'sitebanner2' => isset($_GET['banner_id']) && $_GET['banner_id'] == 'sitebanner2' ? '' : self::$settings['sitebanner2'],
            ];

            if (\defender::safe()) {
                foreach ($settings_main as $settings_key => $settings_value) {
                    dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:value WHERE settings_name=:name", [':value' => $settings_value, ':name' => $settings_key]);
                }
                addNotice('warning', self::$locale['BN_013']);
                redirect(clean_request('', ['section=banners_list', 'aid'], TRUE));
            }
        }
    }

    public function display_admin() {
        opentable(self::$locale['BN_000']);
        $allowed_section = ['banners_form', 'banners_list', 'bannerUp_form'];
        if (isset($_GET['section']) && $_GET['section'] == "back") {
            redirect(clean_request('', ['ref', 'section', 'action', 'banner_id'], FALSE));
        }
        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'banners_list';
        $edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? TRUE : FALSE;

        if (!empty($_GET['ref']) || isset($_GET['banner_id'])) {
            $master_tab_title['title'][] = self::$locale['back'];
            $master_tab_title['id'][] = "back";
            $master_tab_title['icon'][] = "fa fa-fw fa-arrow-left";
        }
        $master_tab_title['title'][] = self::$locale['BN_000'];
        $master_tab_title['id'][] = 'banners_list';
        $master_tab_title['icon'][] = "";

        if ($edit) {
            $master_tab_title['title'][] = self::$locale['edit'];
            $master_tab_title['id'][] = 'banners_form';
            $master_tab_title['icon'][] = "";
        }
        if (isset($_GET['section']) && $edit) {
            \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $master_tab_title['title'][1]]);
        }

        echo opentab($master_tab_title, $_GET['section'], 'banners_list', TRUE, 'nav-tabs m-b-20');
        switch ($_GET['section']) {
            case "banners_form":
                if ($edit) {
                    $this->bannerForm();
                }
                break;
            default:
                $this->list_banner();
                break;
        }
        echo closetab();
        closetable();
    }

    public function list_banner() {
        $aidlink = fusion_get_aidlink();

        echo openform('bannerform', 'post', FUSION_SELF.$aidlink."&amp;section=banners_form");
        $banner1 = "<div class='pull-right btn-group'>";
        $banner1 .= "<a class='btn btn-default btn-sm' href='".FUSION_SELF.$aidlink."&amp;ref=banners_form&amp;section=banners_form&amp;action=edit&amp;banner_id=sitebanner1'><i class='fa fa-edit fa-fw'></i> ".self::$locale['edit']."</a>";
        $banner1 .= "<a class='btn btn-danger btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=banners_list&amp;action=delete&amp;banner_id=sitebanner1' onclick=\"return confirm('".self::$locale['BN_015']."');\"><i class='fa fa-trash fa-fw'></i> ".self::$locale['delete']."</a>";
        $banner1 .= "</div>\n";
        openside(self::$locale['sitebanner1'].$banner1);
        if (!empty(self::$settings['sitebanner1'])) {
            if (self::$settings['allow_php_exe']) {
                eval("?>".stripslashes(self::$settings['sitebanner1'])."<?php ");
            } else {
                echo stripslashes(self::$settings['sitebanner1']);
            }
        }
        closeside();
        $banner2 = "<div class='pull-right btn-group'>";
        $banner2 .= "<a class='btn btn-default btn-sm' href='".FUSION_SELF.$aidlink."&amp;ref=banners_form&amp;section=banners_form&amp;action=edit&amp;banner_id=sitebanner2'><i class='fa fa-edit fa-fw'></i> ".self::$locale['edit']."</a>";
        $banner2 .= "<a class='btn btn-danger btn-sm' href='".FUSION_SELF.$aidlink."&amp;section=banners_list&amp;action=delete&amp;banner_id=sitebanner2' onclick=\"return confirm('".self::$locale['BN_015']."');\"><i class='fa fa-trash fa-fw'></i> ".self::$locale['delete']."</a>";
        $banner2 .= "</div>\n";
        openside(self::$locale['sitebanner2'].$banner2);
        if (!empty(self::$settings['sitebanner2'])) {
            if (self::$settings['allow_php_exe']) {
                eval("?>".stripslashes(self::$settings['sitebanner2'])."<?php ");
            } else {
                echo stripslashes(self::$settings['sitebanner2']);
            }
        }
        closeside();
        echo closeform();
    }

    public function bannerForm() {
        openside('');
        echo openform('banner_form', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;section=banners_list&amp;action=edit", ['enctype' => TRUE]);
        echo form_textarea($_GET['banner_id'], self::$locale[$_GET['banner_id']], stripslashes(self::$settings[$_GET['banner_id']]), [
            'preview'   => TRUE,
            'type'      => self::$settings['tinymce_enabled'] ? 'tinymce' : 'html',
            'tinymce'   => self::$settings['tinymce_enabled'] && iADMIN ? 'advanced' : 'simple',
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
