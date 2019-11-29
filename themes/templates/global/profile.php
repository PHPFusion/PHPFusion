<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: profile.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\SiteLinks;
use PHPFusion\Template;

defined('IN_FUSION') || exit;

if (!function_exists('display_register_form')) {
    /**
     * Registration Form Template
     * @param array $info
     *
     * @return string
     */
    function display_register_form(array $info = []) {
    
        $tpl = Template::getInstance( 'user-register-form' );
        $tpl->set_locale(fusion_get_locale());
        $tpl->set_css(THEMES.'templates/global/css/profile.css');
        $tpl->set_template(__DIR__.'/tpl/register.html');

        // old one.
        $tpl->set_tag('open_form', '');
        $tpl->set_tag('user_name', '');
        $tpl->set_tag('user_email', '');
        $tpl->set_tag('user_hide_email', '');
        $tpl->set_tag('user_password', '');
        $tpl->set_tag('sitename', $info['sitename']);
        $tpl->set_tag('title', $info['title']);

        if (empty($info['user_name']) && empty($info['user_field'])) {
            $tpl->set_block('form_error');
        } else {
            $tpl->set_tag('open_form', $info['openform']);
            $tpl->set_tag('user_name', $info['user_name']);
            $tpl->set_tag('user_email', $info['user_email']);
            $tpl->set_tag('user_avatar', $info['user_avatar']);
            $tpl->set_tag('user_hide_email', $info['user_hide_email']);
            $tpl->set_tag('user_password', $info['user_password']);
            if (iADMIN) {
                $tpl->set_block('user_admin_password', ['field' => $info['user_admin_password']]);
            }
            if (!empty($info['user_field'])) {
                foreach ($info['user_field'] as $fieldData) {
                    $fields = '';
                    $field_title = '';
                    if (!empty($fieldData['title'])) {
                        $field_title = $fieldData['title'];
                    }
                    if (!empty($fieldData['fields']) && is_array($fieldData['fields'])) {
                        foreach ($fieldData['fields'] as $cFieldData) {
                            if (!empty($cFieldData)) {
                                $fields .= $cFieldData;
                            }
                        }
                    }
                    $tpl->set_block('user_fields', [
                            'field_title' => $field_title,
                            'fields' => $fields,
                    ]);
                }
            }

            if (!empty($info['validate'])) {
                $tpl->set_block('validate', ['content' => $info['validate'] ]);
            }
            if (!empty($info['terms'])) {
                $tpl->set_block('terms', ['content' => $info['terms']]);
            }
            $tpl->set_tag('button', $info['button']);
            $tpl->set_tag('close_form', $info['closeform']);

        }

        return (string)$tpl->get_output();

    }
}

