<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: user_groups.php
| Author: Core Development Team
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
require_once THEMES.'templates/admin_header.php';
pageaccess('UG');

/**
 * Class UserGroups
 * Administration
 */
class UserGroups {
    private static $instance = NULL;
    private static $locale = [];
    private static $limit = 20;
    private static $groups = [];
    private static $default_groups = [];
    private static $group_users = [];
    public $allowed_sections = ["usergroup", "usergroup_form", "user_form"];

    private $data = [
        'group_id'          => 0,
        'group_name'        => '',
        'group_description' => '',
        'group_icon'        => ''
    ];

    public function __construct() {
        self::$locale = fusion_get_locale( "", LOCALE . LOCALESET . "admin/user_groups.php" );

        add_breadcrumb( ['link' => ADMIN . 'user_groups.php' . fusion_get_aidlink(), "title" => self::$locale['GRP_420']] );

        self::$groups = array_slice( getusergroups(), 4 ); //delete 0,-101,-102,-103
        self::$default_groups = array_slice( getusergroups(), 0, 4 ); //delete 0,-101,-102,-103

        switch ( get( 'action' ) ) {
            case 'delete':
                if ( self::verifyGroup( get( 'group_id' ) ) ) {
                    dbquery( "DELETE FROM " . DB_USER_GROUPS . " WHERE group_id = '" . (int)get( 'group_id' ) . "'" );
                    addnotice( 'success', self::$locale['GRP_407'] );
                } else {
                    addnotice( 'warning', self::$locale['GRP_405']." ".self::$locale['GRP_406'] );
                }
                redirect( clean_request( "", ["section=usergroup", "aid"] ) );
                break;
            case 'edit':
                if ( check_get( 'group_id' ) ) {
                    foreach ( self::$groups as $groups ) {
                        if ( get( 'group_id' ) == $groups[0]) {
                            $this->data = [
                                'group_id'          => $groups[0],
                                'group_name'        => $groups[1],
                                'group_description' => $groups[2],
                                'group_icon'        => $groups[3],
                            ];
                        }
                    }
                }
                break;
            case 'user_edit':
                if ( check_post( 'user_send' ) && empty( post( 'user_send' ) ) ) {
                    fusion_stop();
                    addnotice( 'danger', self::$locale['GRP_403'] );
                    redirect( clean_request( "section=user_group", ["", "aid"] ) );
                }
                if ( check_post( 'user_send' ) && !empty( post( 'user_send' ) ) ) {
                    $group_userSend = sanitizer( 'user_send', '', 'user_send' );
                    $group_userSender = explode( ',', $group_userSend );
                    foreach ( $group_userSender as $grp ) {
                        self::$group_users[] = fusion_get_user( $grp );
                    }
                }
                break;
            case 'user_add':
                if ( empty( post( 'groups_add' ) ) or empty( get( 'group_id' ) ) ) {
                    fusion_stop();
                    addnotice( 'danger', self::$locale['GRP_408'] );
                    redirect( clean_request( "", ["section=user_form", "aid"] ) );
                }
                break;
            case 'user_del':
                if ( empty( post( 'group' ) ) or empty( get( 'group_id' ) ) ) {
                    fusion_stop();
                    addnotice( 'danger', self::$locale['GRP_408'] );
                    redirect( clean_request( "", ["section=user_form", "aid"] ) );
                }
                break;
            default:
                break;
        }
    }

