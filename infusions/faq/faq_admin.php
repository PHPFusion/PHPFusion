<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: faq/faq_admin.php
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
require_once "../../maincore.php";
pageAccess('FQ');
require_once THEMES."templates/admin_header.php";
require_once INCLUDES."html_buttons_include.php";
include INFUSIONS."faq/locale/".LOCALESET."faq_admin.php";
add_breadcrumb(array('link' => INFUSIONS."faq/faq_admin.php".$aidlink, 'title' => $locale['502']));
$show_faqs = 5;

$data = array(
	"faq_cat_name" => "",
	"faq_cat_description" => "",
	"faq_cat_language" => LANGUAGE,
);
$locale['faq_0100'] = "FAQs";
$locale['faq_0101'] = "Currrent FAQs";
$locale['faq_0102'] = "FAQ Category";
$locale['faq_0102b'] = "Add FAQ";
$locale['faq_0102c'] = "Edit FAQ";
$locale['faq_0102d'] = "Edit FAQ Category";
$locale['faq_0103'] = "Category Name";
$locale['faq_0104'] = "Questions Count";
$locale['faq_0105'] = "Category Id";
$locale['faq_0106'] = "Options";
$locale['faq_0107'] = "Edit";
$locale['faq_0108'] = "Delete";
$locale['faq_0109'] = "Delete this FAQ Category?";
$locale['faq_0110'] = "Question:";
$locale['faq_0111'] = "Answer:";
$locale['faq_0112'] = "Delete this Question?";
$locale['faq_0113'] = "No Frequently Asked Question defined";
$locale['faq_0114'] = "Listing %d of total %d FAQs entries";
$locale['faq_0115'] = "Listing %d of total %d Categories";


// Faq Category form
$locale['faq_0200'] = "Category Name";
$locale['faq_0201'] = "Fill in category name";
$locale['faq_0202'] = "Category Description";
$locale['faq_0203'] = "Save Category";
$locale['faq_0204'] = "FAQ Category saved";
$locale['faq_0205'] = "FAQ Category updated";
$locale['faq_0206'] = "FAQ Category deleted";
$locale['faq_0207'] = "FAQ Category cannot be deleted because there are %d questions in this category";
// Faq Form
$locale['faq_0300'] = "Category";
$locale['faq_0301'] = "Question";
$locale['faq_0302'] = "Answer";
$locale['faq_0303'] = "Save FAQ";
$locale['faq_0304'] = "FAQ is not available currently because there are no FAQ Category defined. Please click <a href='%s'>here</a> to add a FAQ category";
$locale['faq_0305'] = "FAQ added";
$locale['faq_0306'] = "FAQ updated";
$locale['faq_0307'] = "FAQ deleted";

$faq_edit = isset($_GET['action']) && $_GET['action'] == "edit" && isset($_GET['faq_id']) && isnum($_GET['faq_id']) ? TRUE : FALSE;
$faqCat_edit = isset($_GET['action']) && $_GET['action'] == "edit" && isset($_GET['cat_id']) && isnum($_GET['cat_id']) ? TRUE : FALSE;
opentable($locale['faq_0100']);
$faq_tab['title'][] = $locale['faq_0101'];
$faq_tab['id'][] = "faq-list";
$faq_tab['icon'][] = "";
$faq_tab['title'][] = $faq_edit ? $locale['faq_0102c'] : $locale['faq_0102b'];
$faq_tab['id'][] = "faqs";
$faq_tab['icon'][] = "";
$faq_tab['title'][] = $faqCat_edit ? $locale['faq_0102d'] : $locale['faq_0102'];
$faq_tab['id'][] = "faq-category";
$faq_tab['icon'][] = "";
$allowed_pages = array("faq-list", "faq-category", "faqs");

$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_pages) ? $_GET['section'] : "faq-list";

