<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: /faq/upgrade/1.2.upgrade.inc
| Upgrade FAQ to Version 9.0.2 (RC5)
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
$inf_version = '1.2';
require_once (INCLUDES.'infusions_include.php');
// Add multilingual support
if (!defined("DB_FAQ_CATS")) {
define("DB_FAQ_CATS", DB_PREFIX."faq_cats");
}

if (column_exists(DB_FAQ_CATS, 'faq_cat_id', FALSE)) {
	$result = dbquery("SELECT * FROM ".DB_FAQ_CATS);
	if (dbrows($result)) {
            $lid = 0;
	    while ($data = dbarray($result)) {
	        $resulta = dbquery("INSERT INTO ".DB_FAQS." (faq_cat_id, faq_question, faq_answer, faq_breaks, faq_name, faq_datestamp, faq_visibility, faq_status, faq_language) VALUES ('0', '".$data['faq_cat_name']."', '".$data['faq_cat_description']."', 'y', '".fusion_get_userdata('user_id')."', '".time()."', '0', '1', '".(!empty($data['faq_cat_language']) ? $data['faq_cat_language'] : fusion_get_settings('locale'))."')");
        	$lid = dblastid();
			$result0 = dbquery("SELECT * FROM ".DB_FAQS." WHERE faq_cat_id='".$data['faq_cat_id']."'");
				if (dbrows($result0)) {
				    while ($data0 = dbarray($result0)) {
    			    $inf_updatedbrow[] = DB_FAQS." SET faq_cat_id='".$lid."', faq_breaks='y', faq_name='".fusion_get_userdata('user_id')."', faq_datestamp='".time()."', faq_visibility='0', faq_status='1', faq_language='".(!empty($data['faq_cat_language']) ? $data['faq_cat_language'] : fusion_get_settings('locale'))."' WHERE faq_cat_id='".$data['faq_cat_id']."'";

			    }
		    }

	    }
	}
}

if (column_exists(DB_FAQ_CATS, "faq_cat_id", FALSE)) {
    dbquery("DROP TABLE ".DB_FAQ_CATS."");
}
