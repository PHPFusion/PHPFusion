<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: weblinks/admin/controllers/weblinks_admin_view.php
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
namespace PHPFusion\Weblinks;

use \PHPFusion\Breadcrumbs;

class WeblinksAdminView extends WeblinksAdminModel {

    private $allowed_pages = array("weblinks", "weblinks_category", "weblinks_form", "submissions", "settings");

    public function display_admin() {

	$this->locale = self::get_WeblinkAdminLocale();

		// Back and Check Section
        if (isset($_GET['section']) && $_GET['section'] == "back") {
            redirect(clean_request("", array("ref", "section", "weblink_id", "action", "cat_id", "weblink_cat_id", "submit_id"), FALSE));
        }
        $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $this->allowed_pages) ? $_GET['section'] : $this->allowed_pages[0];

		// Sitetitle
        add_to_title($this->locale['WLS_0001']);

		// Handle Breadcrumbs and Titles
		$weblinkTitle = $this->locale['WLS_0001'];
		$weblinkCatTitle = $this->locale['WLS_0004'];
		BreadCrumbs::getInstance()->addBreadCrumb(array("link" => INFUSIONS."weblinks/weblinks_admin.php".fusion_get_aidlink(), "title" => $weblinkTitle));

		if (!empty($_GET['section'])){
        	switch ($_GET['section']) {
	            case "weblinks":
				if (isset($_GET['ref']) && $_GET['ref'] == "weblinks_form") {
					$weblinkTitle = (empty($_GET['weblink_id']) ? $this->locale['WLS_0002'] : $this->locale['WLS_0003']);
    	            BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $weblinkTitle]);
				}
        	        break;
            	case "weblinks_category":
                	BreadCrumbs::getInstance()->addBreadCrumb(['link' => INFUSIONS."weblinks/weblinks_admin.php".fusion_get_aidlink()."&amp;section=weblinks_category", 'title' => $weblinkCatTitle]);
				if (isset($_GET['ref']) && $_GET['ref'] == "weblink_cat_form") {
					$weblinkCatTitle = (empty($_GET['cat_id']) ? $this->locale['WLS_0005'] : $this->locale['WLS_0006']);
					BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => $weblinkCatTitle]);
				}
	                break;
            	case "settings":
                	BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $this->locale['WLS_0008']]);
	                break;
    	        case "submissions":
        	        BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $this->locale['WLS_0007']]);
            	    break;
	            default:
    	    }
		}

		// Handle Tabs
        if (!empty($_GET['ref']) || isset($_GET['submit_id'])) {
            $master_title['title'][] = $this->locale['back'];
            $master_title['id'][] = "back";
            $master_title['icon'][] = "fa fa-fw fa-arrow-left";
        }
        $master_title['title'][] = $weblinkTitle;
        $master_title['id'][] = "weblinks";
        $master_title['icon'][] = "fa fa-fw fa-file-text";
        $master_title['title'][] = $weblinkCatTitle;
        $master_title['id'][] = "weblinks_category";
        $master_title['icon'][] = "fa fa-fw fa-folder";
		$master_title['title'][] = $this->locale['WLS_0007']."&nbsp;<span class='badge'>".dbcount("(submit_id)", DB_SUBMISSIONS, "submit_type='l'")."</span>";
        $master_title['id'][] = "submissions";
        $master_title['icon'][] = "fa fa-fw fa-inbox";
        $master_title['title'][] = $this->locale['WLS_0008'];
        $master_title['id'][] = "settings";
        $master_title['icon'][] = "fa fa-fw fa-cogs";

		// Display Content
        opentable($this->locale['WLS_0001']);

        echo opentab($master_title, $_GET['section'], "weblinks_admin", TRUE, "", "section");
        switch ($_GET['section']) {
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
