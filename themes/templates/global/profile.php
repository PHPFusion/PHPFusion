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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

if (!function_exists('display_register_form')) {

    /**
     * Registration Form Template
     * The tags {%xyz%} are default replacement that the core will perform
     * echo output design in compatible with Version 7.xx theme set.
     *
     * Template ID      core_user_registration_form
     * @param array $info
     *
     * @return string
     */
    function display_register_form(array $info = []) {
        add_to_head("<link href='".THEMES."templates/global/css/profile.css' rel='stylesheet'/>");
        $tpl = \PHPFusion\Template::getInstance('core_user_registration_form');
        $tpl->set_tag('sitename', fusion_get_settings('sitename'));
        $tpl->set_block('register_form', [
            'openform'            => $info['openform'],
            'closeform'           => $info['closeform'],
            'user_name_field'     => $info['user_name'],
            'user_email_field'    => $info['user_email'],
            'user_password_field' => $info['user_password'],
            'custom_fields'       => $info['user_fields'],
            'captcha_fields'      => $info['validate'],
            'eula'                => $info['terms'],
            'post_button'         => $info['button'],
            'opentable'           => fusion_get_function('opentable', ''),
            'closetable'          => fusion_get_function('closetable', ''),
        ]);

        $tpl->set_template(__DIR__.'/../../templates/global/tpl/register.html');

        return $tpl->get_output();
    }
}

