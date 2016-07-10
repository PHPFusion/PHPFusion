<?php

if (!defined("FORUM_LOCALE")) {
    if (file_exists(INFUSIONS."forum/locale/".LOCALESET."forum.php")) {
        define("FORUM_LOCALE", INFUSIONS."forum/locale/".LOCALESET."forum.php");
    } else {
        define("FORUM_LOCALE", INFUSIONS."forum/locale/English/forum.php");
    }
}

if (!defined("FORUM_CLASS")) define("FORUM_CLASS", INFUSIONS."forum/classes/");
if (!defined("FORUM_SECTIONS")) define("FORUM_SECTIONS", INFUSIONS."forum/sections/");