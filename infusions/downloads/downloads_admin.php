<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: downloads.php
| Author: Nick Jones (Digitanium)
| Co-Author: PHP-Fusion Development Team
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
pageAccess('D');
require_once THEMES."templates/admin_header.php";
include INFUSIONS."downloads/locale/".LOCALESET."downloads_admin.php";
include LOCALE.LOCALESET."admin/settings.php";
require_once INCLUDES."infusions_include.php";
$dl_settings = get_settings("downloads");
add_breadcrumb(array('link' => FUSION_SELF.$aidlink, 'title' => $locale['download_0001']));

$allowed_section = array('downloads', 'dlopts', 'sform');
$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'downloads';
$_GET['download_cat_id'] = isset($_GET['download_cat_id']) && isnum($_GET['download_cat_id']) ? $_GET['download_cat_id'] : 0;

$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? 1 : 0;

// master template
$master_tab_title['title'][] = $locale['download_0000'];
$master_tab_title['id'][] = "downloads";
$master_tab_title['icon'][] = "";

$master_tab_title['title'][] = isset($_GET['action']) ? $locale['download_0003'] : $locale['download_0002'];
$master_tab_title['id'][] = "dlopts";
$master_tab_title['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';

$master_tab_title['title'][] = $locale['download_settings'];
$master_tab_title['id'][] = "sform";
$master_tab_title['icon'][] = "";

opentable($locale['download_0001']);
echo opentab($master_tab_title, $_GET['section'], "download_admin", true);
switch($_GET['section']) {
	case "download_category":
		break;
	case "sform":
		add_breadcrumb(array('link' => '', 'title' => $locale['download_settings']));
		include "admin/download_settings.php";
		break;
	case "dlopts":
		add_breadcrumb(array('link' => '', 'title' => $edit ? $locale['download_0003'] : $locale['download_0002']));
		if (dbcount("('download_cat_id')", DB_DOWNLOAD_CATS, "")) {
			include "admin/downloads.php";
		} else {

		}
		break;
	default:
		download_listing();
		break;
}
echo closetab();
require_once THEMES."templates/footer.php";

/* Download Listing */
function download_listing() {
	global $aidlink, $locale;
	echo "<div class='m-t-20'>\n";
	$result = dbcount("(download_cat_id)", DB_DOWNLOAD_CATS." ".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."'" : "")."");
	if (!empty($result)) {
		$result = dbquery("SELECT dc.*,	count(d.download_id) as download_count
		 		FROM ".DB_DOWNLOAD_CATS." dc
		 		LEFT JOIN ".DB_DOWNLOADS." d on dc.download_cat_id = d.download_cat
				".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."'" : "")."
				GROUP BY download_cat_id
				ORDER BY download_cat_name");
		if (dbrows($result)) {
			$i = 0;
			while ($data = dbarray($result)) {
				echo "<div class='panel panel-default'>\n";
				echo "<div class='panel-heading clearfix'>\n";
				echo "<div class='btn-group pull-right m-t-5'>\n";
				echo "<a class='btn btn-default btn-sm' href='".INFUSIONS."downloads/download_cats_admin.php".$aidlink."&amp;action=edit&amp;section=dadd&amp;cat_id=".$data['download_cat_id']."'><i class='fa fa-pencil fa-fw'></i> ".$locale['edit']."</a>\n";
				echo "<a class='btn btn-default btn-sm' href='".INFUSIONS."downloads/download_cats_admin.php".$aidlink."&amp;action=delete&cat_id=".$data['download_cat_id']."' onclick=\"return confirm('".$locale['download_0350']."');\"><i class='fa fa-trash fa-fw'></i> ".$locale['delete']."</a>\n";
				echo "</div>\n";
				echo "<div class='overflow-hide p-r-10'>\n";
				echo "<h4 class='panel-title display-inline-block'><a ".collapse_header_link('download-list', $data['download_cat_id'], $i < 1 ? 1 : 0, 'm-r-10 text-bigger strong').">".$data['download_cat_name']."</a> <span class='badge'>".$data['download_count']."</h4>\n";
				echo "<br/><span class='text-smaller text-uppercase'>".$data['download_cat_language']."</span>";
				echo "</div>\n"; /// end overflow-hide
				echo "</div>\n"; // end panel heading
				echo "<div ".collapse_footer_link('download-list', $data['download_cat_id'], $i < 1 ? 1 : 0).">\n";
				echo "<ul class='list-group m-10'>\n";
				$result2 = dbquery("SELECT download_id, download_title, download_description_short, download_url, download_file, download_image, download_image_thumb FROM ".DB_DOWNLOADS." WHERE download_cat='".$data['download_cat_id']."' ORDER BY download_title");
				if (dbrows($result2) > 0) {
					while ($data2 = dbarray($result2)) {
						$download_url = '';
						if (!empty($data2['download_file']) && file_exists(DOWNLOADS."files/".$data2['download_file'])) {
							// Link to download file changed to : //http://localhost/PHP-Fusion/infusions/downloads/downloads.php?file_id=3
							$download_url = INFUSIONS."downloads/downloads.php?file_id=".$data2['download_id'];
						} elseif (!strstr($data2['download_url'], "http://") && !strstr($data2['download_url'], "../")) {
							$download_url = BASEDIR.$data2['download_url'];
						}
						echo "<li class='list-group-item'>\n";
						echo "<div class='pull-left m-r-10'>\n";
						echo thumbnail(DOWNLOADS."images/".$data2['download_image_thumb'], '50px');
						echo "</div>\n";
						echo "<div class='overflow-hide'>\n";
						echo "<span class='strong text-dark'>".$data2['download_title']."</span><br/>\n";
						echo nl2br(parseubb($data2['download_description_short']));
						echo "<div class='pull-right'>\n";
						echo "<a class='m-r-10' target='_blank' href='$download_url'>".$locale['download_0214']."</a>\n";
						echo "<a class='m-r-10' href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;section=dlopts&amp;download_cat_id=".$data['download_cat_id']."&amp;download_id=".$data2['download_id']."'>".$locale['edit']."</a>\n";
						echo "<a  class='m-r-10' href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;section=dlopts&amp;download_cat_id=".$data['download_cat_id']."&amp;download_id=".$data2['download_id']."' onclick=\"return confirm('".$locale['download_0255']."');\">".$locale['delete']."</a>\n";
						echo "</div>\n";
						echo "</div>\n";
						echo "</li>\n";
					}
				} else {
					echo "<div class='panel-body text-center'>\n";
					echo $locale['download_0250'];
					echo "</div>\n";
				}
				echo "</ul>\n";
				echo "</div>\n"; // panel default
				echo closecollapse();
				$i++;
			}
		} else {
			echo "<div class='well text-center'>".$locale['download_0250']."</div>\n";
		}
	} else {
		echo "<div class='well text-center'>\n";
		echo "".$locale['download_0251']."<br />\n".$locale['download_0252']."<br />\n";
		echo "<a href='".INFUSIONS."downloads/download_cats_admin.php".$aidlink."&amp;section=dadd'>".$locale['download_0253']."</a>".$locale['download_0254'];
		echo "</div>\n";
	}
	echo "</div>\n";
}

function calculate_byte($download_max_b) {
	$calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
	foreach ($calc_opts as $byte => $val) {
		if ($download_max_b/$byte <= 999) {
			return $byte;
		}
	}
	return 1000000;
}