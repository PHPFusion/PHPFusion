<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: submit.php
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
require_once "maincore.php";
if (!iMEMBER) {
    redirect("index.php");
}
require_once THEMES."templates/header.php";
include_once INCLUDES."infusions_include.php";
$modules = array(
    'n' => infusion_exists('news'),
    'p' => infusion_exists('gallery'),
    'a' => infusion_exists('articles'),
    'd' => infusion_exists('downloads'),
    'l' => infusion_exists('weblinks'),
    'b' => infusion_exists('blog'),
    'q' => infusion_exists('faq')
);
$submit_types = array(
    'n' => array('link' => INFUSIONS."news/news_submit.php"),
    'p' => array('link' => INFUSIONS."gallery/photo_submit.php"),
    'a' => array('link' => INFUSIONS."articles/article_submit.php"),
    'd' => array('link' => INFUSIONS."downloads/download_submit.php"),
    'l' => array('link' => INFUSIONS."weblinks/weblink_submit.php"),
    'b' => array('link' => INFUSIONS."blog/blog_submit.php"),
    'q' => array('link' => INFUSIONS."faq/faq_submit.php"),
);
$_GET['stype'] = !empty($_GET['stype']) && isset($modules[$_GET['stype']]) && isset($submit_types[$_GET['stype']]) ? $_GET['stype'] : "";
$sum = array_sum($modules);
if ($sum && $_GET['stype']) {
   require_once $submit_types[$_GET['stype']]['link'];
} else {
    redirect('index.php');
}
require_once THEMES."templates/footer.php";