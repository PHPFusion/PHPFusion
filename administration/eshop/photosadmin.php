<?php
/*--------------------------------------------------------------+
| PHP-Fusion Content Management System 				|
| Copyright � 2002 - 2008 Nick Jones 				|
| http://www.php-fusion.co.uk/ 					|
+---------------------------------------------------------------+
| Filename:photosadmin.php                                      |
| Author:Joakim Falk (Domi) Based on PHP-Fusion V6 Admin Gallery|
+--------------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }
if (isset($_GET['ephoto_id']) && !isnum($_GET['ephoto_id'])) die("Denied");
if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }
if (!isset($_GET['action'])) $_GET['action'] = "";
if (isset($_GET['ealbum_id']) && !isnum($_GET['ealbum_id'])) die("Denied");

define("ESHPHOTOS", BASEDIR."eshop/pictures/");
define("SAFEMODE", @ini_get("safe_mode") ? true : false);
global $userdata;
$settings = fusion_get_settings();
$error = "";
$photo_thumb = "";
$photo_dest = "";
$imagebytes = $settings['eshop_image_b'];
$imagewidth = $settings['eshop_image_w'];
$imageheight = $settings['eshop_image_h'];
$thumbwidth = $settings['eshop_image_tw'];
$thumbheight = $settings['eshop_image_th'];
$thumb2width = $settings['eshop_image_t2w'];
$thumb2height = $settings['eshop_image_t2h'];
$albumthumbs_per_page = "16";
$albumthumbs_per_row = "4";

if (isset($_GET['psearch'])) {
include ADMIN."eshop/photosearch.php";
} else {


if (!isset($_GET['ealbum_id']))
{
echo "<div style='width:40%;' class='scapmain'> &raquo; ".$ESHPALBUMS['460']."</div>";

$rows = dbcount("(id)", "".DB_ESHOP."");


if ($rows) {
echo "<div class='admin-message'> ".$locale['ESHPHOTOS108']." </div>";

	$result = dbquery("SELECT * FROM ".DB_ESHOP."  ORDER BY id DESC LIMIT ".$_GET['rowstart'].",".$albumthumbs_per_page);
	$counter = 0; $k = ($_GET['rowstart'] == 0 ? 1 : $_GET['rowstart'] + 1);
	echo "<table cellpadding='0' cellspacing='1' width='100%'>\n<tr>\n";
	while ($data = dbarray($result)) {

		if ($counter != 0 && ($counter % $albumthumbs_per_row == 0)) echo "</tr>\n<tr>\n";
		echo "<td align='center' valign='top' class='tbl'>\n";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;a_page=photos&ealbum_id=".$data['id']."'><b>".$data['title']."</b><br />\n";
		if ($data['thumb'] && file_exists(ESHPHOTOS.$data['thumb'])){
			echo "<img src='".ESHPHOTOS.$data['thumb']."' alt='' border='0' width='100' height='100'>";
		} else {
			echo $ESHPALBUMS['462'];
		}
		echo "<br /><a href='".FUSION_SELF.$aidlink."&amp;a_page=Main&amp;action=edit&amp;id=".$data['id']."".($settings['eshop_cats'] == "1" ? "&amp;category=".$data['cid']."" : "")."'>".$ESHPALBUMS['469']."</a> &middot;\n";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;a_page=photos&amp;ealbum_id=".$data['id']."'>".$locale['ESHPHOTOS107']."</a> &middot;\n";
		echo "<br /><br />\n";
		echo $ESHPALBUMS['466'].dbcount("(photo_id)", "".DB_ESHOP_PHOTOS."", "album_id='".$data['id']."'")."<br />\n";
		echo "</td>\n";
		$counter++; $k++;
	}
echo "</tr>\n</table>\n";
if ($rows > $albumthumbs_per_page) echo "<div align='center' style='margin-top:5px;'>\n".makeeshoppagenav($_GET['rowstart'],$albumthumbs_per_page,$rows,3,FUSION_SELF.$aidlink."&amp;adminalbums&a_page=photos&amp;")."\n</div>\n";

}else{
	echo "<center>".$ESHPALBUMS['481']."</center>\n";
echo "<div style='float:left;margin-top 15px;padding:10px;'><a class='eshpbutton ".$settings['eshop_return_color']."' href='javascript:history.back(-1)'>&laquo; ".$locale['ESHP030']."</a></div>";
echo '<div style="clear:both"></div>';

}

}
else
{

if (isset($_GET['status'])) {
	if ($_GET['status'] == "savepn") {
		$title = $locale['ESHPHOTOS110'];
		$message = "<b>".$ESHPHOTOSL['410']."</b>";
	} elseif ($_GET['status'] == "savepu") {
		$title = $ESHPHOTOSL['401'];
		$message = "<b>".$ESHPHOTOSL['411']."</b>";
	} elseif ($_GET['status'] == "delp") {
		$title = $ESHPHOTOSL['402'];
		$message = "<b>".$ESHPHOTOSL['412']."</b>";
	} elseif ($_GET['status'] == "delpd") {
		$title = $ESHPHOTOSL['402'];
		$message = "<b>".$ESHPHOTOSL['413']."</b>";
	} elseif ($_GET['status'] == "savepe") {
		$title = $ESHPHOTOSL['420'];
		$message = "<b>".$ESHPHOTOSL['421']."</b><br />\n";
		if ($_GET['error'] == 1) { $message .= $ESHPHOTOSL['422']; }
		elseif ($_GET['error'] == 2) { $message .= sprintf($ESHPHOTOSL['423'], parsebytesize($imagebytes)); }
		elseif ($_GET['error'] == 3) { $message .= $ESHPHOTOSL['424']; }
		elseif ($_GET['error'] == 4) { $message .= sprintf($ESHPHOTOSL['425'], $imagewidth, $imageheight); }
	}
echo "<div style='width:40%;'> &raquo; ".$title."</div>";
	echo "<br />";
	echo "<div class='admin-message'>".$message."</div>\n";
	echo "<br />";
}

if (isset($_POST['cancel'])) {
	redirect(FUSION_SELF.$aidlink."&amp;a_page=photos&ealbum_id=".$_GET['ealbum_id']."");
}


define("PHOTODIR", ESHPHOTOS.(!SAFEMODE ? "album_".$_GET['ealbum_id']."/" : ""));

if ($_GET['action'] == "deletepic") {
	$data = dbarray(dbquery("SELECT photo_filename,photo_thumb1,photo_thumb2 FROM ".DB_ESHOP_PHOTOS." WHERE photo_id='".$_GET['ephoto_id']."' AND photo_user='".$userdata['user_id']."'"));
	$result = dbquery("UPDATE ".DB_ESHOP_PHOTOS." SET photo_filename='', photo_thumb1='', photo_thumb2='' WHERE photo_id='".$_GET['ephoto_id']."' AND photo_user='".$userdata['user_id']."'");
	@unlink(PHOTODIR.$data['photo_filename']);
	@unlink(PHOTODIR.$data['photo_thumb1']);
	if ($data['photo_thumb2']) @unlink(PHOTODIR.$data['photo_thumb2']);
	redirect(FUSION_SELF.$aidlink."&amp;a_page=photos&status=delp&ealbum_id=".$_GET['ealbum_id']."");
} elseif ($_GET['action'] == "delete") {
	$data = dbarray(dbquery("SELECT album_id,photo_filename,photo_thumb1,photo_thumb2,photo_order FROM ".DB_ESHOP_PHOTOS." WHERE photo_id='".$_GET['ephoto_id']."' AND photo_user='".$userdata['user_id']."'"));
	$result = dbquery("UPDATE ".DB_ESHOP_PHOTOS." SET photo_order=(photo_order-1) WHERE photo_order>'".$data['photo_order']."' AND album_id='".$_GET['ealbum_id']."' AND photo_user='".$userdata['user_id']."'");
	$result = dbquery("DELETE FROM ".DB_ESHOP_PHOTOS." WHERE photo_id='".$_GET['ephoto_id']."'");
	$result = dbquery("DELETE FROM ".$db_prefix."comments WHERE comment_item_id='".$_GET['ephoto_id']."' and comment_type='P'");
	if ($data['photo_filename']) @unlink(PHOTODIR.$data['photo_filename']);
	if ($data['photo_thumb1']) @unlink(PHOTODIR.$data['photo_thumb1']);
	if ($data['photo_thumb2']) @unlink(PHOTODIR.$data['photo_thumb2']);
	redirect(FUSION_SELF.$aidlink."&amp;a_page=photos&status=delpd&ealbum_id=".$_GET['ealbum_id']."");
} elseif($_GET['action']=="mup") {
	if (!isnum($_GET['order'])) redirect(FUSION_SELF.$aidlink."&amp;a_page=photos&ealbum_id=".$_GET['ealbum_id']."");
	$data = dbarray(dbquery("SELECT photo_id FROM ".DB_ESHOP_PHOTOS." WHERE album_id='".$_GET['ealbum_id']."' AND photo_order='".$_GET['order']."' AND photo_user='".$userdata['user_id']."'"));
	$result = dbquery("UPDATE ".DB_ESHOP_PHOTOS." SET photo_order=photo_order+1 WHERE photo_id='".$data['photo_id']."' AND photo_user='".$userdata['user_id']."'");
	$result = dbquery("UPDATE ".DB_ESHOP_PHOTOS." SET photo_order=photo_order-1 WHERE photo_id='".$_GET['ephoto_id']."' AND photo_user='".$userdata['user_id']."'");
	$rowstart = $_GET['order'] > $albumthumbs_per_page ? ((ceil($_GET['order'] / $albumthumbs_per_page)-1)*$albumthumbs_per_page) : "0";
	redirect(FUSION_SELF.$aidlink."&amp;a_page=photos&ealbum_id=".$_GET['ealbum_id']."&rowstart=$rowstart");
} elseif ($_GET['action']=="mdown") {
	if (!isnum($_GET['order'])) { die("Denied"); }
	$data = dbarray(dbquery("SELECT photo_id FROM ".DB_ESHOP_PHOTOS." WHERE album_id='".$_GET['ealbum_id']."' AND photo_order='".$_GET['order']."' AND photo_user='".$userdata['user_id']."'"));
	$result = dbquery("UPDATE ".DB_ESHOP_PHOTOS." SET photo_order=photo_order-1 WHERE photo_id='".$data['photo_id']."' AND photo_user='".$userdata['user_id']."'");
	$result = dbquery("UPDATE ".DB_ESHOP_PHOTOS." SET photo_order=photo_order+1 WHERE photo_id='".$_GET['ephoto_id']."' AND photo_user='".$userdata['user_id']."'");
	$rowstart = $_GET['order'] > $albumthumbs_per_page ? ((ceil($_GET['order'] / $albumthumbs_per_page)-1)*$albumthumbs_per_page) : "0";
	redirect(FUSION_SELF.$aidlink."&amp;a_page=photos&ealbum_id=".$_GET['ealbum_id']."&rowstart=$rowstart");
} elseif (isset($_POST['save_photo'])) {


	if (!SAFEMODE && $_GET['action'] != "edit") {

		@mkdir(ESHPHOTOS."album_".$_GET['ealbum_id'], 0755);
		@copy(IMAGES."index.php", ESHPHOTOS."album_".$_GET['ealbum_id']."/index.php");

	}
	$error="";
	$photo_title = stripinput($_POST['photo_title']);
	$photo_description = stripinput($_POST['photo_description']);
	$photo_order = isnum($_POST['photo_order']) ? $_POST['photo_order'] : "";
	$photo_comments = isset($_POST['photo_comments']) ? "1" : "0";
	$photo_last_viewed = stripinput($_POST['photo_last_viewed']);
	$photo_file = ""; $photo_thumb1 = ""; $photo_thumb2 = "";
	if (is_uploaded_file($_FILES['photo_pic_file']['tmp_name'])) {
		$photo_types = array(".gif",".jpg",".jpeg",".png");
		$photo_pic = $_FILES['photo_pic_file'];
		$photo_name = strtolower(substr($photo_pic['name'], 0, strrpos($photo_pic['name'], ".")));
		$photo_ext = strtolower(strrchr($photo_pic['name'],"."));
		$photo_dest = PHOTODIR;
		if (!preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $photo_pic['name'])) {
			$error = 1;
		} elseif ($photo_pic['size'] > $imagebytes){
			$error = 2;
		} elseif (!in_array($photo_ext, $photo_types)) {
			$error = 3;
		} else {
			$photo_file = image_exists($photo_dest, $photo_name.$photo_ext);
			move_uploaded_file($photo_pic['tmp_name'], $photo_dest.$photo_file);
			chmod($photo_dest.$photo_file, 0644);
			$imagefile = @getimagesize($photo_dest.$photo_file);
			if ($imagefile[0] > $imagewidth || $imagefile[1] > $imageheight) {
				$error = 4;
				unlink($photo_dest.$photo_file);
			} else {
				$photo_thumb1 = image_exists($photo_dest, $photo_name."_t1".$photo_ext);
				createthumbnail($imagefile[2], $photo_dest.$photo_file, $photo_dest.$photo_thumb1, $thumbwidth, $thumbheight);
				if ($imagefile[0] > $imagewidth || $imagefile[1] > $imageheight) {
					$photo_thumb2 = image_exists($photo_dest, $photo_name."_t2".$photo_ext);
					createthumbnail($imagefile[2], $photo_dest.$photo_file, $photo_dest.$photo_thumb2, $thumb2width, $thumb2height);
				if (!verify_image("".$photo_dest.$photo_thumb2."")) {
				unlink("".$photo_dest.$photo_thumb2."");
				}
			}
			}
		}
	}
	if (!$_GET['error']) {
			if (!verify_image("".$photo_dest.$photo_file."")) {
			unlink("".$photo_dest.$photo_file."");
			}
			if (!verify_image("".$photo_dest.$photo_thumb."")) {
			unlink("".$photo_dest.$photo_thumb."");
			}
		if ($_GET['action'] == "edit") {
			$old_photo_order = dbresult(dbquery("SELECT photo_order FROM ".DB_ESHOP_PHOTOS." WHERE photo_id='".$_GET['ephoto_id']."' AND photo_user='".$userdata['user_id']."'"),0);
			if ($photo_order > $old_photo_order) {
				$result = dbquery("UPDATE ".DB_ESHOP_PHOTOS." SET photo_order=(photo_order-1) WHERE photo_order>'$old_photo_order' AND photo_order<='$photo_order' AND album_id='".$_GET['ealbum_id']."'");
			} elseif ($photo_order < $old_photo_order) {
				$result = dbquery("UPDATE ".DB_ESHOP_PHOTOS." SET photo_order=(photo_order+1) WHERE photo_order<'$old_photo_order' AND photo_order>='$photo_order' AND album_id='".$_GET['ealbum_id']."'");
			}
			$update_photos = $photo_file ? "photo_filename='$photo_file', photo_thumb1='$photo_thumb1', photo_thumb2='$photo_thumb2', " : "";
			$result = dbquery("UPDATE ".DB_ESHOP_PHOTOS." SET photo_title='$photo_title', photo_description='$photo_description', ".$update_photos."photo_datestamp='".time()."', photo_order='$photo_order', photo_allow_comments='$photo_comments', photo_last_viewed='$photo_last_viewed' WHERE photo_id='".$_GET['ephoto_id']."'");
			$rowstart = $photo_order > $albumthumbs_per_page ? ((ceil($photo_order / $albumthumbs_per_page)-1)*$albumthumbs_per_page) : "0";
			redirect(FUSION_SELF.$aidlink."&amp;a_page=photos&status=savepu&ealbum_id=".$_GET['ealbum_id']."&rowstart=$rowstart");
		}else{
			if (!$photo_order) $photo_order = dbresult(dbquery("SELECT MAX(photo_order) FROM ".DB_ESHOP_PHOTOS." WHERE album_id='".$_GET['ealbum_id']."' AND photo_user='".$userdata['user_id']."'"), 0) + 1;
			$result = dbquery("UPDATE ".DB_ESHOP_PHOTOS." SET photo_order=(photo_order+1) WHERE photo_order>='$photo_order' AND album_id='".$_GET['ealbum_id']."'");
			$result = dbquery("INSERT INTO ".DB_ESHOP_PHOTOS." (album_id, photo_title, photo_description, photo_filename, photo_thumb1, photo_thumb2, photo_datestamp, photo_user, photo_views, photo_order, photo_allow_comments, photo_last_viewed) VALUES ('".$_GET['ealbum_id']."', '$photo_title', '$photo_description', '$photo_file', '$photo_thumb1', '$photo_thumb2', '".time()."', '".$userdata['user_id']."', '0', '$photo_order', '$photo_comments', '".time()."')");
			$rowstart = $photo_order > $albumthumbs_per_page ? ((ceil($photo_order / $albumthumbs_per_page)-1)*$albumthumbs_per_page) : "0";
			redirect(FUSION_SELF.$aidlink."&amp;a_page=photos&status=savepn&ealbum_id=".$_GET['ealbum_id']."&rowstart=$rowstart");
		}
	}
	if ($_GET['error']) {
		redirect(FUSION_SELF.$aidlink."&amp;a_page=photos&status=savepe&error=$error&ealbum_id=".$_GET['ealbum_id']."");
	}
}else{
	if ($_GET['action'] == "edit") {
		$result = dbquery("SELECT * FROM ".DB_ESHOP_PHOTOS." WHERE photo_id='".$_GET['ephoto_id']."' AND photo_user='".$userdata['user_id']."'");
		$data = dbarray($result);
		$photo_title = $data['photo_title'];
		$photo_description = $data['photo_description'];
		$photo_filename = $data['photo_filename'];
		$photo_thumb1 = $data['photo_thumb1'];
		$photo_thumb2 = $data['photo_thumb2'];
		$photo_order = $data['photo_order'];
		$photo_comments = $data['photo_allow_comments'] == "1" ? " checked" : "";
		$photo_last_viewed = $data['photo_last_viewed'];
		$formaction = FUSION_SELF.$aidlink."&amp;a_page=photos&action=edit&amp;ealbum_id=".$_GET['ealbum_id']."&amp;ephoto_id=".$data['photo_id'];
echo "<div style='width:50%;'> &raquo; <b> ".$ESHPHOTOSL['401']." - (".$_GET['ephoto_id']." - ".$photo_title.") </b> </div>";

echo "<form name='inputform' method='post' action='$formaction' enctype='multipart/form-data'>
<table align='center' cellspacing='0' cellpadding='0'>
<tr>
<td class='tbl'>".$ESHPHOTOSL['440']."</td>
<td class='tbl'><input type='text' name='photo_title' value='$photo_title' maxlength='100' class='textbox' style='width:330px;'></td>
</tr>
<tr>
<td class='tbl'><input type='hidden' name='photo_description' class='textbox' value='$photo_description'></td>
</tr>
<tr>
<td class='tbl'>".$ESHPHOTOSL['442']."</td>
<td class='tbl'><input type='text' name='photo_order' value='$photo_order' maxlength='5' class='textbox' style='width:40px;'></td>
</tr>\n";
	if ($_GET['action'] && $photo_thumb1 && file_exists(PHOTODIR.$photo_thumb1)) {
		echo "<tr>\n<td valign='top' class='tbl'>".$ESHPHOTOSL['443']."</td>
<td class='tbl'><img src='".PHOTODIR.$photo_thumb1."' border='1' alt='$photo_thumb1'></td>
</tr>\n";
	}
	echo "<tr>\n<td valign='top' class='tbl'>".$ESHPHOTOSL['444'];
	if ($_GET['action'] && $photo_thumb2 && file_exists(PHOTODIR.$photo_thumb2)) {
		echo "<br /><br />\n<a class='small' href='".FUSION_SELF.$aidlink."&amp;a_page=photos&action=deletepic&amp;ealbum_id=".$_GET['ealbum_id']."&amp;ephoto_id=".$_GET['ephoto_id']."'>".$ESHPHOTOSL['470']."</a></td>
<td class='tbl'><img src='".PHOTODIR.$photo_thumb2."' border='1' alt='$photo_thumb2'>";
	} elseif ($_GET['action'] && $photo_filename && file_exists(PHOTODIR.$photo_filename)) {
		echo "<br /><br />\n<a class='small' href='".FUSION_SELF.$aidlink."&amp;a_page=photos&action=deletepic&amp;ealbum_id=".$_GET['ealbum_id']."&amp;ephoto_id=".$_GET['ephoto_id']."'>".$ESHPHOTOSL['470']."</a></td>
<td class='tbl'><img src='".PHOTODIR.$photo_filename."' border='1' alt='$photo_filename'>";
	} else {
		echo "</td>\n<td class='tbl'><input type='file' name='photo_pic_file' class='textbox' style='width:250px;'>\n";
	}

	echo "</td>
</tr>
<tr>
<td colspan='2' align='center' class='tbl'><br />
<input type='hidden' name='photo_comments' value='yes'>
<input type='hidden' name='photo_last_viewed' value='$photo_last_viewed'>
<input type='submit' name='save_photo' value='".$ESHPHOTOSL['447']."' class='button'>\n";
	if ($_GET['action']) {
		echo "<input type='submit' name='cancel' value='".$ESHPHOTOSL['448']."' class='button'>\n";
	}
	echo "</td></tr>\n</table></form>\n";
	} else {
$getalbumname = dbarray(dbquery("SELECT title,id FROM ".DB_ESHOP." WHERE id='".$_GET['ealbum_id']."'"));
echo "<div style='width:40%;'> &raquo; ".$ESHPHOTOSL['400']." &raquo; ".$getalbumname['title']."</div>";
		$photo_title = "";
		$photo_description = "";
		$photo_filename = "";
		$photo_thumb1 = "";
		$photo_thumb2 = "";
		$photo_order = "";
		$photo_comments = " checked";
		$photo_last_viewed = "";
		$formaction = "".FUSION_SELF.$aidlink."&amp;a_page=photos&ealbum_id=".$_GET['ealbum_id']."";
echo "<form name='inputform' method='post' action='$formaction' enctype='multipart/form-data'>
<table align='center' cellspacing='0' cellpadding='0'>
<tr>
<td class='tbl'>".$ESHPHOTOSL['440']."</td>
<td class='tbl'><input type='text' name='photo_title' value='$photo_title' maxlength='100' class='textbox' style='width:330px;'></td>
</tr>
<tr>
<td class='tbl'><input type='hidden' name='photo_description' value='$photo_description' class='textbox'>
</td>
</tr>
<tr>
<td class='tbl'>".$ESHPHOTOSL['442']."</td>
<td class='tbl'><input type='text' name='photo_order' value='$photo_order' maxlength='5' class='textbox' style='width:40px;'></td>
</tr>\n";
	if ($_GET['action'] && $photo_thumb1 && file_exists(PHOTODIR.$photo_thumb1)) {
		echo "<tr>\n<td valign='top' class='tbl'>".$ESHPHOTOSL['443']."</td>
<td class='tbl'><img src='".PHOTODIR.$photo_thumb1."' border='1' alt='$photo_thumb1'></td>
</tr>\n";
	}
	echo "<tr>\n<td valign='top' class='tbl'>".$ESHPHOTOSL['444'];
	if ($_GET['action'] && $photo_thumb2 && file_exists(PHOTODIR.$photo_thumb2)) {
		echo "<br /><br />\n<a class='small' href='".FUSION_SELF.$aidlink."&amp;a_page=photos&action=deletepic&amp;ealbum_id=".$_GET['ealbum_id']."&amp;ephoto_id=".$_GET['ephoto_id']."'>".$ESHPHOTOSL['470']."</a></td>
<td class='tbl'><img src='".PHOTODIR.$photo_thumb2."' border='1' alt='$photo_thumb2'>";
	} elseif ($_GET['action'] && $photo_filename && file_exists(PHOTODIR.$photo_filename)) {
		echo "<br /><br />\n<a class='small' href='".FUSION_SELF.$aidlink."&amp;a_page=photos&action=deletepic&amp;ealbum_id=".$_GET['ealbum_id']."&amp;ephoto_id=".$_GET['ephoto_id']."'>".$ESHPHOTOSL['470']."</a></td>
<td class='tbl'><img src='".PHOTODIR.$photo_filename."' border='1' alt='$photo_filename'>";
	} else {
		echo "</td>\n<td class='tbl'><input type='file' name='photo_pic_file' class='textbox' style='width:250px;'>\n";
	}

	echo "</td>
</tr>
<tr>
<td colspan='2' align='center' class='tbl'><br />
<input type='hidden' name='photo_comments' value='yes'$photo_comments>
<input type='hidden' name='photo_last_viewed' value='$photo_last_viewed'>
<input type='submit' name='save_photo' value='".$ESHPHOTOSL['447']."' class='button'>\n";
	if ($_GET['action']) {
		echo "<input type='submit' name='cancel' value='".$ESHPHOTOSL['448']."' class='button'>\n";
	}
	echo "</td></tr>\n</table></form>\n";

	}
}
tablebreak();
echo "<div style='width:30%;' class='scapmain'> &raquo; ".$ESHPHOTOSL['460']." </div>";
$rows = dbcount("(photo_id)", "".DB_ESHOP_PHOTOS."", "album_id='".$_GET['ealbum_id']."' AND photo_user='".$userdata['user_id']."'");
if ($rows) {
	$result = dbquery(
		"SELECT tp.*, tu.user_id,user_name FROM ".DB_ESHOP_PHOTOS." tp
		LEFT JOIN ".DB_USERS." tu ON tp.photo_user=tu.user_id
		WHERE album_id='".$_GET['ealbum_id']."' AND photo_user='".$userdata['user_id']."' ORDER BY photo_order
		LIMIT ".$_GET['rowstart'].",".$albumthumbs_per_page
	);
	$counter = 0; $k = ($_GET['rowstart'] == 0 ? 1 : $_GET['rowstart'] + 1);
	echo "<table cellpadding='0' cellspacing='1' width='100%'>\n<tr>\n";
	while ($data = dbarray($result)) {
		$up = ""; $down = "";
		if ($rows != 1){
			$orderu = $data['photo_order'] - 1;
			$orderd = $data['photo_order'] + 1;
			if ($k == 1) {
				$down = " &middot;\n<a href='".FUSION_SELF.$aidlink."&amp;a_page=photos&ealbum_id=".$_GET['ealbum_id']."&amp;rowstart=".$_GET['rowstart']."&amp;action=mdown&amp;order=$orderd&amp;ephoto_id=".$data['photo_id']."'><img src='".THEME."images/right.gif' alt='".$ESHPHOTOSL['469']."' title='".$ESHPHOTOSL['469']."' border='0' style='vertical-align:middle'></a>\n";
			} elseif ($k < $rows){
				$up = "<a href='".FUSION_SELF.$aidlink."&amp;a_page=photos&ealbum_id=".$_GET['ealbum_id']."&amp;rowstart=".$_GET['rowstart']."&amp;action=mup&amp;order=$orderu&amp;ephoto_id=".$data['photo_id']."'><img src='".THEME."images/left.gif' alt='".$ESHPHOTOSL['468']."' title='".$ESHPHOTOSL['468']."' border='0' style='vertical-align:middle'></a> &middot;\n";
				$down = " &middot;\n<a href='".FUSION_SELF.$aidlink."&amp;a_page=photos&ealbum_id=".$_GET['ealbum_id']."&amp;rowstart=".$_GET['rowstart']."&amp;action=mdown&amp;order=$orderd&amp;ephoto_id=".$data['photo_id']."'><img src='".THEME."images/right.gif' alt='".$ESHPHOTOSL['469']."' title='".$ESHPHOTOSL['469']."' border='0' style='vertical-align:middle'></a>\n";
			} else {
				$up = "<a href='".FUSION_SELF.$aidlink."&amp;a_page=photos&ealbum_id=".$_GET['ealbum_id']."&amp;rowstart=".$_GET['rowstart']."&amp;action=mup&amp;order=$orderu&amp;ephoto_id=".$data['photo_id']."'><img src='".THEME."images/left.gif' alt='".$ESHPHOTOSL['468']."' title='".$ESHPHOTOSL['468']."' border='0' style='vertical-align:middle'></a> &middot;\n";
			}
		}
		if ($counter != 0 && ($counter % $albumthumbs_per_row == 0)) echo "</tr>\n<tr>\n";
		echo "<td align='center' valign='top' class='tbl'>\n";
		echo "<b>".$data['photo_order']." ".$data['photo_title']."</b><br /><br />\n";
		if ($data['photo_thumb1'] && file_exists(PHOTODIR.$data['photo_thumb1'])){
			echo "<img src='".PHOTODIR.$data['photo_thumb1']."' alt='".$ESHPHOTOSL['461']."' border='0' width='100' height='100'>";
		} else {
			echo $ESHPHOTOSL['462'];
		}
		echo "<br /><br />\n ".$up;
		echo "<a href='".FUSION_SELF.$aidlink."&amp;a_page=photos&action=edit&amp;ealbum_id=".$_GET['ealbum_id']."&amp;ephoto_id=".$data['photo_id']."'>".$ESHPHOTOSL['469']."</a> &middot;\n";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;a_page=photos&action=delete&amp;ealbum_id=".$_GET['ealbum_id']."&amp;ephoto_id=".$data['photo_id']."'>".$ESHPHOTOSL['470']."</a> ".$down;
		echo "<br /><br />\n".$ESHPHOTOSL['463'].showdate("shortdate", $data['photo_datestamp'])."<br />\n";
		echo "</td>\n";
		$counter++; $k++;
	}
	echo "</tr>\n<tr>\n<td align='center' colspan='".$albumthumbs_per_row."' class='tbl2'><a class='button' href='".FUSION_SELF.$aidlink."&amp;a_page=photos'>".$ESHPHOTOSL['481']."</a><br />
    <div style='float:left;margin-top 15px;padding:10px;'><a class='eshpbutton ".$settings['eshop_return_color']."' href='javascript:history.back(-1)'>&laquo; Return</a></div>";
	echo '<div style="clear:both"></div>';
	echo "</td>\n</tr>\n</table>\n";
	if ($rows > $albumthumbs_per_page) echo "<div align='center' style='margin-top:5px;'>\n".makeeshoppagenav($_GET['rowstart'],$albumthumbs_per_page,$rows,3,FUSION_SELF.$aidlink."&amp;a_page=photos&amp;ealbum_id=".$_GET['ealbum_id']."&amp;")."\n</div>\n";
}else{
	echo "<center>".$ESHPHOTOSL['480']."</center>\n";
 }
}

if (isset($_POST['psrchtext'])) {
$searchtext = stripinput($_POST['psrchtext']);
} else  { $searchtext = $locale['SRCH162']; }

}
echo "<div style='float:right;margin-top:5px;'><form id='search_form'  name='inputform' method='post' action='".FUSION_SELF.$aidlink."&amp;a_page=photos&amp;psearch'>
<span style='vertical-align:middle;font-size:14px;'>".$locale['ESHPHOTOS111']."</span>";
echo "<input type='text' name='psrchtext' class='textbox' style='margin-left:1px; margin-right:1px; margin-bottom:5px; width:160px;'  value='".$searchtext."' onblur=\"if(this.value=='') this.value='".$searchtext."';\" onfocus=\"if(this.value=='".$searchtext."') this.value='';\" />";
echo "<input type='image' id='search_image' src='".BASEDIR."eshop/img/search_icon.png' alt='".$locale['SRCH162']."' />";
echo "</form></div>";

?>