if (!function_exists('display_profile_form')) {
    /**
     * Edit Profile Form Template
     * The tags {%xyz%} are default replacement that the core will perform
     * echo output design in compatible with Version 7.xx theme set.
     *
     * Template ID      core_edit_profile_form
     *
     * @param array $info
     *
     * @return string
     */
    function display_profile_form(array $info = []) {

        // this one can move into core, make it light and easy
        $tpl = \PHPFusion\Template::getInstance('core_edit_profile_form');
        add_to_head("<link href='".THEMES."templates/global/css/profile.css' rel='stylesheet'/>");
        // pages
        $current_page = $info['current_page'];

        foreach ($info['pages'] as $page_id => $pages) {
            $tpl->set_block('page_tab', [
                'title' => $pages['title'],
                'link'  => BASEDIR.'edit_profile.php?ref='.$page_id
            ]);
        }

        $tpl->set_tag('openform', '');
        $tpl->set_tag('closeform', '');
        $tpl->set_tag('opentable', fusion_get_function('opentable', ''));
        $tpl->set_tag('closetable', fusion_get_function('closetable', ''));
        $tpl->set_tag('tab_header', '');
        $tpl->set_tag('tab_footer', '');
        $tpl->set_tag('button', '');
        $tpl->set_tag('page_title', $info['title']);

        // Custom page include

        // Default profile page
        switch ($current_page) {
            default:
                if ($info['custom_page'] === TRUE) {
                    // Set content
                    $tpl->set_block('content', ['page_content' => $info['page_content']]);
                    if (!empty($info['section'])) $tab = $info['section'];
                    break;
                }
            case 'pu_profile': // public profile.
                $tpl->set_tag('openform', $info['openform']);
                $tpl->set_tag('closeform', $info['closeform']);
                foreach ($info['section'] as $id => $sections) {
                    $tab['title'][] = $sections['name'];
                    $tab['id'][] = $sections['id'];
                }
                if ($info['current_section'] == 1) {
                    $tpl->set_block('public_fields', [
                        'eula'                  => $info['terms'],
                        'user_avatar_field'     => $info['user_avatar'],
                        'custom_fields'         => $info['user_fields'],
                        'post_button'           => $info['button'],
                        'user_reputation_field' => $info['user_reputation'],
                        'captcha_field'         => $info['validate'],
                        'hash_field'            => $info['user_hash']
                    ]);
                } else {
                    if (!empty($info['user_fields'])) {
                        $tpl->set_block('public_fields', [
                            'eula'              => '',
                            'user_avatar_field' => '',
                            'custom_fields'     => $info['user_fields'],
                            'user_password'     => $info['user_password'],
                            'post_button'       => $info['button'],
                            'captcha_field'     => $info['validate'],
                            'hash_field'        => $info['user_hash']
                        ]);
                    } else {
                        $tpl->set_block('no_fields', []);
                    }
                }
                break;
            case 'se_profile':
                // Add user fields integration in, with a seperate folder.
                foreach ($info['section'] as $id => $sections) {
                    $tab['title'][] = $sections['name'];
                    $tab['id'][] = $sections['id'];
                }
                switch ($info['current_section']) {
                    default:
                    case 'acc_settings':
                        $tpl->set_block("settings_fields", [
                            'user_name'         => $info['name'],
                            'joined_date'       => $info['joined_date'],
                            'email'             => $info['email'],
                            'email_status'      => 'Confirmed',
                            'user_email'        => $info['user_email'],
                            'edit_profile_link' => BASEDIR.'edit_profile.php',
                            'post_button'       => $info['button'],
                        ]);
                        $tpl->set_block('content_fields', [
                            'title'       => "Change User Name",
                            'description' => "You ".(fusion_get_settings("userNameChange") ? "can change your username." : "cannot change your username.")."
                                <ul class='block spacer-sm'>
                                <li>You are not allowed to change back to your old username</li>
                                <li>All contents created under the old username will be moved to your new username account</li>
                                <li>All visitors created under the old username will be redirected to your new username account</li>
                                </ul>",
                            'content'     => $info['username_openform'].$info['user_name'],
                            'post_button' => $info['update_user_name'].$info['username_closeform']
                        ]);

                        $tpl->set_block('content_fields', [
                            'title'       => "Password",
                            'description' => "",
                            'content'     => $info['user_password_openform']."<div class='row'>\n<div class='col-xs-12 col-sm-7'>\n
                            ".$info['user_password'].$info['user_admin_password']."
                            </div>\n<div class='col-xs-12 col-sm-5'>\n
                            ".$info['user_password_notice']."
                            </div>\n</div>\n",
                            'post_button' => $info['update_password_button'].$info['user_password_closeform'],
                        ]);

                        $tpl->set_block('content_fields', [
                            'title'       => 'Email',
                            'description' => "",
                            'content'     => $info['user_email_openform']."<div class='m-b-20'>Current email:<br/>\n".$info['email']."</div>\n
                            <div class='m-b-20'>Status:<br/>Confirmed</div>\n<hr/>\n
                            <div class='m-b-20'>Change your email</div>\n
                            <div class='row'><div class='col-xs-12 col-sm-7'>".$info['user_email']."</div>
                            <div class='col-xs-12 col-sm-5'>Your email address will not change until you confirm it via email
                            </div>\n</div>\n",
                            'post_button' => $info['update_email_button'].$info['user_email_closeform']
                        ]);

                        $tpl->set_block('content_fields', [
                            'title'       => 'Close your Account',
                            'description' => "
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
                            'content'     => $info['user_close_openform'].$info['user_close_message'],
                            'post_button' => $info['user_close_button'].$info['user_close_closeform'],
                        ]);
                        break;

                    case 'preferences':
                        $tpl->set_tag('openform', $info['openform']);
                        $tpl->set_tag('closeform', $info['closeform']);
                        $tpl->set_tag('button', $info['update_preference_button']);
                        // Language Inputs
                        $tpl->set_block('options_fields', [
                            'title'       => 'Language',
                            'description' => 'Choose your preferred language',
                            'content_a'   => $info['user_language'],
                            'content_b'   => $info['language'],
                            'post_button' => '',
                        ]);
                        $tpl->set_block('content_fields', [
                            "title"       => "Email Visibility",
                            'description' => "Show or hide email in profile page.<hr/>\n",
                            'content'     => $info['user_hide_email'],
                            'post_button' => '',
                        ]);
                        $tpl->set_block('content_fields', [
                            'title'       => "Your Notifications",
                            'description' => "Email me when:<hr/>",
                            'content'     => $info['pm_notify'],
                            'post_button' => '',
                        ]);
                        $tpl->set_block('content_fields', [
                            'title'       => "Change Location",
                            'description' => "Choose your location to help us show you custom content from your area.",
                            'content'     => $info['user_location'],
                            'post_button' => '',
                        ]);
                        break;
                    case 'security':
                        $tpl->set_tag('openform', $info['openform']);
                        $tpl->set_tag('closeform', $info['closeform']);
                        $tpl->set_block('content_fields', [
                            "title"       => "Two Factor Authentication",
                            'description' => "Prevent unauthorized access to your account by requiring authentication app when signing in.<hr/>\n",
                            'content'     => $info['user_2fa'],
                            'post_button' => '',
                        ]);

                        $tpl->set_tag('button', $info['update_security_button']);
                }
                // account deletion
                break;
        }

        if (isset($tab) && isset($tab['title'])) {
            $tpl->set_tag('tab_header', opentab($tab, $info['current_section'], 'user-profile-form', TRUE, '', 'section', ['search', 'sref', 'category', 'id', 'section', 'aid', 'action', 'id'], FALSE));
            $tpl->set_tag('tab_footer', closetab());
        }

        $tpl->set_locale(fusion_get_locale());
        $tpl->set_template(__DIR__.'/../../templates/global/tpl/edit_profile.html');

        return $tpl->get_output();
    }
}

