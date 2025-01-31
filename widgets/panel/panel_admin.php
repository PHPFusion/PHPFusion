<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: panel_admin.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * Class panelWidgetAdmin
 */
class panelWidgetAdmin extends \PHPFusion\Page\Composer\Node\ComposeEngine implements \PHPFusion\Page\WidgetAdminInterface {

    private static $widget_data = [];

    private static $instance = NULL;

    public static function widgetInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function excludeReturn() {
    }

    public function validateSettings() {
    }

    public function validateInput() {
        self::$widget_data = [
            'panel_include' => form_sanitizer($_POST['panel_include'], '', 'panel_include')
        ];
        if (fusion_safe()) {
            return \Defender::serialize(self::$widget_data);
        }

        return NULL;
    }

    public function validateDelete() {
    }

    public function displayFormInput() {
        $lang = file_exists(WIDGETS."panel/locale/".LANGUAGE.".php") ? WIDGETS."panel/locale/".LANGUAGE.".php" : WIDGETS."panel/locale/English.php";
        $widget_locale = fusion_get_locale('', $lang);

        self::$widget_data = [
            'panel_include' => '',
        ];
        if (!empty(self::$colData['page_content'])) {
            self::$widget_data = \Defender::unserialize(self::$colData['page_content']);
        }
        // Installed panel is displayed here. The visibility should be also seperately configured
        $panel_alt = strtr($widget_locale['PW_0201'], [
            '[LINK]'  => "<a href='".ADMIN."panels.php".fusion_get_aidlink()."' title='".$widget_locale['PW_0100']."'>",
            '[/LINK]' => "</a>"
        ]);
        $panel_opts = \PHPFusion\Panels::getAvailablePanels();
        unset($panel_opts['none']);
        echo form_select('panel_include', $widget_locale['PW_0200'], self::$widget_data['panel_include'],
            [
                'class'   => 'm-b-0',
                'inline'  => TRUE,
                'options' => $panel_opts,
                'ext_tip' => $panel_alt,
            ]
        );
    }

    public function displayFormButton() {
        $widget_locale = fusion_get_locale('', WIDGETS."/panel/locale/".LANGUAGE.".php");
        $html = form_button('save_widget', $widget_locale['PW_0220'], 'widget', ['class' => 'btn-primary']);
        $html .= form_button('save_and_close_widget', $widget_locale['PW_0221'], 'widget', ['class' => 'btn-success']);
        return $html;
    }

}
