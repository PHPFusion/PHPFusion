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
function display_home($info)
{
fusion_load_script(TEMPLATES.'phpfusion/styles/index.css', 'css');

    // Disable all panels on this template. 
    PHPFusion\Panels::getInstance()->hideAll();
    // Add methods to disable the home calculations.

    ?>
    <div class="home-wrapper">
        <div class="w-100 position-relative">
            <div class="position-absolute w100 overflow-hidden content-height">
                <div class="bg-img overflow-hidden"><!--needs to clip-->
                    <img class="bg-img" src="<?php echo IMAGES ?>assets/bg.png">
                </div>
            </div>
            <div class="bg-img">
                <div class="position-absolute overflow-hidden">
                    <div class="position-absolute absolute-center">
                        Color
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
}
