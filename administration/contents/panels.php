<?php

/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: panels.php
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

$locale = fusion_get_locale('', LOCALE . LOCALESET . 'admin/panels.php');

$contents = [
    'post'        => 'pf_post',
    'view'        => 'pf_view',
    'button'      => 'pf_button',
    'js'          => 'pf_js',
    'link'        => ( $admin_link ?? '' ),
    'settings'    => TRUE,
    'title'       => $locale['600'],
    'description' => '',
    'files'=> [
        ADMIN . 'panels/panel_helper.php',
        ADMIN . 'panels/panel_view.php',
        ADMIN . 'panels/panel_form.php'
    ],
    'actions'     => [
        'post' => [
            'cancel'    => 'cancelFrm',
            'savepanel' => 'panelFrm'
        ]
    ]
];

function pf_post() {

    if ( admin_post('cancel') ) {
        redirect(ADMIN_CURRENT_DIR);
    }

}


function pf_view() {

    $edit = ( isset($_GET['action']) && $_GET['action'] == 'edit' ) ? verify_panel($_GET['panel_id']) : 0;

    $page = get_page();

    $locale = fusion_get_locale();

    if ( $page == 'form' ) {
        add_breadcrumb([ 'link' => FORM_REQUEST, 'title' => $locale['408'] ]);
        panel_form();
    }
    else {
        panel_list();
    }
}

function pf_button()
: string {

    $locale = fusion_get_locale();

    $settings = fusion_get_settings();

    $page = get_page();

    if ( $page == 'form' ) {

        return form_button('cancel', $locale['cancel'], $locale['cancel'], [ 'class' => 'btn-primary' ]) .
               ( $settings['allow_php_exe'] ?
                   form_button('panel_preview',
                               $locale['preview'],
                               $locale['preview'],
                               [ 'class' => 'btn-primary' ]) : '' ) .
               form_button('savepanel', $locale['461'], $locale['460'], [ 'class' => 'btn-primary' ]);
    }
    else {
        return '<a class="btn btn-primary" href="' . ADMIN_CURRENT_DIR . '&pg=form"><span>' . $locale['408'] . '</span></a>';

    }
}

