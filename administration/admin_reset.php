<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin_reset.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'/../maincore.php';
pageAccess( 'APWR' );
require_once THEMES.'templates/admin_header.php';

class AdminPasswordResetAdministration {

    private $data = [
        'reset_id'        => 0,
        'reset_admin_id'  => '',
        'reset_timestamp' => '',
        'reset_sucess'    => '',
        'reset_failed'    => '',
        'reset_admins'    => '',
        'reset_reason'    => ''
    ];

    public function __construct() {
        $locale = fusion_get_locale( "", LOCALE.LOCALESET."admin/admin_reset.php" );

        if ( post( 'reset_id' ) ) {
            $input = explode( ",", sanitizer( 'reset_id', "", "reset_id" ) );

            if ( !empty( $input ) ) {
                foreach ( $input as $reset_id ) {
                    dbquery( "DELETE FROM ".DB_ADMIN_RESETLOG." WHERE reset_id=:resetid", [ ':resetid' => (int)$reset_id ] );
                }
            }

            add_notice( 'success', $locale['apw_429'] );
            redirect( clean_request( '', [ 'section', 'action', 'reset_id' ], FALSE ) );
        }

        if ( get( 'action' ) == 'delete' ) {
            if ( $this->verify_admin_reset( get( 'reset_id' ) ) ) {
                dbquery( "DELETE FROM ".DB_ADMIN_RESETLOG." WHERE reset_id=:resetid", [ ':resetid' => get( 'reset_id' ) ] );
                add_notice( 'success', $locale['apw_429'] );
                redirect( clean_request( '', [ 'section', 'action', 'reset_id' ], FALSE ) );
            }
        }

        add_breadcrumb( [ 'link' => ADMIN.'admin_reset.php'.fusion_get_aidlink(), 'title' => $locale['apw_title'] ] );
        $this->set_adminsdb();
    }

    private function verify_admin_reset( $id ) {
        if ( isnum( $id ) ) {
            return dbcount( "(reset_id)", DB_ADMIN_RESETLOG, "reset_id=:id", [ ':id' => (int)$id ] );
        }

        return FALSE;
    }

