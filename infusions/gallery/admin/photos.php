<?php

// 2 tabs. 1 for normal , 2 for batch
$phototab['title'][] = $locale['gallery_0009'];
$phototab['id'][] = "single_photo";
$phototab['icon'][] = "";
$phototab['title'][] = $locale['gallery_0010'];
$phototab['id'][] = "mass_photo";
$phototab['icon'][] = "";
$tab_active = tab_active($phototab, 0);
echo opentab($phototab, $tab_active, "phototabs", FALSE, "m-t-20");
echo opentabbody($phototab['title'][0], $phototab['id'][0], $tab_active);
photo_form();
echo closetabbody();
echo opentabbody($phototab['title'][1], $phototab['id'][1], $tab_active);
mass_photo_form();
echo closetabbody();
echo closetab();
// done.
function photo_form() {
	global $locale, $aidlink, $userdata, $gll_settings, $defender, $photo_edit;
	$albumRows = dbcount("(album_id)", DB_PHOTO_ALBUMS, multilang_table("PG") ? "album_language='".LANGUAGE."'" : "");
	if ($albumRows) {
		$data = array(
			"photo_id" => 0,
			"photo_title" => "",
			"album_id" => 0,
			"photo_description" => "",
			"photo_keywords" => "",
			"photo_filename" => "",
			"photo_thumb1" => "",
			"photo_thumb2" => "",
			"photo_datestamp" => time(),
			"photo_user" => $userdata['user_id'],
			"photo_views" => 0,
			"photo_order" => 0,
			"photo_allow_comments" => TRUE,
			"photo_allow_ratings" => TRUE,
		);
		if (isset($_POST['save_photo'])) {
			$data = array(
				"photo_id" => form_sanitizer($_POST['photo_id'], "", "photo_id"),
				"photo_title" => form_sanitizer($_POST['photo_title'], "", "photo_title"),
				"album_id" => form_sanitizer($_POST['album_id'], "", "album_id"),
				"photo_description" => form_sanitizer($_POST['photo_description'], "", "photo_description"),
				"photo_keywords" => form_sanitizer($_POST['photo_keywords'], "", "photo_keywords"),
				"photo_order" => form_sanitizer($_POST['photo_order'], "", "photo_order"),
				"photo_datestamp" => form_sanitizer($_POST['photo_datestamp'], "", "photo_datestamp"),
				"photo_user" => form_sanitizer($_POST['photo_user'], "", "photo_user"),
				"photo_allow_comments" => isset($_POST['photo_allow_comments']) ? TRUE : FALSE,
				"photo_allow_ratings" => isset($_POST['photo_allow_ratings']) ? TRUE : FALSE,
				"photo_views" => 0,
				"photo_filename" => "",
				"photo_thumb1" => "",
				"photo_thumb2" => "",
			);
			if (empty($data['photo_order'])) {
				$data['photo_order'] = dbresult(dbquery("SELECT MAX(photo_order) FROM ".DB_PHOTOS."
				where album_id='".$data['album_id']."'"), 0)+1;
			}
			if (defender::safe()) {
				if (!empty($_FILES['photo_image']) && is_uploaded_file($_FILES['photo_image']['tmp_name'])) {
					$upload = form_sanitizer($_FILES['photo_image'], "", "photo_image");
					if (empty($upload['error'])) {
						$data['photo_filename'] = $upload['image_name'];
						$data['photo_thumb1'] = $upload['thumb1_name'];
						$data['photo_thumb2'] = $upload['thumb2_name'];
					}
				} elseif ($data['photo_id'] > 0) { // during edit, photo_id is not 0.
					// delete image
					if (isset($_POST['del_image'])) {
						// album_id
						$result = dbquery("select photo_filename, photo_thumb1, photo_thumb2 FROM ".DB_PHOTOS." WHERE photo_id='".$data['photo_id']."'");
						if (dbrows($result) > 0) {
							$pData = dbarray($result);
							if ($pData['photo_filename'] && file_exists(IMAGES_G.$pData['photo_filename'])) {
								unlink(IMAGES_G.$pData['photo_filename']);
							}
							if ($pData['photo_thumb1'] && file_exists(IMAGES_G.$pData['photo_thumb1'])) {
								unlink(IMAGES_G_T.$pData['photo_thumb1']);
							}
							if ($pData['photo_thumb2'] && file_exists(IMAGES_G.$pData['photo_thumb2'])) {
								unlink(IMAGES_G_T.$pData['photo_thumb2']);
							}
							$data['photo_filename'] = "";
							$data['photo_thumb1'] = "";
							$data['photo_thumb2'] = "";
						}
					} else {
						$data['photo_filename'] = form_sanitizer($_POST['photo_filename'], "", "photo_filename");
						$data['photo_thumb2'] = form_sanitizer($_POST['photo_thumb2'], "", "photo_thumb2");
						$data['photo_thumb1'] = form_sanitizer($_POST['photo_thumb1'], "", "photo_thumb1");
					}
				} else {
					// because we require the photo image must be uploaded.
					$defender->stop();
					$defender->setInputError("photo_image");
					addNotice("danger", $locale['photo_0014']);
				}
			}
			if (defender::safe()) {
				if (dbcount("(photo_id)", DB_PHOTOS, "photo_id='".intval($data['photo_id'])."'")) {
					// update album
					$result = dbquery_order(DB_PHOTOS, $data['photo_order'], 'photo_order', $data['photo_id'], 'photo_id', FALSE, FALSE, FALSE, '', 'update');
					dbquery_insert(DB_PHOTOS, $data, "update");
					addNotice('success', $locale['photo_0015']);
					redirect(FUSION_SELF.$aidlink."&amp;album_id=".$data['album_id']);
				} else {
					// create album
					$result = dbquery_order(DB_PHOTOS, $data['photo_order'], 'photo_order', 0, "photo_id", FALSE, FALSE, FALSE, '', 'save');
					dbquery_insert(DB_PHOTOS, $data, "save");
					addNotice('success', $locale['photo_0016']);
					redirect(FUSION_SELF.$aidlink."&amp;album_id=".$data['album_id']);
				}
			}
		}
		if ($photo_edit) {
			$result = dbquery("select * from ".DB_PHOTOS." WHERE photo_id='".intval($_GET['photo_id'])."'");
			if (dbrows($result) > 0) {
				$data = dbarray($result);
			} else {
				redirect(FUSION_SELF.$aidlink);
			}
		}
		echo openform('photoform', 'post', FUSION_REQUEST, array('enctype' => TRUE, 'class' => 'm-t-20'));
		echo "<div class='row'>\n<div class='col-xs-12 col-sm-8'>\n";
		echo form_hidden("photo_id", "", $data['photo_id']);
		echo form_hidden("photo_datestamp", "", $data['photo_datestamp']);
		echo form_hidden("photo_user", "", $data['photo_user']);
		echo form_text("photo_title", $locale['photo_0001'], $data['photo_title'], array(
			"required" => TRUE,
			"placeholder" => $locale['photo_0002'],
			"inline" => TRUE
		));
		echo form_select('photo_keywords', $locale['photo_0006'], $data['photo_keywords'], array(
			'placeholder' => $locale['photo_0007'],
			'inline' => TRUE,
			'multiple' => TRUE,
			"tags" => TRUE,
			'width' => '100%',
		));
		echo form_text('photo_order', $locale['photo_0013'], $data['photo_order'], array(
			"type" => "number",
			"inline" => TRUE,
			"width" => "100px"
		));
		if ($data['photo_filename'] || $data['photo_thumb1']) {
			echo "<div class='well col-sm-offset-3'>\n";
			$image = '';
			if ($data['photo_filename']) {
				$image = thumbnail(IMAGES_G.$data['photo_filename'], $gll_settings['thumb_w']);
				echo form_hidden("photo_filename", "", $data['photo_filename']);
			}
			if ($data['photo_thumb2']) {
				$image = thumbnail(IMAGES_G_T.$data['photo_thumb2'], $gll_settings['thumb_w']);
				echo form_hidden("photo_thumb2", "", $data['photo_thumb2']);
			}
			if ($data['photo_thumb1']) {
				$image = thumbnail(IMAGES_G_T.$data['photo_thumb1'], $gll_settings['thumb_w']);
				echo form_hidden("photo_thumb1", "", $data['photo_thumb1']);
			}
			echo "<label for='del_image'>\n";
			echo $image;
			echo "</label>\n";
			echo form_checkbox("del_image", $locale['photo_0018'], "");
			echo "</div>\n";
		} else {
			$upload_settings = array(
				"upload_path" => IMAGES_G,
				"required" => TRUE,
				'thumbnail_folder' => 'thumbs',
				'thumbnail' => TRUE,
				'thumbnail_w' => $gll_settings['thumb_w'],
				'thumbnail_h' => $gll_settings['thumb_h'],
				'thumbnail_suffix' => '_t1',
				'thumbnail2' => TRUE,
				'thumbnail2_w' => $gll_settings['photo_w'],
				'thumbnail2_h' => $gll_settings['photo_h'],
				'thumbnail2_suffix' => '_t2',
				'max_width' => $gll_settings['photo_max_w'],
				'max_height' => $gll_settings['photo_max_h'],
				'max_byte' => $gll_settings['photo_max_b'],
				'multiple' => FALSE,
				'delete_original' => FALSE,
				"template" => "modern",
				"inline" => TRUE,
				"error_text" => $locale['photo_0014'],
			);
			echo form_fileinput('photo_image', $locale['photo_0004'], "", $upload_settings);
			echo "<div class='m-b-10 col-xs-12 col-sm-offset-3'>".sprintf($locale['photo_0017'], parsebytesize($gll_settings['photo_max_b']), str_replace(',', ' ', ".jpg,.gif,.png"), $gll_settings['photo_max_w'], $gll_settings['photo_max_h'])."</div>\n";
		}

		$snippetSettings = array(
			"required" => TRUE,
			"preview" => TRUE,
			"html" => TRUE,
			"autosize" => TRUE,
			"form_name" => "inputform",
			'placeholder' => $locale['photo_0009'],
		);
		if (fusion_get_settings("tinymce_enabled")) {
			$snippetSettings = array("form_name" => "inputform", "required" => TRUE, 'placeholder' => $locale['photo_0009'],);
		}
		echo form_textarea('photo_description', $locale['photo_0008'], $data['photo_description'], $snippetSettings);

		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-4'>\n";
		echo form_select('album_id', $locale['photo_0003'], $data['album_id'], array(
			'options' => get_albumOpts(),
			'inline' => TRUE
		));
		echo form_checkbox('photo_allow_comments', $locale['photo_0010'], $data['photo_allow_comments']);
		echo form_checkbox('photo_allow_ratings', $locale['photo_0011'], $data['photo_allow_ratings']);
		echo "</div>\n</div>\n";
		echo form_button('save_photo', $locale['photo_0012'], $locale['photo_0012'], array('class' => 'btn-success btn-sm m-r-10'));
		echo closeform();
	} else {
		echo "<div class='well m-t-20 text-center'>\n";
		echo sprintf($locale['gallery_0012'], FUSION_SELF.$aidlink."&amp;section=album_form");
		echo "</div>\n";
	}
}

function mass_photo_form() {
	global $locale, $aidlink, $gll_settings, $userdata;
	$albumRows = dbcount("(album_id)", DB_PHOTO_ALBUMS, multilang_table("PG") ? "album_language='".LANGUAGE."'" : "");
	if ($albumRows) {
		if (isset($_POST['upload_photo'])) {
			$data['album_id'] = form_sanitizer($_POST['album_id'], 0, "album_id");
			if (defender::safe()) {
				$upload = form_sanitizer($_FILES['photo_mass_image'], "", "photo_mass_image");
				if (!empty($upload)) {
					$total_files_uploaded = count($upload);
					$x = 0;
					for ($i = 0; $i < $total_files_uploaded; $i++) {
						$current_upload = $upload[$i];
						if (!$current_upload['error']) {
							$current_photos = array(
								"photo_id" => 0,
								"album_id" => $data['album_id'],
								"photo_title" => $current_upload['image_name'],
								"photo_filename" => $current_upload['image_name'],
								"photo_thumb1" => $current_upload['thumb1_name'],
								"photo_thumb2" => $current_upload['thumb2_name'],
								"photo_datestamp" => time(),
								"photo_user" => $userdata['user_id'],
								"photo_order" => dbresult(dbquery("SELECT MAX(photo_order) FROM ".DB_PHOTOS." where album_id='".$data['album_id']."'"), 0)+1,
							);
							dbquery_insert(DB_PHOTOS, $current_photos, "save");
							$x++;
						}
					}
					addNotice("success", sprintf($locale['photo_0021'], $x));
					redirect(FUSION_SELF.$aidlink."&amp;album_id='".$data['album_id']);
				}
			}
		}
		$upload_settings = array(
			"upload_path" => IMAGES_G,
			"required" => TRUE,
			'thumbnail_folder' => 'thumbs',
			'thumbnail' => TRUE,
			'thumbnail_w' => $gll_settings['thumb_w'],
			'thumbnail_h' => $gll_settings['thumb_h'],
			'thumbnail_suffix' => '_t1',
			'thumbnail2' => TRUE,
			'thumbnail2_w' => $gll_settings['photo_w'],
			'thumbnail2_h' => $gll_settings['photo_h'],
			'thumbnail2_suffix' => '_t2',
			'max_width' => $gll_settings['photo_max_w'],
			'max_height' => $gll_settings['photo_max_h'],
			'max_byte' => $gll_settings['photo_max_b'],
			'delete_original' => FALSE,
			"template" => "modern",
			"multiple" => TRUE,
			"inline" => TRUE,
			"error_text" => $locale['photo_0014'],
		);
		echo openform("mass_form", "post", FUSION_REQUEST, array("enctype" => TRUE, "class" => "clearfix"));
		echo "<div class='well text-center'>\n".$locale['photo_0019']."</div>\n";
		echo form_select('album_id', $locale['photo_0003'], "", array(
			"input_id" => "album",
			"options" => get_albumOpts(),
			"inline" => TRUE
		));
		echo form_fileinput('photo_mass_image[]', $locale['photo_0004'], "", $upload_settings);
		echo "<div class='m-b-10 col-xs-12 col-sm-offset-3'>".sprintf($locale['photo_0017'], parsebytesize($gll_settings['photo_max_b']), str_replace(',', ' ', ".jpg,.gif,.png"), $gll_settings['photo_max_w'], $gll_settings['photo_max_h'])."</div>\n";
		echo form_button("upload_photo", $locale['photo_0020'], $locale['photo_0020'], array("class" => "btn-primary"));
		echo closeform();
	} else {
		echo "<div class='well m-t-20 text-center'>\n";
		echo sprintf($locale['gallery_0012'], FUSION_SELF.$aidlink."&amp;section=album_form");
		echo "</div>\n";
	}
}