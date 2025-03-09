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
    fusion_load_script(TEMPLATES . 'phpfusion/assets/styles/index.css', 'css');
    fusion_load_script(TEMPLATES . 'phpfusion/assets/scripts/index.js');

    // Disable all panels on this template. 
    PHPFusion\Panels::getInstance()->hideAll();
    // Add methods to disable the home calculations.

?>
    <div class="home-wrapper">
        <div class="w-100 position-relative">
            <div class="position-sticky w100 overflow-hidden content-height">
                <?php
                // Display the homepage content here.
                ?>
            </div>
        </div>
    </div>
    <!--background-->
    <div class="bg-img overflow-hidden">
        <div class="bg-img">
            <img class="bg-img" src="<?php echo IMAGES ?>assets/bg.png" style="transform:scaleX(1.1);">
        </div>
    </div>
    <!--whitebg-->
    <div class="bg-img overflow-hidden">
        <div class="opacity-0 absolute w100 h100 -left-10 -top-10 bg-layer" bg-layer="" style="clip-path: inset(50.46% 42.55% round 40px); transition: background 0.3s; background: rgb(245, 245, 245);"></div>
    </div>
    <!--clip-bg-->
    <div class="position-absolute absolute-center overflow-hidden">

        <!-- Laptop bg -->
        <div class="position-absolute absolute-center overflow-hidden w-screen" laptop-bg-position style="width: 1155px;">
            <!-- Laptop background clip path -->
            <div class="bg-img overflow-hidden" inner-bg-wrapper style="clip-path: inset(41.65% 29.85% round 0px);height:100%; width:100%;">
                <div class="bg-img">
                    <img class="bg-img opacity-0 loaded" src="<?php echo IMAGES ?>assets/bg.png" laptop-bg style="transform: scale(1); opacity: 1;">
                </div>
            </div>
        </div>


        <div class="position-absolute absolute-center overflow-hidden w-screen content-height" style="width:calc(var(--vw, 1vw)* 50.3);height: calc(var(--vw, 1vw)* 30.889);">
            <!-- Background -->
            <!-- Logo -->
            <div class="bg-img" laptop-transform="">
                <div class="bg-img" laptop-scale="">
                    <!-- Laptop     -->
                    <div class="bg-img opacity-0" laptop-opacity="">
                        <img class="bg-img loaded" src="<?php echo IMAGES ?>assets/laptop.png" data-ll-status="loaded">
                    </div>
                    <!-- Logo -->
                    <div class="relative w100 h100" init-enter-os="" style="opacity: 1; transform: translateY(0px);">
                        <img class="logo-v9 absolute absolute-center" src="<?php echo IMAGES ?>assets/9logo.png">
                    </div>
                </div>
            </div>
        </div>

    </div>


    </div>
    </div>
    </div>
<?php

}
