<?php
(defined("IN_FUSION") || exit);

function update_menu($data) {
    parse_str($data["link_sort"], $data["link_sort"]);;
    /** update link category and ordering */
    if (!empty($data['link_sort']['menuItem'])) {
        $order = 1;
        foreach ($data['link_sort']["menuItem"] as $link_id => $link_cat) {
            $link_cat = (int)($link_cat !== NULL ? $link_cat : 0);
            dbquery("UPDATE ".DB_SITE_LINKS." SET link_cat=:cid, link_order=:oid WHERE link_id=:id", array(
                ":cid" => (int)$link_cat,
                ":id"  => (int)$link_id,
                ":oid" => (int)$order,
            ));
            $order++;
        }
    }
}
