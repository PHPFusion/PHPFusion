<?php
(defined("IN_FUSION") || exit);

function remove_menu($data) {
    $menu_id = $data["links_menu"];
    $status = "Fail";
    if (isnum($menu_id)) {
        if (dbcount("(menu_id)", DB_SITE_MENUS, "menu_id=:mid", array(":mid" => (int)$menu_id))) {
            dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_position=:mid", array(":mid" => (int)$menu_id));
            dbquery("DELETE FROM ".DB_SITE_MENUS." WHERE menu_id=:mid", array(":mid" => (int)$menu_id));
            $status = "Success";
        }
    }
    echo json_encode(array("status" => $status));
    die();
}
