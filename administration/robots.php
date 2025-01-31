<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: robots.php
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
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
pageaccess('ROB');

$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/robots.php");

add_breadcrumb(['link' => ADMIN.'robots.php'.fusion_get_aidlink(), 'title' => $locale['ROBOT_400']]);

function write_default() {
    $robots_content = "User-agent: *\n";
    $robots_content .= "Disallow: /config.php\n";
    $robots_content .= "Disallow: /administration/\n";
    $robots_content .= "Disallow: /includes/\n";
    $robots_content .= "Disallow: /locale/\n";
    $robots_content .= "Disallow: /themes/\n";
    $robots_content .= "Disallow: /print.php\n";

    return $robots_content;
}

$file = BASEDIR."robots.txt";

if (check_post('save_robots')) {
    $robots_content = sanitizer('robots_content', '', 'robots_content');

    if (!preg_check("/^[-0-9A-Z._\*\:\.\!\/@\s]+$/i", $robots_content)) {
        fusion_stop();
        addnotice("danger", $locale['ROBOT_417']);
    }

    if (fusion_safe()) {
        $message = !file_exists($file) ? $locale['ROBOT_416'] : $locale['ROBOT_412'];
        write_file($file, $robots_content);
        addnotice("success", $message);
        redirect(FUSION_REQUEST);
    }
}

if (check_post('set_default')) {

    if (!is_writable($file)) {
        fusion_stop();
        addnotice("danger", $locale['ROBOT_414']);
    }
    if (fusion_safe() && !defined('FUSION_NULL')) {
        write_file($file, write_default());
        addnotice("success", $locale['ROBOT_412']);
        redirect(FUSION_REQUEST);
    }
}

opentable($locale['ROBOT_400']);

if (!file_exists($file)) {
    echo "<div class='alert alert-danger text-center'><strong>".$locale['ROBOT_411']."</strong></div>\n";
    $current = write_default();
    $button = $locale['ROBOT_422'];
} else {

    $current = file_get_contents($file);
    $button = $locale['save'];
}

echo openform('robotsform', 'post', FUSION_REQUEST);
echo "<div class='text-center well'><strong>".$locale['ROBOT_420']."</strong>";
echo "<br/>";
echo str_replace(['[LINK]', '[/LINK]'], ["<a href='http://www.robotstxt.org/' target='_blank'>", "</a>",], $locale['ROBOT_421']);
echo "</div>";
echo form_textarea('robots_content', '', $current, ['height' => '300px']);
echo form_button('save_robots', $button, $button, ['class' => 'btn-primary m-r-10']);
echo file_exists($file) ? form_button('set_default', $locale['ROBOT_423'], $locale['ROBOT_423'], ['class' => 'btn-default']) : "";
echo closeform();

closetable();
add_to_jquery("$('#set_default').bind('click', function() { return confirm('".$locale['ROBOT_410']."'); });");

require_once THEMES.'templates/footer.php';
