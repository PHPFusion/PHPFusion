<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: site_links.php
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

// @todo: link reoder drag and drop

$locale = fusion_get_locale('',
                            [ LOCALE . LOCALESET . 'admin/sitelinks.php', LOCALE . LOCALESET . 'admin/html_buttons.php' ]);

$contents = [
    'fullwidth'   => TRUE,
    'post'        => 'pf_post',
    'view'        => 'pf_view',
    'button'      => 'pf_submit',
    'js'          => 'pf_js',
    'settings'    => TRUE,
    // 'left_nav'    => 'pf_left',
    'link'        => ( $admin_link ?? '' ),
    'title'       => $locale['SL_0001'],
    'description' => 'Arrange and customize your site navigation links. PHPFusion supports multiple dynamic menus for supported themes. Simply drag the links to reorder the navigation links.',
    'actions'     => [
        'post' => [
            'savelink' => 'linkFrm',
        ]
    ],
    'files'       => [
        ADMIN . 'sitelinks/sitelinks-helper.php',
        ADMIN . 'sitelinks/sitelinks-form.php',
    ]
];


function pf_post() {

    if ( check_post('cancel') ) {
        redirect(ADMIN_CURRENT_DIR);
    }

}


function pf_view() {

    fusion_load_script(INCLUDES . 'jscripts/administration/sitelinks.js');

    $locale = fusion_get_locale();

    if ( get_page() == 'form' ) {

        if ( get_action() == 'edit' ) {

            add_breadcrumb([ 'link' => FUSION_REQUEST, 'title' => $locale['SL_0011'] ]);
        }
        else {
            add_breadcrumb([ 'link' => ADMIN_CURRENT_DIR . '&pg=form', 'title' => $locale['SL_0010'] ]);
        }

        display_link_form();
    }
    else {
        display_link_listing();
    }

    make_page_breadcrumbs(get_link_index(), get_link_tree(), 'link_id', 'link_name', 'link_cat');
}

function pf_js()
: string {

    $locale = fusion_get_locale();
    $token = fusion_get_token('menu');

    return /** @lang JavaScript */ "
    slAdmin.slListing({
        'SL_0080' : '" . $locale['SL_0080'] . "',
        'SL_0016' : '" . $locale['SL_0016'] . "',
        'error_preview' : '" . $locale['error_preview'] . "',
        'error_preview_text' : '" . $locale['error_preview_text'] . "',
        }, '$token');

    $(document).on('click', '#add-navigation', function(e){
        e.preventDefault();
        $('#newmenu').slideToggle();
    });
    ";
}

// Submit buttons
function pf_submit()
: string {

    $locale = fusion_get_locale();
    $refs = get('refs') ?? '0';
    $link_cat = get_link_cat();

    $links = '';

    if ( get_page() == 'form' ) {

        $links .= form_button('savelink',
                              $locale['SL_0040'],
                              $locale['SL_0040'],
                              [ 'class' => 'btn-primary' ]);

        $links .= '<a href="' . ADMIN_CURRENT_DIR . '&refs=' . (int)get_refs() . '&cat=' . $link_cat . '" class="btn btn-default"><span>' . $locale['cancel'] . '</span></a>';

    }
    else {

        $links .= '<a href="' . ADMIN_CURRENT_DIR . '&pg=form&nrefs=' . $refs . '&cat=' . $link_cat . '" class="btn btn-primary"><span>' . $locale['SL_0010'] . '</span></a>';
        $links .= '<a href="" id="add-navigation" class="btn btn-primary"><span>New Navigation</span></a>';

    }

    return $links;
}