    public static function getInstance() {
        if ( self::$instance === NULL ) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /*
     * Update add member, remove member
     */
    private function updateGroup() {
        if ( check_post( 'save_group' ) ) {
            $this->data = [
                'group_id'          => sanitizer( 'group_id', 0, 'group_id' ),
                'group_name'        => sanitizer( 'group_name', '', 'group_name' ),
                'group_description' => sanitizer( 'group_description', '', 'group_description' ),
                'group_icon'        => sanitizer( 'group_icon', '', 'group_icon' ),
            ];
            if ( fusion_safe() ) {
                dbquery_insert( DB_USER_GROUPS, $this->data, empty( $this->data['group_id'] ) ? "save" : "update" );
                addnotice( "success", empty( $this->data['group_id'] ) ? self::$locale['GRP_401'] : self::$locale['GRP_400'] );
                redirect( clean_request( "section=usergroup", ["", "aid"] ) );
            }
        }

        if ( check_post( 'add_sel' ) ) {
            $group_userSend = sanitizer( ['groups_add'], '', 'groups_add' );
            $group_userSender = explode( ',', $group_userSend );
            if ( check_get( 'group_id' ) && get( 'group_id', FILTER_SANITIZE_NUMBER_INT ) ) {
                $group = getgroupname( get( 'group_id' ) );
                if ( $group ) {
                    $added_user = [];
                    foreach ( $group_userSender as $grp ) {
                        $groupadduser = fusion_get_user( $grp );
                        if ( !in_array( get( 'group_id' ), explode( ".", $groupadduser['user_groups'] ) ) ) {
                            $groupadduser['user_groups'] = $groupadduser['user_groups'] . "." . get( 'group_id' );
                            $added_user['adduser'][] = $groupadduser['user_name'];
                            dbquery_insert( DB_USERS, $groupadduser, "update" );
                        } else {
                            $added_user['nouser'][] = $groupadduser['user_name'];
                        }
                    }
                    !empty( $added_user['adduser'] ) ? addnotice( "success", sprintf( self::$locale['GRP_410'], implode( ', ', $added_user['adduser'] ), $group ) ) : '';
                    !empty( $added_user['nouser'] ) ? addnotice( "warning", sprintf( self::$locale['GRP_411'], implode( ', ', $added_user['nouser'] ), $group ) ) : '';
                    redirect( FUSION_REQUEST );
                }
            }
        }

        if ( check_post( 'remove_sel' ) ) {
            $group_userSend = sanitizer( ['group'], '', 'group' );
            $group_userSender = explode( ',', $group_userSend );
            if ( check_get( 'group_id' ) && get( 'group_id', FILTER_SANITIZE_NUMBER_INT ) ) {
                $group = getgroupname( get( 'group_id' ) );
                if ($group) {
                    $rem_user = [];
                    foreach ( $group_userSender as $user ) {
                        $groupadduser = fusion_get_user( $user );
                        if ( !empty( $groupadduser['user_groups'] ) && in_array( get( 'group_id' ), explode( ".", $groupadduser['user_groups'] ) ) ) {
                            $groupadduser['user_groups'] = self::addUserGroup( get( 'group_id' ), $groupadduser['user_groups'] );
                            $rem_user[] = $groupadduser['user_name'];
                            dbquery_insert( DB_USERS, $groupadduser, "update" );
                        }
                    }
                    addnotice( "success", sprintf( self::$locale['GRP_411'], implode( ', ', $rem_user ), $group ) );
                    redirect( FUSION_REQUEST );
                }
            }
        }

        if ( check_post( 'remove_all' ) ) {
            if ( check_get( 'group_id' ) && get( 'group_id', FILTER_SANITIZE_NUMBER_INT ) ) {
                $group_name = getgroupname( get( 'group_id' ) );
                if ( $group_name ) {
                    $group_id = get( 'group_id' );
                    $result = dbquery( "SELECT user_id, user_name, user_groups
                        FROM " . DB_USERS . "
                        WHERE user_groups REGEXP('^\\\.$group_id$|\\\.$group_id\\\.|\\\.$group_id$')
                    " );
                    $i = 0;
                    if ( dbrows( $result ) ) {
                        while ( $data = dbarray( $result ) ) {
                            $data['user_groups'] = self::addUserGroup( get( 'group_id' ), $data['user_groups'] );
                            dbquery_insert( DB_USERS, $data, "update" );
                            $i++;
                        }
                        addnotice( "success", sprintf( self::$locale['GRP_411'], $i, $group_name ) );
                        redirect( FUSION_REQUEST );
                    }
                }
            }
        }

    }

    static function addUserGroup( $group_id, $groups ) {
        return preg_replace( ["(^\.$group_id$)", "(\.$group_id\.)", "(\.$group_id$)"], ["", ".", ""], $groups );
    }

    static function countUserGroup($id) {
        if ( isnum( $id ) ) {
            return dbcount( "(user_id)", DB_USERS, "user_groups REGEXP('^\\\.$id$|\\\.$id\\\.|\\\.$id$')" );
        }

        return FALSE;
    }

    static function verifyGroup($id) {
        if ( isnum( $id ) ) {
            if ( !dbcount( "(user_id)", DB_USERS, "user_groups REGEXP('^\\\.$id$|\\\.$id\\\.|\\\.$id$')")
                && dbcount( "(group_id)", DB_USER_GROUPS, "group_id = '" . (int)$id . "'" )
            ) {
                return TRUE;
            }
        }

        return FALSE;
    }

    public function displayAdmin() {
        $this->updateGroup();
        $edit = ( check_get( 'action' ) && get( 'action' ) == 'edit' ) && check_get( 'group_id' );

        $sections = in_array( get( 'section' ), $this->allowed_sections ) ? get( 'section' ) : $this->allowed_sections[0];

        $tabs['title'][] = self::$locale['GRP_420'];
        $tabs['id'][] = "usergroup";
        $tabs['icon'][] = "";
        $tabs['title'][] = $edit ? self::$locale['GRP_421'] : self::$locale['GRP_428'];
        $tabs['id'][] = "usergroup_form";
        $tabs['icon'][] = "";
        if ( check_get( 'group_id' ) && $sections == 'user_form' ) {
            $tabs['title'][] = self::$locale['GRP_423'];
            $tabs['id'][] = "user_form";
            $tabs['icon'][] = "";
        }

        $view = '';
        switch ( $sections ) {
            case "usergroup_form":
                $view = $this->groupForm();
                break;
            case "user_form":
                if ( !empty( get( 'group_id' ) ) ) {
                    $view = $this->userForm();
                } else {
                    redirect( clean_request( 'section=usergroup', ['section'], FALSE ) );
                }
                break;
            default:
                $view = $this->groupListing();
                break;
        }

        opentable( self::$locale['GRP_420'] );
        echo opentab( $tabs, $sections, "usergroup", TRUE, FALSE, 'section', ['action', 'group_id'] );
        echo $view;
        echo closetab();
        closetable();
    }

    /*
     * Displays Group Listing
     */
    public function groupListing() {
        $aidlink = fusion_get_aidlink();
        $add_link = clean_request( "section=usergroup_form", ['section'], FALSE );

        $html = "<div class='clearfix'>
            <div class='text-right'>
                <a class='btn btn-success' href='" . $add_link . "'>" . get_icon( 'fa fa-plus', 'fa-fw m-r-10' ) . self::$locale['GRP_428'] . "</a>
            </div>
        </div>
        <div class='table-responsive'><table class='table table-striped'>
            <thead>
                <tr>
                    <th>" . self::$locale['GRP_432'] . "</th>
                    <th>" . self::$locale['GRP_433'] . "</th>
                    <th class='min'>" . self::$locale['GRP_436'] . "</th>
                    <th>" . self::$locale['GRP_437'] . "</th>
                    <th>" . self::$locale['GRP_435'] . "</th>
                <tr>
            </thead>
            <tbody>\n";
            if ( !empty( self::$groups ) ) {
                foreach (self::$groups as $groups) {
                    $edit_link = clean_request( "section=usergroup_form&action=edit&group_id=" . $groups[0], ['section', 'action', 'group_id'], FALSE );
                    $member_link = clean_request( "section=user_form&action=user_edit&group_id=" . $groups[0], ['section', 'action', 'group_id'], FALSE );
                    $del_link = clean_request( "section=usergroup&action=delete&group_id=" . $groups[0], ['section', 'action', 'group_id'], FALSE );
                    $html .= "<tr>
                        <td><a href='" . $edit_link . "'>" . $groups[1] . " (" . self::countUserGroup( $groups[0] ) . ")</a></td>
                        <td>" . $groups[2] . "</td>
                        <td class='text-center'>" . ( !empty( $groups[3] ) ? get_icon( $groups[3] ) : $groups[3] ) . "</td>
                        <td>
                            <a href='" . $member_link . "'>" . self::$locale['GRP_438'] . "</a> -
                            <a href='" . $edit_link . "'>" . self::$locale['edit'] . "</a> -
                            <a href='" . $del_link . "' onclick=\"return confirm('" . self::$locale['GRP_425'] . "');\">" . self::$locale['delete'] . "</a>
                        </td>
                        <td>" . $groups[0] . "</td>
                    </tr>";
                }
            } else {
                $html .= "<tr>\n<td class='text-center' colspan='5'>" . self::$locale['GRP_404'] . "</td>\n</tr>\n";
            }
            $html .= "</tbody>\n<tfoot>\n";
            $html .= "<tr><td class='text-center' colspan='5'><strong>" . self::$locale['GRP_426'] . "</strong></td></tr>\n";
            foreach ( self::$default_groups as $groups ) {
            	$html .= "<tr>
            	    <td>" . $groups[1] . "</td>
            	    <td>" . $groups[2] . "</td>
            	    <td class='text-center'>" . ( !empty( $groups[3] ) ? get_icon( $groups[3] ) : $groups[3] ) . "</td>
            	    <td>&nbsp;</td>
            	    <td>" . $groups[0] . "</td>
            	</tr>";
            }
            $html .= "</tfoot>\n";
            $html .= "</table>\n</div>";

        return $html;
    }

    /*
     * Group Add/Edit Form
     */
    public function groupForm() {
        $html = openform( 'editform', 'post', FUSION_REQUEST );
        $html .= form_hidden( 'group_id', '', $this->data['group_id']);
        $html .= form_text( 'group_name', self::$locale['GRP_432'], $this->data['group_name'], ['required' => TRUE, 'maxlength' => '100', 'error_text' => self::$locale['GRP_464']] );
        $html .= form_textarea( 'group_description', self::$locale['GRP_433'], $this->data['group_description'], ['autosize' => TRUE, 'maxlength' => '200'] );
        $html .= form_text( 'group_icon', self::$locale['GRP_439'], $this->data['group_icon'], ['maxlength' => '100', 'placeholder' => 'fa fa-user'] );
        $html .= form_button( 'save_group', self::$locale['GRP_434'], self::$locale['GRP_434'], ['class' => 'btn-primary'] );
        $html .= closeform();
        return $html;
    }

    /*
     * User Management Form
     */
    public function userForm() {
        $total_rows = $this->countUserGroup( get( 'group_id' ) );
        $rowstart = get_rowstart( "rowstart", $total_rows );

        $group = get( 'group_id', FILTER_SANITIZE_NUMBER_INT );
        $result = dbquery( "SELECT user_id, user_name, user_level, user_avatar, user_status
            FROM " . DB_USERS . "
            WHERE user_groups REGEXP('^\\\.$group$|\\\.$group\\\.|\\\.$group$')
            ORDER BY user_level DESC, user_name
            LIMIT " . (int)$rowstart . ", " . self::$limit
        );

        $rows = dbrows( $result );

        $html = "<h4>" . self::$locale['GRP_452'] . getgroupname( get( 'group_id' ) ) . "</h4>\n";
        $html .= "<hr/>\n";
        $html .= "<div class='row'>\n";
        $html .= "<div class='col-xs-12 col-sm-4'>\n";
        $html .= fusion_get_function( 'openside', self::$locale['GRP_440'] );
        $html .= openform( 'searchuserform', 'post', FUSION_REQUEST );
        $html .= form_user_select( 'user_send', self::$locale['GRP_440'], '', [
            'inline'      => FALSE,
            'required'    => TRUE,
            'max_select'  => 10,
            'inner_width' => '100%',
            'width'       => '100%',
            'allow_self'  => TRUE,
            'placeholder' => self::$locale['GRP_451'],
            'ext_tip'     => self::$locale['GRP_441']."<br />".self::$locale['GRP_442']
        ] );
        $html .= form_button( 'search_users', self::$locale['confirm'], self::$locale['confirm'], ['class' => 'btn-primary'] );
        $html .= closeform();
        if ( !empty( self::$group_users ) ) {
            $html .= openform( 'add_users_form', 'post', FUSION_REQUEST);
            $html .= "<div class='table-responsive'><table class='table table-striped table-hover'>\n";
            $html .= "<thead>\n";
            $html .= "<tr>\n";
            $html .= "<th></th>\n";
            $html .= "<th>" . self::$locale['GRP_446'] . "</th>\n";
            $html .= "<th>" . self::$locale['GRP_447'] . "</th>\n";
            $html .= "<tr>\n";
            $html .= "</thead>\n";
            $html .= "<tbody>\n";
            foreach ( self::$group_users as $groupusers ) {
                $html .= "<tr>\n";
                $html .= "<td>" . form_checkbox("groups_add[]", '', '', ["inline" => FALSE, 'value' => $groupusers['user_id']] ) . "</td>\n";
                $html .= "<td>" . $groupusers['user_name'] . "</td>\n";
                $html .= "<td>" . getuserlevel( $groupusers['user_level'] ) . "</td>\n";
                $html .= "</tr>\n";
            }
            $html .= "</tbody>\n";
            $html .= "</table>\n</div>";
            $html .= "<div class='spacer-xs'>\n";
            $html .= "<a class='btn btn-default' href='#' onclick=\"setChecked('add_users_form','groups_add[]',1);return false;\">" . self::$locale['GRP_448'] . "</a>\n";
            $html .= "<a class='btn btn-default' href='#' onclick=\"setChecked('add_users_form','groups_add[]',0);return false;\">" . self::$locale['GRP_449'] . "</a>\n";
            $html .= form_button( 'add_sel', self::$locale['GRP_450'], self::$locale['GRP_450'], ['class' => 'btn-primary'] );
            $html .= "</div>\n";
            $html .= closeform();
        }
        $html .= fusion_get_function( 'closeside', '' );
        $html .= "</div>\n";
        $html .= "<div class='col-xs-12 col-sm-8'>\n";

        $html .= fusion_get_function( 'openside', self::$locale['GRP_460'] );
        if ( $rows > 0 ) {
            $html .= "<div class='clearfix spacer-xs'>\n";
            $html .= ($total_rows > $rows ? "<div class='text-right'>\n" . makepagenav( $rowstart, self::$limit, $total_rows, self::$limit, clean_request( "", ['rowstart'], FALSE)."&" ) . "</div>\n" : "" );
            $html .= "<div class='overflow-hide'>" . sprintf( self::$locale['GRP_427'], $rows, $total_rows ) . "</div>\n";
            $html .= "</div>\n";
            $html .= openform( 'rem_users_form', 'post', FUSION_REQUEST );
            $html .= "<table class='table table-striped table-hover table-responsive'>\n";
            $html .= "<thead>\n";
            $html .= "<tr>\n";
            $html .= "<th>" . self::$locale['GRP_437'] . "</th>\n";
            $html .= "<th>" . self::$locale['GRP_446'] . "</th>\n";
            $html .= "<th>" . self::$locale['GRP_447'] . "</th>\n";
            $html .= "<tr>\n";
            $html .= "</thead>\n";
            $html .= "<tbody>\n";
            while ( $data = dbarray( $result ) ) {
                $html .= "<tr>\n";
                $html .= "<td>" . form_checkbox( "group[]", '', '', ["inline" => FALSE, 'value' => $data['user_id']] ) . "</td>\n";
                $html .= "<td>" . $data['user_name'] . "</td>\n";
                $html .= "<td>" . getuserlevel( $data['user_level'] ) . "</td>\n";
                $html .= "</tr>\n";
            }
            $html .= "</tbody></table>\n";
            $html .= "<div class='spacer-xs pull-right m-t-10'>\n";
            $html .= "<a class='btn btn-default' href='#' onclick=\"setChecked('rem_users_form','group[]',1);return false;\">" . self::$locale['GRP_448'] . "</a>\n";
            $html .= "<a class='btn btn-default' href='#' onclick=\"setChecked('rem_users_form','group[]',0);return false;\">" . self::$locale['GRP_449'] . "</a>\n";
            $html .= form_button('remove_sel', self::$locale['GRP_461'], self::$locale['GRP_461'], ['class' => 'btn-danger']);
            $html .= form_button('remove_all', self::$locale['GRP_462'], self::$locale['GRP_462'], ['class' => 'btn-danger']);
            $html .= "</div>\n";
            $html .= "</div>\n";
            $html .= closeform();
            add_to_jquery("$('#remove_sel').bind('click', function() { return confirm('" . self::$locale['GRP_465'] . "'); });");
            add_to_jquery("$('#remove_all').bind('click', function() { return confirm('" . self::$locale['GRP_466'] . "'); });");
        } else {
            $html .= "<div class='well text-center'>" . self::$locale['GRP_463'] . "</div>\n";
        }
        $html .= fusion_get_function( 'closeside', '' );

        $html .= "</div>\n";

        add_to_footer("<script type='text/javascript'>\n
        function setChecked(frmName, chkName, val) {"."\n
            dml=document.forms[frmName];"."\n"."len=dml.elements.length;"."\n"."for(i=0;i < len;i++) {"."\n
            if (dml.elements[i].name == chkName) {"."\n"."dml.elements[i].checked = val;"."\n
        }}}
        </script>\n");

        return $html;
    }
}

UserGroups::getInstance()->displayAdmin();
require_once FUSION_FOOTER;
