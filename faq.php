<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq.php
| Author: Nick Jones (Digitanium)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "maincore.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."faq.php";

add_to_title($locale['global_203']);

if (!isset($_GET['cat_id']) || !isnum($_GET['cat_id'])) {
	opentable($locale['400']);
	echo "<!--pre_faq_idx-->";
	$result = dbquery("SELECT faq_cat_id, faq_cat_name, faq_cat_description FROM ".DB_FAQ_CATS." ORDER BY faq_cat_name");
	$rows = dbrows($result);
	if ($rows) {
		$columns = 2; $i = 0;
		echo "<table cellpadding='0' cellspacing='0' width='100%' class='tbl'>\n<tr>\n";
		while($data = dbarray($result)) {
			if ($i != 0 && ($i % $columns == 0)) { echo "</tr>\n<tr>\n"; }
			$num = dbcount("(faq_id)", DB_FAQS, "faq_cat_id='".$data['faq_cat_id']."'");
			echo "<td valign='top'><a href='".FUSION_SELF."?cat_id=".$data['faq_cat_id']."'>".$data['faq_cat_name']."</a> <span class='small2'>($num)</span>\n";
			if ($data['faq_cat_description']) { echo "<br />\n<span class='small'>".$data['faq_cat_description']."</span>"; }
			echo "</td>\n";
			$i++;
		}
		echo "</tr>\n</table>\n";
	} else {
	echo "<div style='text-align:center'><br />\n".$locale['410']."<br /><br />\n</div>\n";
	}
	closetable();
} else {
	if ($data = dbarray(dbquery("SELECT faq_cat_name FROM ".DB_FAQ_CATS." WHERE faq_cat_id='".$_GET['cat_id']."'"))) {
		add_to_title($locale['global_201'].$data['faq_cat_name']);
		opentable($locale['401'].": ".$data['faq_cat_name']);
		echo "<table cellpadding='0' cellspacing='1' width='100%'>\n<tr>\n";
		echo "<td class='tbl2'>\n<a href='".FUSION_SELF."'>".$locale['400']."</a> &gt;";
		echo "<a href='".FUSION_SELF."?cat_id=".$_GET['cat_id']."'>".$data['faq_cat_name']."</a></td>\n";
		echo "</tr>\n</table>\n";
		$rows = dbcount("(faq_id)", DB_FAQS, "faq_cat_id='".$_GET['cat_id']."'");
		if ($rows) {
			$i = 0; $ii = 1; $columns = 4; $faq_content = "";
			echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
			$result = dbquery("SELECT faq_id, faq_question, faq_answer from ".DB_FAQS." WHERE faq_cat_id='".$_GET['cat_id']."' ORDER BY faq_question");
			$numrows = dbrows($result);
			while ($data = dbarray($result)) {
				if ($i != 0 && ($i % $columns == 0)) { echo "</tr>\n<tr>\n"; }
				echo "<td class='tbl1' width='25%'><a href='#faq_".$data['faq_id']."'>".$data['faq_question']."</a></td>";
				$faq_content .= "<div class='".($ii % 2 == 0 ? "tbl1" : "tbl2")."' style='display:block; padding:10px 5px'>\n";
				$faq_content .= "<a id='faq_".$data['faq_id']."'></a><strong>".$data['faq_question']."</strong><br />\n".nl2br(stripslashes($data['faq_answer']));
				$faq_content .= "<br style='clear:both' /><a href='#content'><span class='small'>".$locale['402']."</span></a><br />\n";
				$faq_content .= "</div>\n";
				$i++; $ii++;
			}
			echo "</tr>\n</table>\n";
			echo "<div style='margin:5px'></div>\n";
			echo "<div class='tbl-border' style='padding:1px'>\n".$faq_content."</div>\n";

			closetable();
		} else {
			echo $locale['411']."\n";
			closetable();
		}
	} else {
		redirect(FUSION_SELF);
	}
}

require_once THEMES."templates/footer.php";
?>