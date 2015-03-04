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
require_once "../maincore.php";
pageAccess('D');
require_once THEMES."templates/admin_header.php";
require_once INCLUDES."html_buttons_include.php";
include LOCALE.LOCALESET."admin/downloads.php";
add_to_breadcrumbs(array('link'=>FUSION_SELF.$aidlink, 'title'=>$locale['download_0001']));
$_GET['download_cat_id'] = isset($_GET['download_cat_id']) && isnum($_GET['download_cat_id']) ? $_GET['download_cat_id'] : 0;
$allowed_section = array('downloads', 'dlopts');
$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'downloads';
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit') ? 1 : 0;
// master template
$master_tab_title['title'][] = $locale['download_0000'];
$master_tab_title['id'][] = "downloads";
$master_tab_title['icon'][] = "";

$master_tab_title['title'][] = isset($_GET['action']) ? $locale['download_0003'] : $locale['download_0002'];
$master_tab_title['id'][] = "dlopts";
$master_tab_title['icon'][] =  $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';

$master_tab_active = tab_active($master_tab_title, $_GET['section'], 1);

opentable($locale['download_0001']);
$message = '';
if (isset($_GET['status'])) {
	if ($_GET['status'] == "sn") {
		$message .= $locale['download_0100'];
	} elseif ($_GET['status'] == "su") {
		$message .= $locale['download_0101'];
	} elseif ($_GET['status'] == "del") {
		$message .= $locale['download_0102'];
	}
}
if ($message != "") {
	echo admin_message($message);
}

echo opentab($master_tab_title, $master_tab_active, 'download-master', 1);
echo opentabbody($master_tab_title['title'][0], 'downloads', $master_tab_active, 1);
echo "<div class='m-t-20'>\n";
download_listing();
echo "</div>\n";
echo closetabbody();
if ($_GET['section'] == 'dlopts') {
	fusion_confirm_exit();
	add_to_breadcrumbs(array('link'=>'', 'title'=>$edit ? $locale['download_0003']: $locale['download_0002']));
	echo opentabbody($master_tab_title['title'][1], 'dlopts', $master_tab_active, 1);
	echo "<div class='m-t-20'>\n";
	download_form();
	echo "</div>\n";
	echo closetabbody();
}
echo closetab();
closetable();


