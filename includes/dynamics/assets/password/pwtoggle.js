/*-------------------------------------------------------+
 | PHP-Fusion Content Management System
 | Copyright (C) PHP-Fusion Inc
 | https://www.php-fusion.co.uk/
 +--------------------------------------------------------+
 | Filename: pwtoggle.js
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
function togglePasswordInput(button_id, field_id) {
    var button = $('#'+button_id);
    var input = $('#'+field_id);
    if (input.attr('type') == 'password') {
        input.attr('type', 'text');
        button.text(locale['hide']);
    } else {
        input.attr('type', 'password');
        button.text(locale['show']);
    }
}