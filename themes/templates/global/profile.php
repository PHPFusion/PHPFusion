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
defined('IN_FUSION') || exit;

if (!function_exists('display_register_form')) {
    /**
     * Registration Form Template
     * The tags {%xyz%} are default replacement that the core will perform
     * echo output design in compatible with Version 7.xx theme set.
     *
     * @param $info - the array output that is accessible for your custom requirements
     */
    function display_register_form(array $info = []) {
        add_to_head("<link href='".THEMES."templates/global/css/profile.css' rel='stylesheet'/>");
        ?>
        <!---HTML---->
        {%tab_header%}
        <!--register_pre_idx-->
        <div id='register_form' class='row m-t-20'>
            <div class='col-xs-12 col-sm-12'>
                {%open_form%}
                {%user_id%}
                {%user_name_field%}
                {%user_email_field%}
                {%user_hide_email_field%}
                {%user_avatar_field%}
                {%user_password_field%}
                {%user_admin_password_field%}
                {%custom_fields%}
                {%captcha_fields%}
                {%eula%}
                {%post_button%}
                {%close_form%}
            </div>
        </div>
        <!--register_sub_idx-->
        {%tab_footer%}
        <!---//HTML---->
        <?php
    }
}

if (!function_exists('display_profile_form')) {
    /**
     * Edit Profile Form Template
     * The tags {%xyz%} are default replacement that the core will perform
     * echo output design in compatible with Version 7.xx theme set.
     *
     * @param $info - the array output that is accessible for your custom requirements
     */
    function display_profile_form(array $info = []) {
        add_to_head("<link href='".THEMES."templates/global/css/profile.css' rel='stylesheet'/>");
        ?>
        <!--HTML-->
        {%opentable%}
        {%tab_header%}
        <!--editprofile_pre_idx-->
        <div id='profile_form' class='row m-t-20'>
            <div class='col-xs-12 col-sm-12'>
                {%open_form%}
                {%user_id%}
                {%user_name_field%}
                {%user_email_field%}
                {%user_hide_email_field%}
                {%user_reputation_field%}
                {%user_avatar_field%}
                {%user_password_field%}
                {%user_admin_password_field%}
                {%custom_fields%}
                {%captcha_fields%}
                {%eula%}
                {%post_button%}
                {%close_form%}
            </div>
        </div>
        <!--editprofile_sub_idx-->
        {%tab_footer%}
        {%closetable%}
        <!--//HTML-->
        <?php
    }
}

/**
 * Profile display view
 * @param $info (array) - prepared responsive fields
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

        //User Fields Module Information
        if (!empty($info['user_field'])) {
            // first we need to identify the wrapper
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
                $tab_title['icon'][$page_section['id']] = $page_section['icon'];
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
