<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: profile.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__."/maincore.php";
require_once THEMES."templates/header.php";
$locale = fusion_get_locale("", LOCALE.LOCALESET."user_fields.php");
$settings = fusion_get_settings();
$profile_id = get("lookup", FILTER_VALIDATE_INT);
$group_id = get("group_id", FILTER_VALIDATE_INT);
if ($profile_id) {
    /*
     * Show user profile
     */
    require_once THEMES."templates/global/profile.php";
    $userFields = new PHPFusion\UserFields();
    $userFields->show_admin_options = TRUE;
    $userFields->method = 'display';
    $userFields->display_profile_output();

} else if ($group_id) {
    /*
     * Show group
     */
    \PHPFusion\UserGroups::getInstance()->setGroup($group_id)->showGroup();
} else {
    redirect(BASEDIR."index.php");
}

require_once THEMES."templates/footer.php";
