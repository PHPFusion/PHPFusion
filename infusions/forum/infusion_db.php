<?php

if (!defined("FORUM_LOCALE")) {
    if (file_exists(INFUSIONS."forum/locale/".LOCALESET."forum.php")) {
        define("FORUM_LOCALE", INFUSIONS."forum/locale/".LOCALESET."forum.php");
    } else {
        define("FORUM_LOCALE", INFUSIONS."forum/locale/English/forum.php");
    }
}

if (!defined("FORUM_ADMIN_LOCALE")) {
    if (file_exists(INFUSIONS."forum/locale/".LOCALESET."forum_admin.php")) {
        define("FORUM_ADMIN_LOCALE", INFUSIONS."forum/locale/".LOCALESET."forum_admin.php");
    } else {
        define("FORUM_ADMIN_LOCALE", INFUSIONS."forum/locale/English/forum_admin.php");
    }
}

if (!defined("SETTINGS_LOCALE")) {
    if (file_exists(LOCALE.LOCALESET."admin/settings.php")) {
        define("SETTINGS_LOCALE", LOCALE.LOCALESET."admin/settings.php");
    } else {
        define("SETTINGS_LOCALE", LOCALE."English/admin/settings.php");
    }
}

if (!defined("FORUM_RANKS_LOCALE")) {
    if (file_exists(INFUSIONS."forum/locale/".LOCALESET."forum_ranks.php")) {
        define("FORUM_RANKS_LOCALE", INFUSIONS."forum/locale/".LOCALESET."forum_ranks.php");
    } else {
        define("FORUM_RANKS_LOCALE", INFUSIONS."forum/locale/English/forum_ranks.php");
    }
}

if (!defined("FORUM_TAGS_LOCALE")) {
    if (file_exists(INFUSIONS."forum/locale/".LOCALESET."forum_tags.php")) {
        define("FORUM_TAGS_LOCALE", INFUSIONS."forum/locale/".LOCALESET."forum_tags.php");
    } else {
        define("FORUM_TAGS_LOCALE", INFUSIONS."forum/locale/English/forum_tags.php");
    }
}

if (!defined("DB_FORUM_TAGS")) {
    define("DB_FORUM_TAGS", DB_PREFIX."forum_thread_tags");
}

if (!defined("FORUM_CLASS")) define("FORUM_CLASS", INFUSIONS."forum/classes/");
if (!defined("FORUM_SECTIONS")) define("FORUM_SECTIONS", INFUSIONS."forum/sections/");