<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: smileys.php
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
require_once FUSION_ADMIN_HEADER;
pageaccess('SM');

$locale = fusion_get_locale( '', LOCALE . LOCALESET . 'admin/smileys.php' );

add_breadcrumb( ['link' => ADMIN . 'smileys.php' . fusion_get_aidlink(), "title" => $locale['SMLY_403']] );

opentable( $locale['SMLY_403'] );
$allowed_sections = ["smiley_form", "smiley_list"];
$sections = in_array( get( 'section' ), $allowed_sections ) ? get( 'section' ) : $allowed_sections[1];
$edit = ( check_get( 'action' ) && get( 'action' ) == 'edit' && check_get( 'smiley_id' ) );

$tabs['title'][] = $locale['SMLY_400'];
$tabs['id'][] = 'smiley_list';
$tabs['icon'][] = 'fa fa-smile-o';
$tabs['title'][] = $edit ? $locale['SMLY_402'] : $locale['SMLY_401'];
$tabs['id'][] = 'smiley_form';
$tabs['icon'][] = $edit ? "fa fa-pencil m-r-10" : 'fa fa-plus-square m-r-10';

echo opentab( $tabs, $sections, 'smiley_list', TRUE, 'nav-tabs', "section", ['action', 'smiley_id', 'smiley_text', 'disable'] );
switch ( $sections ) {
    case "smiley_form":
        add_smiley_form();
        break;
    default:
        smiley_listing();
        break;
}

echo closetab();
closetable();

require_once FUSION_FOOTER;

function smiley_listing() {
    $locale = fusion_get_locale();
    if ( check_get( 'action' ) && get( 'action' ) == 'delete' && check_get( 'smiley_id' ) && get( 'smiley_id', FILTER_SANITIZE_NUMBER_INT ) ) {
        $data = dbarray( dbquery( "SELECT * FROM " . DB_SMILEYS . " WHERE smiley_id = :smileyid", [':smileyid' => get( 'smiley_id' )] ) );
        dbquery( "DELETE FROM " . DB_SMILEYS . " WHERE smiley_id = :smileyid", [':smileyid' => get( 'smiley_id' )] );
        cdreset( 'smileys_cache' );
        if ( check_get( 'disable' ) ) {
            if ( !empty( $data['smiley_image'] ) && file_exists( IMAGES . "smiley/" . $data['smiley_image'] ) ) {
                unlink( IMAGES . "smiley/" . $data['smiley_image'] );
            }
        }
        addnotice( 'success', check_get( 'disable' ) ? $locale['SMLY_412'] : $locale['SMLY_413'] );
        redirect( clean_request( '', ['section=smiley_list', 'aid'] ) );
    }

    $tab_cats = [
        'title' => [$locale['SMLY_404'], $locale['SMLY_405']],
        'id'    => ['active', 'inactive'],
        'icon'  => ['', '']
    ];
    $tab_cats_active = tab_active( $tab_cats, 0 );
    echo opentab( $tab_cats, $tab_cats_active, 'dcategory', FALSE, "nav-tabs" );
        echo opentabbody( $tab_cats['title'][0], $tab_cats['id'][0], $tab_cats_active );
            active_smiley();
        echo closetabbody();
        echo opentabbody( $tab_cats['title'][1], $tab_cats['id'][1], $tab_cats_active );
            inactive_smiley();
        echo closetabbody();
    echo closetab();
}

