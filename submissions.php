<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: submissions.php
| Author: Frederick MC Chan
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "maincore.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."submissions.php";
add_to_title(str_replace('...', '', $locale['UM089']));

$modules = array(
    'n' => infusion_exists('news'),
    'p' => infusion_exists('gallery'),
    'a' => infusion_exists('articles'),
    'd' => infusion_exists('downloads'),
    'l' => infusion_exists('weblinks'),
    'b' => infusion_exists('blog'),
    'q' => infusion_exists('faq'),
);
$sum = array_sum($modules);
if (!$sum) {
    redirect("index.php");
}

$submission_types = array(
    'news'      => array('link' => "submit.php?stype=n", 'title' => $locale['submit_0000']),
    'blog'      => array('link' => "submit.php?stype=b", 'title' => $locale['submit_0005']),
    'articles'  => array('link' => "submit.php?stype=a", 'title' => $locale['submit_0001']),
    'downloads' => array('link' => "submit.php?stype=d", 'title' => $locale['submit_0002']),
    'gallery'   => array('link' => "submit.php?stype=p", 'title' => $locale['submit_0003']),
    'weblinks'  => array('link' => "submit.php?stype=l", 'title' => $locale['submit_0004']),
    'faq'       => array('link' => "submit.php?stype=q", 'title' => $locale['submit_0006']),
);

foreach ($submission_types as $db => $submit) {
    if (infusion_exists($db)) {
        opentable(sprintf($submit['title'], ''));
        echo "<a href='".$submit['link']."'>".sprintf($submit['title'], str_replace('...', '', $locale['UM089']))."</a>";
        closetable();
    }
}
require_once THEMES."templates/footer.php";
