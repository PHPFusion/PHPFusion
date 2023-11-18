<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: user_log.php
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
pageaccess('UL');

$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/user_log.php");

add_breadcrumb(['link' => ADMIN.'user_log.php'.fusion_get_aidlink(), 'title' => $locale['UL_001']]);

$rowstart = (check_get('rowstart') && get('rowstart', FILTER_VALIDATE_INT) ? get('rowstart') : 0);

$aidlink = fusion_get_aidlink();

// Set default values
$limit = 20;
$get_string = $aidlink;
$user = "";
$user_field = "";

$filters = [
    'condition'  => "",
    'orderby'    => "l.userlog_timestamp",
    'expr'       => "DESC",
    'user'       => '',
    'userfields' => ''
];

$orderby_array = [
    'userlog_timestamp' => $locale['UL_002'],
    'user_name'         => $locale['UL_003'],
    'userlog_field'     => $locale['UL_004']
];

$expr_array = ["DESC" => $locale['UL_019'], "ASC" => $locale['UL_018']];

if ( check_post( 'orderby' ) ) {
    $filters['orderby'] = sanitizer( 'orderby', 'userlog_timestamp', 'orderby' );
    if ( check_post( 'expr' ) ) {
        $filters['expr'] = sanitizer( 'expr', 'DESC', 'expr' );
    }
    $get_string .= !empty( $filters['orderby'] ) ? "&orderby=" . $filters['orderby'] : '';
    $get_string .= !empty( $filters['expr'] ) ? "&expr=" . $filters['expr'] : '';
}
if ( check_post( 'user' ) && post( 'user' , FILTER_VALIDATE_INT) ) {
    $user = sanitizer( 'user', '', 'user' );
    $filters['user'] = $user;
    if ( isnum( $user ) ) {
        $filters['condition'] = "l.userlog_user_id = '" . $user . "'";
    } else if ( post( 'user' ) != "" ) {
        $filters['condition'] = "u.user_name LIKE '" . $user . "%'";
    }
    $get_string .= !empty( $filters['user'] ) ? "&user=" . $filters['user'] : '';
}
if ( check_post( 'user_field' ) && (post( 'user_field' ) != "---" ) && (post( 'user_field' ) != "" ) ) {
    $user_field = sanitizer( 'user_field', '', 'user_field' );
    $filters['condition'] .= (!empty($filters['condition']) ? " AND l.userlog_field = '" . $user_field . "'" : "l.userlog_field = '" . $user_field . "'" );
    $get_string .= !empty( $user_field ) ? "&user_field=" . $user_field . "" : '';
}

// End $_GET Vars
if ( check_post( ['log_id'] ) ) {
    if ( check_post( 'table_action' ) && check_post( ['log_id'] ) ) {
        $input = post( ['log_id'] ) ? explode( ",", sanitizer( ['log_id'], "", "log_id" ) ) : [];
        if ( !empty( $input ) ) {
            foreach ( $input as $log_id ) {
                deleteLog( $log_id );
            }
        }
    }
    addnotice( 'info', $locale['UL_006'] );
    redirect( clean_request( '', ['delete'], FALSE ) );
}

if ( check_post( 'day_delete' ) && post( 'day_delete', FILTER_VALIDATE_INT ) ) {
    $delete = sanitizer( 'day_delete', 0, 'day_delete' );
    $result = dbquery("DELETE FROM ".DB_USER_LOG." WHERE userlog_timestamp<:time", [
        ':time' => time() - $delete * 24 * 60 * 60,
    ]);
    addnotice( 'info', sprintf( $locale['UL_005'], $delete ) );
    redirect( clean_request( '', ['delete'], FALSE ) );
}

if ( check_get( 'delete' ) && get( 'delete', FILTER_VALIDATE_INT ) ) {
	deleteLog( get( 'delete' ) );
    addnotice( 'info', $locale['UL_006'] );
    redirect( clean_request( '', ['delete'], FALSE ) );
}


opentable( $locale['UL_001'] );

openside('');
echo openform( 'userlog_search', 'post', FUSION_REQUEST );
echo form_select( 'orderby', $locale['UL_008'], $filters['orderby'], [
    'options'    => $orderby_array,
    'placholder' => $locale['choose'],
    'inline'     => TRUE
] );
echo form_select( 'expr', ' ', $filters['expr'], [
    'options'    => $expr_array,
    'placholder' => $locale['choose'],
    'inline'     => TRUE
] );
echo form_user_select( 'user', $locale['UL_009'], $user, [
    'max_select'  => 1,
    'inline'      => TRUE,
    'inner_width' => '100%',
    'allow_self'  => TRUE,
] );
echo form_select( 'user_field', $locale['UL_010'], $user_field, [
    'options'     => user_field_options(),
    'placeholder' => $locale['choose'],
    'allowclear'  => 1,
    'inline'      => TRUE
] );
echo form_button( 'submit_uf', $locale['UL_011'], $locale['UL_011'], ['class' => 'btn-primary'] );
echo closeform();
closeside();

