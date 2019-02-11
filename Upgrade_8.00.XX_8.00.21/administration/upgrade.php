<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: upgrade.php
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
require_once "../maincore.php";
if (!checkrights("U") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
    redirect("../index.php");
}

require_once THEMES."templates/admin_header.php";
if (file_exists(LOCALE.LOCALESET."admin/upgrade.php")) {
    include LOCALE.LOCALESET."admin/upgrade.php";
} else {
    include LOCALE."English/admin/upgrade.php";
}

opentable($locale['400']);

echo "<div style='text-align:center' class='text-center' ><br />\n";

opentable($locale['400']);
echo "<div style='text-align:center'><br />\n";

if (isset($_GET['upgrade_ok'])) {
    echo "<div class='alert alert-success'>".$locale['502']."</div>\n";
}

echo "<form name='upgradeform' method='post' action='".FUSION_SELF.$aidlink."'>\n";
if (str_replace(".", "", $settings['version']) < "80021") {
    if (!isset($_POST['stage'])) {
        echo "<div class='well'>\n";
        echo sprintf($locale['500'], $locale['504'])."<br />\n".$locale['501']."\n";
        echo "</div>\n";
        echo "<input type='hidden' name='stage' value='2'>\n";
        echo "<input type='submit' name='upgrade' value='".$locale['400']."' class='button'><br /><br />\n";
    } else if (isset($_POST['upgrade']) && isset($_POST['stage']) && $_POST['stage'] == 2) {

        // Convert the Database to utf8mb4
        $result = dbquery("SELECT @@character_set_database as charset, @@collation_database as collation;");

        while ($db = dbarray($result)) {
            if ($db['charset'] == 'utf8' || $db['charset'] !== 'utf8mb4') {
                dbquery("SET NAMES 'utf8mb4'");
            }

            if ($db['collation'] == 'utf8_general_ci' || $db['collation'] !== 'utf8mb4_general_ci') {
                dbquery("ALTER DATABASE ".$db_name." CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            }
        }

        $result = dbquery("SHOW TABLES");

        while ($table = dbarraynum($result)) {
            if (preg_match("/^".DB_PREFIX."/i", $table[0])) {
                dbquery("ALTER TABLE ".$table[0]." CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            }
        }

        // Set a new version
        $result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value='8.00.21' WHERE settings_name='version'");
        redirect(FUSION_SELF.$aidlink."&amp;upgrade_ok");
    }

} else {
    echo $locale['401']."<br /><br />\n";
}

echo "</form>\n</div>\n";
closetable();

require_once THEMES."templates/footer.php";
