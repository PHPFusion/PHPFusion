<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: user_log.php
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
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';

$user_log = new UserLogAdmin();
$user_log->view();

require_once THEMES.'templates/footer.php';

class UserLogAdmin {

    private $dbOrder = "ORDER BY userlog_timestamp DESC";

    private $dbWhere = '';

    private $dbWhereCount = '';

    private $orderby = "userlog_timestamp";

    private $expr = "DESC";

    private $userField = "";

    private $orderbyArray = [];

    private $exprArray = [];

    private $getString = '';

    private $locale = [];

    private function getRowstart() {
        $rowstart = get( 'rowstart', FILTER_VALIDATE_INT );
        if ( !$rowstart ) {
            $rowstart = 0;
        }
        return $rowstart;
    }


    public function __construct() {
        pageAccess( 'UL' );
        $this->locale = fusion_get_locale( '', LOCALE.LOCALESET."admin/user_log.php" );
    }


    public function setFilterInfo() {
        // Set default values
        $user = '';

        $this->getString = fusion_get_aidlink();

        $this->orderbyArray = [
            'userlog_timestamp' => $this->locale['UL_002'],
            'user_name'         => $this->locale['UL_003'],
            'userlog_field'     => $this->locale['UL_004']
        ];

        $this->exprArray = [ "DESC" => $this->locale['UL_019'], "ASC" => $this->locale['UL_018'] ];

        if ( check_post( 'orderby' ) && in_array( post( 'orderby' ), $this->orderbyArray ) ) {

            $orderby = sanitizer( 'orderby', 'DESC', 'orderby' );

            $this->dbOrder = "ORDER BY ".$orderby;

            if ( check_post( 'expr' ) && in_array( post( 'expr' ), $this->exprArray ) ) {

                $expr = sanitizer( 'expr', '', 'expr' );

                $this->dbOrder .= " ".$expr;
            }

            if ( check_post( 'user' ) ) {

                $user = sanitizer( 'user', '', 'user' );

                if ( isnum( $user ) ) {

                    $this->dbWhere = "userlog_user_id='".$user."'";

                } else if ( $user != "" ) {

                    $user = trim( stripinput( $user ) );

                    $this->dbWhere = "user_name LIKE '".$user."%'";
                }
            }


            if ( check_post( 'userField' ) && post( 'userField' ) != "---" && post( 'userField' ) != "" ) {

                $this->userField = trim( stripinput( post( 'userField' ) ) );
                $this->dbWhere .= ( $this->dbWhere != "" ? " AND userlog_field='".$this->userField."'" : "userlog_field='".$this->userField."'" );
            }

            $this->dbWhereCount = $this->dbWhere;

            $this->dbWhere = ( $this->dbWhere != "" ? "WHERE ".$this->dbWhere : "" );

            // build get string
            $this->getString .= "&amp;orderby=".$orderby."&amp;expr=".$this->expr."&amp;user=".$user."&amp;userField=".$this->userField;

        }

    }

    private function logAction() {
        if ( check_post( 'log_id' ) ) {

            if ( check_post( 'table_action' ) ) {
                $input = explode( ",", sanitizer( 'log_id', "", "log_id" ) );
                if ( !empty( $input ) ) {
                    foreach ( $input as $log_id ) {
                        dbquery( "DELETE FROM ".DB_USER_LOG." WHERE userlog_id=:logid", [ ':logid' => (int)$log_id ] );
                    }
                }
            }

            add_notice( 'info', $this->locale['UL_006'] );
            redirect( clean_request( '', [ 'delete' ], FALSE ) );
        }
        return FALSE;
    }

    private function dayDelete() {
        if ( post( 'daydelete', FILTER_VALIDATE_INT ) ) {

            $delete = sanitizer( 'daydelete', 0, 'daydelete' );
            $bind = [
                ':timer' => TIME - $delete * 24 * 60 * 60,
            ];
            dbquery( "DELETE FROM ".DB_USER_LOG." WHERE userlog_timestamp<:timer", $bind );
            add_notice( 'info', sprintf( $this->locale['UL_005'], $delete ) );
            redirect( clean_request( '', [ 'delete' ], FALSE ) );
        }
        return FALSE;
    }

    private function delete() {
        if ( $delete = post( 'delete', FILTER_VALIDATE_INT ) ) {
            dbquery( "DELETE FROM ".DB_USER_LOG." WHERE userlog_id=:delete", [ ':delete' => (int)$delete ] );
            add_notice( 'info', $this->locale['UL_006'] );
            redirect( clean_request( '', [ 'delete' ], FALSE ) );
        }
        return FALSE;
    }

    private function userFieldOptions() {
        $options['user_name'] = $this->locale['UL_003'];
        $options['user_email'] = $this->locale['UL_007'];
        $result = dbquery( "SELECT field_name, field_title FROM ".DB_USER_FIELDS." WHERE field_log='1'" );
        if ( dbrows( $result ) ) {
            while ( $data = dbarray( $result ) ) {
                $options[ $data['field_name'] ] = $data['field_title'];
            }
        }

        return $options;
    }

