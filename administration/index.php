<?php

/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: index.php
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
require_once __DIR__ . '/../maincore.php';

define('ADMIN_DIR', ADMIN . fusion_get_aidlink());

define('ADMIN_CURRENT_DIR', ADMIN_DIR . ( check_get('p') ? '&p=' . get('p') : '' ));

const ADMIN_ERROR_DIR = ADMIN_DIR . '&p=404';

/* Reverse load breadcrumb support*/
require_once INCLUDES . 'breadcrumbs.php';

/* Loads up administration file */
$contents = load_administration(); // Load the file

require_once __DIR__ . '/../themes/templates/admin_header.php'; // Load the theme

if ( isset($contents['files']) ) {
    if ( is_array($contents['files']) ) {
        foreach ( $contents['files'] as $file_path ) {
            require_once $file_path;
        }
    }
    else if ( is_file($contents['files']) ) {
        require_once $contents['files'];
    }
}

/* Run breadcrumbs */
if ( isset($contents['title']) && isset($contents['link']) ) {
    if ( isset($contents['settings']) ) {
        add_breadcrumb([ 'link' => ADMIN . fusion_get_aidlink() . '&s=settings', 'title' => 'Settings' ]);
    }
    //https://firstcamp.test/administration/?aid=5d7df8bfa0034f6a&p=erro
    add_breadcrumb([ 'link' => $contents['link'], 'title' => $contents['title'] ]);
    add_to_title($contents['title']);
}

/* Run data callback hook */
if ( $filter = fusion_filter_hook('pf_admin_data') ) {
    if ( isset($filter[0]) ) {
        $filter = $filter[0];
    }
}

/* Run POST hook */
if ( isset($contents['actions']['post']) ) {
    if ( is_array($contents['actions']['post']) ) {
        foreach ( $contents['actions']['post'] as $button_name => $form_id ) { // savesettings, clearcache
            if ( post('form_id') == $form_id ) {
                fusion_apply_hook('pf_admin_post');
            }
        }
    }
    else if ( check_post($contents['actions']['post']) ) {
        fusion_apply_hook('pf_admin_post');
    }
}


/* Run view hook */
fusion_apply_hook('pf_admin_view')[0];

/* Run js hook */
if ( $js = fusion_filter_hook('pf_admin_js') ) {

    if ( isset($js[0]) ) {
        add_to_jquery($js[0]);
    }
}

require_once __DIR__ . '/../themes/templates/footer.php';

/**
 * @param       $s
 * @param false $use_forwarded_host
 *
 * @return string
 */
