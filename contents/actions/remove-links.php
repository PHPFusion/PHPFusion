<?php
(defined("IN_FUSION") || exit);

function remove_links($data) {
    $error_status= TRUE;
    if (isnum($data["_id"])) {
        // Find the link category
        $link_cat = (int)dbresult(dbquery("SELECT link_cat FROM ".DB_SITE_LINKS." WHERE link_id=:lid", array(":lid" => (int)$data["_id"])), 0);
        // Remove link
        dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_id=:lid", array(":lid" => (int)$data["_id"]));
        // Resort order after link removal
        $result = dbquery("SELECT link_id FROM ".DB_SITE_LINKS." WHERE link_position=:pos AND link_cat=:cat ORDER BY link_order ASC, link_name ASC", array(
            ":cat" => $link_cat,
            ":pos" => $data["_position"]
        ));
        if (dbrows($result)) {
            // reorder
            $order = 1;
            while ($cdata = dbarray($result)) {
                dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=:order WHERE link_id=:lid", array(
                    ":lid"   => (int)$cdata["link_id"],
                    ":order" => (int)$order
                ));
                $order++;
            }
        }
        $error_status = FALSE;
    }
    echo json_encode(array("error" => $error_status));
}
