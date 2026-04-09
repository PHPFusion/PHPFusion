<?php
(defined("IN_FUSION") || exit);

//$result = dbquery("SELECT w.* FROM ".DB_USER_WALLET." w INNER JOIN ".DB_USERS." u using(user_id) WHERE first_name !='' OR address !='' ORDER BY user_id");
//if (dbrows($result)) {
//    while ($data = dbarray($result)) {
//        $udata["user_id"] = $data["user_id"];
//        $udata["user_firstname"] = $data["first_name"];
//        $udata["user_lastname"] = $data["last_name"];
//        $udata["user_address"] = $data["address"]."|".$data["address_2"]."|".$data["country"]."|".$data["region"]."|".$data["city"]."|".$data["postcode"];
//        if ($udata["user_id"]) {
//            dbquery("UPDATE ".DB_USERS." SET user_firstname=:index01, user_lastname=:index02, user_address=:index03 WHERE user_id=:index04", [
//                ":index01" => $udata["user_firstname"],
//                ":index02" => $udata["user_lastname"],
//                ":index03" => $udata["user_address"],
//                ":index04" => $udata["user_user_id"],
//            ]);
//        }
//    }
//}
