/*-------------------------------------------------------+
 | PHP-Fusion Content Management System
 | Copyright (C) 2002 - 2016 PHP-Fusion Inc.
 | https://www.php-fusion.co.uk/
 +--------------------------------------------------------+
 | Name: Septenary Theme
 | Filename: includes/search.js
 | Version: 1.00
 | Author: PHP-Fusion Mods UK
 | Developer & Designer:
 | Craig (http://www.phpfusionmods.co.uk),
 | Chan (Lead developer of PHP-Fusion)
 +--------------------------------------------------------+
 | This program is released as free software under the
 | Affero GPL license. You can redistribute it and/or
 | modify it under the terms of this license which you
 | can read by viewing the included agpl.txt or online
 | at www.gnu.org/licenses/agpl.html. Removal of this
 | copyright header is strictly prohibited without
 | written permission from the original author(s).
 +--------------------------------------------------------*/

function ValidateForm(frm) {
    if (frm.stext.value == '') {
        alert('You Must Enter Something In The Search!');
        return false;
    }
    if (frm.stext.value.length < 3) {
        alert('Search text must be at least 3 characters long!');
        return false;
    }
}