echo opentab($faq_tab, $_GET['section'], "faq_tab", "m-t-20");
switch ($_GET['section']) {
	case "faq-category":
		add_breadcrumb(array("link"=>"", "title"=>
			$faqCat_edit ? $locale['faq_0102d'] : $locale['faq_0102']
					   ));
		include "admin/faq_cats.php";
		break;
	case "faqs":
		add_breadcrumb(array("link"=>"", "title"=>
			$faq_edit ? $locale['faq_0102c'] : $locale['faq_0102b']
					   ));
		include "admin/faqs.php";
		break;
	default:
		faq_listing();
}
echo closetab();
closetable();
if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['faq_id']) && isnum($_GET['faq_id'])) && (isset($_GET['t']) && $_GET['t'] == "faq")) {
	$faq_count = dbcount("(faq_id)", DB_FAQS, "faq_id='".$_GET['faq_id']."'");
	$result = dbquery("DELETE FROM ".DB_FAQS." WHERE faq_id='".$_GET['faq_id']."'");
	addNotice('warning', $locale['512']);
	if ($faq_count) {
		redirect(FUSION_SELF.$aidlink."&faq_cat_id=".intval($_GET['faq_cat_id']));
	} else {
		redirect(FUSION_SELF.$aidlink."&status=del");
	}
}
require_once THEMES."templates/footer.php";
function faq_listing() {
	global $locale, $aidlink, $show_faqs;
	$total_cat_count = dbcount("(faq_cat_id)", DB_FAQ_CATS, multilang_table("FQ") ? "faq_cat_language='".LANGUAGE."'" : "");
	$_GET['show_faq'] = (isset($_GET['show_faq']) && isnum($_GET['show_faq'])) ? $_GET['show_faq'] : 0;
	$_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart'])
		&& $_GET['rowstart'] <= $total_cat_count
	) ? $_GET['rowstart'] : 0;

	$result = dbquery("SELECT fc.faq_cat_id, fc.faq_cat_name,
	count(faq_id) 'faq_count'
	FROM ".DB_FAQ_CATS." fc
	left join ".DB_FAQS." f using (faq_cat_id)
	".(multilang_table("FQ") ? "WHERE fc.faq_cat_language='".LANGUAGE."'" : "")."
	group by fc.faq_cat_id
	ORDER BY fc.faq_cat_name
	limit ".intval($_GET['rowstart']).", ".intval($show_faqs)."
	");

	$cat_rows = dbrows($result);
	if ($cat_rows > 0) {
		echo "<div class='m-t-10'>\n";
		echo "<div class='clearfix'>\n";
		if ($total_cat_count > $cat_rows) {
			echo "<div class='pull-right'>\n";
			echo makepagenav($_GET['rowstart'], $show_faqs, $total_cat_count,  3, FUSION_SELF.$aidlink."&amp;", "rowstart");
			echo "</div>\n";
		}
		echo sprintf($locale['faq_0115'], $cat_rows, $total_cat_count);
		echo "</div>\n";
		echo "</div>\n";

		echo "<table class='table table-responsive table-striped m-t-20'>\n<thead><tr>\n";
		echo "<th class='col-xs-4'>".$locale['faq_0103']."</th>\n";
		echo "<th>".$locale['faq_0104']."</th>\n";
		echo "<th>".$locale['faq_0105']."</th>\n";
		echo "<th class='text-right'>".$locale['faq_0106']."</th>\n";
		echo "</tr>\n";
		echo "</thead>\n<tbody>\n";
		while ($data = dbarray($result)) {
			echo "<tr>\n";
			// let us use 2 page nav. :)
			echo "<td><a href='".FUSION_SELF.$aidlink."&amp;show_faq=".$data['faq_cat_id']."'>".$data['faq_cat_name']."</a></td>\n";
			echo "<td><span class='badge'>".$data['faq_count']."</span></td>\n";
			echo "<td>".$data['faq_cat_id']."</td>\n";
			echo "<td class='text-right'>
			<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;cat_id=".$data['faq_cat_id']."&amp;section=faq-category'>".$locale['faq_0107']."</a> -\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;cat_id=".$data['faq_cat_id']."&amp;section=faq-category' onclick=\"return confirm('".$locale['faq_0109']."');\">".$locale['faq_0108']."</a></td>\n";
			echo "</tr>\n";
			if ($_GET['show_faq'] == $data['faq_cat_id']) {
				show_faq($data['faq_cat_id'], $data['faq_count']);
			}
		}
		// simple toggle
		add_to_jquery("
		$('.faq_toggle').bind('click', function() {
			var faqs = $(this).data('target');
			var faq_length = $('#' + faqs + ':visible').length;
			$('.faq_list').hide();
			if (faq_length > 0) {
				$('#'+faqs).hide();
			} else {
				$('#'+faqs).show();
			}
		});
		");
		echo "</table>\n";
	} else {
		echo "<div class='well text-center'>".$locale['545']."<br />\n</div>\n";
	}
}

function show_faq($faq_cat_id, $total_faq_count) {
	global $locale, $aidlink, $show_faqs;
	// xss
	$_GET['faq_start'] = isset($_GET['faq_start'])
						 && isnum($_GET['faq_start']) && $_GET['faq_start'] <= $total_faq_count ? $_GET['faq_start'] : 0;

	echo "<tr id='faq_".$faq_cat_id."' class='faq_list'>\n<td colspan='4'>\n";
	echo "<div class='panel panel-default'>\n";
	echo "<div class='panel-body'>\n";
	// need to improve a faq ordering .. it's hard manage content
	$result2 = dbquery("SELECT faq_id, faq_question, faq_answer
			FROM ".DB_FAQS." WHERE faq_cat_id='".intval($faq_cat_id)."'
			ORDER BY faq_id
			limit ".intval($_GET['faq_start']).", ".intval($show_faqs)."
			");
	$faq_rows = dbrows($result2);
	if ($faq_rows) {
		echo "<table class='table table-responsive table-hover table-striped'>\n";
		echo "<tr><th colspan='2' style='border-top:0;'>\n";
		echo "<div class='pull-right'>".sprintf($locale['faq_0114'], $faq_rows, $total_faq_count)."</div>\n";
		if ($total_faq_count > $faq_rows) {
			echo makepagenav($_GET['faq_start'], $show_faqs, $total_faq_count,  3, FUSION_SELF.$aidlink."&amp;show_faq=".$faq_cat_id."&amp;", "faq_start");
		}
		echo "</td></th>\n";
		echo "<tbody>\n";
		while ($data2 = dbarray($result2)) {
			echo "<tr>\n<td>\n
					<strong>".$locale['faq_0110']." ".$data2['faq_question']."</strong><br/>\n
					<strong>".$locale['faq_0111']."</strong>".trim_text($data2['faq_answer'], 60)."<br/>\n
					</td>\n";
			echo "<td align='right'>\n<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;faq_cat_id=".$faq_cat_id."&amp;faq_id=".$data2['faq_id']."&amp;section=faqs'>".$locale['faq_0107']."</a> -\n";
			echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;faq_cat_id=".$faq_cat_id."&amp;faq_id=".$data2['faq_id']."&amp;section=faqs' onclick=\"return confirm('".$locale['faq_0112']."');\">".$locale['faq_0108']."</a></td>\n";
			echo "</tr>\n";
		}
		echo "</tbody>\n</table>\n";
	} else {
		echo $locale['faq_0113'];
	}
	echo "</div>\n</div></td></tr>";
}