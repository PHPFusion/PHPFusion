<?php
(defined("IN_FUSION") || exit);

if (!function_exists("display_lostpassword")) {
    function display_lostpassword($content) {
        $locale = fusion_get_locale();
        opentable($locale['400']);
        echo $content;
        closetable();
    }
}