// at least validate token.
if ( !defined( 'FUSION_NULL' ) ) {
    openside( '' );
    $result = dbquery( "SELECT l.userlog_id, l.userlog_user_id, l.userlog_field, l.userlog_value_old, l.userlog_value_new, l.userlog_timestamp, u.user_name, u.user_status
        FROM " . DB_USER_LOG . " AS l
        LEFT JOIN " . DB_USERS . " AS u ON l.userlog_user_id = u.user_id
        " . ( !empty( $filters['condition'] ) ? 'WHERE ' . $filters['condition'] : '' ) . "
        " . ( !empty( $filters['orderby'] ) ? 'ORDER BY ' . $filters['orderby'] . ' ' . $filters['expr']  : '') . "
        LIMIT " . $rowstart . "," . $limit . "
    " );
    $rows = dbrows( $result );
    if ( $rows ) {
        echo "<div class='table-responsive'><table id='log-table' class='table table-striped'>\n";
        echo "<thead>\n<tr>\n";
        echo "<th></th>\n";
        echo "<th class='strong'>" . $locale['UL_002'] . "</th>\n";
        echo "<th class='strong'>" . $locale['UL_003'] . "</th>\n";
        echo "<th class='strong'>" . $locale['UL_004'] . "</th>\n";
        echo "<th class='strong'>" . $locale['UL_012'] . "</th>\n";
        echo "<th class='strong'>" . $locale['UL_013'] . "</th>\n";
        echo "<th class='strong'>" . $locale['UL_014'] . "</th>\n";
        echo "</tr>\n</thead>\n";

        echo "<tbody>\n";
        echo openform( 'userlog_table', 'post', FUSION_REQUEST );
        echo form_hidden( 'table_action' );
        while ( $data = dbarray( $result ) ) {
            echo "<tr>";
            echo "<td>".form_checkbox( "log_id[]", "", "", ["value" => $data['userlog_id'], 'input_id' => 'log_id' . $data['userlog_id'], "class" => "m-0"]) . "</td>\n";
            echo "<td>" . showdate( "shortdate", $data['userlog_timestamp'] ) . "</td>\n";
            echo "<td>" . profile_link( $data['userlog_user_id'], $data['user_name'], $data['user_status'] ) . "</td>\n";
            echo "<td>" . $data['userlog_field'] . "</td>\n";
            echo "<td>" . trimlink( $data['userlog_value_old'], 100 ) . "</td>\n";
            echo "<td>" . trimlink( $data['userlog_value_new'], 100 ) . "</td>\n";
            echo "<td><a href='" . FUSION_SELF . $get_string . "&delete=" . $data['userlog_id'] . "'>" . $locale['delete'] . "</a></td>\n";
            echo "</tr>\n";
        }

        echo "</tbody>\n";
        echo "</table>\n</div>";
        echo "<div class='clearfix display-block'>\n";
        echo "<div class='display-inline-block pull-left m-r-20'>" . form_checkbox( 'check_all', $locale['UL_020'], '', ['class' => 'm-b-0', 'reverse_label' => TRUE] ) . "</div>";
        echo "<div class='display-inline-block'><a class='btn btn-danger btn-sm' onclick=\"run_admin('delete', '#table_action', '#userlog_table');\"><i class='fa fa-fw fa-trash-o m-r-10'></i>" . $locale['delete'] . "</a></div>";
        echo "</div>\n";
        echo closeform();
        add_to_jquery("
            $('#check_all').bind('click', function() {
                if ($(this).is(':checked')) {
                    $('input[name^=log_id]:checkbox').prop('checked', true);
                    $('#log-table tbody tr').addClass('active');
                } else {
                    $('input[name^=log_id]:checkbox').prop('checked', false);
                     $('#log-table tbody tr').removeClass('active');
                }
            });
        ");
    } else {
        echo "<div class='well text-center'>" . $locale['UL_015'] . "</div>";
    }

    if ($rows > $limit) {
        echo "<div class='display-inline-block pull-right'>" . makepagenav( $rowstart, $limit, $rows, 3, FUSION_SELF . $get_string . "&" ) . "</div>";
    }
    closeside();
}

if ( $rows ) {
    openside( '', 'm-t-20' );
    echo openform( 'userlog_delete', 'post', FUSION_REQUEST );
    echo form_text( 'day_delete', $locale['UL_016'], '', [
        'max_length'  => 3,
        'type'        => 'number',
        'placeholder' => $locale['UL_017']
    ] );
    echo form_button( 'submit', $locale['UL_011'], $locale['UL_011'], ['class' => 'btn-primary'] );
    echo closeform();
    closeside();
}

closetable();

require_once THEMES.'templates/footer.php';

function user_field_options() {
    $locale = fusion_get_locale();
    $options['user_name'] = $locale['UL_003'];
    $options['user_email'] = $locale['UL_007'];
    $result = dbquery( "SELECT field_name, field_title
        FROM " . DB_USER_FIELDS . "
        WHERE field_log = '1'
    " );
    if ( dbrows( $result ) ) {
        while ( $data = dbarray( $result ) ) {
            $options[$data['field_name']] = $data['field_title'];
        }
    }
    return $options;
}

function deleteLog( $logid ) {
    if ( isnum( $logid ) ) {
        dbquery( "DELETE FROM " . DB_USER_LOG . " WHERE userlog_id = :delete", [':delete' => $logid] );
        return TRUE;
    }
    return FALSE;
}
