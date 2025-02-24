<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: phpfusion/index.php
| Author: Meangczac (Chan), Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * Display homepage
 *
 * @param [type] $info
 * @return void
 */
function display_home($info) {

    // Disable all panels on this template. 
    PHPFusion\Panels::getInstance()->hideAll();
    // Add methods to disable the home calculations.

    ?>
    <h4>You have successfully overriden your homepage</h4>
    <?php    
}