function url_origin($s, bool $use_forwarded_host = FALSE) {

    $ssl = ( ! empty($s['HTTPS']) && $s['HTTPS'] == 'on' );
    $sp = strtolower($s['SERVER_PROTOCOL']);
    $protocol = substr($sp, 0, strpos($sp, '/')) . ( ( $ssl ) ? 's' : '' );
    $port = $s['SERVER_PORT'];
    $port = ( ( ! $ssl && $port == '80' ) || ( $ssl && $port == '443' ) ) ? '' : ':' . $port;
    $host = ( $use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST']) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : NULL );
    $host = $host ?? $s['SERVER_NAME'] . $port;

    return $protocol . '://' . $host;
}

/**
 * @param       $s
 * @param false $use_forwarded_host
 *
 * @return string
 */
function full_url($s, bool $use_forwarded_host = FALSE)
: string {

    return url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
}

/**
 * Do administration exit to 404 due to error
 *
 * @return void
 */
function admin_exit() {

    if ( defined('FP_DEV') && FP_DEV === TRUE ) {

        add_notice('info',
                   '<pre style="background:none;border:0;color:#fff;">' . fusion_get_function('debug_print_backtrace',
                                                                                              DEBUG_BACKTRACE_IGNORE_ARGS) . '</pre>');

        add_notice('danger', 'Whops! The page has encountered an error. Please refresh the page.');

    }
    else {
        redirect(ADMIN_ERROR_DIR);
    }
}

/**
 * Checks current post.
 *
 * @param $key
 *
 * @return bool
 */
function admin_post($key)
: bool {

    return ( post('form_action') === $key );
}


function load_administration()
: array {

    $contents = [];
    $aidlink = fusion_get_aidlink();

    if ( $s = get('s') ) {

        $acceptable = [
            'settings' => 'settings.php'
        ];

        if ( isset($acceptable[ $s ]) ) {

            $admin_link = ADMIN . $aidlink . '&s=' . $acceptable[ $s ];

            include ADMIN . $acceptable[ $s ];

        }
        else {

            include ADMIN . 'contents/error_na.php';
        }

    }
    else if ( $p = get('p') ) {

        $result = dbquery("SELECT admin_rights, admin_title, admin_link, admin_page FROM " . DB_ADMIN . " WHERE admin_rights=:page",
                          [ ':page' => strtoupper($p) ]);

        if ( dbrows($result) ) {

            $data = dbarray($result);

            $c_admin_link = $data['admin_link'];

            $admin_title = $locale[ $data['admin_rights'] ] ?? $data['admin_title'];

            if ( $data['admin_page'] != '5' ) {
                $c_admin_link = ADMIN . 'contents/' . $data['admin_link'];
            }


            if ( is_file($c_admin_link) ) {

                // with the p
                $admin_link = ADMIN_CURRENT_DIR;

                include $c_admin_link;
                // run page access
                pageaccess($data['admin_rights']);

            }
            else {
                // show page 404
                include ADMIN . 'contents/error_na.php';
            }
        }
        else {
            // show page 404
            include ADMIN . 'contents/error_na.php';
        }
    }
    else {
        // show page 404
        include ADMIN . 'contents/error_na.php';
    }

    if ( isset($contents['actions']['post']) ) {

        $_js = '';
        if ( is_array($contents['actions']['post']) ) {

            $button_names = [];
            if ( $notices = getnotices(FUSION_SELF, FALSE) ) {
                $notices = array_keys($notices);
                $button_class = 'btn-danger';
                $button_text = '<i class=\'fad fa-times m-r-10\'></i>Error!';
                if ( in_array('success', $notices) ) {
                    $button_class = 'btn-success';
                    $button_text = '<i class=\'fad fa-check m-r-10\'></i>Success!';
                }
            }

            foreach ( $contents['actions']['post'] as $button_name => $form_id ) {

                $button_name_js = 'button[name=\"' . $button_name . '\"]';

                $_js .= /** @lang JavaScript */
                    '$(document).on("click", "' . $button_name_js . '", function(event) {
                    event.preventDefault();
                    let button = $(this),
                        button_ct = button.children("span"),
                        button_text = button_ct.text(),
                        button_name = "' . $button_name . '",
                        event_url = "' . ADMIN . 'api/?api=nts",
                        form = $("#' . $form_id . '"),
                        form_action = $("input[name=\'form_action\'");
  
                        button_ct.html("<i class=\'fad fa-spinner fa-spin m-r-10\'></i>"+ button_text);
                        button.prop("disabled", true);
                        
                        if (form_action.length) {
                            
                            form_action.val("' . $button_name . '");
                            
                            // add button to session
                            $.post(event_url, {"name": button_name});
                            
                            form.trigger("submit");
                            
                        } else {
                            alert("The form has some anomaly and should be injected with a hidden field");
                        }
                });';

                $button_names['name'][] = $button_name;
            }

            if ( isset($button_class) && isset($button_text) ) {
                $_js .= /** @lang JavaScript */
                    '$.post("' . ADMIN . 'api/?api=ntt", ' . json_encode($button_names) . ', function(response) {
                    
                    let button = $("button[name=\'"+ response +"\']");

                    if (button.length) {

                        let button_class = button.attr("class"),
                            button_ct = button.children("span"),
                            button_text = button_ct.text();

                        console.log(button_class);
                        
                            button.addClass("' . $button_class . '");
                            button_ct.html("' . $button_text . '");
                    
                            setTimeout(function(){
                                button.removeClass("' . $button_class . '");
                                button_ct.html(button_text);
                        }, 1300);
                    }
                });';
            }

            add_to_jquery($_js);
        }
    }

    if ( isset($contents['left_nav']) && is_callable($contents['left_nav']) ) {
        fusion_add_hook('pf_admin_left_nav', $contents['left_nav']);
    }

    if ( isset($contents['fullwidth']) && $contents['fullwidth'] ) {
        fusion_add_hook('pf_admin_full_width', 'fullwidth');
    }

    if ( ! empty($contents['js']) && is_callable($contents['js']) ) {
        fusion_add_hook('pf_admin_js', $contents['js']);
    }

    if ( ! empty($contents['post']) ) {
        // do we need to have this in array?
        if ( is_array($contents['post']) ) {
            foreach ( $contents['post'] as $key ) {
                if ( is_callable($key) ) {
                    fusion_add_hook('pf_admin_post', $key);
                }
            }
        }
        else if ( is_callable($contents['post']) ) {
            fusion_add_hook('pf_admin_post', $contents['post']);
        }
    }

    if ( ! empty($contents['button']) && is_callable($contents['button']) ) {
        fusion_add_hook('pf_admin_buttons', $contents['button']);
    }

    if ( ! empty($contents['view']) && is_callable($contents['view']) ) {
        fusion_add_hook('pf_admin_view', $contents['view']);
    }

    return $contents;
}


/**
 * @param $data
 *
 * @return string
 */
function avatar_src($data)
: string {

    $avatar_image_path = IMAGES . 'avatars/no-avatar.jpg';
    if ( $data['user_avatar'] && is_file(IMAGES . 'avatars/' . $data['user_avatar']) ) {
        $avatar_image_path = IMAGES . 'avatars/' . $data['user_avatar'];
    }

    return $avatar_image_path;
}

/**
 * @param $data
 *
 * @return string
 */
function cover_src($data)
: string {

    $cover_image_path = IMAGES . 'default-cover.jpg';
    if ( $data['user_cover'] && is_file(IMAGES . 'covers/' . $data['user_cover']) ) {
        $cover_image_path = IMAGES . 'covers/' . $data['user_cover'];
    }

    return $cover_image_path;
}
