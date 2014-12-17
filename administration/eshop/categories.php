<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: categories.php
| Author: Joakim Falk (Domi)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

if (isset($_POST['cid']) && !isnum($_POST['cid'])) die("Denied");
if (isset($_POST['status']) && !isnum($_POST['status'])) die("Denied");
if (isset($_POST['access']) && !isnum($_POST['access'])) die("Denied");

define("CAT_DIR", BASEDIR."eshop/categoryimgs/");
$cat_files = makefilelist(CAT_DIR, ".|..|index.php", true);
$cat_list = makefileopts($cat_files);

if (isset($_POST['access'])) { $access = isnum($_POST['access']) ? $_POST['access'] : "0"; } else { $access = "0"; }

$visibility_opts = ""; $sel = "";
$user_groups = getusergroups();
while(list($key, $user_group) = each($user_groups)){
$sel = ($access == $user_group['0'] ? " selected" : "");
$visibility_opts .= "<option value='".$user_group['0']."'$sel>".$user_group['1']."</option>\n";
}


if (isset($_GET['deletecat'])) {
$result = dbquery("DELETE FROM ".DB_ESHOP_CATS." WHERE cid='".$_REQUEST['cid']."'");
$result2 = dbquery("UPDATE ".DB_ESHOP." SET cid='0' WHERE cid='".$_REQUEST['cid']."'");
redirect("".FUSION_SELF.$aidlink."&amp;a_page=Categories&catdeleted");
	}

if (isset($_GET['catdeleted'])) {
echo "<br /><div class='admin-message'>".$locale['ESHPCATS118']."</div><br />";
}
if (isset($_GET['catadded'])) {
echo "<br /><div class='admin-message'>".$locale['ESHPCATS119']."</div><br />";
}
if (isset($_GET['catupdated'])) {
echo "<br /><div class='admin-message'>".$locale['ESHPCATS120']."</div><br />";
}


if (isset($_POST['SaveCategoryChanges'])) {

$cid = stripinput($_POST['cid']);
$title = stripinput($_POST['title']);
$image = stripinput($_POST['image']);
$parentid = stripinput($_POST['parentid']);
$status = stripinput($_POST['status']);
$order = "";
$languages = "";

for ($pl=0;$pl<sizeof($_POST['languages']);$pl++) {
   $languages .= $_POST['languages'][$pl].($pl<(sizeof($_POST['languages'])-1)?".":"");
}

dbquery("UPDATE ".DB_ESHOP_CATS." SET title = '$title', access= '$access',  image = '$image', parentid = '$parentid', status = '$status', cat_order='$order', cat_languages='$languages' WHERE cid ='$cid' LIMIT 1");
//redirect("".FUSION_SELF.$aidlink."&amp;a_page=Categories&catupdated");
}

