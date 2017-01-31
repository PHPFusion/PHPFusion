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

    private $allowed_pages = array('faq', 'faq_cat', 'faq_cat_form', 'faq_form', 'submissions', 'settings');
    private $locale = [];

    public function display_admin() {
        $this->locale = self::get_faqAdminLocale();

        // Back and Check Section
        if (isset($_GET['section']) && $_GET['section'] == 'back') {
            redirect(clean_request('', array('ref', 'section', 'action', 'faq_id', 'cat_id', 'submit_id'), FALSE));
        }
        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $this->allowed_pages) ? $_GET['section'] : $this->allowed_pages[0];

        // Sitetitle
        add_to_title($this->locale['faq_0000']);

        // Handle Breadcrumbs and Titles
        BreadCrumbs::getInstance()->addBreadCrumb(array("link" => INFUSIONS."faq/faq_admin.php".fusion_get_aidlink(), "title" => $this->locale['faq_0001']));

        $faqTitle = $this->locale['faq_0002'];
        if ($_GET['section'] == 'faq') {
            if (isset($_GET['ref'])) {
                switch ($_GET['ref']) {
                    case 'faq_form':
                        $faqTitle = (!empty($_GET['faq_id']) && isset($_GET['action']) && $_GET['action'] == 'edit' ? $this->locale['faq_0004'] : $this->locale['faq_0003']);
                        BreadCrumbs::getInstance()->addBreadCrumb(
                            array(
                                'link'  => INFUSIONS.'faq/faq_admin.php'.fusion_get_aidlink().'&amp;section=faq&amp;ref=faq_form',
                                'title' => $faqTitle
                            )
                        );
                        break;
                    case 'faq_cat_form':
                        $faqTitle = (!empty($_GET['faq_cat_id']) && isset($_GET['action']) && $_GET['action'] == 'edit' ? $this->locale['faq_0008'] : $this->locale['faq_0007']);
                        BreadCrumbs::getInstance()->addBreadCrumb(
                            array(
                                'link'  => INFUSIONS.'faq/faq_admin.php'.fusion_get_aidlink().'&amp;section=faq&amp;ref=faq_form',
                                'title' => $faqTitle
                            )
                        );
                        break;
                }
            }
        }

        // Handle Tabs
        if (!empty($_GET['ref']) || isset($_GET['submit_id'])) {
            $master_title['title'][] = $this->locale['back'];
            $master_title['id'][] = 'back';
            $master_title['icon'][] = 'fa fa-fw fa-arrow-left';
        }

        $master_title['title'][] = $faqTitle;
        $master_title['id'][] = 'faq';
        $master_title['icon'][] = 'fa fa-question-circle';

        $master_title['title'][] = $this->locale['faq_0005'].'&nbsp;<span class=\'badge\'>'.dbcount('(submit_id)', DB_SUBMISSIONS, "submit_type='q'").'</span>';
        $master_title['id'][] = 'submissions';
        $master_title['icon'][] = 'fa fa-inbox';

        $master_title['title'][] = $this->locale['faq_0006'];
        $master_title['id'][] = 'settings';
        $master_title['icon'][] = 'fa fa-cogs';

        // Display Content
        opentable($this->locale['faq_0000']);
        echo opentab($master_title, $_GET['section'], 'faq_admin', TRUE, '', 'section');
        switch ($_GET['section']) {
            case 'submissions':
                FaqSubmissionsAdmin::getInstance()->displayFaqAdmin();
                BreadCrumbs::getInstance()->addBreadCrumb(
                    array(
                        'link'  => INFUSIONS.'faq/faq_admin.php'.fusion_get_aidlink().'&amp;section=submissions',
                        'title' => $this->locale['faq_0005']
                    )
                );
                break;
            case 'settings':
                FaqSettingsAdmin::getInstance()->displayFaqAdmin();
                BreadCrumbs::getInstance()->addBreadCrumb(
                    array(
                        'link'  => INFUSIONS.'faq/faq_admin.php'.fusion_get_aidlink().'&amp;section=settings',
                        'title' => $this->locale['faq_0006'])
                );
                break;
            default:
                FaqAdmin::getInstance()->displayFaqAdmin();
        }
        echo closetab();
        closetable();
    }
}