if (!function_exists('display_profile_form')) {
    /**
     * Edit Profile Form Template
     *
     * @template-key user-profile-form
     *
     * @param $info - the array output that is accessible for your custom requirements
     */
    function display_profile_form(array $info = []) {

        add_to_head("<link href='".THEMES."templates/global/css/profile.css' rel='stylesheet'/>");
    
        $tpl = Template::getInstance( 'user-profile-form' );
        $tpl->set_locale(fusion_get_locale());

        $tpl->set_template(__DIR__.'/tpl/edit_profile.html');

        $current_page = $info['current_page'];
    
        $page_arr = [];
        foreach ( $info['pages'] as $page_key => $page ) {
            $page_arr[0][ $page_key ] = [
                'link_id'     => $page_key,
                'link_name'   => $page['title'],
                'link_active' => $page['active'],
                'link_url'    => BASEDIR.'edit_profile.php?ref='.$page_key
            ];
        }
    
        $tpl->set_tag( 'profile_nav', SiteLinks::setSubLinks( [
            'id'            => 'profile-link-menu',
            'callback_data' => $page_arr,
            'navbar_class'  => 'navbar-default',
            'container'     => TRUE,
            'show_banner'   => TRUE,
            'show_header'   => TRUE,
            'custom_banner' => '<h4>Profile & Settings</h4>',
        ] )->showSubLinks() );
        

        $tpl->set_tag("openform", "");
        $tpl->set_tag("closeform", "");
        $tpl->set_tag("opentable", fusion_get_function("opentable", ""));
        $tpl->set_tag("closetable", fusion_get_function("closetable", ""));
        $tpl->set_tag("tab_header", "");
        $tpl->set_tag("tab_footer", "");
        $tpl->set_tag("button", "");
        $tpl->set_tag("page_title", $info['title']);
        $tpl->set_tag('sitename', $info['sitename']);

        $current_section = get('section', FILTER_VALIDATE_INT);

        // Default profile page
        switch ($current_page) {
            default:
                if ($info['custom_page'] === TRUE) {
                    // Add navigation menu
    
                    // Set menu
                    $section_a = '';
                    if ( !empty( $info['section'] ) ) {
                        $ctpl = Template::getInstance( 'profile-menu' );
                        $ctpl->set_text( '<ul class="list-group menu">
                        {section_link.{
                        <li class="list-group-item{%class_active%}" role="listitem"><a href="{%link%}" title="{%title%}">{%title%}</a></li>
                        }}
                        </ul>' );
                        $counter = 0;
                        $section = get( 'section' );
                        foreach ( $info['section']['title'] as $kid => $title ) {
                            $id = $info['section']['id'][ $kid ];
            
                            $active = $section == $id || !$section && $counter == 0 ? TRUE : FALSE;
            
                            $ctpl->set_block( 'section_link', [
                                'title'        => $title,
                                'link'         => BASEDIR.'edit_profile.php?ref='.get( 'ref' ).'&amp;section='.$id,
                                'class'        => $active ? ' class="active"' : '',
                                'class_active' => $active ? ' active' : '',
                            ] );
                            $counter++;
                        }
                        $section_a = $ctpl->get_output();
                    }
                    $section_b = '';
                    if ( !empty( $info['section_nav'] ) ) {
        
                        $ctpl = Template::getInstance( 'profile-menu' );
                        $ctpl->set_text( '
                        <ul class="list-group menu">
                        {section_link.{
                        <li class="list-group-item{%class_active%}" role="listitem"><a href="{%link%}" title="{%title%}">{%title%}</a></li>
                        }}
                        </ul>' );
        
                        $counter = 0;
                        $sref = get( 'sref' );
                        $ref = get( 'ref' );
                        $section = get( 'section' );
        
                        foreach ( $info['section_nav']['title'] as $kid => $title ) {
                            $id = $info['section_nav']['id'][ $kid ];
                            $active = $sref == $id || !$sref && $counter == 0 ? TRUE : FALSE;
                            $ctpl->set_block( 'section_link', [
                                'title'        => $title,
                                'link'         => BASEDIR.'edit_profile.php?ref='.$ref.'&amp;section='.$section.'&amp;sref='.$id,
                                'class'        => $active ? ' class="active"' : '',
                                'class_active' => $active ? ' active' : '',
                            ] );
                            $counter++;
                        }
                        $section_b = $ctpl->get_output();
                    }
                    // Set menu
                    if ( $section_a || $section_b ) {
                        $tpl->set_block( 'menu', [ 'content' => $section_a.$section_b ] );
                    }
                    
                    // Set content
                    $tpl->set_block('content', ['page_content' => $info['page_content']]);
    
                    if (!empty($info['section'])) $tab = $info['section'];
    
                    break;
                }
            case 'pu_profile': // public profile.
                if (!$current_section && !empty($info['section'])) {
                    $section_arr = reset($info['section']);
                    $current_section = $section_arr['id'];
                }

                $tpl->set_tag('openform', $info['openform']);
                $tpl->set_tag('closeform', $info['closeform']);
                foreach ($info['section'] as $id => $sections) {
                    $tab['title'][] = $sections['name'];
                    $tab['id'][] = $sections['id'];
                }
                if ($current_section== 1) {
                    $tpl->set_block('public_fields', [
                        "eula"                  => $info['terms'],
                        "user_avatar_field"     => $info['user_avatar'],
                        "custom_fields"         => $info['user_fields'],
                        "post_button"           => $info['button'],
                        "user_reputation_field" => $info['user_reputation'],
                        "captcha_field"         => $info['validate'],
                        "hash_field"            => $info['user_password_verify']
                    ]);
                } else {
                    if (!empty($info['user_fields'])) {
                        $tpl->set_block('public_fields', [
                            "eula"              => '',
                            "user_avatar_field" => '',
                            "custom_fields"     => $info['user_fields'],
                            "user_password"     => $info['user_password'],
                            "post_button"       => $info['button'],
                            "captcha_field"     => $info['validate'],
                            "hash_field"        => $info['user_password_verify']
                        ]);
                    } else {
                        $tpl->set_block("no_fields", []);
                    }
                }
                break;
            case 'se_profile':
                // Settings Profile
                // Add user fields integration in, with a seperate folder.
                foreach ($info['section'] as $id => $sections) {
                    $tab['title'][] = $sections['name'];
                    $tab['id'][] = $sections['id'];
                }
                switch ($info['current_section']) {
                    default:
                    case "acc_settings":
                        // social connectors
                        $social_connectors = "";
                        if (!empty($info['social_connectors'])) {
                            $stpl = Template::getInstance( 'social_connector' );
                            $stpl->set_template(__DIR__."/tpl/edit_profile_connector.html");
                            foreach ($info['social_connectors'] as $connector) {
                                $stpl->set_block("social_connector", [
                                    "connector_icon"  => $connector['icon'],
                                    "connector_title" => $connector['title'],
                                    "connector"       => $connector['connector']
                                ]);
                            }
                            $social_connectors = $stpl->get_output();
                        }
                        $tpl->set_block("settings_fields", [
                            "user_name"         => $info['name'],
                            "joined_date"       => $info['joined_date'],
                            "email"             => $info['email'],
                            "email_status"      => 'Confirmed',
                            "user_email"        => $info['user_email'],
                            "edit_profile_link" => BASEDIR.'edit_profile.php',
                            "post_button"       => $info['button'],
                            "social_connectors" => $social_connectors,
                        ]);

                        $tpl->set_block('content_fields', [
                            "title"       => "Change User Name",
                            "description" => "You ".(fusion_get_settings("userNameChange") ? "can change your username." : "cannot change your username.")."
                                <ul class='block spacer-sm'>
                                <li>You are not allowed to change back to your old username</li>
                                <li>All contents created under the old username will be moved to your new username account</li>
                                <li>All visitors created under the old username will be redirected to your new username account</li>
                                </ul>",
                            "content"     => $info['username_openform'].$info['user_name'],
                            "post_button" => $info['update_user_name'].$info['username_closeform']
                        ]);

                        $tpl->set_block('content_fields', [
                            "title"       => "Password",
                            "description" => "",
                            "content"     => $info['user_password_openform']."<div class='row'>\n<div class='col-xs-12 col-sm-7'>\n
                            ".$info['user_password'].$info['user_admin_password']."
                            </div>\n<div class='col-xs-12 col-sm-5'>\n
                            ".$info['user_password_notice'].$info['user_admin_password_notice']."
                            </div>\n</div>\n",
                            "post_button" => $info['update_password_button'].$info['user_password_closeform'],
                        ]);

                        $tpl->set_block("content_fields", [
                            "title"       => 'Email',
                            "description" => "",
                            "content"     => $info['user_email_openform']."<div class='m-b-20'>Current email:<br/>\n".$info['email']."</div>\n
                            <div class='m-b-20'>Status:<br/>Confirmed</div>\n<hr/>\n
                            <div class='m-b-20'>Change your email</div>\n
                            <div class='row'><div class='col-xs-12 col-sm-7'>".$info['user_email']."</div>
                            <div class='col-xs-12 col-sm-5'>Your email address will not change until you confirm it via email
                            </div>\n</div>\n",
                            "post_button" => $info['update_email_button'].$info['user_email_closeform']
                        ]);

                        $tpl->set_block("content_fields", [
                            "title"       => 'Close your Account',
                            "description" => "
                            <p class='strong'>What happens when you close your account?</p>
                            <div class='m-b-20'>
                                <small>
                                    <strong>Your profile, posts, and communication data will not appear in {%sitename%}.</strong><br/>
                                    People who try to view your profile will see a message that the page is not available. All posts
                                    made by your account will no longer be available.
                                </small>
                            </div>
                            <p class='strong'>All non-delivery cases and transactions will be closed.</p>
                            <div class='m-b-20'>
                                <small>
                                    Reports on transactions done by or to you, or other party that is involved in the transaction
                                    will no longer
                                    be active.
                                </small>
                            </div>
                            <p class='strong'>You can reopen your account any time.</p>
                            <div class='m-b-20'>
                                <small>
                                    If you want to reopen your account, simply sign into {%sitename%} when you want to return.
                                    You can also contact ".fusion_get_settings('site_email')." to help you reopen your account.<br/>
                                    No one will be able to use your username, and your account settings will remain intact.
                                </small>
                            </div>
                            <hr/>
                            ",
                            "content"     => $info['user_close_openform'].$info['user_close_message'],
                            "post_button" => $info['user_close_button'].$info['user_close_closeform'],
                        ]);
                        break;
                    case 'preferences':
                        $tpl->set_tag("openform", $info['openform']);
                        $tpl->set_tag("closeform", $info['closeform']);
                        $tpl->set_tag("button", $info['update_preference_button']);
                        // Language Inputs
                        $tpl->set_block("options_fields", [
                            "title"       => 'Language',
                            "description" => 'Choose your preferred language',
                            "content_a"   => $info['user_language'],
                            "content_b"   => $info['language'],
                            "post_button" => '',
                        ]);
                        $tpl->set_block("content_fields", [
                            "title"       => "Email Visibility",
                            "description" => "Show or hide email in profile page.<hr/>\n",
                            "content"     => $info['user_hide_email'],
                            'post_button' => '',
                        ]);
                        $tpl->set_block("content_fields", [
                            "title"       => "Your Notifications",
                            "description" => "Email me when:<hr/>",
                            "content"     => $info['pm_notify'],
                            "post_button" => "",
                        ]);
                        $tpl->set_block("content_fields", [
                            "title"       => "Change Location",
                            "description" => "Choose your location to help us show you custom content from your area.",
                            "content"     => $info['user_location'],
                            "post_button" => "",
                        ]);
                        break;
                    case 'security':
                        $tpl->set_tag("openform", $info['openform']);
                        $tpl->set_tag("closeform", $info['closeform']);
                        $tpl->set_block("content_fields", [
                            "title"       => "Block Users",
                            "description" => "Block any user so they can no longer see the things you post or interact with you.<hr/>\n",
                            "content"     => $info['user_block'],
                            "post_button" => $info['user_block_content'],
                        ]);

                        if (!empty($info['security_connectors'])) {
                            foreach ($info['security_connectors'] as $connector) {
                                $tpl->set_block("content_fields", [
                                    "title"       => $connector['title'],
                                    "description" => "",
                                    "icon"        => $connector['icon'],
                                    "content"     => $connector['connector'],
                                    "post_button" => "",
                                ]);
                            }
                        }

                        $tpl->set_tag("button", $info['update_security_button']);
                }
                // account deletion
                break;
        }


        if (isset($tab) && isset($tab['title'])) {
            $tpl->set_tag("tab_header", opentab($tab, $current_section, "user-profile-form", TRUE, "", "section", ['search', 'sref', 'category', 'id', 'section', 'aid', 'action', 'id'], FALSE));
            $tpl->set_tag("tab_footer", closetab());
        }

        echo $tpl->get_output();

    }
}

if (!function_exists('display_profile')) {
    /**
     * Main profile page
     *
     * @param $info
     *
     * @return string
     */
    function display_profile($info) {

        $locale = fusion_get_locale();

        add_to_head("<link href='".THEMES."templates/global/css/profile.css' rel='stylesheet'/>" );
    
        $tpl = Template::getInstance( 'profile');

        $tpl->set_template(__DIR__.'/tpl/user_profile.html');

        $tpl->set_locale($locale);

        foreach($info['profile_pages'] as $profile_page) {
            $tpl->set_block('profile_nav', $profile_page);
        }

        $tpl->set_tag("profile_content", $info['profile_content']);

        $tpl->set_tag('opentable', fusion_get_function('opentable', ''));

        $tpl->set_tag('closetable', fusion_get_function('closetable'));

        $user_name = '';
        $user_avatar = '';
        $user_level = '';
        $basic_info = '';
        // Basic user information in the header
        if (!empty($info['core_field'])) {
            // Core field put on top
            $basic_info = '';
            foreach ($info['core_field'] as $field_id => $field_data) {
                // Sets data to core field block
                $tpl->set_block($field_id, $field_data);
                $skip = [
                    'profile_user_avatar',
                    'profile_user_name',
                    'profile_user_level',
                    'profile_user_group'
                ];
                if (!in_array($field_id, $skip)) {
                    $tpl->set_block('user_core_fields', $field_data);
                }
                // old method
                switch ($field_id) {
                    case 'profile_user_group':
                        if (!empty($field_data['value'])) {
                            foreach ($field_data['value'] as $groups) {
                                $tpl->set_block('user_groups', $groups);
                            }
                        } else {
                            $tpl->set_block('user_group_na', []);
                        }
                        break;
                    case 'profile_user_avatar':
                        $avatar['user_id'] = $info['user_id'];
                        $avatar['user_name'] = $info['user_name'];
                        $avatar['user_avatar'] = $field_data['value'];
                        $avatar['user_status'] = $field_data['status'];
                        $user_avatar = display_avatar($avatar, '130px', 'profile-avatar', FALSE, 'img-responsive');
                        break;
                    case 'profile_user_name':
                        $user_name = $field_data['value'];
                        break;
                    case 'profile_user_level':
                        $user_level = $field_data['value'];
                        break;
                    default:
                        break;
                }
            }
        }

        //
        // //User Fields Module Information
        // if (!empty($info['user_field'])) {
        //
        //     // first we need to identify the wrapper
        //     foreach ($info['user_field'] as $catID => $categoryData) {
        //         $tpl2 = \PHPFusion\Template::getInstance('user-fields');
        //
        //         $tpl2->set_template(__DIR__.'/tpl/user_profile_fields.html');
        //
        //         if (!empty($categoryData['fields'])) {
        //             foreach ($categoryData['fields'] as $_id => $_fields) {
        //                 if (!empty($_fields)) {
        //                     if (isset($_fields['type']) && $_fields['type'] == 'social') {
        //                         $tpl->set_block('social_icons', $_fields);
        //                     } else {
        //                         $block_type = ($_fields['title'] ? 'user_fields_inline' : 'user_fields');
        //                         $tpl2->set_block($block_type, [
        //                             'id'    => $_id,
        //                             'icon'  => !empty($_fields['icon']) ? $_fields['icon'] : '',
        //                             'title' => $_fields['title'],
        //                             'value' => $_fields['value']
        //                         ]);
        //                     }
        //                 }
        //             }
        //             if (!empty($categoryData['title'])) {
        //                 $tpl2->set_block('user_fields_cat', ['category_title' => $categoryData['title']]);
        //             }
        //             $tpl->set_block('fields_block', ["fields" => $tpl2->get_output()]);
        //         }
        //     }
        // } else {
        //     $info['no_fields'] = $locale['uf_108'];
        // }
        // // Tabs
        // if (!empty($info['section'])) {
        //     foreach ($info['section'] as $page_section) {
        //         $tab_title['title'][$page_section['id']] = $page_section['name'];
        //         $tab_title['id'][$page_section['id']] = $page_section['id'];
        //         $tab_title['icon'][$page_section['id']] = $page_section['icon'];
        //     }
        // }
        //$tpl->set_tag('tab_header', (isset($tab_title) ? opentab($tab_title, $info['section_id'], 'profile_tab', TRUE, FALSE, 'section') : ''));

        $tpl->set_tag('user_name', $user_name);

        $tpl->set_tag('user_avatar', $user_avatar);

        $tpl->set_tag('user_level', $user_level);

        if (!empty($info['user_admin'])) {
            $tpl->set_block('user_admin', $info['user_admin']);
        }

        if (!empty($info['buttons'])) {
            $tpl->set_block('buttons', $info['buttons']);
        }

        $tpl->set_tag('basic_info', $basic_info);

        $tpl->set_tag('tab_footer', (isset($tab_title) ? closetab() : ''));

        $tpl->set_tag('no_fields', (!empty($info['no_fields']) ? $info['no_fields'] : ''));

        return $tpl->get_output();

    }
}

/**
 * Public Profile User Fields Page
 * @param $info (array) - prepared responsive fields
 * To get information of the current raw userData
 * Uncomment and include the 3 lines at bottom inside render_userprofile()
 * global $userFields; // profile object at profile.php
 * $current_user_info = $userFields->getUserData(); // returns array();
 * print_p($current_user_info); // debug print
 */
if ( !function_exists( 'display_public_profile' ) ) {
    function display_public_profile( $info) {
        $locale = fusion_get_locale();
        
        $tpl = Template::getInstance( 'user-profile');
        $tpl->set_template(__DIR__.'/tpl/profile/profile.html');
        $tpl->set_locale($locale);

        foreach($info['section'] as $section) {
            $section['class'] = $section['active'] ? " class='active'" : "";
            $tpl->set_block("sections", $section);
        }

        //User Fields Module Information
        if (!empty($info['user_field'])) {

            // first we need to identify the wrapper
            foreach ($info['user_field'] as $catID => $categoryData ) {
    
                $tpl2 = Template::getInstance( 'user-profile-fields');

                $tpl2->set_template(__DIR__.'/tpl/profile/profile-fields.html');

                if (!empty($categoryData['fields'])) {

                    foreach ($categoryData['fields'] as $_id => $_fields) {

                        if (!empty($_fields)) {

                            if (isset($_fields['type']) && $_fields['type'] == 'social') {

                                $tpl->set_block('social_icons', $_fields);

                            } else {

                                // do not need the block type.
                                $tpl2->set_block('user_fields', [
                                    'id'    => $_id,
                                    'icon'  => !empty($_fields['icon']) ? $_fields['icon'] : '',
                                    'title' => $_fields['title'],
                                    'value' => $_fields['value']
                                ]);

                            }
                        }

                    }
                    if (!empty($categoryData['title'])) {

                        $tpl2->set_block('user_fields_cat', ['category_title' => $categoryData['title']]);

                    }

                    $tpl->set_block('fields_block', ["fields" => $tpl2->get_output()]);
                }
            }

        } else {
            $info['no_fields'] = $locale['uf_108'];
        }

        if (!empty($info['user_admin'])) {
            $tpl->set_block('user_admin', $info['user_admin']);
        }

        if (!empty($info['group_admin'])) {
            $tpl->set_block('group_admin', $info['group_admin']);
        }

        if (!empty($info['buttons'])) {
            $tpl->set_block('buttons', $info['buttons']);
        }

        $tpl->set_tag('no_fields', (!empty($info['no_fields']) ? $info['no_fields'] : ''));

        return (string) $tpl->get_output();
    }
}

if (!function_exists('display_profile_groups')) {
    function display_profile_groups($info ) {
    
        $tpl = Template::getInstance( "user-groups");

        $tpl->set_template(__DIR__."/tpl/profile/profile-groups.html");

        $tpl->set_locale( fusion_get_locale() );
        $tpl->set_tag("pagenav", $info['pagenav']);
        $tpl->set_tag("total", $info['group_max_count']);

        if (!empty($info['group_admin'])) {

            $tpl->set_block('group_admin', $info['group_admin']);
        }

        if (!empty($info['user_groups'])) {
            foreach($info['user_groups'] as $groups) {
                $tpl->set_block("groups", $groups);
            }
        } else {
            $tpl->set_block("groups_na");
        }
        return $tpl->get_output();
    }
}

if (!function_exists('display_profile_activity')) {
    function display_profile_activity($info ) {
        $tpl = Template::getInstance( 'activity-profile' );
        $tpl->set_template( THEMES.'templates/global/tpl/profile/profile-activity.html' );
        foreach ( $info['pages'] as $key => $page_info ) {
            $page_info['active'] = $info['section'] == $key ? ' class="active"' : '';
            $tpl->set_block( 'tab', $page_info );
        }
        $tpl->set_tag( 'no_item', 'There are no activity found.' );
        if ( !empty( $info['items'] ) ) {
            $tpl->set_tag( 'no_item', '' );
            foreach ( $info['items'] as $feed_id => $feed_info ) {
                $tpl->set_block( 'activity_item', $feed_info );
            }
        }
        if ( $info['pagenav'] ) {
            $tpl->set_block( 'page_nav', [ 'content' => $info['pagenav'] ] );
        }
    
        return $tpl->get_output();
    
    }
}
