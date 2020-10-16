<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: blog_admin.php
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

use PHPFusion\Infusions\Blog\Classes\BlogList;
use PHPFusion\Infusions\Blog\Classes\Functions;

require_once __DIR__.'/../../maincore.php';
pageAccess( 'BLOG' );
require_once THEMES.'templates/admin_header.php';

$locale = fusion_get_locale( '', [
    LOCALE.LOCALESET."admin/settings.php",
    INFUSIONS."blog/locale/".LOCALESET."blog_admin.php"
] );

require_once INFUSIONS."blog/classes/functions.php";
require_once INCLUDES."infusions_include.php";

if ( post( 'cancel' ) ) {
    redirect( FUSION_SELF.$aidlink );
}

$blog_settings = get_settings( "blog" );

$aidlink = fusion_get_aidlink();

$allowed_pages = [
    "blog", "blog_category", "blog_form", "submissions", "settings"
];

$section = get( 'section' );

$section = in_array( $section, $allowed_pages ) ? $section : 'blog';

$action = get( 'action' );

$blog_edit_id = (int)get( 'blog_id', FILTER_VALIDATE_INT );

$edit = ( $action == 'edit' && $blog_edit_id ) ? TRUE : FALSE;

if ( $action == 'delete' && $blog_edit_id ) {
    $del_data['blog_id'] = $blog_edit_id;
    $result = dbquery( "SELECT blog_image, blog_image_t1, blog_image_t2 FROM ".DB_BLOG." WHERE blog_id='".$del_data['blog_id']."'" );
    if ( dbrows( $result ) ) {
        $data = dbarray( $result );
        if ( !empty( $data['blog_image'] ) && file_exists( IMAGES_B.$data['blog_image'] ) ) {
            unlink( IMAGES_B.$data['blog_image'] );
        }
        if ( !empty( $data['blog_image_t1'] ) && file_exists( IMAGES_B_T.$data['blog_image_t1'] ) ) {
            unlink( IMAGES_B_T.$data['blog_image_t1'] );
        }
        if ( !empty( $data['blog_image_t2'] ) && file_exists( IMAGES_B_T.$data['blog_image_t2'] ) ) {
            unlink( IMAGES_B_T.$data['blog_image_t2'] );
        }
        $result = dbquery( "DELETE FROM ".DB_BLOG." WHERE blog_id='".$del_data['blog_id']."'" );
        $result = dbquery( "DELETE FROM ".DB_COMMENTS."  WHERE comment_item_id='".$del_data['blog_id']."' and comment_type='B'" );
        $result = dbquery( "DELETE FROM ".DB_RATINGS." WHERE rating_item_id='".$del_data['blog_id']."' and rating_type='B'" );
        add_notice( 'success', $locale['blog_0412'] );
        redirect( FUSION_SELF.$aidlink );
    }

    redirect( FUSION_SELF.$aidlink );
}

$tab = getBlogTab( $edit );
$tab_active = $section;
$admin_file = get_admin_file( $section );
opentable( $locale['blog_0405'] );
echo opentab( $tab, $tab_active, "blog", TRUE, "", "section", [ 'rowstart', 'filter_cid' ] );
if ( $admin_file ) {
    require_once $admin_file;
} else {
    new PHPFusion\Tables( new BlogList() );
}
echo closetab();
closetable();
require_once THEMES.'templates/footer.php';


/**
 * Get blog edit id.
 *
 * @param $is_edit
 *
 * @return mixed
 */
function getBlogTab( $is_edit ) {
    $locale = fusion_get_locale();
    $tab['title'][] = $locale['blog_0400'];
    $tab['id'][] = 'blog';
    $tab['icon'][] = 'fa fa-graduation-cap';
    $tab['title'][] = ( $is_edit ? $locale['blog_0402'] : $locale['blog_0401'] );
    $tab['id'][] = 'blog_form';
    $tab['icon'][] = 'fa fa-plus';
    $tab['title'][] = $locale['blog_0502'];
    $tab['id'][] = 'blog_category';
    $tab['icon'][] = 'fa fa-folder';
    $tab['title'][] = $locale['blog_0600']."&nbsp;<span class='badge'>".dbcount( "(submit_id)", DB_SUBMISSIONS, "submit_type='b'" )."</span>";
    $tab['id'][] = 'submissions';
    $tab['icon'][] = 'fa fa-fw fa-inbox';
    $tab['title'][] = $locale['blog_0406'];
    $tab['id'][] = 'settings';
    $tab['icon'][] = 'fa fa-cogs';

    return $tab;
}

/**
 * @param $section
 *
 * @return string
 */
function get_admin_file( $section ) {
    $locale = fusion_get_locale();
    $admin_file = '';
    add_breadcrumb( [ 'link' => INFUSIONS.'blog/blog_admin.php'.fusion_get_aidlink(), 'title' => $locale['blog_0405'] ] );
    add_to_title( $locale['blog_0405'] );
    switch ( $section ) {
        case "blog_form":
            add_breadcrumb( [ 'link' => FUSION_REQUEST, 'title' => $locale['blog_0401'] ] );
            $admin_file = __DIR__.'/admin/blog.php';
            break;
        case "blog_category":
            add_breadcrumb( [ 'link' => FUSION_REQUEST, 'title' => $locale['blog_0502'] ] );
            $admin_file = __DIR__.'/admin/blog_cat.php';
            break;
        case "settings":
            add_breadcrumb( [ 'link' => FUSION_REQUEST, 'title' => $locale['blog_0406'] ] );
            $admin_file = __DIR__.'/admin/blog_settings.php';
            break;
        case "submissions":
            add_breadcrumb( [ "link" => FUSION_REQUEST, "title" => $locale['blog_0600'] ] );
            $admin_file = __DIR__.'/admin/blog_submissions.php';
            break;
        default:
    }
    return $admin_file;
}

function get_image_path( $blog_image, $blog_image_t1, $blog_image_t2, $hiRes = FALSE ) {
    return Functions::get_blog_image_path( $blog_image, $blog_image_t1, $blog_image_t2, $hiRes );
}
