<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: weblinks_admin_view.php
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
namespace PHPFusion\Weblinks;

class WeblinksAdminView extends WeblinksAdminModel {
    private $allowed_pages = ['weblinks', 'weblinks_category', 'weblinks_form', 'submissions', 'settings'];

    public function displayAdmin() {
        $locale = self::getWeblinkAdminLocale();

        // Back and Check Section
        if (check_get('section') && get('section') == "back") {
            redirect(clean_request('', ['ref', 'section', 'weblink_id', 'action', 'cat_id', 'weblink_cat_id', 'submit_id'], FALSE));
        }

        $sections = in_array(get('section'), $this->allowed_pages) ? get('section') : $this->allowed_pages[0];
        // Sitetitle
        add_to_title($locale['WLS_0001']);
        add_breadcrumb(['link' => INFUSIONS."weblinks/weblinks_admin.php".fusion_get_aidlink(), 'title' => $locale['WLS_0001']]);

        // Handle Tabs
        if (check_get('ref') || get('submit_id', FILTER_VALIDATE_INT)) {
            $master_title['title'][] = $locale['back'];
            $master_title['id'][] = "back";
            $master_title['icon'][] = "fa fa-fw fa-arrow-left";
        }
        $master_title['title'][] = $locale['WLS_0001'];
        $master_title['id'][] = "weblinks";
        $master_title['icon'][] = "fa fa-fw fa-file-text";
        $master_title['title'][] = $locale['WLS_0004'];
        $master_title['id'][] = "weblinks_category";
        $master_title['icon'][] = "fa fa-fw fa-folder";
        $master_title['title'][] = $locale['WLS_0007'].' <span class="badge">'.dbcount('(submit_id)', DB_SUBMISSIONS, "submit_type='l'").'</span>';
        $master_title['id'][] = "submissions";
        $master_title['icon'][] = "fa fa-fw fa-inbox";
        $master_title['title'][] = $locale['WLS_0008'];
        $master_title['id'][] = "settings";
        $master_title['icon'][] = "fa fa-fw fa-cogs";

        // Display Content
        opentable($locale['WLS_0001']);

        echo opentab($master_title, $sections, "weblinks_admin", TRUE, "nav-tabs", "section", ['ref', 'rowstart', 'submit_id', 'action', 'weblink_id', 'cat_id']);
        switch ($sections) {
            case "weblinks_category":
                WeblinksCategoryAdmin::getInstance()->displayWeblinksAdmin();
                break;
            case "submissions":
                WeblinksSubmissionsAdmin::getInstance()->displayWeblinksAdmin();
                break;
            case "settings":
                WeblinksSettingsAdmin::getInstance()->displayWeblinksAdmin();
                break;
            default:
                WeblinksAdmin::getInstance()->displayWeblinksAdmin();
        }
        echo closetab();
        closetable();
    }
}
