<?php
(defined("IN_FUSION") || exit);

function update_menu($data) {

    $menu_id = $data["links_menu"];

    if (in_array($data['links_menu'], array("M1", "M2", "M3")) ? TRUE : FALSE) {
        // update or create this if missing
        $menu_settings = array(
            "links_bbcode_".$data['links_menu']   => $data['links_bbcode'],
            "links_grouping_".$data['links_menu'] => $data['links_grouping'],
            "links_per_page_".$data['links_menu'] => $data['links_per_page'],
        );
        dbquery_update_settings($menu_settings);

    } else if (dbcount("(menu_id)", DB_SITE_MENUS, "menu_id=:mid", array(":mid" => $data['links_menu']))) {
        // update menu
        $menu_settings = array(
            "menu_id"             => $data['links_menu'],
            "menu_name"           => $data['links_menu_name'],
            "menu_bbcode"         => $data['links_bbcode'],
            "menu_grouping"       => $data['links_grouping'],
            "menu_links_per_page" => $data['links_per_page'],
        );
        dbquery_insert(DB_SITE_MENUS, $menu_settings, "update");
    } else {
        // create this menu.
        $menu_settings = array(
            "menu_id"             => 0,
            "menu_name"           => $data['links_menu_name'],
            "menu_bbcode"         => $data['links_bbcode'],
            "menu_grouping"       => $data['links_grouping'],
            "menu_links_per_page" => $data['links_per_page'],
        );
        $menu_id = dbquery_insert(DB_SITE_MENUS, $menu_settings, "save");
    }

    /** Update category and sort order */
    parse_str($data['links_sort'], $data['links_sort']);
    /** update link category and ordering */
    if (!empty($data['links_sort']['menuItem'])) {
        $order = 1;
        foreach ($data['links_sort']['menuItem'] as $link_id => $link_cat) {
            $link_cat = (int)($link_cat !== NULL ? $link_cat : 0);
            dbquery("UPDATE ".DB_SITE_LINKS." SET link_cat=:cid, link_order=:oid WHERE link_id=:id", array(
                ":cid" => (int)$link_cat,
                ":id"  => (int)$link_id,
                ":oid" => (int)$order,
            ));
            $order++;
        }
    }

    echo json_encode(array("menu_id" => $menu_id));

}
