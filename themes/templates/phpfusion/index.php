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

        <!-- Devices -->
        <div class="position-absolute absolute-center overflow-hidden w-screen" style="width: 100vw; height:1862px;">

            <!-- cms logo -->
            <div class="bg-img opacity-0" cms-text style="width:100vw; height:2000px;">
                <img class="bg-img cms-logo loaded" src="<?php echo IMAGES ?>assets/cmslogo.png">
                <div class="bg-img cms-text">Discover the power of PHPFusion, a lightweight yet
                    powerful CMS designed for simplicity and flexibility.
                    From dynamic content management to built-in security,
                    PHPFusion has everything you need to create and
                    manage your website with ease. Whether you're a beginner or an experienced developer,
                    PHPFusion is the perfect choice for your next project.
                </div>
            </div>


            <div class="position-absolute absolute-center overflow-hidden w-screen content-height" style="width:calc(var(--vw, 1vw)* 50.3);height: calc(var(--vw, 1vw)* 30.889);">
                <!-- Logo -->
                <div class="bg-img" laptop-transform>
                    <div class="bg-img" laptop-scale>
                        <!-- Laptop     -->
                        <div class="bg-img opacity-0" laptop-opacity>
                            <img class="bg-img loaded" src="<?php echo IMAGES ?>assets/laptop.png" data-ll-status="loaded">
                        </div>
                        <!-- Logo -->
                        <div class="relative w100 h100" init-enter-os="" style="opacity: 1; transform: translateY(0px);">
                            <img class="logo-v9 absolute absolute-center" src="<?php echo IMAGES ?>assets/9logo.png">
                        </div>
                        <!--Fusion LTE background-->
                        <div class="relative w100 h100" lte-bg style="opacity:0; transform:scale(0), translateY(0px);">
                            <img class="admin-v9 position-absolute absolute-center" src="<?php echo IMAGES ?>assets/phpfusion_admin_LTE.png">
                        </div>
                    </div>
                </div>
            </div>


        </div>


    </div>

    <div class="home-wrapper mt-spacer">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <div class="hero">
                        <h3 class="text-success bold">Infusions</h3>
                        <h2 class="text-dark bold">Expandability with modular applications</h4>
                            <p style="font-size:1.8rem;color:#202020;">Enhance your PHPFusion experience with modular applications—easily installable extensions that expand system functionality to meet your needs.</p>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-6">
                    <div class="well">
                        <h3 class="text-success bold">Themes</h3>
                        <h2 class="text-dark bold">Customizable with themes and templates</h4>
                            <p style="font-size:1.8rem;color:#202020;">Personalize your site with PHPFusion’s flexible themes and templates—effortless customization to match your style.</p>

                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-6">
                    <div class="well">Lightweight</div>
                    <h2 class="text-dark bold">Fast performant</h4>
                        <p style="font-size:1.8rem;color:#202020;">Enhance your PHPFusion experience with modular applications—easily installable extensions that expand system functionality to meet your needs.</p>
                </div>
            </div>
        </div>

    </div>


<?php

}
