<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: members.php
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

use Pro\Admin\Members\Members;

defined('IN_FUSION') || exit;

$locale = fusion_get_locale('');


$contents = [
    'view'        => 'pf_view',
    'button'      => 'pf_button',
    'js'          => 'pf_js',
    'link'        => ( $admin_link ?? '' ),
    'settings'    => TRUE,
    'title'       => 'Members',
    'description' => '',
    'files'       => [
        INCLUDES . 'theme_functions_include.php'
    ],
    'actions'     => memberadmin_actions(),
];

$settings = fusion_get_settings();


function memberadmin_actions()
: array {

    if ( $ref = get('action') ) {

        if ( admin_post('cancel') ) {
            redirect(ADMIN_CURRENT_DIR);
        }

        if ( $ref == 'add' ) {

            return [
                'post' => [
                    'savesettings' => 'newAccountFrm',
                    'cancel'       => 'newAccountFrm',
                    'loginas'      => 'newAccountFrm',
                ]
            ];

        }
        else {

            if ( $ref == 'edit' ) {
                // Impersonate modal pop-up
                if ( admin_post('loginas') ) {

                    if ( $user_id = get('id', FILTER_VALIDATE_INT) ) {
                        redirect(ADMIN_CURRENT_DIR . '&login=' . $user_id);
                    }
                    else {
                        redirect(ADMIN_CURRENT_DIR);
                    }
                }

                return [
                    'post' => [
                        'savesettings' => 'editAccountFrm',
                        'cancel'       => 'editAccountFrm',
                        'loginas'      => 'editAccountFrm',
                        'adminreset'   => 'adminresetFrm',
                    ]
                ];
            }
        }
    }

    return [];
}

function pf_button() {

    $locale = fusion_get_locale();

    if ( $ref = get('action') ) {
        if ( $ref == 'edit' ) {

            return
                form_button('cancel', $locale['cancel'], $locale['cancel'], [ 'class' => 'btn-default' ]) .

                ( ( get('id', FILTER_VALIDATE_INT) != 1 ) ? form_button('impersonate',
                                                                        'Impersonate',
                                                                        'i',
                                                                        [ 'class' => 'btn-inverse m-r-10' ]) .
                                                            form_button('loginas',
                                                                        'Login As..',
                                                                        'login_as',
                                                                        [ 'class' => 'btn-inverse m-r-10' ])
                    : '' ) .
                form_button('savesettings', 'Save', 'savesettings', [ 'class' => 'btn-primary' ]);
        }
        else {
            if ( $ref == 'add' ) {

                return
                    form_button('cancel', $locale['cancel'], $locale['cancel'], [ 'class' => 'btn-default' ]) .
                    form_button('savesettings', 'Save', 'savesettings', [ 'class' => 'btn-primary' ]);
            }
        }
    }

    // else {
    return openform('searchfrm', 'POST', FORM_REQUEST, [ 'inline' => TRUE ]) .
           form_text('search', '', '', [ 'class' => 'm-r-10', 'placeholder' => 'Search members' ]) .
           form_button('filter',
                       'Filter',
                       'filter',
                       [ 'class' => 'm-r-10 btn-inverse', 'icon' => 'far fa-filter' ]) .
           '<a href="' . ADMIN_CURRENT_DIR . '&action=add" class="btn btn-primary"><span>New Member</span></a>' .
           closeform();
    // }
}


function pf_view() {

    Members::getInstance()->displayAdmin();
}

function pf_js() {

    if ( $ref = get('action') ) {
        if ( $ref == 'edit' ) {

            return /** @lang JavaScript */ "
            let clipboard = new ClipboardJS('#copy-persona-link', {container: document.getElementById('impersona-Modal')});
            
            clipboard.on('success', function(e) {
                
                let el = $('button#copy-persona-link'), el_txt = el.children('span');
                
                
                el.removeClass('btn-primary').addClass('btn-success');
                el_txt.text('Link copied!');
                
                setTimeout(function(){            
                    el.removeClass('btn-success').addClass('btn-primary');
                    el_txt.text('Copy link');
                }, 2000);
                e.clearSelection();
            });
            ";

        }
    }
    else {
        return "
        $('#filter').on('click', function(e) {
            e.preventDefault();
            $('#view-filter').toggle('slide');
        });
        ";
    }
}

require_once ADMIN . 'members/Members.php';