if (isset($_POST['EditCurrentCategory'])) {

	$result=dbquery("select * from ".DB_ESHOP_CATS." where cid='".$_POST['cid']."'");
	$cat_data = dbarray($result);
	$stitle=getparent($cat_data['parentid'],$cat_data['title']);
	$title1="$stitle";
	$image=$cat_data['image'];
	$access = $cat_data['access'];
	$order = $cat_data['cat_order'];
	$languages = $cat_data['cat_languages'];


echo"<fieldset style='align:left;width:97%;display:block;float:left;margin-left:10px;margin-right:10px;margin-top:2px;margin-bottom:2px;'>
<legend>&nbsp;<b> ".$locale['ESHPCATS121']." </b>&nbsp;</legend>";

echo "<form name='addcat' action='".FUSION_SELF.$aidlink."&amp;a_page=Categories&SaveCategoryChanges' method='post'>";
echo '<table width="100%" cellspacing="1" cellpadding="1" border="0" align="center">
<tr><td>'.$locale['ESHPCATS106'].'</td><td><input class="textbox" type="text" name="title" size="30" value="'.$cat_data['title'].'"/></td></tr>';

for ($x=0;$x<sizeof($enabled_languages);$x++) {
	$languages .= $enabled_languages[$x].(($x<sizeof($enabled_languages)-1)?".":"");
}

$langs = explode('.', $languages);
$locale_files = makefilelist(LOCALE, ".|..", true, "folders");

echo "<td>".$locale['ESHPPRO191']."</td>";
echo "<td colspan='2'>";
for ($i=0;$i<sizeof($locale_files);$i++) {
if (in_array($locale_files[$i], $enabled_languages)) {
echo "<input type='checkbox' value='".$locale_files[$i]."' name='languages[]' class='textbox' ".(in_array($locale_files[$i], $langs)?"checked='checked'":"")."> ".str_replace('_', ' ', $locale_files[$i])." ";
}
if ($i%2==0 && $i!=0) echo "<br  />";
}
echo "</td></tr>";

echo '<tr><td>'.$locale['ESHPCATS105'].'</td>';
echo "<td width='35%'><select name='image' class='textbox' style='width:200px;'><option value='".$image."' ".($image == "$image" ? " selected" : "").">".$image."</option>$cat_list</select>
</td><td width='25%'><img style='height:50px;width:50px;' src='".CAT_DIR.($image!=''?$image:"")."' name='image_preview' alt='' /></td>";

echo '<tr><td>'.$locale['ESHPCATS107'].'</td><td><select class="textbox" name="parentid">';
if ($cat_data['parentid']) {
echo '<option value="'.$cat_data['parentid'].'">'.$title1.'</option>';
}
echo '<option value="0">'.$locale['ESHPCATS108'].'</option>';

$result=dbquery("select cid, title, parentid from ".DB_ESHOP_CATS." WHERE cid!='".$_REQUEST['cid']."' order by parentid,title");
while(list($cidp, $title, $parentid) = dbarraynum($result)) {
if ($parentid!=0) {
$title=getparent($parentid,$title);
}
echo '<option value="'.$cidp.'">'.$title.'</option>';
}
echo '</select></td></tr><tr><td>'.$locale['ESHPCATS101'].'</td>
<td><select class="textbox" name="status" size="1">
<option value="'.$cat_data['status'].'" SELECTED>'.$locale['ESHPCATS102'].'</option>
<option value="1">'.$locale['ESHPCATS103'].'</option>
<option value="2">'.$locale['ESHPCATS104'].'</option>
</select></td></tr>';
echo "<tr><td>".$locale['ESHPCATS109']."</td>
<td><select name='access' class='textbox'>
$visibility_opts</select></td>
</tr>";

echo '<tr><td colspan="2" align="center">
<input type="hidden" name="cid" value="'.$_POST['cid'].'">
<input type="hidden" name="SaveCategoryChanges" value="SaveCategoryChanges">
<input class="button" type="submit" name="submit" value="'.$locale['ESHPCATS112'].'  &raquo; '.$cat_data['title'].'"><br /><br />';
echo "<a href='".FUSION_SELF.$aidlink."&amp;a_page=Categories&deletecat&cid=".$_POST['cid']."'><b>".$locale['ESHPCATS117']."  &raquo;  ".$stitle."</b></a>";
echo '</td></tr></table></form></fieldset>';
echo "<HR />";
}

if (isset($_POST['AddSubCategory'])) {

$cid = stripinput($_POST['cid']);
$title = stripinput($_POST['title']);
$image = stripinput($_POST['image']);
$status = stripinput($_POST['status']);
$languages = "";

for ($pl=0;$pl<sizeof($_POST['languages']);$pl++) {
   $languages .= $_POST['languages'][$pl].($pl<(sizeof($_POST['languages'])-1)?".":"");
}

$order = "";
dbquery("INSERT INTO ".DB_ESHOP_CATS." (cid,title,access,image,parentid,status,cat_order,cat_languages)VALUES (NULL, '$title','$access', '$image', '$cid', '$status','$order','$languages');"); 
redirect("".FUSION_SELF.$aidlink."&amp;a_page=Categories&catadded");
}