/**
 * Profile display view
 * $info (array) - prepared responsive fields
 * To get information of the current raw userData
 * Uncomment and include the 3 lines at bottom inside render_userprofile()
 * global $userFields; // profile object at profile.php
 * $current_user_info = $userFields->getUserData(); // returns array();
 * print_p($current_user_info); // debug print
 */
if (!function_exists('display_user_profile')) {
    function display_user_profile($info) {
        $locale = fusion_get_locale();
        add_to_head("<link href='".THEMES."templates/global/css/profile.css' rel='stylesheet'/>");

        $tpl = \PHPFusion\Template::getInstance('user_profile');
        $tpl->set_template(__DIR__.'/tpl/user_profile.html');
        $tpl->set_locale(fusion_get_locale());

        $tpl->set_tag('opentable', fusion_get_function('opentable', ''));
        $tpl->set_tag('closetable', fusion_get_function('closetable'));

        $user_name = '';
        $user_avatar = '';
        $user_level = '';
        $basic_info = '';
        // Basic User Information
        if (!empty($info['core_field'])) {
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
                        $user_avatar = display_avatar($avatar, '550px', 'profile-avatar', FALSE, 'img-responsive');
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
        //User Fields Module Information
        if (!empty($info['user_field'])) {
            foreach ($info['user_field'] as $catID => $categoryData) {
                $tpl2 = \PHPFusion\Template::getInstance('user_fields');
                $tpl2->set_template(__DIR__.'/tpl/user_profile_fields.html');
                if (!empty($categoryData['fields'])) {
                    foreach ($categoryData['fields'] as $_id => $_fields) {
                        if (!empty($_fields)) {
                            if (isset($_fields['type']) && $_fields['type'] == 'social') {
                                $tpl->set_block('social_icons', $_fields);
                            } else {
                                $block_type = ($_fields['title'] ? 'user_fields_inline' : 'user_fields');
                                $tpl2->set_block($block_type, [
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
        // Tabs
        if (!empty($info['section'])) {
            foreach ($info['section'] as $page_section) {
                $tab_title['title'][$page_section['id']] = $page_section['name'];
                $tab_title['id'][$page_section['id']] = $page_section['id'];
            }
        }
        $tpl->set_tag('tab_header', (isset($tab_title) ? opentab($tab_title, $_GET['section'], 'profile_tab', TRUE, FALSE, 'section') : ''));
        $tpl->set_tag('user_name', $user_name);
        $tpl->set_tag('user_avatar', $user_avatar);
        $tpl->set_tag('user_level', $user_level);
        if (!empty($info['user_admin'])) {
            $tpl->set_block('user_admin', $info['user_admin']);
        }
        if (!empty($info['group_admin'])) {
            $tpl->set_block('group_admin', $info['group_admin']);
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
