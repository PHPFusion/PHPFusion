<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: theme.php
| Author: meangczac (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\LegalDocs;

const BOOTSTRAP = 5;
const WEBICON = ['fa6', 'phpfusion-icons', 'bootstrap-icons', 'entypo'];

// Autoload template
$file_path = THEME . 'templates/' . preg_replace( '(.php)', '.luna.php', basename( $_SERVER['PHP_SELF'] ) );
if (file_exists( $file_path )) {
    require_once $file_path;
}

// Override User Info Panel (Template Override to BS5)
//require_once __DIR__.'/templates/user_info.tpl.php';

/**
 * Theme
 */
function render_page() {

    $settings = fusion_get_settings();
    $userdata = fusion_get_userdata();

    echo '<div id="error-logs"></div>';

    echo '<header>';

    //fixed-top header-static
    echo showsublinks( '', 'navbar-expand-lg bg-light navbar-light', [
        'container'        => TRUE,
        'responsive'       => TRUE,
        'show_banner'      => TRUE,
        'banner'           => '<img src="' . BASEDIR . $settings['sitebanner'] . '" alt="' . $settings['sitename'] . '" class="img-fluid">',
        'nav_class'        => 'nav navbar-nav primary ms-auto',
        'html_pre_content' => form_text( 'stext', '', '', [
            'placeholder' => 'Search...', 'prepend' => TRUE, 'prepend_value' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
          <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
        </svg>'
        ] ),
        'additional_data'  => (iMEMBER ? [
            0               => [
                'pm'            => [
                    'link_id'         => 'pm',
                    'link_name'       => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chat-left-text-fill" viewBox="0 0 16 16"><path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4.414a1 1 0 0 0-.707.293L.854 15.146A.5.5 0 0 1 0 14.793V2zm3.5 1a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1h-9zm0 2.5a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1h-9zm0 2.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5z"/></svg>',
                    'link_class'      => 'btn btn-light',
                    'link_item_class' => 'ms-2',
                    'link_url'        => BASEDIR . 'messages.php',
                ],
                'editprofile'   => [
                    'link_id'         => 'editprofile',
                    'link_name'       => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear-wide-connected" viewBox="0 0 16 16"><path d="M7.068.727c.243-.97 1.62-.97 1.864 0l.071.286a.96.96 0 0 0 1.622.434l.205-.211c.695-.719 1.888-.03 1.613.931l-.08.284a.96.96 0 0 0 1.187 1.187l.283-.081c.96-.275 1.65.918.931 1.613l-.211.205a.96.96 0 0 0 .434 1.622l.286.071c.97.243.97 1.62 0 1.864l-.286.071a.96.96 0 0 0-.434 1.622l.211.205c.719.695.03 1.888-.931 1.613l-.284-.08a.96.96 0 0 0-1.187 1.187l.081.283c.275.96-.918 1.65-1.613.931l-.205-.211a.96.96 0 0 0-1.622.434l-.071.286c-.243.97-1.62.97-1.864 0l-.071-.286a.96.96 0 0 0-1.622-.434l-.205.211c-.695.719-1.888.03-1.613-.931l.08-.284a.96.96 0 0 0-1.186-1.187l-.284.081c-.96.275-1.65-.918-.931-1.613l.211-.205a.96.96 0 0 0-.434-1.622l-.286-.071c-.97-.243-.97-1.62 0-1.864l.286-.071a.96.96 0 0 0 .434-1.622l-.211-.205c-.719-.695-.03-1.888.931-1.613l.284.08a.96.96 0 0 0 1.187-1.186l-.081-.284c-.275-.96.918-1.65 1.613-.931l.205.211a.96.96 0 0 0 1.622-.434l.071-.286zM12.973 8.5H8.25l-2.834 3.779A4.998 4.998 0 0 0 12.973 8.5zm0-1a4.998 4.998 0 0 0-7.557-3.779l2.834 3.78h4.723zM5.048 3.967c-.03.021-.058.043-.087.065l.087-.065zm-.431.355A4.984 4.984 0 0 0 3.002 8c0 1.455.622 2.765 1.615 3.678L7.375 8 4.617 4.322zm.344 7.646.087.065-.087-.065z"/></svg>',
                    'link_class'      => 'btn btn-light',
                    'link_item_class' => 'ms-2',
                    'link_url'        => BASEDIR . 'edit_profile.php',
                ],
                'notifications' => [
                    'link_id'         => 'notifications',
                    'link_name'       => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell-fill" viewBox="0 0 16 16"><path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zm.995-14.901a1 1 0 1 0-1.99 0A5.002 5.002 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901z"/></svg>',
                    'link_class'      => 'btn btn-light',
                    'link_item_class' => 'ms-2',
                    'link_url'        => '#',
                ],
                'uipheader'     => [
                    'link_id'         => 'uipheader',
                    'link_name'       => display_avatar( $userdata, '38px', 'rounded-2', FALSE, 'rounded-2 overflow-hide' ),
                    'link_item_class' => 'ms-2',
                    'link_class'      => 'btn btn-default',
                    'link_url'        => '#',
                ],
            ],
            // child items
            'notifications' => notification_menu(),
            'uipheader'     => uip_menu(),
        ] :
            // Guest
            [
                0 => [
                    'join'   => [
                        'link_id'         => 'join',
                        'link_name'       => 'Join now',
                        'link_class'      => 'btn btn-outline-secondary',
                        'link_item_class' => 'ms-2',
                        'link_url'        => BASEDIR . 'register.php',
                    ],
                    'signin' => [
                        'link_id'         => 'signin',
                        'link_name'       => 'Sign in',
                        'link_class'      => 'btn btn-outline-primary',
                        'link_item_class' => 'ms-2',
                        'link_url'        => BASEDIR . 'login.php',
                    ]
                ]
            ])
    ] );

    $side_unit = 3;
    $left = defined( 'LEFT' ) && LEFT;
    $right = defined( 'RIGHT' ) && RIGHT;
    $lg_span = 12 - (($left + $right) * 3);
    $md_span = 8;

    echo '</header>';

    echo '<main class="luna' . (defined( 'LUNA_BODY_CLASS' ) ? whitespace( LUNA_BODY_CLASS ) : '') . '">';
    echo '<div class="container">';
    echo '<div class="row g-4">';

    if (defined( 'LEFT' )) {
        echo '<div class="col-lg-' . $side_unit . '">' . LEFT . '</div>';
    }

    echo '<div class="col-md-' . $md_span . ' col-lg-' . $lg_span . '">' . CONTENT . '</div>';

    if (defined( 'RIGHT' )) {
        echo '<div class="col-lg-' . $side_unit . '">' . RIGHT . '</div>';
    }

    echo '</div>';

    echo '</div></main>';

    echo '<div class="copyright-bottom"><div class="container">' .
        '<div class="site-policies">' . showpolicies() . '</div>' .
        '<div class="site-copyright">' . showcopyright() . '</div>' .
        showrendertime(). showfootererrors().
        '</div></div>';

}

// show the error logs


function showpolicies() {

    fusion_get_locale( '', LOCALE . LOCALESET . 'policies.php' );

    $html = '';
    $policies = LegalDocs::getInstance()->getPolicies( 5 );

    if (!empty( $policies )) {
        $count = 1;
        foreach ($policies as $key => $name) {
            if ($count < 6) {
                $html .= '<a href="' . BASEDIR . 'legal.php?type=' . $key . '">' . $name . '</a>';
            } else {
                break;
            }
        }
    }

    return $html;
}


function opentable( $title = '', $class = '' ) {
    echo '<div class="card mb-4' . whitespace( $class ?? '' ) . '">';

    if ($title) {
        echo '<div class="card-header pb-0 border-0">';
        echo '<h5 class="card-title mb-0">' . $title . '</h5>';
        echo '</div>';
    }

    echo '<div class="card-body">';
}

function closetable() {
    echo '</div></div>';
}

function openside( $title = '', $class = '' ) {
    echo '<div class="card mb-4' . whitespace( $class ?? '' ) . '">';

    if ($title) {
        echo '<div class="card-header pb-0 border-0">';
        echo '<h5 class="card-title mb-0">' . $title . '</h5>';
        echo '</div>';
    }

    echo '<div class="card-body">';
}

function closeside() {
    echo '</div></div>';
}


require_once __DIR__ . '/notifications_include.php';