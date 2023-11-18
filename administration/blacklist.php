<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: blacklist.php
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
require_once __DIR__ . '/../maincore.php';
require_once THEMES . 'templates/admin_header.php';
pageaccess('B');

$locale = fusion_get_locale( '', LOCALE . LOCALESET . 'admin/blacklist.php' );

add_breadcrumb( ['link' => ADMIN . 'blacklist.php' . fusion_get_aidlink(), 'title' => $locale['BLS_000']] );

$tabs['title'][] = $locale['BLS_020'];
$tabs['id'][] = 'blacklist';
$tabs['icon'][] = '';

$edit = ( check_get( 'action' ) && get( 'action' ) == 'edit' ) && check_get( 'blacklist_id' );
$title = !empty( $edit ) ? $locale['BLS_021'] : $locale['BLS_022'];

if ( get( 'section' ) == 'blacklist_form' ) {
    add_breadcrumb( ['link' => FUSION_REQUEST, 'title' => $title] );
    $tabs['title'][] = $edit ? $locale['BLS_021'] : $locale['BLS_022'];
    $tabs['id'][] = 'blacklist_form';
    $tabs['icon'][] = '';
}

$allowed_sections = ['blacklist', 'blacklist_form'];
$sections = in_array( get( 'section' ), $allowed_sections ) ? get( 'section' ) : 'blacklist';

opentable( $locale['BLS_000'] );
echo opentab( $tabs, $sections, 'blacklist', TRUE, 'nav-tabs' );
switch ( $sections ) {
    case 'blacklist_form':
        blacklist_form();
        break;
    default:
        blacklist_listing();
        break;
}
echo closetab();
closetable();

function blacklist_form() {
    $locale = fusion_get_locale();

    fusion_confirm_exit();

    $data = [
        'blacklist_id'        => 0,
        'blacklist_user_id'   => fusion_get_userdata( 'user_id' ),
        'blacklist_ip'        => '',
        'blacklist_ip_type'   => '4',
        'blacklist_email'     => '',
        'blacklist_reason'    => '',
        'blacklist_datestamp' => ''
    ];

    if ( check_post( 'blacklist_admins' ) ) {

        if ( !empty( post( 'blacklist_ip' ) ) ) {
            $blacklist_ip_type = 6;

            if ( strpos( post( 'blacklist_ip' ), '.' ) ) {
                $blacklist_ip_type = strpos( post('blacklist_ip' ), ':' ) === FALSE ? 4 : 5;
            }
        }
        $data = [
            'blacklist_id'        => sanitizer( 'blacklist_id', 0, 'blacklist_id' ),
            'blacklist_user_id'   => sanitizer( 'blacklist_user_id', 0, 'blacklist_user_id' ),
            'blacklist_ip'        => '',
            'blacklist_ip_type'   => !empty( $blacklist_ip_type ) ? $blacklist_ip_type : 0,
            'blacklist_email'     => '',
            'blacklist_reason'    => sanitizer( 'blacklist_reason', '', 'blacklist_reason' ),
            'blacklist_datestamp' => empty( post( 'blacklist_datestamp' ) ) ? time() : post( 'blacklist_datestamp' )
        ];

        if ( !empty( post( 'blacklist_email' ) ) ) {
            $data['blacklist_email'] = sanitizer( 'blacklist_email', '', 'blacklist_email' );
        }

        if ( !empty( post( 'blacklist_ip' ) ) ) {
            $data['blacklist_ip'] = sanitizer( 'blacklist_ip', '', 'blacklist_ip' );
        }

        if ( fusion_safe() ) {
            if ( empty( $data['blacklist_ip'] ) && empty( $data['blacklist_email'] ) ) {
                fusion_stop();
                addnotice( 'danger', $locale['BLS_010'] );
            } else {
                dbquery_insert( DB_BLACKLIST, $data, empty( $data['blacklist_id'] ) ? 'save' : 'update' );
                addnotice( 'success', empty( $data['blacklist_id'] ) ? $locale['BLS_011'] : $locale['BLS_012'] );
                redirect( clean_request( '', ['section', 'action', 'blacklist_id'], FALSE ) );
            }
        }
    }

    if ( check_get( 'action' ) && get( 'action' ) == 'edit' && check_get( 'blacklist_id' ) && get( 'blacklist_id', FILTER_SANITIZE_NUMBER_INT ) ) {
        $result = dbquery("SELECT blacklist_id, blacklist_user_id, blacklist_ip, blacklist_ip_type, blacklist_email, blacklist_reason, blacklist_datestamp
            FROM ".DB_BLACKLIST."
            WHERE blacklist_id = :blacklistid", [':blacklistid' => get('blacklist_id')]
        );

        if ( dbrows( $result ) > 0 ) {
            $data = dbarray( $result );
        }
    }

    openside( '' );
    echo "<div class='well'>" . $locale['BLS_MS'] . "</div>\n";
    echo openform( 'blacklist_form', 'post', FUSION_REQUEST );
    echo form_hidden( 'blacklist_id', '', $data['blacklist_id'] );
    echo form_hidden( 'blacklist_datestamp', '', $data['blacklist_datestamp'] );
    echo form_hidden( 'blacklist_user_id', '', $data['blacklist_user_id'] );

    echo form_text( 'blacklist_ip', $locale['BLS_034'], $data['blacklist_ip'], ['required' => TRUE, 'inline' => TRUE] );
    echo form_para( $locale['or'], 'or', 'm-t-20');
    echo form_text( 'blacklist_email', $locale['BLS_035'], $data['blacklist_email'], ['required' => TRUE, 'inline' => TRUE, 'type' => 'text', 'error_text' => $locale['BLS_016']] );

    echo form_textarea( 'blacklist_reason', $locale['BLS_036'], $data['blacklist_reason'], ['inline' => TRUE, 'autosize' => TRUE] );

    echo form_button( 'blacklist_admins', empty( get( 'blacklist_id' ) ) ? $locale['BLS_037'] : $locale['BLS_038'], empty( get( 'blacklist_id' ) ) ? $locale['BLS_037'] : $locale['BLS_038'], ['class' => 'btn-primary'] );
    echo closeform();

    closeside();
}

