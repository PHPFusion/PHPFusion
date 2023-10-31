<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: profile.tpl.php
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
defined( 'IN_FUSION' ) || exit;

if (!function_exists( 'display_register_form' )) {
    /**
     * Registration Form Template
     * echo output design in compatible with Version 7.xx theme set.
     *
     * @param $info - the array output that is accessible for your custom requirements
     */
    function display_register_form( array $info = [] ) {
        $locale = fusion_get_locale();

        echo "<!--HTML-->";
        opentable( $locale['u101'] );
        echo "<!--register_pre_idx-->";
        echo openform( 'registerFrm', 'POST' ) .
            $info['user_id'] .
            $info['user_name'] .
            $info['user_email'] .
            $info['user_avatar'] .
            $info['user_password'] .
            $info['user_admin_password'] .
            $info['user_custom'] .
            $info['validate'] .
            $info['terms'] .
            $info['button'] .
            closeform();
        echo "<!--register_sub_idx-->";
        closetable();
        echo "<!--//HTML-->";
    }
}

if (!function_exists( 'display_profile_form' )) {
    /**
     * Edit Profile Form Template
     * echo output design in compatible with Version 7.xx theme set.
     *
     * @param $info - the array output that is accessible for your custom requirements
     */
    function display_profile_form( array $info = [] ) {
        $opentab = '';
        $closetab = '';
        if (!empty( $info['tab_info'] )) {
            $opentab = opentab( $info['tab_info'], check_get( 'section' ) ? get( 'section' ) : 1, 'user-profile-form', TRUE );
            $closetab = closetab();
        }
        opentable( '' );
        echo $opentab;

        echo "<!--editprofile_pre_idx--><div id='profile_form' class='spacer-sm'>";
        echo openform( 'profileFrm', 'POST', FORM_REQUEST, ['enctype' => TRUE] );
        echo $info['user_id'];
        echo $info['user_name'];
        echo $info['user_firstname'];
        echo $info['user_lastname'];
        echo $info['user_addname'];
        echo $info['user_phone'];
        echo $info['user_email'];
        echo $info['user_hide_email'];
        echo $info['user_avatar'];
        echo $info['user_password'];
        echo $info['user_admin_password'];
        echo $info['user_custom'];
        echo $info['user_bio'];
        echo $info['validate'];
        echo $info['terms'];
        echo $info['button'];
        echo closeform();
        echo "</div><!--editprofile_sub_idx-->";

        echo $closetab;
        closetable();
    }
}

/**
 * Profile display view
 *
 * @param $info (array) - prepared responsive fields
 * To get information of the current raw userData
 * global $userFields; // profile object at profile.php
 * $current_user_info = $userFields->getUserData(); // returns array();
 * print_p($current_user_info); // debug print
 */