    public function view() {

        $this->setFilterInfo();

        if ( !$this->logAction() && !$this->dayDelete() && !$this->delete() ) {

            add_breadcrumb( [ 'link' => ADMIN.'administrators.php'.fusion_get_aidlink(), 'title' => $this->locale['UL_001'] ] );

            opentable( $this->locale['UL_001'] );

            openside();
            echo openform( 'userlog_search', 'post', FUSION_REQUEST );
            echo form_hidden( 'aid', '', iAUTH );
            echo form_select( 'orderby', $this->locale['UL_008'], $this->orderby, [
                'options'    => $this->orderbyArray,
                'placholder' => $this->locale['choose'],
                'inline'     => TRUE,
                'select_alt' => TRUE,
            ] );
            echo form_select( 'expr', ' ', $this->orderby, [
                'options'    => $this->exprArray,
                'placholder' => $this->locale['choose'],
                'inline'     => TRUE,
                'select_alt' => TRUE,
            ] );
            echo form_user_select( "user", $this->locale['UL_009'], '', [
                'max_select'  => 1,
                'inline'      => TRUE,
                'inner_width' => '100%',
                'width'       => '100%',
                'allow_self'  => TRUE,
            ] );
            echo form_select( 'userField', $this->locale['UL_010'], $this->userField, [
                'options'     => $this->userFieldOptions(),
                'placeholder' => $this->locale['choose'],
                'allowclear'  => 1,
                'inline'      => TRUE,
                'select_alt'  => TRUE,
            ] );
            echo form_button( 'submit', $this->locale['UL_011'], $this->locale['UL_011'], [ 'class' => 'btn-primary' ] );
            echo closeform();
            closeside();

            // at least validate token.

            if ( fusion_safe() ) {
                $rowstart = (int)$this->getRowstart();

                $result = dbquery( "SELECT SQL_CALC_FOUND_ROWS userlog_id, userlog_user_id, userlog_field, userlog_value_old, userlog_value_new, userlog_timestamp, user_name, user_status
                   FROM ".DB_USER_LOG."
                   LEFT JOIN ".DB_USERS." ON userlog_user_id=user_id
                   ".$this->dbWhere."
                   ".$this->dbOrder."
                   LIMIT $rowstart,20" );

                $rows = dbresult( dbquery( "SELECT FOUND_ROWS()" ), 0 );

                if ( dbrows( $result ) ) {

                    echo "<div class='table-responsive'><table id='log-table' class='table table-striped'>\n";
                    echo "<thead>\n<tr>\n";
                    echo "<th></th>\n";
                    echo "<th>".$this->locale['UL_002']."</th>\n";
                    echo "<th style='width: 150px;'>".$this->locale['UL_003']."</th>\n";
                    echo "<th style='width: 140px;'>".$this->locale['UL_004']."</th>\n";
                    echo "<th style='width: 160px;'>".$this->locale['UL_012']."</th>\n";
                    echo "<th style='width: 160px;'>".$this->locale['UL_013']."</th>\n";
                    echo "<th style='width: 160px;'>".$this->locale['UL_014']."</th>\n";
                    echo "</tr>\n</thead>\n";

                    echo "<tbody>\n";
                    echo openform( 'userlog_table', 'post', FUSION_REQUEST );
                    echo form_hidden( 'table_action', '', '' );
                    while ( $data = dbarray( $result ) ) {
                        echo "<tr>";
                        echo "<td>".form_checkbox( "log_id[]", "", "", [ "value" => $data['userlog_id'], "class" => "m-0" ] )."</td>\n";
                        echo "<td>".showdate( "shortdate", $data['userlog_timestamp'] )."</td>\n";
                        echo "<td>".profile_link( $data['userlog_user_id'], $data['user_name'], $data['user_status'] )."</td>\n";
                        echo "<td>".$data['userlog_field']."</td>\n";
                        echo "<td>".trimlink( $data['userlog_value_old'], 100 )."</td>\n";
                        echo "<td>".trimlink( $data['userlog_value_new'], 100 )."</td>\n";
                        echo "<td><a href='".FUSION_SELF.$this->getString."&amp;delete=".$data['userlog_id']."'>".$this->locale['delete']."</a></td>\n";
                        echo "</tr>\n";
                    }

                    echo "</tbody>\n";
                    echo "</table>\n</div>";
                    echo "<div class='clearfix display-block'>\n";
                    echo "<div class='display-inline-block pull-left m-r-20'>".form_checkbox( 'check_all', $this->locale['UL_020'], '', [ 'class' => 'm-b-0', 'reverse_label' => TRUE ] )."</div>";
                    echo "<div class='display-inline-block'><a class='btn btn-danger btn-sm' onclick=\"run_admin('delete', '#table_action', '#userlog_table');\"><i class='fa fa-fw fa-trash-o'></i> ".$this->locale['delete']."</a></div>";
                    echo "</div>\n";
                    echo closeform();
                    add_to_jquery( /** @lang JavaScript 1.5 */ "
                    $('#check_all').bind('click', function() {
                        if ($(this).is(':checked')) {
                            $('input[name^=log_id]:checkbox').prop('checked', true);
                            $('#log-table tbody tr').addClass('active');
                        } else {
                            $('input[name^=log_id]:checkbox').prop('checked', false);
                             $('#log-table tbody tr').removeClass('active');
                        }
                    });
                    " );

                } else {
                    echo "<div class='well text-center'>".$this->locale['UL_015']."</div>\n";
                }

                if ( $rows > 20 ) {
                    echo "<div class='m-t-5 text-center'>\n".makepagenav( $rowstart, 20, $rows, 3, FUSION_SELF.$this->getString."&amp;" )."\n</div>\n";
                }

            }

            openside( '', 'm-t-20' );
            echo openform( 'userlog_delete', 'post', FUSION_REQUEST );
            echo form_text( 'daydelete', $this->locale['UL_016'], '', [
                'max_length'  => 3,
                'type'        => 'number',
                'placeholder' => $this->locale['UL_017'],
                'inline'      => TRUE
            ] );
            echo form_button( 'submit', $this->locale['UL_011'], $this->locale['UL_011'], [ 'class' => 'btn-primary' ] );
            echo closeform();
            closeside();

            closetable();
        }
    }
}
