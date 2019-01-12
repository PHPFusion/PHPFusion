<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq/classes/admin/faq_admin_view.inc
| Author: PHP-Fusion Development Team
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

use PHPFusion\BreadCrumbs;

class FaqAdminView extends FaqAdminModel {
    private $allowed_pages = ['faq', 'faq_cat', 'faq_cat_form', 'faq_form', 'submissions', 'settings'];
    private $locale = [];

    public function display_admin() {
        $this->locale = self::get_faqAdminLocale();

        // Back and Check Section
        if (isset($_GET['section']) && $_GET['section'] == 'back') {
            redirect(clean_request('', ['ref', 'section', 'action', 'faq_id', 'cat_id', 'submit_id'], FALSE));
        }
        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $this->allowed_pages) ? $_GET['section'] : $this->allowed_pages[0];

        // Sitetitle
        add_to_title($this->locale['faq_0000']);

        // Handle Breadcrumbs and Titles
        $faqTitle = $this->locale['faq_0000'];
        $faqicon = 'fa fa-question-circle';
        BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => $faqTitle]);

        if ($submissions = dbcount('(submit_id)', DB_SUBMISSIONS, "submit_type='q'")) {
            addNotice("info", sprintf($this->locale['faq_0064'], format_word($submissions, $this->locale['fmt_submission'])));
        }

        if (!empty($_GET['section'])) {
            switch ($_GET['section']) {
                case "settings":
                    BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => $this->locale['faq_0006']]);
                    break;
                case "submissions":
                    BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => $this->locale['faq_0005']]);
                    break;
                default:
            }

            if ($_GET['section'] == 'faq') {
                if (isset($_GET['ref'])) {
                    switch ($_GET['ref']) {
                        case 'faq_form':
                            $faqTitle = (!empty($_GET['faq_id']) && isset($_GET['action']) && $_GET['action'] == 'edit' ? $this->locale['faq_0004'] : $this->locale['faq_0003']);
                            $faqicon = (!empty($_GET['faq_id']) && isset($_GET['action']) && $_GET['action'] == 'edit' ? 'fa fa-pencil m-r-5' : 'fa fa-plus m-r-5');
                            BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => $faqTitle]);
                            break;
                        case 'faq_cat_form':
                            $faqTitle = (!empty($_GET['cat_id']) && isset($_GET['action']) && $_GET['action'] == 'edit' ? $this->locale['faq_0008'] : $this->locale['faq_0007']);
                            $faqicon = (!empty($_GET['cat_id']) && isset($_GET['action']) && $_GET['action'] == 'edit' ? 'fa fa-pencil m-r-5' : 'fa fa-plus m-r-5');
                            BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => $faqTitle]);
                            break;
                    }
                }
            }
        }

        // Handle Tabs
        if (!empty($_GET['ref']) || isset($_GET['submit_id'])) {
            $tab['title'][] = $this->locale['back'];
            $tab['id'][] = 'back';
            $tab['icon'][] = 'fa fa-fw fa-arrow-left';
        }

        $tab['title'][] = $faqTitle;
        $tab['id'][] = 'faq';
        $tab['icon'][] = $faqicon;

        $tab['title'][] = $this->locale['faq_0005'];
        $tab['id'][] = 'submissions';
        $tab['icon'][] = 'fa fa-inbox';

        $tab['title'][] = $this->locale['faq_0006'];
        $tab['id'][] = 'settings';
        $tab['icon'][] = 'fa fa-cogs';

        // Display Content
        opentable($this->locale['faq_0000']);
        echo opentab($tab, $_GET['section'], 'faq_admin', TRUE, '', 'section');
        switch ($_GET['section']) {
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
