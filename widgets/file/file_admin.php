<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: File/file_admin.php
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
    private static $widget_data = array();

    public static function widgetInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function exclude_return() {
    }

    public function validate_settings() {
    }

    public function validate_input() {

        self::$widget_data = array(
            'file_title' => form_sanitizer($_POST['file_title'], '', 'file_title'),
            'file_url'   => form_sanitizer($_POST['file_url'], '', 'file_url'),
        );
        if (\defender::safe()) {
            return \defender::serialize(self::$widget_data);
        }
    }

    public function validate_delete() {
    }

    public function display_form_input() {
        $lang = file_exists(WIDGETS."file/locale/".LANGUAGE.".php") ? WIDGETS."file/locale/".LANGUAGE.".php" : WIDGETS."file/locale/English.php";
        $widget_locale = fusion_get_locale('', $lang);

        self::$widget_data = array(
            'file_title' => '',
            'file_url'   => '',
        );
        if (!empty(self::$colData['page_content'])) {
            self::$widget_data = \defender::unserialize(self::$colData['page_content']);
        }
        echo form_text('file_title', $widget_locale['f0100'], self::$widget_data['file_title'], array('inline' => TRUE, 'required' => TRUE));
        echo form_text('file_url', $widget_locale['f0102'], self::$widget_data['file_url'], array('inline' => TRUE, 'required' => TRUE));
    }

    public function display_form_button() {
        $widget_locale = fusion_get_locale('', WIDGETS."/file/locale/".LANGUAGE.".php");
        echo form_button('save_widget', $widget_locale['f0103'], 'widget', array('class' => 'btn-primary'));
        echo form_button('save_and_close_widget', $widget_locale['f0104'], 'widget', array('class' => 'btn-success'));
    }

}