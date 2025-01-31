<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: faq_admin_view.php
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
namespace PHPFusion\FAQ;

class FaqAdminView extends FaqAdminModel {
    private $allowed_pages = ['faq', 'faq_cat', 'faq_cat_form', 'faq_form', 'submissions', 'settings'];

    public function displayAdmin() {
        $locale = self::getFaqAdminLocale();

        // Back and Check Section
        if (check_get('section') && get('section') == "back") {
            redirect(clean_request('', ['ref', 'section', 'action', 'faq_id', 'cat_id', 'submit_id'], FALSE));
        }
        $sections = in_array(get('section'), $this->allowed_pages) ? get('section') : $this->allowed_pages[0];

        // Sitetitle
        add_to_title($locale['faq_0000']);

        // Handle Breadcrumbs and Titles
        $faqTitle = $locale['faq_0000'];
        $faqicon = 'fa fa-question-circle';
        add_breadcrumb(["link" => FUSION_REQUEST, "title" => $faqTitle]);

        if (!empty($sections)) {
            switch ($sections) {
                case "settings":
                    add_breadcrumb(["link" => FUSION_REQUEST, "title" => $locale['faq_0006']]);
                    break;
                case "submissions":
                    add_breadcrumb(["link" => FUSION_REQUEST, "title" => $locale['faq_0005']]);
                    break;
                default:
            }

            if ($sections == 'faq') {
                if (check_get('ref')) {
                    switch (get('ref')) {
                        case 'faq_form':
                            $faqTitle = (check_get('faq_id') && get('action') && get('action') == 'edit' ? $locale['faq_0004'] : $locale['faq_0003']);
                            $faqicon = (check_get('faq_id') && get('action') && get('action') == 'edit' ? 'fa fa-pencil m-r-5' : 'fa fa-plus m-r-5');
                            add_breadcrumb(["link" => FUSION_REQUEST, "title" => $faqTitle]);
                            break;
                        case 'faq_cat_form':
                            $faqTitle = (check_get('cat_id') && get('action') && get('action') == 'edit' ? $locale['faq_0008'] : $locale['faq_0007']);
                            $faqicon = (check_get('cat_id') && get('action') && get('action') == 'edit' ? 'fa fa-pencil m-r-5' : 'fa fa-plus m-r-5');
                            add_breadcrumb(["link" => FUSION_REQUEST, "title" => $faqTitle]);
                            break;
                    }
                }
            }
        }

        // Handle Tabs
        if (check_get('ref') || get('submit_id', FILTER_VALIDATE_INT)) {
            $tab['title'][] = $locale['back'];
            $tab['id'][] = 'back';
            $tab['icon'][] = 'fa fa-fw fa-arrow-left';
        }
        $tab['title'][] = $faqTitle;
        $tab['id'][] = 'faq';
        $tab['icon'][] = $faqicon;
        $tab['title'][] = $locale['faq_0005'].'&nbsp;<span class="badge">'.dbcount('(submit_id)', DB_SUBMISSIONS, "submit_type='q'").'</span>';
        $tab['id'][] = 'submissions';
        $tab['icon'][] = 'fa fa-inbox';
        $tab['title'][] = $locale['faq_0006'];
        $tab['id'][] = 'settings';
        $tab['icon'][] = 'fa fa-cogs';

        // Display Content
        opentable($locale['faq_0000']);
        echo opentab($tab, $sections, 'faq_admin', TRUE, '', 'section', ['ref', 'section', 'action', 'faq_id', 'cat_id', 'submit_id']);
        switch ($sections) {
            case 'submissions':
                FaqSubmissionsAdmin::getInstance()->displayFaqAdmin();
                break;
            case 'settings':
                FaqSettingsAdmin::getInstance()->displayFaqAdmin();
                break;
            default:
                FaqAdmin::getInstance()->displayFaqAdmin();
        }
        echo closetab();
        closetable();
    }
}