function blacklist_listing() {
    $locale = fusion_get_locale();

    if ( check_get( 'action' ) && get( 'action' ) == 'delete' && dbcount( "(blacklist_id)", DB_BLACKLIST, "blacklist_id='" . get( 'blacklist_id', FILTER_SANITIZE_NUMBER_INT ) . "'") && fusion_safe() ) {
        dbquery( "DELETE FROM " . DB_BLACKLIST . " WHERE blacklist_id='" . get( 'blacklist_id' ) . "'" );
        addnotice( 'success', $locale['BLS_013'] );
        redirect( clean_request( '', ['section', 'action', 'blacklist_id'], FALSE ) );
    }

    // Table Actions
    if ( check_post( 'table_action' ) ) {
        $input = check_post( 'blacklist_id' ) ? explode( ",", sanitizer( ['blacklist_id'], '', 'blacklist_id' ) ) : '';

        if ( !empty( $input ) ) {
            foreach ( $input as $blacklist_id ) {
                if ( dbcount( "(blacklist_id)", DB_BLACKLIST, "blacklist_id = :blacklistid", [':blacklistid' => (int)$blacklist_id ] ) && fusion_safe() ) {
                    if ( post( 'table_action' ) == 'delete' ) {
                        dbquery( "DELETE FROM " . DB_BLACKLIST . " WHERE blacklist_id='" . $blacklist_id . "'" );
                        addnotice( 'success', $locale['BLS_013'] );
                    }
                }
            }
            redirect( clean_request( '', ['section', 'action', 'blacklist_id'], FALSE ) );
        }
    }

    $aidlink = fusion_get_aidlink();
    $total_rows = dbcount( "(blacklist_id)", DB_BLACKLIST );
    $rowstart = check_get( 'rowstart' ) && get( 'rowstart', FILTER_SANITIZE_NUMBER_INT ) && ( get('rowstart' ) <= $total_rows) ? get( 'rowstart' ) : 0;

    $result = dbquery( "SELECT b.blacklist_id, b.blacklist_ip, b.blacklist_email, b.blacklist_reason, b.blacklist_datestamp, u.user_id, u.user_name, u.user_status
        FROM ".DB_BLACKLIST." AS b
        LEFT JOIN ".DB_USERS." AS u ON u.user_id=b.blacklist_user_id
        ORDER BY blacklist_datestamp DESC
        LIMIT ".$rowstart.", 20
    " );

    $rows = dbrows( $result );

    openside('');
    echo openform( 'blacklist_table', 'post', FUSION_REQUEST );
    echo form_hidden( 'table_action' );
    echo "<div class='m-t-15'>\n";
    echo "<div class='clearfix m-b-20'>\n";
    echo "<div class='pull-right'>";
    echo "<a class='btn btn-success btn-sm m-r-10' href=" . clean_request( 'section=blacklist_form', ['section', 'rowstart'], FALSE ) . "><i class='fa fa-fw fa-plus'></i>" . $locale['BLS_022'] . "</a>";
    if (!empty( $total_rows ) ) {
        echo "<a class='btn btn-danger btn-sm' onclick=\"run_admin('delete', '#table_action','#blacklist_table');\"><i class='fa fa-fw fa-trash-o'></i>" . $locale['delete'] . "</a>";
    }
    echo "</div>";
    //echo "<div class='pull-left'><span class='pull-right m-t-10'>".sprintf($locale['BLS_023'], $rows, $total_rows)."</span></div>\n";
    echo "</div>\n";
    echo ( $total_rows > $rows ) ? makepagenav( $rowstart, 20, $total_rows, 3, clean_request( '', ['section'], FALSE ).'&' ) : '';
    echo "</div>\n";

    if ( $rows > 0 ) {
        echo "<div class='table-responsive'><table id='blist-table' class='table table-hover table-striped'>\n";
        echo "<thead><tr>\n";
        echo "<th>&nbsp;</th>\n";
        echo "<th>" . $locale['BLS_030'] . "</th>\n";
        echo "<th>" . $locale['BLS_031'] . "</th>\n";
        echo "<th>" . $locale['BLS_032'] . "</th>\n";
        echo "<th>" . $locale['BLS_033'] . "</th>\n";
        echo "</tr>\n</thead>";
        echo "<tbody>\n";

        while ( $data = dbarray( $result ) ) {
            echo "<tr id='blist-" . $data['blacklist_id'] . "' data-id=" . $data['blacklist_id'] . ">\n";
            echo "<td>";
            echo form_checkbox( 'blacklist_id[]', '', '', ['value' => $data['blacklist_id'], 'input_id' => 'blist-id-' . $data['blacklist_id']] );
            echo "</td>";
            echo "<td>" . ( !empty( $data['blacklist_ip'] ) ? $data['blacklist_ip'] : $data['blacklist_email'] );
            if ( $data['blacklist_reason'] ) {
                echo "<br /><span class='small2'>" . $data['blacklist_reason'] . "</span>";
            }
            echo "</td>\n<td>" . ( !empty( $data['user_name'] ) ? profile_link( $data['user_id'], $data['user_name'], $data['user_status'] ) : $locale['na'] )."</td>\n";
            echo "<td>" . ( !empty( $data['blacklist_datestamp'] ) ? showdate( "shortdate", $data['blacklist_datestamp'] ) : $locale['na'] ) . "</td>\n";
            echo "<td>
                <a class='btn btn-default btn-sm' href='" . FUSION_SELF . $aidlink . "&section=blacklist_form&action=edit&blacklist_id=" . $data['blacklist_id'] . "'><i class='fa fa-edit fa-fw m-r-10'></i>" . $locale['edit'] . "</a>
                <a class='btn btn-danger btn-sm' href='" . FUSION_SELF . $aidlink . "&section=blacklist&action=delete&blacklist_id=" . $data['blacklist_id'] . "' onclick=\"return confirm('" . $locale['BLS_014'] . "');\">" . $locale['delete'] . "<i class='fa fa-trash m-l-10'></i></a>
                </td>\n";
            echo "</tr>\n";
            add_to_jquery('$("#blist-id-' . $data['blacklist_id'] . '").click(function() {
                if ($(this).prop("checked")) {
                    $("#blist-' . $data['blacklist_id'] . '").addClass("active");
                } else {
                    $("#blist-' . $data['blacklist_id'] . '").removeClass("active");
                }
                });
            ');
        }

        echo "</tbody>\n";
        echo "</table>\n</div>\n";

        echo form_checkbox( 'check_all', $locale['BLS_039'], '', ['class' => 'm-b-0', 'reverse_label' => TRUE] );
        echo closeform();

        add_to_jquery("
            $('#check_all').bind('click', function() {
                if ($(this).is(':checked')) {
                    $('input[name^=blacklist_id]:checkbox').prop('checked', true);
                    $('#blist-table tbody tr').addClass('active');
                } else {
                    $('input[name^=blacklist_id]:checkbox').prop('checked', false);
                    $('#blist-table tbody tr').removeClass('active');
                }
            });
        ");
    } else {
        echo "<div class='text-center'>"  .$locale['BLS_015'] . "</div>\n";
    }
    closeside();
}

require_once THEMES.'templates/footer.php';
