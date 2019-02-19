<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_blacklist.ajax.php
| Author: Chan (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__."../../../maincore.php";

require_once LOCALE.LOCALESET."user_fields/user_blacklist.php";
$user_id = (isset($_POST['user_id']) && isnum($_POST['user_id'])) ? $_POST['user_id'] : 0;
$user_query = dbquery("SELECT user_id, user_blacklist FROM ".DB_USERS." WHERE user_id='".$userdata['user_id']."' LIMIT 1");
if (dbrows($user_query) > 0) {
    $data = dbarray($user_query);
    $user_blacklist = $data['user_blacklist'] ? explode('.', $data['user_blacklist']) : [];
    if (in_array($user_id, $user_blacklist)) {
        $user_blacklist = array_flip($user_blacklist);
        unset($user_blacklist[$user_id]);
        $data['user_blacklist'] = implode('.', array_flip($user_blacklist));
        $result = dbquery("UPDATE ".DB_USERS." SET user_blacklist='".$data['user_blacklist']."' WHERE user_id='".$userdata['user_id']."'");
        if ($result) {
            $users = dbarray(dbquery("SELECT user_name FROM ".DB_USERS." WHERE user_id='".$user_id."' LIMIT 1"));
            echo "<strong>".(sprintf($locale['uf_blacklist_004'], $users['user_name']))."</strong>";
        }
    } else {
        echo $locale['uf_blacklist_005'];
    }
} else {
    echo $locale['uf_blacklist_006'];
}
