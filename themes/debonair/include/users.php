<?php
/**
 * Displays all users
 */

echo "<h3 class='icon1 margin'>".$locale['debonair_0400']."</h3>\n";
$result = dbquery("select user_id, user_name, user_status from ".DB_USERS." order by user_joined DESC");
if (dbrows($result)>0) {
	echo "<div class='m-b-10'>\n";
	while ($data = dbarray($result)) {
		echo display_avatar($data, '25px', "", true, "img-circle");
	}
	echo "</div>\n";
	echo "<div class='link-holder'><a href='".BASEDIR."members.php' class='more-dark'>".$locale['debonair_0401']."</a></div>\n";
} else {
	echo $locale['debonair_0402'];
}