/* Download Form */
function download_form() {
	global $locale, $settings, $userdata, $aidlink, $defender;

	$data = array(
		'download_id' => 0,
		'download_user' => $userdata['user_id'],
		'download_homepage' => '',
		'download_title' => '',
		'download_cat' => 0,
		'download_description_short' => '',
		'download_description' => '',
		'download_keywords' => '',
		'download_image_thumb' => '',
		'download_url' => '',
		'download_file' => '',
		'download_license' => '',
		'download_copyright' => '',
		'download_os' => '',
		'download_version' => '',
		'download_filesize' => '',
		'download_visibility' => 0,
		'download_allow_comments' => 0,
		'download_allow_ratings' => 0,
	);
	$formaction = FUSION_SELF.$aidlink."&amp;section=dlopts";

	/* save */
	if (isset($_POST['save_download'])) {
		$error = 0;
		$data['download_id'] = isset($_POST['download_id']) && isnum($_POST['download_id']) ? $_POST['download_id'] : 0;
		$data['download_user'] = $userdata['user_id'];
		$data['download_homepage'] = isset($_POST['download_homepage']) ? form_sanitizer($_POST['download_homepage'], '', 'download_homepage') : '';
		$data['download_title'] = isset($_POST['download_title']) ? form_sanitizer($_POST['download_title'], '', 'download_title') : '';
		$data['download_cat'] = isset($_POST['download_cat']) ? form_sanitizer($_POST['download_cat'], '', 'download_cat') : '';
		$data['download_description_short'] = isset($_POST['download_description_short']) ? form_sanitizer($_POST['download_description_short'], '', 'download_description_short') : '';
		$data['download_description'] = isset($_POST['download_description']) ? form_sanitizer($_POST['download_description'], '', 'download_description') : '';
		$data['download_keywords'] = isset($_POST['download_keywords']) ? form_sanitizer($_POST['download_keywords'], '', 'download_keywords') : '';
		$data['download_image_thumb'] = isset($_POST['download_image_thumb']) ? form_sanitizer($_POST['download_image_thumb'], '', 'download_image_thumb') : '';
		$data['download_url'] = isset($_POST['download_url']) ? form_sanitizer($_POST['download_url'], '', 'download_url') : '';
		$data['download_file'] = isset($_POST['download_file']) ? form_sanitizer($_POST['download_file'], '', 'download_file') : '';
		$data['download_license'] = isset($_POST['download_license']) ? form_sanitizer($_POST['download_license'], '', 'download_license') : '';
		$data['download_copyright'] = isset($_POST['download_copyright']) ? form_sanitizer($_POST['download_copyright'], '', 'download_copyright') : '';
		$data['download_os'] = isset($_POST['download_os']) ? form_sanitizer($_POST['download_os'], '', 'download_os') : '';
		$data['download_version'] = isset($_POST['download_version']) ? form_sanitizer($_POST['download_version'], '', 'download_version') : '';
		$data['download_filesize'] = isset($_POST['download_filesize']) ? form_sanitizer($_POST['download_filesize'], '', 'download_filesize') : '';
		$data['download_visibility'] = isset($_POST['download_visibility']) ? form_sanitizer($_POST['download_visibility'], '', 'download_visibility') : '';
		$data['download_allow_comments'] = isset($_POST['download_allow_comments']) ? 1 : 0;
		$data['download_allow_ratings'] = isset($_POST['download_allow_ratings']) ? 1 : 0;

		$upload = form_sanitizer($_FILES['download_file'], '', 'download_file');
		if ($upload) {
			// new upload
			$data['download_file'] = $upload['target_file'];
			if ($data['download_filesize'] == "" || isset($_POST['calc_upload'])) {
				$data['download_filesize'] = parsebytesize($upload['source_size']);
			}
		} elseif (isset($_POST['del_upload']) && isset($_GET['download_id']) && isnum($_GET['download_id'])) {
			// delete
			$result2 = dbquery("SELECT download_file FROM ".DB_DOWNLOADS." WHERE download_id='".$_GET['download_id']."'");
			if (dbrows($result2)) {
				$data2 = dbarray($result2);
				if (!empty($data2['download_file']) && file_exists(DOWNLOADS.$data2['download_file'])) {
					@unlink(DOWNLOADS.$data2['download_file']);
				}
			}
			$data['download_file'] = '';
			$data['download_filesize'] = '';
		}
		elseif (isset($_POST['download_file']) && $_POST['download_file'] != "") {
			// post an edited hidden field file name
			$data['download_file'] = $_POST['download_file'];
		}
		elseif ((isset($_POST['download_url']) && $_POST['download_url'] != "")) {
			// post a url
			$data['download_url'] = (isset($_POST['download_url']) ? stripinput($_POST['download_url']) : "");
			$data['download_file'] = '';
		}
		else {
			// no filename
			$defender->stop();
			$defender->addNotice($locale['download_0111']);
		}

		$upload = form_sanitizer($_FILES['download_image'], '', 'download_image');
		if ($upload) {
			// new image
			$data['download_image'] = $upload['image_name'];
			$data['download_image_thumb'] = $upload['thumb1_name'];
		} elseif (isset($_POST['del_image']) && isset($_GET['download_id']) && isnum($_GET['download_id'])) {
			// delete image
			$result = dbquery("SELECT download_image, download_image_thumb FROM ".DB_DOWNLOADS." WHERE download_id='".$_GET['download_id']."'");
			if (dbrows($result)) {
				$data += dbarray($result);
				if (!empty($data['download_image']) && file_exists(DOWNLOADS."images/".$data['download_image'])) {
					@unlink(DOWNLOADS."images/".$data['download_image']);
				}
				if (!empty($data['download_image_thumb']) && file_exists(DOWNLOADS."images/".$data['download_image_thumb'])) {
					@unlink(DOWNLOADS."images/".$data['download_image_thumb']);
				}
			}
			$data['download_image'] = '';
			$data['download_image_thumb'] = '';
		} elseif (isset($_POST['download_image']) && $_POST['download_image'] != "") {
			// posted image file name
			$data['download_image'] = $_POST['download_image'];
			$data['download_image_thumb'] = $_POST['download_image_thumb'];
		}

		$row_check = dbcount("(download_id)", DB_DOWNLOADS, "download_id='".$data['download_id']."'");
		if ($row_check > 0) {
			$data['download_datestamp'] = isset($_POST['update_datestamp']) ? time() : '';
			dbquery_insert(DB_DOWNLOADS, $data, 'update');
			if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&status=su");
		} else {
			dbquery_insert(DB_DOWNLOADS, $data, 'save');
			if (!defined('FUSION_NULL')) redirect(FUSION_SELF.$aidlink."&status=sn");
		}
	}

	/* delete */
	if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['download_id']) && isnum($_GET['download_id']))) {
		$result = dbquery("SELECT download_file, download_image, download_image_thumb FROM ".DB_DOWNLOADS." WHERE download_id='".$_GET['download_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			if (!empty($data['download_file']) && file_exists(DOWNLOADS.$data['download_file'])) {
				@unlink(DOWNLOADS.$data['download_file']);
			}
			if (!empty($data['download_image']) && file_exists(DOWNLOADS."images/".$data['download_image'])) {
				@unlink(DOWNLOADS."images/".$data['download_image']);
			}
			if (!empty($data['download_image_thumb']) && file_exists(DOWNLOADS."images/".$data['download_image_thumb'])) {
				@unlink(DOWNLOADS."images/".$data['download_image_thumb']);
			}
			$result = dbquery("DELETE FROM ".DB_DOWNLOADS." WHERE download_id='".$_GET['download_id']."'");
		}
		redirect(FUSION_SELF.$aidlink."&download_cat_id=".intval($_GET['download_cat_id'])."&status=del");
	}
	/* edit */
	if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['download_id']) && isnum($_GET['download_id']))) {
		$result = dbquery("SELECT * FROM ".DB_DOWNLOADS." WHERE download_id='".$_GET['download_id']."'");
		if (dbrows($result)) {
			$data = dbarray($result);
			$data['download_user'] = isset($_POST['download_user']) ? form_sanitizer($_POST['download_user'], '', 'download_user') : $data['download_user'];
			$data['download_homepage'] = isset($_POST['download_homepage']) ? form_sanitizer($_POST['download_homepage'], '', 'download_homepage') : $data['download_homepage'];
			$data['download_title'] = isset($_POST['download_title']) ? form_sanitizer($_POST['download_title'], '', 'download_title') : $data['download_title'];
			$data['download_cat'] = isset($_POST['download_cat']) ? form_sanitizer($_POST['download_cat'], '', 'download_cat') : $data['download_cat'];
			$data['download_description_short'] = isset($_POST['download_description_short']) ? form_sanitizer($_POST['download_description_short'], '', 'download_description_short') : $data['download_description_short'];
			$data['download_description'] = isset($_POST['download_description']) ? form_sanitizer($_POST['download_description'], '', 'download_description') : $data['download_description'];
			$data['download_keywords'] = isset($_POST['download_keywords']) ? form_sanitizer($_POST['download_keywords'], '', 'download_keywords') : $data['download_keywords'];
			$data['download_image'] = isset($_POST['download_image']) ? form_sanitizer($_POST['download_image'], '', 'download_image') : $data['download_image'];
			$data['download_image_thumb'] = isset($_POST['download_image_thumb']) ? form_sanitizer($_POST['download_image_thumb'], '', 'download_image_thumb') : $data['download_image_thumb'];
			$data['download_url'] = isset($_POST['download_url']) ? form_sanitizer($_POST['download_url'], '', 'download_url') : $data['download_url'];
			$data['download_file'] = isset($_POST['download_file']) ? form_sanitizer($_POST['download_file'], '', 'download_file') : $data['download_file'];
			$data['download_license'] = isset($_POST['download_license']) ? form_sanitizer($_POST['download_license'], '', 'download_license') : $data['download_license'];
			$data['download_copyright'] = isset($_POST['download_copyright']) ? form_sanitizer($_POST['download_copyright'], '', 'download_copyright') : $data['download_copyright'];
			$data['download_os'] = isset($_POST['download_os']) ? form_sanitizer($_POST['download_os'], '', 'download_os') : $data['download_os'];
			$data['download_version'] = isset($_POST['download_version']) ? form_sanitizer($_POST['download_version'], '', 'download_version') : $data['download_version'];
			$data['download_visibility'] = isset($_POST['download_visibility']) ? form_sanitizer($_POST['download_visibility'], '', 'download_visibility') : $data['download_visibility'];
			$data['download_allow_comments'] = isset($_POST['download_allow_comments']) ? form_sanitizer($_POST['download_allow_comments'], 0, 'download_allow_comments') : $data['download_allow_comments'];
			$data['download_allow_ratings'] = isset($_POST['download_allow_ratings']) ? form_sanitizer($_POST['download_allow_ratings'], 0, 'download_allow_ratings') : $data['download_allow_ratings'];
			$formaction = FUSION_SELF.$aidlink."&amp;action=edit&amp;download_cat_id=".$data['download_cat']."&amp;download_id=".$_GET['download_id'];

		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}

	$visibility_opts = array();
	$user_groups = getusergroups();
	while (list($key, $user_group) = each($user_groups)) {
		$visibility_opts[$user_group['0']] = $user_group['1'];
	}

	echo openform('inputform', 'inputform', 'post', $formaction, array('downtime' => 1, 'enctype' => 1));
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-8'>\n";
	openside('');
	echo form_text($locale['download_0200'], 'download_title', 'download_title', $data['download_title'], array('required' => 1, 'error_text'=>$locale['download_0110']));
	echo form_select($locale['download_0203'], 'download_keywords', 'download_keywords', array(), $data['download_keywords'], array('max_length' => 320, 'width'=>'100%', 'tags'=>1, 'multiple' => 1));
	echo form_textarea($locale['download_0202'], 'download_description_short', 'download_description_short', $data['download_description_short'], array('required'=>1, 'error_text'=>$locale['download_0112'], 'maxlength' => '255', 'autosize' => 1));
	// go for multiple.
	if ($settings['download_screenshot']) {
		if (!empty($data['download_image']) && !empty($data['download_image_thumb'])) {
			echo "<img src='".DOWNLOADS."images/".$data['download_image_thumb']."' /><br />\n";
			echo "<label><input type='checkbox' name='del_image' value='1' /> ".$locale['download_0216']."</label>\n";
			echo "<input type='hidden' name='download_image' value='".$data['download_image']."' />";
			echo "<input type='hidden' name='download_image_thumb' value='".$data['download_image_thumb']."' />";
		} else {
			$file_options = array(
				'max_width' => $settings['download_screen_max_w'],
				'max_height' => $settings['download_screen_max_w'],
				'max_byte' => $settings['download_screen_max_b'],
				'type' => 'image',
				'delete_original' => 0,
				'thumbnail_folder' => '',
				'thumbnail' => 1,
				'thumbnail_suffix'=> '_thumb',
				'thumbnail_w'=> $settings['download_thumb_max_w'],
				'thumbnail_h' => $settings['download_thumb_max_h'],
				'thumbnail2' => 0
			);
			echo form_fileinput($locale['download_0220'], 'download_image', 'download_image', DOWNLOADS."images/", '', $file_options); // all file types.
			echo "<small>".sprintf($locale['download_0219'], parsebytesize($settings['download_screen_max_b']), str_replace(',', ' ', ".jpg,.gif,.png"), $settings['download_screen_max_w'], $settings['download_screen_max_h'])."</small>\n";
		}
	}
	closeside('');
	echo "</div>\n<div class='col-xs-12 col-sm-4'>\n";
	openside();
	if ($settings['comments_enabled'] == "0" || $settings['ratings_enabled'] == "0") {
		$sys = "";
		if ($settings['comments_enabled'] == "0" && $settings['ratings_enabled'] == "0") {
			$sys = $locale['comments_ratings'];
		} elseif ($settings['comments_enabled'] == "0") {
			$sys = $locale['comments'];
		} else {
			$sys = $locale['ratings'];
		}
		echo "<div class='well'>".sprintf($locale['download_0256'], $sys)."</div>\n";
	}
	echo form_select_tree($locale['download_0207'], "download_cat", "download_cat", $data['download_cat'], array("no_root" => 1, "placeholder" => $locale['choose'],  'width'=>'100%', "query" => (multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."'" : "")), DB_DOWNLOAD_CATS, "download_cat_name", "download_cat_id", "download_cat_parent");
	echo form_hidden('', 'download_id', 'download_id', $data['download_id']);
	echo form_select($locale['download_0205'], 'download_visibility', 'download_visibility', $visibility_opts, $data['download_visibility'], array('placeholder' => $locale['choose'], 'width'=>'100%'));
	echo form_button($locale['download_0212'], 'save_download', 'save_download', $locale['download_0212'], array('class' => 'btn-success m-r-10', 'icon'=>'fa fa-check-square-o'));
	closeside();
	openside('');
	echo form_checkbox($locale['download_0223'], 'download_allow_comments', 'download_allow_comments', $data['download_allow_comments'], array('class'=>'m-b-0'));
	echo form_checkbox($locale['download_0224'], 'download_allow_ratings', 'download_allow_ratings', $data['download_allow_ratings'], array('class'=>'m-b-0'));
	if (isset($_GET['action']) && $_GET['action'] == "edit") {
		echo form_checkbox($locale['download_0213'], 'update_datestamp', 'update_datestamp', '', array('class'=>'m-b-0'));
	}
	closeside();
	echo "</div>\n</div>\n"; // end row.
	// second row

	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-8'>\n";

	$tab_title['title'][] = $locale['download_0214'];
	$tab_title['id'][] = 'dlf';
	$tab_title['icon'][] = '';
	$tab_title['title'][] = $locale['download_0215'];
	$tab_title['id'][] = 'dll';
	$tab_title['icon'][] = '';
	$tab_active = tab_active($tab_title, 0);

	echo opentab($tab_title, $tab_active, 'downloadtab');
	echo opentabbody($tab_title['title'][0], 'dlf', $tab_active);

	if (!empty($data['download_file'])) {
		echo "<div class='list-group-item m-t-10'>".$locale['download_0214']." - <a href='".DOWNLOADS.$data['download_file']."'>".DOWNLOADS.$data['download_file']."</a>\n";
		echo "<hr/>\n";
		echo form_checkbox($locale['download_0216'], 'del_upload', 'del_upload', '', array('class'=>'m-b-0'));
		echo "</div>\n";
		echo form_hidden('', 'download_file', 'download_file', $data['download_file']);
	} else {
		$file_options = array(
			'max_bytes' => $settings['download_max_b'],
			'valid_ext' => $settings['download_types'],
		);
		echo "<div class='list-group m-t-10'><div class='list-group-item'>\n";
		echo form_fileinput($locale['download_0214'], 'download_file', 'download_file', DOWNLOADS, '', $file_options);
		echo sprintf($locale['download_0218'], parsebytesize($settings['download_max_b']), str_replace(',', ' ', $settings['download_types']))."<br />\n";
		echo "</div>\n";
		echo "<div class='list-group-item'>\n";
		echo "<input type='checkbox' name='calc_upload' id='calc_upload' value='1' /> <label for='calc_upload'>".$locale['download_0217']."</label>\n";
		echo "</div>\n";
		echo "</div>\n";
	}
	echo closetabbody();
	echo opentabbody($tab_title['title'][1], 'dll', $tab_active);
	if (empty($data['download_file'])) {
		echo "<div class='list-group m-t-10'><div class='list-group-item'>\n";
		echo form_text($locale['download_0206'], 'download_url', 'download_url', $data['download_url']);
		echo "</div>\n</div>\n";
	} else {
		echo "<div class='alert alert-info m-t-10'>\n";
		echo "There is a download file attached. Remove it to change to url type";
		echo "</div>\n";
		echo form_hidden('', 'download_url', 'download_url', $data['download_url']);
	}
	echo closetabbody();
	echo closetab();
	openside('');
	echo "<div class='row'>\n<div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
	echo form_text($locale['download_0208'], 'download_license', 'download_license', $data['download_license'], array('inline' => 1));
	echo "</div><div class='col-xs-12 col-sm-6 col-md-6 col-lg-6'>\n";
	echo form_text($locale['download_0222'], 'download_copyright', 'download_copyright', $data['download_copyright'], array('inline' => 1));
	echo "</div></div>\n";
	closeside();
	echo "</div>\n<div class='col-xs-12 col-sm-4'>\n";
	openside();
	echo form_text($locale['download_0209'], 'download_os', 'download_os', $data['download_os']);
	echo form_text($locale['download_0210'], 'download_version', 'download_version', $data['download_version']);
	echo form_text($locale['download_0221'], 'download_homepage', 'download_homepage', $data['download_homepage']);
	echo form_text($locale['download_0211'], 'download_filesize', 'download_filesize', $data['download_filesize']);
	closeside();
	echo "</div>\n</div>\n"; // end row.
	echo form_textarea($locale['download_0201'], 'download_description', 'download_description', $data['download_description'], array('no_resize' => '1', 'form_name' => 'inputform', 'html' => 1, 'autosize' => 1, 'preview' => 1));
	echo form_hidden('', 'download_user', 'download_user', $userdata['user_id']);
	echo "<div class='m-t-20'>\n";
	echo form_button($locale['download_0212'], 'save_download', 'save_download2', $locale['download_0212'], array('class' => 'btn-success m-r-10', 'icon'=>'fa fa-check-square-o'));
	if (isset($_GET['action']) && $_GET['action'] == "edit") {
		echo "<button type='reset' name='reset' value='".$locale['download_0225']."' class='button btn btn-default' onclick=\"location.href='".FUSION_SELF.$aidlink."';\"/>".$locale['download_0225']."</button>";
	}
	echo "</div>\n";
	echo closeform();
}

