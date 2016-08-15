<?php

/**
 * Class contentWidgetAdmin
 */
class commentsWidgetAdmin extends \PHPFusion\Page\Composer\Network\ComposeEngine {

    public function display_input() {
        self::$colData['page_content'] = 'comments';
        self::$colData['page_content_id'] = 0;
        $colId = dbquery_insert(DB_CUSTOM_PAGES_CONTENT, self::$colData, 'save');
        if ($colId) {
            addNotice('success', 'Page Comments Created');
        } else {
            addNotice('danger', 'Unable to create Comments');
        }
        redirect(clean_request('', self::getComposerExlude(), FALSE));
    }

}