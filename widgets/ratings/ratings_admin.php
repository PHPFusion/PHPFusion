<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------*
| Filename: Ratings/ratings_admin.php
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
 * Class ratingsWidgetAdmin
 */
class ratingsWidgetAdmin extends \PHPFusion\Page\Composer\Network\ComposeEngine implements \PHPFusion\Page\WidgetAdminInterface {

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
        dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_type='C' AND rating_item_id=".self::$data['page_id']);
    }

    public function validate_settings() {
    }

    public function display_form_button() {
    }

    public function display_form_input() {
        $widget_locale = fusion_get_locale('', WIDGETS."ratings/locale/".LANGUAGE.".php");
        self::$colData['page_content'] = 'ratings';
        self::$colData['page_content_id'] = 0;
        $colId = dbquery_insert(DB_CUSTOM_PAGES_CONTENT, self::$colData, 'save');
        if ($colId) {
            addNotice('success', $widget_locale['0102']);
        } else {
            addNotice('danger', $widget_locale['0104']);
        }
        redirect(clean_request('', self::getComposerExclude(), FALSE));
    }

}