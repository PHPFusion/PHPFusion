<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_news_include_button.php
| Author: Robert Gaudyn (Wooya)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
    die("Access Denied");
}
if (db_exists(DB_NEWS)) {
    include LOCALE.LOCALESET."search/news.php";
    $form_elements['news']['enabled'] = ["datelimit", "fields1", "fields2", "fields3", "sort", "order1", "order2", "chars"];
    $form_elements['news']['disabled'] = [];
    $form_elements['news']['display'] = [];
    $form_elements['news']['nodisplay'] = [];
    $radio_button['news'] = "<label><input type='radio' name='stype' value='news'".($_REQUEST['stype'] == "news" ? " checked='checked'" : "")." onclick=\"display(this.value)\" /> ".$locale['n400']."</label>";
}
