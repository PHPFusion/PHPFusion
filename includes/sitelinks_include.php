<?php
(defined('IN_FUSION') || exit);

function verify_sitelinks($link_id) {
    if (isnum($link_id)) {
        return dbcount("(link_id)", DB_SITE_LINKS, "link_id='".intval($link_id)."'");
    }

    return FALSE;
}

function delelte_sitelinks($link_id) {
    $result = NULL;
    if (isnum($link_id)) {
        $data = dbarray(dbquery("SELECT link_order FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_id='".$_GET['link_id']."'"));
        $result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order-1 ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_order>'".$data['link_order']."'");
        if ($result) {
            $result = dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_id='".$_GET['link_id']."'");
        }

        return $result;
    }

    return $result;
}

function get_sitelinks($id) {
    $data = [];
    $link_query = "SELECT * FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."' AND" : "WHERE")." link_id='$id'";
    $result = dbquery($link_query);
    if (dbrows($result)) {
        $data = dbarray($result);
    }

    return $data;
}
