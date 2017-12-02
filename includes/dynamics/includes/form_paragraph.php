<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_paragraph.php
| Author: Frederick MC CHan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
function form_para($title, $id, $class = 'underline', array $options = []) {
    $options += [
        'tip' => !empty($options['tip']) ? "title='".$options['tip']."'" : '',
    ];
    $html = "<h4 id='$id' class='m-b-20 $class'>$title ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."</h4>\n";

    return $html;
}
