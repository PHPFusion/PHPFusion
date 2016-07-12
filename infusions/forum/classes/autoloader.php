<?php

spl_autoload_register(function ($className) {

    $autoload_register_paths = array(
        "PHPFusion\\Forums\\Forum"  => FORUM_CLASS."forum/forum.php",
        "PHPFusion\\Forums\\Threads\\ViewThread"     =>  FORUM_CLASS."threads/view.php",
        "PHPFusion\\Forums\\Threads\\ForumThreads"     =>  FORUM_CLASS."threads/threads.php",
        "PHPFusion\\Forums\\Threads\\Poll"     =>  FORUM_CLASS."threads/poll.php",
        "PHPFusion\\Forums\\Threads\\Attachment"     =>  FORUM_CLASS."threads/attachment.php",
        "PHPFusion\\Forums\\ForumServer"     =>  FORUM_CLASS."server.php",
        "PHPFusion\\Forums\\Functions"     =>  FORUM_CLASS."Functions.php",
        "PHPFusion\\Forums\\Moderator"     =>  FORUM_CLASS."mods.php",
        "PHPFusion\\Forums\\Post\\NewThread"     =>  FORUM_CLASS."post/new_thread.php",
        "PHPFusion\\httpdownload"           => INCLUDES."class.httpdownload.php",
        "PHPFusion\\Forums\\Admin\\ForumAdminInterface" => FORUM_CLASS."admin/admin.php",
        "PHPFusion\\Forums\\Admin\\ForumAdminView" => FORUM_CLASS."admin/view.php",
        "PHPFusion\\Forums\\Admin\\ForumAdminRanks" => FORUM_CLASS."admin/ranks.php",
        "PHPFusion\\Forums\\Admin\\ForumAdminSettings" => FORUM_CLASS."admin/settings.php",
    );

    $fullPath = $autoload_register_paths[$className];

    if (is_file($fullPath)) {
        require $fullPath;
    }

});