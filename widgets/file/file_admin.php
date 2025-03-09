<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: file_admin.php
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
 * Class fileWidgetAdmin
 */
class fileWidgetAdmin extends \PHPFusion\Page\Composer\Node\ComposeEngine implements \PHPFusion\Page\WidgetAdminInterface {

    private static $instance = NULL;
    private static $widget_data = [];

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
            //'file_title' => form_sanitizer($_POST['file_title'], '', 'file_title'),
            'file_url'   => form_sanitizer($_POST['file_url'], '', 'file_url'),
        ];
        if (fusion_safe()) {
            return \Defender::serialize(self::$widget_data);
        }

        return NULL;
    }

    public function validateDelete() {
    }

    public function displayFormInput() {
        $lang = file_exists(WIDGETS."file/locale/".LANGUAGE.".php") ? WIDGETS."file/locale/".LANGUAGE.".php" : WIDGETS."file/locale/English.php";
        $widget_locale = fusion_get_locale('', $lang);

        self::$widget_data = [
            'file_title' => '',
            'file_url'   => '',
        ];
        if (!empty(self::$colData['page_content'])) {
            self::$widget_data = \Defender::unserialize(self::$colData['page_content']);
        }
        //echo form_text('file_title', $widget_locale['f0100'], self::$widget_data['file_title'], ['inline' => TRUE, 'required' => TRUE]);
        echo form_text('file_url', $widget_locale['f0102'], self::$widget_data['file_url'], ['inline' => TRUE, 'required' => TRUE]);
    }

    public function displayFormButton() {
        $widget_locale = fusion_get_locale('', WIDGETS."/file/locale/".LANGUAGE.".php");
        //$html = form_button('save_widget', $widget_locale['f0103'], 'widget', ['class' => 'btn-primary']);
        $html = form_button('save_and_close_widget', $widget_locale['f0104'], 'widget', ['class' => 'btn-success']);
        return $html;
    }

}
