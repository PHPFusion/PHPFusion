<?php
$locale['debonair_0400'] = "Latest Users";
$locale['debonair_0401'] = "There are currently no user";

// is a user panel
$result = dbquery("select user_id, user_name, user_status from ".DB_USERS." order by user_joined DESC");
if (dbrows($result)>0) {
	while ($data = dbarray($result)) {
		echo display_avatar($data, '30px');
	}
} else {
	echo $locale['debonair_0401'];
}
