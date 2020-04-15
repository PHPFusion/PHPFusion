<?php
(defined("IN_FUSION") || exit);

function update_links($data) {
    $status = FALSE;
    $target = NULL;
    if (isnum($data['_id'])) {
        $link = array(
            "link_id"          => (int)$data["_id"],
            "link_name"        => (string)$data["_name"],
            "link_url"         => (string)$data["_url"],
            "link_icon"        => (string)$data["_icon"],
            "link_visibility"  => (int)$data["_visibility"],
            "link_position"    => (int)$data["_position"],
            "link_status"      => (int)$data["_status"],
            "link_window"      => (int)(isset($data["_window"]) ? 1 : 0),
            "link_language"    => (string)$data["_language"],
            "link_type"        => (string)$data["_type"],
            "link_title"       => (string)$data["_title"],
            "link_description" => (string)descript($data["_description"]),
        );
        dbquery_insert(DB_SITE_LINKS, $link, "update");
        $target = $link["link_id"]."m-".$link["link_id"];
        $status = TRUE;
    }
    echo json_encode(array("status" => $status, "target" => $target));
}
