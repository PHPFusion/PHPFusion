<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: package.php
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
function bootstrap4() {
    $settings = fusion_get_settings();
    $text_direction = fusion_get_locale('text-direction');

    if ($settings['bootstrap'] || defined('BOOTSTRAP')) {
        $boilerplate = BOILERPLATES;
        if (!empty('CDN')) {
            $boilerplate = CDN.'themes/boilerplates/';
        }
        ?>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="<?php echo $boilerplate.'bootstrap4/css/bootstrap.min.css' ?>">
        <?php add_to_footer('<script src="'.$boilerplate.'bootstrap4/js/bootstrap.bundle.min.js"></script>');
        add_to_jquery("Popper.Defaults.modifiers.computeStyle.gpuAcceleration = false;"); // Will be fixed in Bootstrap 4.1 - https://github.com/twbs/bootstrap/pull/24092
        ?>
        <?php if ($text_direction == 'rtl') : ?>
            <link rel="stylesheet" href="<?php echo $boilerplate.'bootstrap4/css/bootstrap-rtl.min.css' ?>">
        <?php endif;

    }
}

function change_to_twig() {
    return [
        'nav'      => [
            'nav_path'        => BOILERPLATES.'bootstrap4/html/navbar/navbar.html',
            'nav_li_no_link'  => BOILERPLATES.'bootstrap4/html/navbar/nav_li_no_link.html',
            'nav_li_link'     => BOILERPLATES.'bootstrap4/html/navbar/nav_li_link.html',
            'nav_li_dropdown' => BOILERPLATES.'bootstrap4/html/navbar/nav_li_dropdown.html',
            'nav_divider'     => BOILERPLATES.'bootstrap4/html/navbar/nav_li_divider.html',
        ],
        'modal'    => BOILERPLATES.'bootstrap4/html/modal.html',
        'progress' => BOILERPLATES.'bootstrap4/html/progress.html'
    ];
}

// Add the script to head tag
fusion_add_hook('fusion_boiler_header', 'bootstrap4');

// Set the boilerpalte template files to system
fusion_add_hook('fusion_boiler_paths', 'change_to_twig');
