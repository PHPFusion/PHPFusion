<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: news_admin_view.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion\News;

/**
 * Class NewsAdminView
 *
 * @package PHPFusion\News
 */
class NewsAdminView extends NewsAdminModel {

    private $allowed_pages = ["news", "news_category", "news_form", "submissions", "settings"];

    public function displayAdmin() {

        if (check_get('section') && get('section') == "back") {
            redirect(clean_request('', ['ref', 'section', 'news_id', 'action', 'cat_id'], FALSE));
        }

        $locale = self::getNewsAdminLocale();

        $sections = in_array(get('section'), $this->allowed_pages) ? get('section') : $this->allowed_pages[0];

        add_breadcrumb(['link' => INFUSIONS."news/news_admin.php".fusion_get_aidlink(), 'title' => $locale['news_0001']]);

        add_to_title($locale['news_0001']);

        if (!empty($_GET['ref'])) {
            $tab['title'][] = $locale['back'];
            $tab['id'][] = 'back';
            $tab['icon'][] = 'fa fa-arrow-left';
        }

        $news_title = $locale['news_0001'];
        $news_icon = 'fa fa-newspaper-o m-r-5';
        if (check_get('ref') && get('ref') == "news_form") {
            $news_title = $locale['news_0002'];
            $news_icon = 'fa fa-plus m-r-5';
            if (check_get('news_id')) {
                $news_title = $locale['edit'];
                $news_icon = 'fa fa-pencil m-r-5';
            }
        }

        $tab['title'][] = $news_title;
        $tab['id'][] = 'news';
        $tab['icon'][] = $news_icon;

        $news_cat_title = $locale['news_0020'];
        if (check_get('ref') && get('ref') == "news_cat_form") {
            $news_cat_title = $locale['news_0022'];
            if (check_get('cat_id')) {
                $news_cat_title = $locale['news_0021'];
            }
        }
        $edit = (check_get('action') && get('action') == 'edit' && check_get('cat_id') && isnum(get('cat_id')));

        $tab['title'][] = $news_cat_title;
        $tab['id'][] = 'news_category';
        $tab['icon'][] = $edit ? 'fa fa-pencil m-r-5' : 'fa fa-folder m-r-5';

        $tab['title'][] = $locale['news_0023'].' <span class="badge">'.dbcount("(submit_id)", DB_SUBMISSIONS, "submit_type='n'").'</span>';
        $tab['id'][] = 'submissions';
        $tab['icon'][] = 'fa fa-inbox m-r-5';

        $tab['title'][] = $locale['news_0004'];
        $tab['id'][] = 'settings';
        $tab['icon'][] = 'fa fa-cogs m-r-5';

        opentable($locale['news_0001']);
        echo opentab($tab, $sections, 'news_admin', TRUE, '', 'section', ['ref', 'rowstart', 'action', 'submit_id', 'cat_id', 'news_id']);
        switch ($sections) {
            case 'news_category':
                NewsCategoryAdmin::getInstance()->displayNewsAdmin();
                break;
            case 'settings':
                NewsSettingsAdmin::getInstance()->displayNewsAdmin();
                break;
            case 'submissions':
                NewsSubmissionsAdmin::getInstance()->displayNewsAdmin();
                break;
            default:
                NewsAdmin::getInstance()->displayNewsAdmin();
        }
        echo closetab();
        closetable();
    }

}
