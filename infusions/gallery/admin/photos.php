<?php

// 2 tabs. 1 for normal , 2 for batch

$phototab['title'][] = $locale['gallery_0009'];
$phototab['id'][] = "single_photo";
$phototab['icon'][] = "";

$phototab['title'][] = $locale['gallery_0010'];
$phototab['id'][] = "mass_photo";
$phototab['icon'][] = "";

$tab_active = tab_active($phototab, 0);

echo opentab($phototab, $tab_active, "phototabs", false, "m-t-20");
echo opentabbody($phototab['title'][0], $phototab['id'][0], $tab_active);
photo_form();
echo closetabbody();
echo opentabbody($phototab['title'][1], $phototab['id'][1], $tab_active);
mass_photo_form();
echo closetabbody();
echo closetab();
// done.


function photo_form() {
	global $locale, $aidlink, $userdata, $gll_settings, $defender;
	$albumRows = dbcount("(album_id)", DB_PHOTO_ALBUMS, multilang_table("PG") ? "album_language='".LANGUAGE."'" : "");
	if ($albumRows)
	{

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
			"photo_allow_comments" => true,
			"photo_allow_ratings" => true,
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
				"photo_allow_comments" => isset($_POST['photo_allow_comments']) ? true : false,
				"photo_allow_ratings" => isset($_POST['photo_allow_ratings']) ? true : false,
				"photo_views" => 0,
				"photo_filename" => "",
				"photo_thumb1" => "",
				"photo_thumb2" => "",
			);

			if (empty($data['photo_order'])) {
				$data['photo_order'] = dbresult(dbquery("SELECT MAX(photo_order) FROM ".DB_PHOTOS."
				where album_id='".$data['album_id']."'"),0)+1;
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
					$data['photo_filename'] = form_sanitizer($_POST['photo_filename'], "", "photo_filename");
					$data['photo_thumb2'] = form_sanitizer($_POST['photo_thumb2'], "", "photo_thumb2");
					$data['photo_thumb1'] = form_sanitizer($_POST['photo_thumb1'], "", "photo_thumb1");
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

		echo openform('photoform', 'post', FUSION_REQUEST, array('enctype' => true, 'class' => 'm-t-20'));
		echo "<div class='row'>\n<div class='col-xs-12 col-sm-8'>\n";
		echo form_hidden("photo_id", "", $data['photo_id']);
		echo form_hidden("photo_datestamp", "", $data['photo_datestamp']);
		echo form_hidden("photo_user", "", $data['photo_user']);
		echo form_text("photo_title", $locale['photo_0001'], $data['photo_title'],
					   array(
						   "required" => true,
			"placeholder" => $locale['photo_0002'],
			"inline" => true)
		);
		echo form_select('photo_keywords', $locale['photo_0006'], $data['photo_keywords'], array(
			'placeholder' => $locale['photo_0007'],
			'inline' => true,
			'multiple' => true,
			"tags" => true,
			'width' => '100%',
		));
		echo form_text('photo_order', $locale['photo_0013'], $data['photo_order'], array("type"=>"number", "inline"=>true, "width"=>"100px"));

		/*
		 * 		if ($photo_edit) {
			echo "<div id='photo_tmb' class='well'>\n";
			$img_path = self::get_virtual_path($data['album_id']).rtrim($this->upload_settings['thumbnail_folder'], '/')."/".$data['photo_thumb1'];
			echo "<img class='img-responsive' style='margin:0 auto;' src='$img_path' alt='".$data['photo_title']."'/>\n";
			echo "</div>\n";
		}

		//echo form_hidden('photo_hfile', '', $this->photo_data['photo_filename']);
		//echo form_hidden('photo_hthumb1', '', $this->photo_data['photo_thumb1']);
		//echo form_hidden('photo_hthumb2', '', $this->photo_data['photo_thumb2']);

		 */

		$upload_settings = array(
			"upload_path" => IMAGES_G,
			"required" => true,
			'thumbnail_folder'=>'thumbs',
			'thumbnail' => true,
			'thumbnail_w' =>  $gll_settings['thumb_w'],
			'thumbnail_h' =>  $gll_settings['thumb_h'],
			'thumbnail_suffix' =>'_t1',
			'thumbnail2'=> true,
			'thumbnail2_w' 	=>  $gll_settings['photo_w'],
			'thumbnail2_h' 	=>  $gll_settings['photo_h'],
			'thumbnail2_suffix' => '_t2',
			'max_width'		=>	$gll_settings['photo_max_w'],
			'max_height'	=>	$gll_settings['photo_max_h'],
			'max_byte'		=>	$gll_settings['photo_max_b'],
			'multiple' => false,
			'delete_original' => false,
			"template"=>"modern",
			"inline"=>true,
			"error_text" => $locale['photo_0014'],
		);
		echo form_fileinput('photo_image', $locale['photo_0004'], "", $upload_settings);
		echo "<div class='m-b-10 col-xs-12 col-sm-offset-3'>".sprintf($locale['photo_0017'], parsebytesize($gll_settings['photo_max_b']), str_replace(',', ' ', ".jpg,.gif,.png"), $gll_settings['photo_max_w'], $gll_settings['photo_max_h'])."</div>\n";

		echo form_textarea('photo_description', $locale['photo_0008'], $data['photo_description'], array('placeholder' => $locale['photo_0009'],
			'inline' => true)
		);
		echo "</div>\n";
		echo "<div class='col-xs-12 col-sm-4'>\n";

		echo form_select('album_id', $locale['photo_0003'], $data['album_id'], array('options' => get_albumOpts(), 'inline' => TRUE));
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


function mass_photo_form()
{
	global $locale, $aidlink;
	$albumRows = dbcount("(album_id)", DB_PHOTO_ALBUMS, multilang_table("PG") ? "album_language='".LANGUAGE."'" : "");
	if ($albumRows)
	{

	} else {
		echo "<div class='well m-t-20 text-center'>\n";
		echo sprintf($locale['gallery_0012'], FUSION_SELF.$aidlink."&amp;section=album_form");
		echo "</div>\n";
	}
}