<?php

/**
 * Class contentWidgetAdmin
 */
class contentWidgetAdmin extends \PHPFusion\Page\Composer\Network\ComposeEngine {

    public function display_input() {
        self::$colData['page_content'] = self::$data['page_content'];
        self::$colData['page_content_id'] = 0;
        $colId = dbquery_insert(DB_CUSTOM_PAGES_CONTENT, self::$colData, 'save');
        if ($colId) {
            addNotice('success', 'Page Content Created');
        } else {
            addNotice('danger', 'Unable to create Content');
        }
        redirect(clean_request('', self::getComposerExlude(), FALSE));
    }

}