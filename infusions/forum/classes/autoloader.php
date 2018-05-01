<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: autoloader.php
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
spl_autoload_register(function ($className) {
    $autoload_register_paths = [
        "PHPFusion\\Forums\\Forum"                      => FORUM_CLASS."forum/forum.php",
        "PHPFusion\\Forums\\Threads\\ViewThread"        => FORUM_CLASS."threads/view.php",
        "PHPFusion\\Forums\\Threads\\ForumThreads"      => FORUM_CLASS."threads/threads.php",
        "PHPFusion\\Forums\\Threads\\ThreadFilter"      => FORUM_CLASS."threads/filter.php",
        "PHPFusion\\Forums\\Threads\\Poll"              => FORUM_CLASS."threads/poll.php",
        "PHPFusion\\Forums\\Threads\\Attachment"        => FORUM_CLASS."threads/attachment.php",
        "PHPFusion\\Forums\\Threads\\Forum_Mood"        => FORUM_CLASS."threads/mood.php",
        "PHPFusion\\Forums\\ForumServer"                => FORUM_CLASS."server.php",
        "PHPFusion\\Forums\\ThreadTags"                 => FORUM_CLASS."forum/tags.php",
        "PHPFusion\\Forums\\Functions"                  => FORUM_CLASS."Functions.php",
        "PHPFusion\\Forums\\Moderator"                  => FORUM_CLASS."mods.php",
        "PHPFusion\\Forums\\Post\\NewThread"            => FORUM_CLASS."post/new_thread.php",
        "PHPFusion\\Forums\\Post\\QuickReply"           => FORUM_CLASS."post/quick_reply.php",
        "PHPFusion\\httpdownload"                       => INCLUDES."class.httpdownload.php",
        "PHPFusion\\Forums\\Admin\\ForumAdminInterface" => FORUM_CLASS."admin/admin.php",
        "PHPFusion\\Forums\\Admin\\ForumAdminView"      => FORUM_CLASS."admin/view.php",
        "PHPFusion\\Forums\\Admin\\ForumAdminRanks"     => FORUM_CLASS."admin/ranks.php",
        "PHPFusion\\Forums\\Admin\\ForumAdminMood"      => FORUM_CLASS."admin/mood.php",
        "PHPFusion\\Forums\\Admin\\ForumAdminSettings"  => FORUM_CLASS."admin/settings.php",
        "PHPFusion\\Forums\\Admin\\ForumAdminTags"      => FORUM_CLASS."admin/tags.php",
        "PHPFusion\\Forums\\Postify\\Forum_Postify"     => FORUM_CLASS."postify.php",
        "PHPFusion\\Forums\\Threads\\Forum_Bounty"      => FORUM_CLASS."threads/bounty.php"
    ];

    if (isset($autoload_register_paths[$className])) {
        $fullPath = $autoload_register_paths[$className];
        if (is_file($fullPath)) {
            require $fullPath;
        }
    }
});
