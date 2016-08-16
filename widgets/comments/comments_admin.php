<?php

/**
 * Class commentsWidgetAdmin
 */
class commentsWidgetAdmin extends \PHPFusion\Page\Composer\Network\ComposeEngine implements \PHPFusion\Page\WidgetAdminInterface {

    public function exclude_return() {
    }

    public function validate_input() {
    }

    public function validate_settings() {
    }

    public function display_form_button() {
    }

    public function display_form_input() {
        $widget_locale = fusion_get_locale('', WIDGETS."comments/locale/".LANGUAGE.".php");
        self::$colData['page_content'] = 'comments';
        self::$colData['page_content_id'] = 0;
        $colId = dbquery_insert(DB_CUSTOM_PAGES_CONTENT, self::$colData, 'save');
        if ($colId) {
            addNotice('success', $widget_locale['0102']);
        } else {
            addNotice('danger', $widget_locale['0104']);
        }
        redirect(clean_request('', self::getComposerExlude(), FALSE));
    }
}