function smileyData() {
    $smiley = [];
    $result = dbquery( "SELECT smiley_id, smiley_code, smiley_image, smiley_text
        FROM " . DB_SMILEYS . "
        ORDER BY smiley_text ASC
    " );
    if ( dbrows( $result ) ) {
        while ( $data = dbarray( $result ) ) {
            $smiley['active'][] = $data;
            $smiley['smiley'][] = $data['smiley_image'];
        }
    }
	return $smiley;
}

function active_smiley() {
    $locale = fusion_get_locale();
    $smiley = smileyData();

    openside( '' );
    if ( !empty( $smiley['active'] ) ) {
        echo "<div class='table-responsive'><table class='table table-hover table-striped'>\n";
            echo "<thead>";
                echo "<tr>\n";
                    echo "<td class='col-xs-2'><strong>" . $locale['SMLY_430'] . "</strong></td>\n";
                    echo "<td class='col-xs-2'><strong>" . $locale['SMLY_431'] . "</strong></td>\n";
                    echo "<td class='col-xs-2'><strong>" . $locale['SMLY_432'] . "</strong></td>\n";
                    echo "<td class='col-xs-4'><strong>" . $locale['options'] . "</strong></td>\n";
                echo "</tr>\n";
            echo "</thead>";
            echo "<tbody>";
                foreach ( $smiley['active'] as $info ) {
                    $edit_link = clean_request( "section=smiley_form&action=edit&smiley_id=" . $info['smiley_id'], ['section', 'action', 'smiley_id'], FALSE );
                    $disable_link = clean_request( "section=smiley_list&action=delete&smiley_id=" . $info['smiley_id'], ['section', 'action', 'smiley_id'], FALSE );
                    $delete_link = clean_request( "section=smiley_list&action=delete&disable=1&smiley_id=" . $info['smiley_id'], ['section', 'action', 'disable', 'smiley_id'], FALSE );
                    echo "<tr>\n";
                        echo "<td class='col-xs-2'>" . $info['smiley_code'] . "</td>\n";
                        echo "<td class='col-xs-2'><img style='width:20px;height:20px;' src='" . IMAGES . "smiley/" . $info['smiley_image'] . "' alt='".$info['smiley_text']."' title='".$info['smiley_text']."' /></td>\n";
                        echo "<td class='col-xs-2'>" . $info['smiley_text'] . "</td>\n";
                        echo "<td class='col-xs-4'>";
                            echo "<a class='btn btn-default btn-sm' href='" . $edit_link . "'>" . $locale['edit'] . get_icon( 'fa fa-edit', 'm-l-10' ) . "</a> \n";
                            echo "<a class='btn btn-default btn-sm' href='" . $disable_link . "' onclick=\"return confirm('".$locale['SMLY_417']."');\">" . $locale['disable'] . get_icon( 'fa fa-close', 'm-l-10' ) . "</a> \n";
                            echo "<a class='btn btn-default btn-sm' href='" . $delete_link . "' onclick=\"return confirm('".$locale['SMLY_416']."');\">" . $locale['delete'] . get_icon( 'fa fa-trash', 'm-l-10' ) . "</a>";
                        echo "</td>\n";
                    echo "</tr>\n";
                }
            echo "</tbody>";
        echo "</table>\n</div>";
    } else {
        echo "<div class='well text-center'>" . $locale['SMLY_440'] . "</div>\n";
    }
    closeside();
}

function inactive_smiley() {
    $locale = fusion_get_locale();
    $smiley = smileyData();
    $smileys_list = [];
    $temp = IMAGES . 'smiley/';
    $smiley_files = makefilelist( $temp, '.|..|.DS_Store|index.php' );
    foreach ( $smiley_files as $smiley_check ) {
        if ( !in_array( $smiley_check, $smiley['smiley'] ) ) {
            $smileys_list[] = $smiley_check;
        }
    }
    openside( '' );
    if ( !empty( $smileys_list ) ) {
        echo "<div class='table-responsive'><table class='table table-hover table-striped'>\n";
            echo "<thead>";
                echo "<tr>\n";
                    echo "<td class='col-xs-2'><strong>" . $locale['SMLY_431'] . "</strong></td>\n";
                    echo "<td class='col-xs-2'><strong>" . $locale['SMLY_432'] . "</strong></td>\n";
                    echo "<td class='col-xs-4'><strong>" . $locale['options'] . "</strong></td>\n";
                echo "</tr>\n";
            echo "</thead>";
            echo "<tbody>";
                foreach ( $smileys_list as $list ) {
                    $add_link = clean_request( "section=smiley_form&smiley_text=" . $list, ['section', 'smiley_text'], FALSE );
                    echo "<tr>\n";
                        echo "<td class='col-xs-2'><img style='width:20px;height:20px;' src='" . IMAGES . "smiley/" . $list . "' alt='' title='' style='border:none;' /></td>\n";
                        echo "<td class='col-xs-2'>".ucwords( str_replace( ['.gif', '.png', '.jpg', '.svg'], '', $list ) ) . "</td>\n";
                        echo "<td class='col-xs-2'><a id='confirm' class='btn btn-default btn-sm' href='" . $add_link . "'>" . $locale['add'] . get_icon( 'fa fa-plus', 'm-l-10' ) . "</a></td>\n";
                    echo "</tr>\n";
                }
            echo "</tbody>";
        echo "</table>\n</div>";
    } else {
        echo "<div class='well text-center'>" . $locale['SMLY_441'] . "</div>\n";
    }
    closeside();
}

function add_smiley_form() {
    $locale = fusion_get_locale();
    fusion_confirm_exit();

    $data = [
        'smiley_id'    => 0,
        'smiley_code'  => "",
        'smiley_image' => "",
        'smiley_text'  => ""
    ];
    if ( check_post( 'smiley_save' ) ) {
        if ( check_post( 'smiley_image' ) ) {
            $data['smiley_image'] = sanitizer( 'smiley_image', '', 'smiley_image' );
        }

        if ( !empty( $_FILES['smiley_file'] ) && is_uploaded_file( $_FILES['smiley_file']['tmp_name'] ) ) {
            $upload = form_sanitizer( $_FILES['smiley_file'], '', 'smiley_file' );
            if ( $upload['error'] == 0 ) {
                $data['smiley_image'] = $upload['image_name'];
            }
        }

        $data['smiley_id'] = sanitizer( 'smiley_id', '0', 'smiley_id' );
        $data['smiley_code'] = sanitizer( 'smiley_code', '', 'smiley_code' );
        $data['smiley_text'] = sanitizer( 'smiley_text', '', 'smiley_text' );

        $error = empty( $data['smiley_image'] ) ? $locale['SMLY_418'] : "";
        $error .= dbcount( "(smiley_id)", DB_SMILEYS, "smiley_id != :smileyid AND smiley_code = :smileycode", [':smileyid' => (int)$data['smiley_id'], ':smileycode' => $data['smiley_code']] ) ? $locale['SMLY_415'] : "";

        if ( fusion_safe() ) {
            if ( $error == "" ) {
                dbquery_insert( DB_SMILEYS, $data, empty( $data['smiley_id'] ) ? 'save' : 'update' );
                cdreset( 'smileys_cache' );
                addnotice( 'success', empty( $data['smiley_id'] ) ? $locale['SMLY_410'] : $locale['SMLY_411'] );
                redirect( clean_request( '', ['section=smiley_list', 'aid'] ) );
            } else {
                addnotice( 'danger', $error );
            }
        }
    }

    if ( check_get( 'action' ) && get( 'action' ) == 'edit' && check_get( 'smiley_id' ) && get( 'smiley_id', FILTER_SANITIZE_NUMBER_INT ) ) {
        $result = dbquery( "SELECT * FROM " . DB_SMILEYS . " WHERE smiley_id = :smileyid", [':smileyid' => get( 'smiley_id' )] );
        if ( dbrows( $result ) ) {
            $data = dbarray( $result );
        }
    }

    if ( check_get( 'smiley_text' ) ) {
        $data['smiley_text'] = ucwords( str_replace( ['.gif', '.png', '.jpg', '.svg'], '', get( 'smiley_text' ) ) );
        $data['smiley_image'] = get( 'smiley_text' );
    }

    echo openform( 'smiley_form', 'post', FUSION_REQUEST, ['enctype' => TRUE] );

    echo form_hidden( 'smiley_id', '', $data['smiley_id'] );
    $image_files = makefilelist( IMAGES."smiley/", ".|..|index.php" );
    $image_opts = [];
    foreach ( $image_files as $filename ) {
        $name = explode( ".", $filename );
        $image_opts[$filename] = ucwords( $name[0] );
    }

    if ( $data['smiley_image'] ) {
        echo form_select( 'smiley_image', $locale['SMLY_421'], $data['smiley_image'], [
            'options'    => $image_opts,
            'required'   => TRUE,
            'inline'     => TRUE,
            'error_text' => $locale['SMLY_438'],
        ] );

        add_to_jquery("
            function showMeSmileys(item) {
                return '<img style=\"height:15px;width:15px;\" src=\"".IMAGES."smiley/'+item.id+'\" alt=\"'+item.text+'\"> - ' + item.text;
            }
            $('#smiley_image').select2({
                formatSelection: function(m) { return showMeSmileys(m); },
                formatResult: function(m) { return showMeSmileys(m); }
            });
        ");
    } else {
        echo form_fileinput( 'smiley_file', '', '', [
            'upload_path'     => IMAGES . 'smiley/',
            'delete_original' => TRUE,
            'template'        => 'modern',
            'type'            => 'image',
            'valid_ext'       => '.jpg,.png,.gif,.bmp,.svg',
            'required'        => TRUE
        ] );
    }
    echo form_text( 'smiley_code', $locale['SMLY_420'], $data['smiley_code'], [
        'required'   => TRUE,
        'inline'     => TRUE,
        'error_text' => $locale['SMLY_437']
    ] );
    echo form_text( 'smiley_text', $locale['SMLY_422'], $data['smiley_text'], [
        'required'   => TRUE,
        'inline'     => TRUE,
        'error_text' => $locale['SMLY_439'],
    ] );
    echo form_button( 'smiley_save', ($data['smiley_id'] ? $locale['SMLY_424'] : $locale['SMLY_423']), ($data['smiley_id'] ? $locale['SMLY_424'] : $locale['SMLY_423']), ['class' => 'btn-primary'] );
    echo closeform();
}