if (isset($_POST['AddMainCategory'])) {

$cid = stripinput($_POST['cid']);
$title = stripinput($_POST['title']);
$image = stripinput($_POST['image']);
$status = stripinput($_POST['status']);
$languages = "";

for ($pl=0;$pl<sizeof($_POST['languages']);$pl++) {
   $languages .= $_POST['languages'][$pl].($pl<(sizeof($_POST['languages'])-1)?".":"");
}

$order = "";
dbquery("INSERT INTO ".DB_ESHOP_CATS." (cid,title,access,image,parentid,status,cat_order,cat_languages)VALUES (NULL, '$title','$access', '$image', '0', '$status','$order','$languages');"); 
redirect("".FUSION_SELF.$aidlink."&amp;a_page=Categories&catadded");
}


echo"<fieldset style='align:left;width:97%;display:block;float:left;margin-left:10px;margin-right:10px;margin-top:2px;margin-bottom:2px;'>
<legend>&nbsp;<b> ".$locale['ESHPCATS123']." </b>&nbsp;</legend>";
echo '<table width="100%" cellspacing="1" cellpadding="1" border="0">';
echo "<form name='addcat' action='".FUSION_SELF.$aidlink."&amp;a_page=Categories&AddMainCategory' method='post'>";
echo '<input type="hidden" name="AddMainCategory" value="AddMainCategory" /><tr>
<td>'.$locale['ESHPCATS100'].'</td>
<td><input class="textbox" type="text" name="title" size="30" maxlength="100"/></td></tr>';

$languages = "";

for ($x=0;$x<sizeof($enabled_languages);$x++) {
	$languages .= $enabled_languages[$x].(($x<sizeof($enabled_languages)-1)?".":"");
}

$langs = explode('.', $languages);
$locale_files = makefilelist(LOCALE, ".|..", true, "folders");

echo "<td>".$locale['ESHPPRO191']."</td>";
echo "<td colspan='2'>";
for ($i=0;$i<sizeof($locale_files);$i++) {
if (in_array($locale_files[$i], $enabled_languages)) {
echo "<input type='checkbox' value='".$locale_files[$i]."' name='languages[]' class='textbox' ".(in_array($locale_files[$i], $langs)?"checked='checked'":"")."> ".str_replace('_', ' ', $locale_files[$i])." ";
}
if ($i%2==0 && $i!=0) echo "<br  />";
}
echo "</td></tr>";

echo '<tr>
<td>'.$locale['ESHPCATS105'].'</td><td>';
$image="default.png";
echo "<select name='image' class='textbox' style='width:200px;'><option value='default.png'>".$locale['ESHPCATS122']."</option>$cat_list</select>";
echo '</td></tr><tr><td>'.$locale['ESHPCATS101'].'</td>
<td><select class="textbox" name="status" size="1">
<option value="1" SELECTED>'.$locale['ESHPCATS102'].'</option>
<option value="1">'.$locale['ESHPCATS103'].'</option>
<option value="2">'.$locale['ESHPCATS104'].'</option>
</select></td></tr>';

echo "<tr><td>".$locale['ESHPCATS109']."</td>
<td><select name='access' class='textbox'>
$visibility_opts</select></td>
</tr>";
echo '<tr><td colspan="2" align="center">
	<input type="hidden" name="cid" value="0">
	<input class="button" type="submit" value="'.$locale['ESHPCATS112'].'" /></td>
</tr>
</table></form></fieldset>';

