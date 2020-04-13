<?php
require_once (__DIR__."/../../maincore.php");
require_once (INCLUDES."theme_functions_include.php");
require_once (TEMPLATES."render_functions.php");

if (check_get("status") && check_get("toast") && check_get("title") && check_get("description") && (check_get("icon") or check_get("image"))) {

    add_notice(get("status"), array(
        "toast"       => TRUE,
        "title"       => get("title"),
        "description" => get("description"),
        "icon"        => get("icon"),
        "image"       => get("image")
    ));

    echo render_notices(get_notices());
}