    private function set_adminsdb() {
        if ( post( 'reset_admins' ) ) {
            require_once INCLUDES."sendmail_include.php";

            $userdata = fusion_get_userdata();
            $reset_message = sanitizer( 'reset_message', '', 'reset_message' );
            $reset_admin = sanitizer( 'reset_admin', '', 'reset_admin' );
            $reset_login = post( 'reset_login' ) ?: FALSE;
            $reset_success = [];
            $reset_failed = [];
            $locale = fusion_get_locale();

            if ( fusion_safe() ) {
                $user_sql = ( isnum( $reset_admin ) ? "user_id='".$reset_admin."'" :
                    ( $reset_admin == "all" ? "user_level=".USER_LEVEL_ADMIN." OR user_level=".USER_LEVEL_SUPER_ADMIN :
                        ( $reset_admin == "sa" ? "user_level=".USER_LEVEL_SUPER_ADMIN :
                            ( $reset_admin == "a" ? "user_level=".USER_LEVEL_ADMIN :
                                ""
                            ) ) ) );

                $result = dbquery( "SELECT user_id, user_password, user_admin_password, user_name, user_email, user_language
                    FROM ".DB_USERS."
                    WHERE ".$user_sql."
                    ORDER BY user_level DESC, user_id"
                );

                while ( $data = dbarray( $result ) ) {
                    $adminPass = new PasswordAuth();
                    $newAdminPass = $adminPass->getNewPassword();
                    $adminPass->inputNewPassword = $newAdminPass;
                    $adminPass->inputNewPassword2 = $newAdminPass;
                    $adminPassIsReset = ( $adminPass->isValidNewPassword() === 0 ? TRUE : FALSE );
                    $newAdminAlgo = $adminPass->getNewAlgo();
                    $newAdminSalt = $adminPass->getNewSalt();
                    $newAdminPassword = $adminPass->getNewHash();
                    $updat = "user_admin_algo='".$newAdminAlgo."', user_admin_salt='".$newAdminSalt."', user_admin_password='".$newAdminPassword."'";

                    if ( !empty( $reset_login ) ) {
                        $loginPass = new PasswordAuth();
                        $newLoginPass = $loginPass->getNewPassword();
                        $loginPass->inputPassword = $data['user_password'];
                        $loginPass->inputNewPassword = $newLoginPass;
                        $loginPass->inputNewPassword2 = $newLoginPass;
                        $loginPassIsReset = ( $loginPass->isValidNewPassword() === 0 ? TRUE : FALSE );
                        $new_admin_algo = $loginPass->getNewAlgo();
                        $new_admin_salt = $loginPass->getNewSalt();
                        $new_admin_password = $loginPass->getNewHash();
                        $updat .= ", user_algo='".$new_admin_algo."', user_salt='".$new_admin_salt."', user_password='".$new_admin_password."'";

                        $message = str_replace(
                            [
                                "[SITEURL]",
                                "[USER_NAME]",
                                "[NEW_PASS]",
                                "[NEW_ADMIN_PASS]",
                                "[ADMIN]",
                                "[RESET_MESSAGE]"
                            ],
                            [
                                "<a href='".fusion_get_settings( "siteurl" )."'>".fusion_get_settings( "sitename" )."</a>",
                                $data['user_name'],
                                $newLoginPass,
                                $newAdminPass,
                                $userdata['user_name'],
                                $reset_message
                            ], fusion_get_locale( 'apw_409', LOCALE.$data['user_language']."/admin/admin_reset.php" ) );

                    } else {
                        $message = str_replace(
                            [
                                "[SITEURL]",
                                "[USER_NAME]",
                                "[NEW_ADMIN_PASS]",
                                "[ADMIN]",
                                "[RESET_MESSAGE]"
                            ],
                            [
                                "<a href='".fusion_get_settings( 'siteurl' )."'>".fusion_get_settings( 'sitename' )."</a>",
                                $data['user_name'],
                                $newAdminPass,
                                $userdata['user_name'],
                                $reset_message
                            ], fusion_get_locale( 'apw_408', LOCALE.$data['user_language']."/admin/admin_reset.php" )
                        );
                        $loginPassIsReset = TRUE;
                    }

                    if ( $loginPassIsReset ) {
                        dbquery( "UPDATE ".DB_USERS." SET ".$updat." WHERE user_id='".$data['user_id']."'" );
                    }

                    if ( $loginPassIsReset && $adminPassIsReset && sendemail( $data['user_name'], $data['user_email'], $userdata['user_name'], $userdata['user_email'], $locale['apw_407'].fusion_get_settings( 'sitename' ), $message ) ) {
                        $reset_success[] = [ 'user_id' => $data['user_id'], 'user_name' => $data['user_name'], 'user_email' => $data['user_email'] ];
                    } else {
                        $reset_failed[] = [ 'user_id' => $data['user_id'], 'user_name' => $data['user_name'], 'user_email' => $data['user_email'] ];
                    }
                }

                $sucess_ids = "";
                $failed_ids = "";
                $text = "<div class='table-responsive'><table class='table table-hover table-striped'>\n";
                if ( !empty( $reset_success ) ) {
                    foreach ( $reset_success as $key => $info ) {
                        $sucess_ids .= $sucess_ids != "" ? ".".$info['user_id'] : $info['user_id'];
                        $text .= "<tr>\n";
                        $text .= "<td class='col-xs-2'><strong>".( $key == 0 ? $locale['apw_424'] : "" )."</strong></td>\n";
                        $text .= "<td class='col-xs-2'>".$info['user_name']." (".$info['user_email'].")</td>\n";
                        $text .= "</tr>\n";
                    }
                }

                if ( !empty( $reset_failed ) ) {
                    foreach ( $reset_failed as $key => $info ) {
                        $failed_ids .= $failed_ids != "" ? ".".$info['user_id'] : $info['user_id'];
                        $text .= "<tr>\n";
                        $text .= "<td class='col-xs-2'><strong>".( $key == 0 ? $locale['apw_425'] : "" )."</strong></td>\n";
                        $text .= "<td class='col-xs-2'>".$info['user_name']." (".$info['user_email'].")</td>\n";
                        $text .= "</tr>\n";
                    }
                }

                $text .= "</table>\n</div>";
                $preview_html = openmodal( 'apw_preview', $locale['apw_410'] );
                $preview_html .= "<p>".$text."</p>\n";
                $preview_html .= closemodal();
                add_to_footer( $preview_html );

                $this->data = [
                    'reset_id'        => 0,
                    'reset_admin_id'  => fusion_get_userdata( 'user_id' ),
                    'reset_timestamp' => time(),
                    'reset_sucess'    => $sucess_ids,
                    'reset_failed'    => $failed_ids,
                    'reset_admins'    => $reset_admin,
                    'reset_reason'    => $reset_message
                ];

                dbquery_insert( DB_ADMIN_RESETLOG, $this->data, 'save' );
                add_notice( 'success', $locale['apw_411'] );
            }
        }
    }

