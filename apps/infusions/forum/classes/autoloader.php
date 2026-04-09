<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: autoloader.php
| Author: Core Development Team
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
        "PHPFusion\\Forums\\Forum"                      => FORUM_CLASSES."forum/forum.php",
        "PHPFusion\\Forums\\Threads\\ViewThread"        => FORUM_CLASSES."threads/view.php",
        "PHPFusion\\Forums\\Threads\\ForumThreads"      => FORUM_CLASSES."threads/threads.php",
        "PHPFusion\\Forums\\Threads\\ThreadFilter"      => FORUM_CLASSES."threads/filter.php",
        "PHPFusion\\Forums\\Threads\\Poll"              => FORUM_CLASSES."threads/poll.php",
        "PHPFusion\\Forums\\Threads\\Attachment"        => FORUM_CLASSES."threads/attachment.php",
        "PHPFusion\\Forums\\Threads\\Forum_Mood"        => FORUM_CLASSES."threads/mood.php",
        "PHPFusion\\Forums\\ForumServer"                => FORUM_CLASSES."server.php",
        "PHPFusion\\Forums\\ThreadTags"                 => FORUM_CLASSES."forum/tags.php",
        "PHPFusion\\Forums\\Moderator"                  => FORUM_CLASSES."mods.php",
        "PHPFusion\\Forums\\Post\\NewThread"            => FORUM_CLASSES."post/new_thread.php",
        "PHPFusion\\Forums\\Post\\QuickReply"           => FORUM_CLASSES."post/quick_reply.php",
        "PHPFusion\\httpdownload"                       => INCLUDES."class.httpdownload.php",
        "PHPFusion\\Forums\\Admin\\ForumAdminInterface" => FORUM_CLASSES."admin/admin.php",
        "PHPFusion\\Forums\\Admin\\ForumAdminView"      => FORUM_CLASSES."admin/view.php",
        "PHPFusion\\Forums\\Admin\\ForumAdminRanks"     => FORUM_CLASSES."admin/ranks.php",
        "PHPFusion\\Forums\\Admin\\ForumAdminMood"      => FORUM_CLASSES."admin/mood.php",
        "PHPFusion\\Forums\\Admin\\ForumAdminSettings"  => FORUM_CLASSES."admin/settings.php",
        "PHPFusion\\Forums\\Admin\\ForumAdminTags"      => FORUM_CLASSES."admin/tags.php",
        "PHPFusion\\Forums\\Postify\\Forum_Postify"     => FORUM_CLASSES."postify.php",
        "PHPFusion\\Forums\\Threads\\Forum_Bounty"      => FORUM_CLASSES."threads/bounty.php"
    ];

    if (isset($autoload_register_paths[$className])) {
        $fullPath = $autoload_register_paths[$className];
        if (is_file($fullPath)) {
            require $fullPath;
        }
    }
});
