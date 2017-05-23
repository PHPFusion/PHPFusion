<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Comments/comments_admin.php
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
 * Class commentsWidgetAdmin
 */
class commentsWidgetAdmin extends \PHPFusion\Page\Composer\Node\ComposeEngine implements \PHPFusion\Page\WidgetAdminInterface {

    private static $instance = NULL;

    public static function widgetInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function exclude_return() {
    }

    public function validate_input() {
    }

    public function validate_delete() {
        dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_type='C' AND comment_item_id=".self::$data['page_id']);
    }

    public function validate_settings() {
    }

    public function display_form_button() {
    }

    public function display_form_input() {
        $lang = file_exists(WIDGETS."comments/locale/".LANGUAGE.".php") ? WIDGETS."comments/locale/".LANGUAGE.".php" : WIDGETS."comments/locale/English.php";
        $widget_locale = fusion_get_locale('', $lang);

        self::$colData['page_content'] = 'comments';
        self::$colData['page_content_id'] = 0;
        $colId = dbquery_insert(DB_CUSTOM_PAGES_CONTENT, self::$colData, 'save');
        if ($colId) {
            addNotice('success', $widget_locale['CMW_0102']);
        } else {
            addNotice('danger', $widget_locale['CMW_0104']);
        }
        redirect(clean_request('', self::getComposerExclude(), FALSE));
    }

}