    public function display_admin() {
        $locale = fusion_get_locale();

        opentable( $locale['apw_title'] );
        $allowed_section = [ "adminreset_form", "adminreset_list" ];
        $section = get( 'section' );
        $section = $section && in_array( $section, $allowed_section ) ? $section : 'adminreset_list';

        $tab_title['title'][] = $locale['apw_415'];
        $tab_title['id'][] = 'adminreset_list';
        $tab_title['icon'][] = "";

        $tab_title['title'][] = $locale['apw_title'];
        $tab_title['id'][] = 'adminreset_form';
        $tab_title['icon'][] = "";

        echo opentab( $tab_title, $section, 'adminreset_list', TRUE );
        switch ( $section ) {
            case "adminreset_form":
                $this->admin_reset_form();
                break;
            default:
                $this->admin_reset_listing();
                break;
        }
        echo closetab();
        closetable();
    }

    public function admin_reset_form() {
        $locale = fusion_get_locale();
        fusion_confirm_exit();
        openside( '', 'm-t-15' );
        echo openform( 'admin_reset', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;section=adminreset_form" );
        echo form_select( 'reset_admin', $locale['apw_400'], '', [
            'required'    => TRUE,
            'options'     => $this->load_admins(),
            'placeholder' => $locale['choose'],
            'allowclear'  => TRUE,
            'inline'      => TRUE
        ] );

        echo form_textarea( 'reset_message', $locale['apw_404'], '', [ 'inline' => TRUE, 'required' => TRUE, 'autosize' => TRUE ] );
        echo form_checkbox( "reset_login", $locale['apw_405'], '', [ "inline" => TRUE ] );
        echo form_button( 'reset_admins', $locale['apw_406'], $locale['apw_406'], [ 'class' => 'btn-primary' ] );
        echo closeform();
        closeside();
    }

    private function load_admins() {
        $list = $this->admin_title();

        $result = dbquery( "SELECT user_id, user_name, user_level
            FROM ".DB_USERS."
            WHERE user_level<=".USER_LEVEL_ADMIN."
            ORDER BY user_level DESC, user_name"
        );

        if ( dbrows( $result ) > 0 ) {
            while ( $data = dbarray( $result ) ) {
                $list[ $data['user_id'] ] = $data['user_name'];
            }
        }

        return (array)$list;
    }

    private function admin_title() {
        $locale = fusion_get_locale();
        return [ "all" => $locale['apw_401'], "sa" => $locale['apw_402'], "a" => $locale['apw_403'] ];
    }

    private function admin_reset_listing() {
        $all_admin_reset = $this->load_all_admin_reset();
        $locale = fusion_get_locale();
        echo "<div class='m-t-15'>";
        if ( !empty( $all_admin_reset ) ) {
            echo openform( 'reset_table', 'post', FUSION_REQUEST );
            echo "<div class='table-responsive'><table id='reset-table' class='table table-hover table-striped'>\n";
            echo "<thead><tr>\n";
            echo "<th>&nbsp;</th>\n";
            echo "<th><small><strong>".$locale['apw_417']."</strong></small></th>\n";
            echo "<th><small><strong>".$locale['apw_418']."</strong></small></th>\n";
            echo "<th><small><strong>".$locale['apw_419']."</strong></small></th>\n";
            echo "<th><small><strong>".$locale['apw_420']."</strong></small></th>\n";
            echo "<th><small><strong>".$locale['apw_421']."</strong></small></th>\n";
            echo "<th><small><strong>".$locale['apw_427']."</strong></small></th>\n";
            echo "</tr></thead>\n";
            echo "<tbody>\n";

            foreach ( $all_admin_reset as $info ) {
                $adm_title = $this->admin_title();
                $reset_passwords = ( isnum( $info['reset_admins'] ) ? profile_link( $info['user_id_reset'], $info['user_name_reset'], $info['user_status_reset'] ) : $adm_title[ $info['reset_admins'] ] );
                $sucess = !empty( $info['reset_sucess'] ) ? count( explode( ".", $info['reset_sucess'] ) ) : 0;
                $failed = !empty( $info['reset_failed'] ) ? count( explode( ".", $info['reset_failed'] ) ) : 0;
                echo "<tr id='rowresetid".$info['reset_id']."'>\n";
                echo "<td>".form_checkbox( 'reset_id[]', '', '', [ 'value' => $info['reset_id'], 'input_id' => 'resetid'.$info['reset_id'] ] )."</td>\n";
                echo "<td>".showdate( "shortdate", $info['reset_timestamp'] )."</td>\n";
                echo "<td>".profile_link( $info['user_id'], $info['user_name'], $info['user_status'] )."</td>\n";
                echo "<td>".$reset_passwords."</td>\n";
                echo "<td>".$sucess." ".$locale['apw_422']." ".( $sucess + $failed )."</td>\n";
                echo "<td>".( $info['reset_reason'] ? $info['reset_reason'] : $locale['apw_423'] )."</td>\n";
                echo "<td><a id='confirm' class='btn btn-danger btn-sm' href='".FUSION_SELF.fusion_get_aidlink()."&amp;section=adminreset_list&amp;action=delete&amp;reset_id=".$info['reset_id']."' onclick=\"return confirm('".$locale['apw_428']."');\"><i class='fa fa-trash'></i> ".$locale['delete']."</a></td>\n";
                echo "</tr>\n";
            }

            echo "</tbody>";
            echo "</table>\n</div>";
            echo "<div class='clearfix display-block'>\n";
            echo "<div class='display-inline-block pull-left m-r-20'>".form_checkbox( 'check_all', $locale['apw_430'], '', [ 'class' => 'm-b-0', 'reverse_label' => TRUE ] )."</div>";
            echo "<div class='display-inline-block'><a class='btn btn-danger btn-sm' onclick=\"run_admin('delete');\"><i class='fa fa-trash-o'></i> ".$locale['delete']."</a></div>";
            echo "</div>\n";
            echo closeform();
            add_to_jquery( "
                $('#check_all').bind('click', function() {
                    if ($(this).is(':checked')) {
                        $('input[name^=reset_id]:checkbox').prop('checked', true);
                        $('#reset-table tbody tr').addClass('active');
                    } else {
                        $('input[name^=reset_id]:checkbox').prop('checked', false);
                        $('#reset-table tbody tr').removeClass('active');
                    }
                });

                $('input[name^=reset_id]').click(function() {
                    var id = $(this).attr('id');
                    if ($(this).is(':checked')) {
                        $('#row + id +').addClass('active');
                    } else {
                        $('#row + id +').removeClass('active');
                    }
                });
            " );
        } else {
            echo "<div class='well text-center'>".$locale['apw_426']."</div>\n";
        }
        echo '</div>';
    }

    private function load_all_admin_reset() {
        $list = [];
        $result = dbquery( "SELECT arl.*, u1.user_status, u1.user_name, u1.user_id, u2.user_name as user_name_reset, u2.user_id as user_id_reset, u2.user_status as user_status_reset
            FROM ".DB_ADMIN_RESETLOG." arl
            LEFT JOIN ".DB_USERS." u1 ON arl.reset_admin_id=u1.user_id
            LEFT JOIN ".DB_USERS." u2 ON arl.reset_admins=u2.user_id
            ORDER BY arl.reset_timestamp DESC"
        );

        if ( dbrows( $result ) > 0 ) {
            while ( $data = dbarray( $result ) ) {
                $list[] = $data;
            }
        }

        return (array)$list;
    }
}

$admres = new AdminPasswordResetAdministration();
$admres->display_admin();
require_once THEMES.'templates/footer.php';
