<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: robots.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/


defined('IN_FUSION') || exit;

$locale = fusion_get_locale('', LOCALE . LOCALESET . 'admin/robots.php');

$contents = [
    'post'        => 'pf_post',
    'view'        => 'pf_view',
    'button'      => 'pf_submit',
    'js'          => 'pf_js',
    'link'        => ( $admin_link ?? '' ),
    'settings'    => TRUE,
    'title'       => $locale['ROBOT_400'],
    'description' => '',
    'actions'     => [ 'post' => [ 'saverobots' => 'robotsfrm', 'setdefault' => 'robotsfrm' ] ]
];


function write_default()
: string {

    $robots_content = "User-agent: *\n";
    $robots_content .= "Disallow: /config.php\n";
    $robots_content .= "Disallow: /administration/\n";
    $robots_content .= "Disallow: /includes/\n";
    $robots_content .= "Disallow: /locale/\n";
    $robots_content .= "Disallow: /themes/\n";
    $robots_content .= "Disallow: /print.php\n";

    return $robots_content;
}

function pf_view() {

    $locale = fusion_get_locale();
    $file = BASEDIR . 'robots.txt';

    if ( ! file_exists($file) ) {
        echo "<div class='alert alert-danger'><strong>" . $locale['ROBOT_411'] . "</strong></div>\n";
        $current = write_Default();
    }
    else {
        $current = file_get_contents($file);
    }

    echo openform('robotsfrm', 'post');
    echo "<div class='alert alert-info'><strong>" . $locale['ROBOT_420'] . '</strong>';
    echo '<br/>';
    echo str_replace([ '[LINK]', '[/LINK]' ],
                     [ "<a href='http://www.robotstxt.org/' target='_blank'>", '</a>', ],
                     $locale['ROBOT_421']);
    echo '</div>';
    echo form_textarea('robots_content', '', $current, [ 'height' => '300px' ]);

    echo closeform();
}

function pf_submit() {

    $locale = fusion_get_locale();

    $file = BASEDIR . 'robots.txt';

    $button = $locale['save'];
    if ( ! is_file($file) ) {
        $button = $locale['ROBOT_422'];
    }

    return form_button('saverobots', $button, $button, [ 'class' => 'btn-primary' ]) .
           ( file_exists($file) ? form_button('setdefault',
                                              $locale['ROBOT_423'],
                                              $locale['ROBOT_423'],
                                              [ 'class' => 'btn-primary' ]) : '' );
}


function pf_post() {

    $file = BASEDIR . 'robots.txt';
    $locale = fusion_get_locale();

    if ( admin_post('saverobots') ) {

        $robots_content = sanitizer('robots_content', '', 'robots_content');
        if ( ! preg_check('/^[-0-9A-Z._\*\:\.\/@\s]+$/i', $robots_content) ) {
            fusion_stop($locale['ROBOT_417']);
        }

        if ( fusion_safe() ) {
            $message = ! file_exists($file) ? $locale['ROBOT_416'] : $locale['ROBOT_412'];
            write_file($file, $robots_content);
            add_notice('success', $message);
            redirect(FUSION_REQUEST);
        }

    }

    if (admin_post('setdefault')) {

        if ( ! is_writable($file) ) {
            fusion_stop($locale['ROBOT_414']);
        }
        if ( \defender::safe() && ! defined('FUSION_NULL') ) {
            write_file($file, write_Default());
            add_notice('success', $locale['ROBOT_412']);
            redirect(FUSION_REQUEST);
        }
    }
}


function pf_js() {
    $locale = fusion_get_locale();
    return "$('#setdefault').bind('click', function() { return confirm('" . $locale['ROBOT_410'] . "'); });";
}
