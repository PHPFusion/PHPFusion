<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: theme.php
| Author: Meangczac (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

const BOOTSTRAP = 5;

function render_page() {

    $settings = fusion_get_settings();

    echo '<header>';

    //fixed-top header-static
    echo showsublinks( '', 'navbar-expand-lg bg-light navbar-light', [
        'container'        => TRUE,
        'responsive'       => TRUE,
        'show_banner'      => TRUE,
        'banner'           => '<img src="' . BASEDIR . $settings['sitebanner'] . '" alt="' . $settings['sitename'] . '" class="img-fluid">',
        'html_pre_content' => form_text( 'stext', '', '', ['placeholder' => 'Search...', 'prepend' => TRUE, 'prepend_value' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
  <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
</svg>'] ),
    ] );

    echo '</header>';
    echo '<main><div class="container">';
    echo '<div class="row g-4">';
    echo '<div class="col-lg-3">';
    echo LEFT;
    echo '</div>';
    echo '<div class="col-md-8 col-lg-6">';
    echo CONTENT;
    echo '</div>';
    echo '<div class="col-lg-3">';
    echo RIGHT;
    echo '</div>';
    echo '</div>';
    echo '</div></main>';
}

function opentable() {
}

function closetable() {
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
