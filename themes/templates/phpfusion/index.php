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

    $version = '9.10.30';
    fusion_load_script(TEMPLATES . 'phpfusion/assets/styles/index.css', 'css');
    // Disable all panels on this template. 
    PHPFusion\Panels::getInstance()->hideAll();
    // Add methods to disable the home calculations.
?>
    <div class="banner spacer-lg">
        <div class="display-flex flex-column align-items-center justify-content-center text-center">
            <h1 class="serif" style="max-width:690px;">Infuse your web presence</h1>
            <h4 style="max-width:690px;font-weight:400;line-height:1.5;">PHPFusion is an award winning content management system (CMS), which enables you to build websites, as well as web applications.</h4>

            <div class="display-flex flex-row align-items-center justify-content-center spacer-sm">
                <?php echo form_text('giturl', '', 'https://github.com/PHPFusion/PHPFusion.git', [
                    'deactivate' => TRUE,
                    'class' => 'm-0 m-r-15',
                    'inner_width' => '300px',
                    'append' => TRUE,
                    'append_value' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16"><path d="M3.626 3.533a.249.249 0 0 0-.126.217v9.5c0 .138.112.25.25.25h8.5a.25.25 0 0 0 .25-.25v-9.5a.249.249 0 0 0-.126-.217.75.75 0 0 1 .752-1.298c.541.313.874.89.874 1.515v9.5A1.75 1.75 0 0 1 12.25 15h-8.5A1.75 1.75 0 0 1 2 13.25v-9.5c0-.625.333-1.202.874-1.515a.75.75 0 0 1 .752 1.298ZM5.75 1h4.5a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-.75.75h-4.5A.75.75 0 0 1 5 4.75v-3A.75.75 0 0 1 5.75 1Zm.75 3h3V2.5h-3Z"></path></svg>',

                ]) ?>
                <a class="btn btn-default" href="<?php echo INFUSIONS ?>helpcenter/">Read the docs</a>
            </div>
            <div>
                Currently <strong>v<?php echo $version ?></strong> &middot; <a href="">Download</a> &middot; <a href="">All releases</a>
            </div>
        </div>
    </div>

    <div class="download spacer-lg">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M12.876.64V.639l8.25 4.763c.541.313.875.89.875 1.515v9.525a1.75 1.75 0 0 1-.875 1.516l-8.25 4.762a1.748 1.748 0 0 1-1.75 0l-8.25-4.763a1.75 1.75 0 0 1-.875-1.515V6.917c0-.625.334-1.202.875-1.515L11.126.64a1.748 1.748 0 0 1 1.75 0Zm-1 1.298L4.251 6.34l7.75 4.474 7.75-4.474-7.625-4.402a.248.248 0 0 0-.25 0Zm.875 19.123 7.625-4.402a.25.25 0 0 0 .125-.216V7.639l-7.75 4.474ZM3.501 7.64v8.803c0 .09.048.172.125.216l7.625 4.402v-8.947Z"></path>
                    </svg>
                    <h4>Install via Github</h4>
                    <div class="m-b-10">Get PHPFusion directly from GitHub—access the latest version and updates straight from the source.</div>
                    <div>Install PHPFusion via GitHub and stay up to date with the latest features and improvements. Follow our step-by-step guide to download, set up, and start building your website with ease.</div>
                    <div class="spacer-sm">
                        <div class="well">git clone https://github.com/php-fusion/PHPFusion.git</div>
                        <div class="well">cd PHPFusion</div>
                        <div>
                            <a href="">Read our installation docs</a> for more info on how to install PHPFusion via GitHub.</a>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-6">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M11.25 9.331V.75a.75.75 0 0 1 1.5 0v8.58l1.949-2.11A.75.75 0 1 1 15.8 8.237l-3.25 3.52a.75.75 0 0 1-1.102 0l-3.25-3.52A.75.75 0 1 1 9.3 7.22l1.949 2.111Z"></path>
                        <path d="M2.5 3.75v11.5c0 .138.112.25.25.25h18.5a.25.25 0 0 0 .25-.25V3.75a.25.25 0 0 0-.25-.25h-5.5a.75.75 0 0 1 0-1.5h5.5c.966 0 1.75.784 1.75 1.75v11.5A1.75 1.75 0 0 1 21.25 17h-6.204c.171 1.375.805 2.652 1.769 3.757A.752.752 0 0 1 16.25 22h-8.5a.75.75 0 0 1-.566-1.243c.965-1.105 1.599-2.382 1.77-3.757H2.75A1.75 1.75 0 0 1 1 15.25V3.75C1 2.784 1.784 2 2.75 2h5.5a.75.75 0 0 1 0 1.5h-5.5a.25.25 0 0 0-.25.25ZM10.463 17c-.126 1.266-.564 2.445-1.223 3.5h5.52c-.66-1.055-1.098-2.234-1.223-3.5Z"></path>
                    </svg>
                    <h4>Direct download</h4>
                    <div>Download PHPFusion directly and start building your website in no time. No complex setup—just grab the latest version and install it with ease. Get started today and explore the powerful features of PHPFusion!</div>
                    <div class="spacer-sm">
                        <h5><a class="go-item-link" href="#">Go to download page<?php echo ARROW ?></a></h5>
                    </div>
                </div>
            </div>
        </div><!--container-->
    </div>
    <div class="blockwall">
        <div class="container">
            <div class="postercard primary">
                <div class="description">
                    <div class="label label-default">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16">
                            <path d="M5 5.782V2.5h-.25a.75.75 0 0 1 0-1.5h6.5a.75.75 0 0 1 0 1.5H11v3.282l3.666 5.76C15.619 13.04 14.543 15 12.767 15H3.233c-1.776 0-2.852-1.96-1.899-3.458Zm-2.4 6.565a.75.75 0 0 0 .633 1.153h9.534a.75.75 0 0 0 .633-1.153L12.225 10.5h-8.45ZM9.5 2.5h-3V6c0 .143-.04.283-.117.403L4.73 9h6.54L9.617 6.403A.746.746 0 0 1 9.5 6Z"></path>
                        </svg>
                        Features
                    </div>
                    <h2 class="serif">Discover the Latest PHPFusion CMS</h3>
                        <div class="spacer-md">PHPFusion offers a wide range of features to help you build a powerful website. From user management to content creation, PHPFusion has everything you need to create a seamless web experience.</div>
                        <div>
                            <a href="#" class="display-inline-block btn btn-default">
                                <div class="go-item-link">Explore features<?php echo ARROW ?></div>
                            </a>
                        </div>
                </div>
                <div class="image">
                    <div class="cover"><img src="<?php echo IMAGES ?>assets/bg1.jpg"></div>
                </div>

            </div>
        </div>
    </div>
    <div class="blockwall">
        <div class="container">
            <div class="postercard info">
                <div class="description">
                    <div class="label label-default">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16">
                            <path d="M4 8H2.5a1 1 0 0 0-1 1v5.25a.75.75 0 0 1-1.5 0V9a2.5 2.5 0 0 1 2.5-2.5H4V5.133a1.75 1.75 0 0 1 1.533-1.737l2.831-.353.76-.913c.332-.4.825-.63 1.344-.63h.782c.966 0 1.75.784 1.75 1.75V4h2.25a.75.75 0 0 1 0 1.5H13v4h2.25a.75.75 0 0 1 0 1.5H13v.75a1.75 1.75 0 0 1-1.75 1.75h-.782c-.519 0-1.012-.23-1.344-.63l-.761-.912-2.83-.354A1.75 1.75 0 0 1 4 9.867Zm6.276-4.91-.95 1.14a.753.753 0 0 1-.483.265l-3.124.39a.25.25 0 0 0-.219.248v4.734c0 .126.094.233.219.249l3.124.39a.752.752 0 0 1 .483.264l.95 1.14a.25.25 0 0 0 .192.09h.782a.25.25 0 0 0 .25-.25v-8.5a.25.25 0 0 0-.25-.25h-.782a.25.25 0 0 0-.192.09Z"></path>
                        </svg>
                        Expansion
                    </div>
                    <h2 class="serif m-b-20">Expand your website easily</h2>
                    <div class="spacer-md">PHPFusion Infusions are apps designed to help you build a powerful website with ease. Choose from a wide range of apps to enhance your website and provide your users with a seamless experience.</div>
                    <div>
                        <a href="#" class="display-inline-block btn btn-default">
                            <div class="go-item-link">Find apps<?php echo ARROW ?></div>
                        </a>
                    </div>
                </div>
                <div class="image">
                    <div class="cover"><img src="<?php echo IMAGES ?>assets/bg2.jpg"></div>
                </div>
            </div>
        </div>
    </div>


<?php

}