/* Download Listing */
function download_listing() {
	global $aidlink, $locale;
	$result = dbcount("(download_cat_id)", DB_DOWNLOAD_CATS." ".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."'" : "")."");
	if (!empty($result)) {
		$result = dbquery("SELECT download_cat_id, download_cat_name FROM ".DB_DOWNLOAD_CATS." ".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."'" : "")." ORDER BY download_cat_name");
		if (dbrows($result)) {
			echo opencollapse('download-list');
			while ($data = dbarray($result)) {
				$result2 = dbquery("SELECT download_id, download_title, download_description_short, download_url, download_file, download_image FROM ".DB_DOWNLOADS." WHERE download_cat='".$data['download_cat_id']."' ORDER BY download_title");
				$rows = dbrows($result2);
				?>
				<div class='panel panel-default'>
					<div class='panel-heading'>
						<div class='row'>
							<div class='col-xs-12 col-sm-8'>
								<?php
								echo "<span class='display-inline-block text-bigger strong m-t-10'><a ".collapse_header_link('download-list', $data['download_cat_id'], '0', 'm-r-10').">".$data['download_cat_name']."</a></span>\n";
								echo "<span class='badge'>".number_format($rows)."</span>\n";
								?>
							</div>
							<div class='col-xs-12 col-sm-4'>
								<?php
								echo "<div class='btn-group pull-right'>\n";
								echo "<a class='btn btn-default btn-sm' href='".ADMIN."download_cats.php".$aidlink."&amp;action=edit&amp;section=dadd&amp;cat_id=".$data['download_cat_id']."'>".$locale['edit']."</a>\n";
								echo "<a class='btn btn-danger btn-sm' href='".ADMIN."download_cats.php".$aidlink."&amp;action=delete&cat_id=".$data['download_cat_id']."'><i class='fa fa-trash'></i> ".$locale['delete']."</a>\n";
								echo "</div>\n";
								?>
							</div>
						</div>
					</div>

					<?php
					if (dbrows($result2) != 0) {
						echo "<div ".collapse_footer_link('download-list', $data['download_cat_id'], '0').">\n";
							echo "<div class='list-group p-15'>\n";
								while($data2 = dbarray($result2)) {
									$download_url = $data2['download_url'];
									if (!empty($data2['download_file']) && file_exists(DOWNLOADS.$data2['download_file'])) {
									$download_url = DOWNLOADS.$data2['download_file'];
									} elseif (!strstr($data2['download_url'], "http://") && !strstr($data2['download_url'], "../")) {
									$download_url = BASEDIR.$data2['download_url'];
									}
									echo "<div class='list-group-item clearfix'>\n";
										echo "<div class='pull-left m-r-10'>\n";
										echo thumbnail(DOWNLOADS."images/".$data2['download_image'], '80px');
										echo "</div>\n";

										echo "<div class='overflow-hide'>\n";

												echo "<span class='strong text-dark'>".$data2['download_title']."</span><br/>\n";
												echo nl2br(parseubb($data2['download_description_short']));

												echo "<div class='pull-right'>\n";
												echo "<a class='m-r-10' href='$download_url'>".$locale['download_0001']."</a>\n";
												echo "<a class='m-r-10' href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;section=dlopts&amp;download_cat_id=".$data['download_cat_id']."&amp;download_id=".$data2['download_id']."'>".$locale['edit']."</a>\n";
												echo "<a  class='m-r-10' href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;section=dlopts&amp;download_cat_id=".$data['download_cat_id']."&amp;download_id=".$data2['download_id']."' onclick=\"return confirm('".$locale['download_0255']."');\">".$locale['delete']."</a>\n";
												echo "</div>\n";

										echo "</div>\n";
									echo "</div>\n";
								}
							echo "</div>\n";
						echo "</div>\n";
					}
					?>
				</div>
				<?php
				}
			echo closecollapse();
		} else {
			echo "<div class='well text-center'>".$locale['download_0250']."</div>\n";
		}

	} else {
		echo "<div class='well text-center'>\n";
		echo "".$locale['download_0251']."<br />\n".$locale['download_0252']."<br />\n";
		echo "<a href='download_cats.php".$aidlink."'>".$locale['download_0253']."</a>".$locale['download_0254']."</div>\n";
		echo "</div>\n";
	}
}

add_to_jquery("
    $('#shortdesc_display').show();
    $('#calc_upload').bind('click', function() {
        if ($('#calc_upload').attr('checked')) {
            $('#download_filesize').attr('readonly', 'readonly');
            $('#download_filesize').val('');
        } else {
           $('#download_filesize').removeAttr('readonly');
        }
    });
    ");

require_once THEMES."templates/footer.php";
?>