echo "<br />";
echo"<fieldset style='align:left;width:97%;display:block;float:left;margin-left:10px;margin-right:10px;margin-top:2px;margin-bottom:2px;'>
<legend>&nbsp;<b> ".$locale['ESHPCATS124']." </b>&nbsp;</legend>";
$result = dbquery("select * from ".DB_ESHOP_CATS."");
	$numrows = dbrows($result);
	if ($numrows > 0) {
$image = "default.png";
echo '<table width="100%" cellspacing="1" cellpadding="1" border="0">';
echo "<form name='addcat' action='".FUSION_SELF.$aidlink."&amp;a_page=Categories&AddSubCategory' method='post'>";
echo '<tr><td>'.$locale['ESHPCATS113'].' </td>
<td><input class="textbox" type="text" name="title" size="30" maxlength="100"/></td></tr>
<tr><td>'.$locale['ESHPCATS105'].'</td><td>';
echo "<select name='image' class='textbox' style='width:200px;'><option value='default.png'>".$locale['ESHPCATS122']."</option>$cat_list</select>";
echo '</td></tr><tr><td>'.$locale['ESHPCATS107'].'</td>
<td><select class="textbox" name="cid">';
$result=dbquery("select cid, title, parentid from ".DB_ESHOP_CATS."  order by parentid,title");
while(list($cidp, $title, $parentid) = dbarraynum($result))	{
	if ($parentid!=0) $title=getparent($parentid,$title);
	echo '<option value="'.$cidp.'">'.$title.'</option>';
}
echo '</select></td></tr>';
for ($x=0;$x<sizeof($enabled_languages);$x++) {
	$languages .= $enabled_languages[$x].(($x<sizeof($enabled_languages)-1)?".":"");
}

$langs = explode('.', $languages);
$locale_files = makefilelist(LOCALE, ".|..", true, "folders");

echo "<td>".$locale['ESHPPRO191']."</td>";
echo "<td colspan='2'>";
for ($i=0;$i<sizeof($locale_files);$i++) {
if (in_array($locale_files[$i], $enabled_languages)) {
echo "<input type='checkbox' value='".$locale_files[$i]."' name='languages[]' class='textbox' ".(in_array($locale_files[$i], $langs)?"checked='checked'":"")."> ".str_replace('_', ' ', $locale_files[$i])." ";
}
if ($i%2==0 && $i!=0) echo "<br  />";
}
echo "</td></tr>";
echo '<tr><td>'.$locale['ESHPCATS101'].' </td>
<td><select class="textbox" name="status" size="1">
<option value="1" SELECTED>'.$locale['ESHPCATS102'].'</option>
<option value="1">'.$locale['ESHPCATS103'].'</option>
<option value="2">'.$locale['ESHPCATS104'].'</option>
</select></td></tr>';

echo "<tr><td>".$locale['ESHPCATS109']."</td>
<td><select name='access' class='textbox'>
$visibility_opts</select></td>
</tr>";

echo '<tr><td colspan="2" align="center">
<input type="hidden" name="AddSubCategory" value="AddSubCategory" />
<input class="button" type="submit" value="'.$locale['ESHPCATS112'].'" /></td>
</tr></table></form></fieldset>';

echo "<br />";


echo"<fieldset style='align:left;width:97%;display:block;float:left;margin-left:10px;margin-right:10px;margin-top:2px;margin-bottom:2px;'>
<legend>&nbsp;<b> ".$locale['ESHPCATS128']." </b>&nbsp;</legend>";

echo "<form name='addcat' action='".FUSION_SELF.$aidlink."&amp;a_page=Categories&EditCurrentCategory' method='post'>";
echo '<table width="100%" border="0" cellspacing="1" cellpadding="1"><tr><td align="center">';
echo ''.$locale['ESHPCATS106'].' <select class="textbox" name="cid">';
$result2=dbquery("select * from ".DB_ESHOP_CATS." order by parentid, title");
while($cat_data = dbarray($result2)) 
{
if ($cat_data['parentid']!=0) $cat_data['title']=getparent($cat_data['parentid'],$cat_data['title']);
echo '<option value="'.$cat_data['cid'].'" selected>'.$cat_data['title'].'</option>';
}
echo '</select>
<input type="hidden" name="EditCurrentCategory" value="EditCurrentCategory">
<input class="button" type="submit" value="'.$locale['ESHPCATS114'].'"></td></tr></table></form></fieldset>';
} else {
echo "<br /><div class='admin-message'>".$locale['ESHPCATS115']."</div><br /></td></tr></table></form></fieldset>";
}

?>