if (!function_exists( 'display_user_profile' )) {
    function display_user_profile( $info ) {
        $locale = fusion_get_locale();

        add_to_css( '.cat-field .field-title > img{max-width:25px;}' );

        opentable( '' );
        echo '<section id="user-profile">';
        echo '<div class="row m-b-20">';
        echo '<div class="col-xs-12 col-sm-2">';
        $avatar['user_id'] = $info['user_id'];
        $avatar['user_name'] = $info['user_name'];
        $avatar['user_avatar'] = $info['core_field']['profile_user_avatar']['value'];
        $avatar['user_status'] = $info['core_field']['profile_user_avatar']['status'];
        echo display_avatar( $avatar, '130px', 'profile-avatar', FALSE, 'img-responsive' );

        if (!empty( $info['buttons'] )) {
            echo '<a class="btn btn-success btn-block spacer-sm" href="' . $info['buttons']['user_pm_link'] . '">' . $locale['send_message'] . '</a>';
        }
        echo '</div>';

        echo '<div class="col-xs-12 col-sm-10">';
        if (!empty( $info['user_admin'] )) {
            $button = $info['user_admin'];
            echo '<div class="pull-right btn-group">
                    <a class="btn btn-sm btn-default" href="' . $button['user_susp_link'] . '">' . $button['user_susp_title'] . '</a>
                    <a class="btn btn-sm btn-default" href="' . $button['user_edit_link'] . '">' . $button['user_edit_title'] . '</a>
                    <a class="btn btn-sm btn-default" href="' . $button['user_ban_link'] . '">' . $button['user_ban_title'] . '</a>
                    <a class="btn btn-sm btn-default" href="' . $button['user_suspend_link'] . '">' . $button['user_suspend_title'] . '</a>
                    <a class="btn btn-sm btn-danger" href="' . $button['user_delete_link'] . '">' . $button['user_delete_title'] . '</a>
                </div>';
        }

        echo '<h2 class="m-0">' . $info['core_field']['profile_user_name']['value'] . '</h2>';
        echo $info['core_field']['profile_user_level']['value'];

        if (!empty( $info['core_field'] )) {
            echo '<hr>';
            foreach ($info['core_field'] as $field_id => $field_data) {
                switch ($field_id) {
                    case 'profile_user_group':
                        echo '<div class="row cat-field">';
                        echo '<div class="col-xs-12 col-sm-3"><strong class="field-title">' . $locale['u057'] . '</strong></div>';
                        echo '<div class="col-xs-12 col-sm-9">';
                        if (!empty( $field_data['value'] ) && is_array( $field_data['value'] )) {
                            $i = 0;
                            foreach ($field_data['value'] as $group) {
                                echo $i > 0 ? ', ' : '';
                                echo '<a href="' . $group['group_url'] . '">' . $group['group_name'] . '</a>';
                                $i++;
                            }
                        } else {
                            echo !empty( $locale['u117'] ) ? $locale['u117'] : $locale['na'];
                        }
                        echo '</div>';
                        echo '</div>';
                        break;
                    case 'profile_user_avatar':
                        $avatar['user_avatar'] = $field_data['value'];
                        $avatar['user_status'] = $field_data['status'];
                        break;
                    case 'profile_user_name':
                    case 'profile_user_level':
                        break;
                    default:
                        if (!empty( $field_data['value'] )) {
                            echo '<div id="' . $field_id . '" class="row cat-field">';
                            echo '<div class="col-xs-12 col-sm-3"><strong class="field-title">' . $field_data['title'] . '</strong></div>';
                            echo '<div class="col-xs-12 col-sm-9">' . $field_data['value'] . '</div>';
                            echo '</div>';
                        }
                }
            }
        }

        echo '</div>';
        echo '</div>'; // .row

        if (!empty( $info['section'] )) {
            $tab_title = [];
            foreach ($info['section'] as $page_section) {
                $tab_title['title'][$page_section['id']] = $page_section['name'];
                $tab_title['id'][$page_section['id']] = $page_section['id'];
                $tab_title['icon'][$page_section['id']] = $page_section['icon'];
            }

            $tab_active = tab_active( $tab_title, get( 'section' ) );

            echo '<div class="profile-section">';
            echo opentab( $tab_title, get( 'section' ), 'profile_tab', TRUE, 'nav-tabs', 'section', ['section'] );
            echo opentabbody( $tab_title['title'][get( 'section' )], $tab_title['id'][get( 'section' )], $tab_active, TRUE );

            if ($tab_title['id'][get( 'section' )] == $tab_title['id'][1]) {
                if (!empty( $info['group_admin'] )) {
                    $group = $info['group_admin'];

                    echo '<div class="well m-t-10">';
                    echo $group['ug_openform'];
                    echo '<div class="row">';
                    echo '<div class="col-xs-12 col-sm-2">' . $group['ug_title'] . '</div>';
                    echo '<div class="col-xs-12 col-sm-8">' . $group['ug_dropdown_input'] . '</div>';
                    echo '<div class="col-xs-12 col-sm-2">' . $group['ug_button'] . '</div>';
                    echo '</div>';
                    echo $group['ug_closeform'];
                    echo '</div>';
                }
            }

            if (!empty( $info['user_field'] )) {
                foreach ($info['user_field'] as $category_data) {
                    if (!empty( $category_data['fields'] )) {
                        if (isset( $category_data['fields'] )) {
                            foreach ($category_data['fields'] as $field_data) {
                                $fields[] = $field_data;
                            }
                        }

                        if (!empty( $fields )) {
                            echo '<h4 class="cat-title text-uppercase">' . $category_data['title'] . '</h4>';

                            if (isset( $category_data['fields'] )) {
                                foreach ($category_data['fields'] as $field_id => $field_data) {
                                    if (!empty($field_data['title'])) {
                                        echo '<div id="field-' . $field_id . '" class="row cat-field m-b-5">';
                                        echo '<div class="col-xs-12 col-sm-3"><strong class="field-title">' . (!empty( $field_data['icon'] ) ? $field_data['icon'] : '') . ' ' . $field_data['title'] . '</strong></div>';
                                        echo '<div class="col-xs-12 col-sm-9">' . $field_data['value'] . '</div>';
                                        echo '</div>';
                                    }
                                }
                            }

                            echo '<hr>';
                        }
                    }
                }
            } else {
                echo '<div class="text-center well">' . $locale['uf_108'] . '</div>';
            }

            echo closetabbody();
            echo closetab();
            echo '</div>';
        }

        echo '</section>';
        closetable();
    }
}


if (!function_exists('display_gateway')) {
    function display_gateway($info) {

        $locale = fusion_get_locale();

        if ($info['showform']) {
            opentable($locale['gateway_069']);
            echo $info['openform'];
            echo $info['hiddeninput'];
            echo '<h3>'.$info['gateway_question'].'</h3>';
            echo $info['textinput'];
            echo $info['button'];
            echo $info['closeform'];
            closetable();
        } else if (!isset($_SESSION["validated"])) {
            echo '<div class="well text-center"><h3 class="m-0">'.$locale['gateway_068'].'</h3></div>';
        }

        if (isset($info['incorrect_answer']) && $info['incorrect_answer'] == TRUE) {
            opentable($locale['gateway_069']);
            echo '<div class="well text-center"><h3 class="m-0">'.$locale['gateway_066'].'</h3></div>';
            echo '<input type="button" value="'.$locale['gateway_067'].'" class="text-center btn btn-info spacer-xs" onclick="location=\''.BASEDIR.'register.php\'"/>';
            closetable();
        }
    }
}
