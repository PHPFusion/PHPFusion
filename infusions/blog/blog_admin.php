<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
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
require_once "../../maincore.php";
pageAccess('BLOG');
require_once THEMES."templates/admin_header.php";

$locale = fusion_get_locale('', [
                                LOCALE.LOCALESET."admin/settings.php",
                                INFUSIONS."blog/locale/".LOCALESET."blog_admin.php"
                            ]);

require_once INFUSIONS."blog/classes/Functions.php";
require_once INCLUDES."infusions_include.php";
$blog_settings = get_settings("blog");
$aidlink = fusion_get_aidlink();

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => INFUSIONS.'blog/blog_admin.php'.fusion_get_aidlink(), 'title' => $locale['blog_0405']]);
add_to_title($locale['blog_0405']);
if (!empty($_GET['section'])){
	switch ($_GET['section']) {
	    case "blog_form":
	        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $locale['blog_0401']]);
	        break;
	    case "blog_category":
	        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $locale['blog_0502']]);
	        break;
	    case "settings":
	        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => $locale['blog_0406']]);
	        break;
	    case "submissions":
	        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(["link" => FUSION_REQUEST, "title" => $locale['blog_0600']]);
	        break;
	    default:
	}
}

if (isset($_POST['cancel'])) {
    redirect(FUSION_SELF.$aidlink);
}
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['blog_id']) && isnum($_GET['blog_id'])) {
    $del_data['blog_id'] = $_GET['blog_id'];
    $result = dbquery("SELECT blog_image, blog_image_t1, blog_image_t2 FROM ".DB_BLOG." WHERE blog_id='".$del_data['blog_id']."'");
    if (dbrows($result)) {
        $data = dbarray($result);
        if (!empty($data['blog_image']) && file_exists(IMAGES_B.$data['blog_image'])) {
            unlink(IMAGES_B.$data['blog_image']);
        }
        if (!empty($data['blog_image_t1']) && file_exists(IMAGES_B_T.$data['blog_image_t1'])) {
            unlink(IMAGES_B_T.$data['blog_image_t1']);
        }
        if (!empty($data['blog_image_t2']) && file_exists(IMAGES_B_T.$data['blog_image_t2'])) {
            unlink(IMAGES_B_T.$data['blog_image_t2']);
        }
        $result = dbquery("DELETE FROM ".DB_BLOG." WHERE blog_id='".$del_data['blog_id']."'");
        $result = dbquery("DELETE FROM ".DB_COMMENTS."  WHERE comment_item_id='".$del_data['blog_id']."' and comment_type='B'");
        $result = dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id='".$del_data['blog_id']."' and rating_type='B'");
        addNotice('success', $locale['blog_0412']);
        redirect(FUSION_SELF.$aidlink);
    } else {
        redirect(FUSION_SELF.$aidlink);
    }
}
$allowed_pages = array(
    "blog", "blog_category", "blog_form", "submissions", "settings"
);
$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_pages) ? $_GET['section'] : "blog";
$edit = (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['blog_id']) && isnum($_GET['blog_id'])) ? TRUE : FALSE;
$master_title['title'][] = $locale['blog_0400'];
$master_title['id'][] = 'blog';
$master_title['icon'][] = 'fa fa-graduation-cap';
$master_title['title'][] = $edit ? $locale['blog_0402'] : $locale['blog_0401'];
$master_title['id'][] = 'blog_form';
$master_title['icon'][] = 'fa fa-plus';
$master_title['title'][] = $locale['blog_0502'];
$master_title['id'][] = 'blog_category';
$master_title['icon'][] = 'fa fa-folder';
$master_title['title'][] = $locale['blog_0600']."&nbsp;<span class='badge'>".dbcount("(submit_id)", DB_SUBMISSIONS, "submit_type='b'")."</span>";
$master_title['id'][] = 'submissions';
$master_title['icon'][] = 'fa fa-fw fa-inbox';
$master_title['title'][] = $locale['blog_0406'];
$master_title['id'][] = 'settings';
$master_title['icon'][] = 'fa fa-cogs';
$tab_active = $_GET['section'];
opentable($locale['blog_0405']);
echo opentab($master_title, $tab_active, 'blog', TRUE);
switch ($_GET['section']) {
    case "blog_form":
        include "admin/blog.php";
        break;
    case "blog_category":
        include "admin/blog_cat.php";
        break;
    case "settings":
        include "admin/blog_settings.php";
        break;
    case "submissions":
        include "admin/blog_submissions.php";
        break;
    default:
        blog_listing();
}
echo closetab();
closetable();
require_once THEMES."templates/footer.php";
/**
 * Blog Listing HTML
 */
function blog_listing() {
    $locale = fusion_get_locale();
    $aidlink = fusion_get_aidlink();
    // Remodel display results into straight view instead category container sorting.
    // consistently monitor sql results rendertime. -- Do not Surpass 0.15
    // all blog are uncategorized by default unless specified.
    $limit = 15;
    $total_rows = dbcount("(blog_id)", DB_BLOG, (multilang_table("BL") ? "blog_language='".LANGUAGE."'" : ""));
    $rowstart = isset($_GET['rowstart']) && ($_GET['rowstart'] <= $total_rows) ? $_GET['rowstart'] : 0;

    // add a filter browser
    $catOpts = array(
        "all" => $locale['blog_0460'],
        "0" => $locale['blog_0424']
    );
    $categories = dbquery("select blog_cat_id, blog_cat_name
				from ".DB_BLOG_CATS." ".(multilang_table("BL") ? "where blog_cat_language='".LANGUAGE."'" : "")."");
    if (dbrows($categories) > 0) {
        while ($cat_data = dbarray($categories)) {
            $catOpts[$cat_data['blog_cat_id']] = $cat_data['blog_cat_name'];
        }
    }
    // prevent xss
    $catFilter = "";
    if (isset($_GET['filter_cid']) && isnum($_GET['filter_cid']) && isset($catOpts[$_GET['filter_cid']])) {
        if ($_GET['filter_cid'] > 0) {
            $catFilter = "and ".in_group("blog_cat", intval($_GET['filter_cid']));
        }
    }

    $langFilter = multilang_table("BL") ? "blog_language='".LANGUAGE."'" : "";

    if ($catFilter && $langFilter) {
        $filter = $catFilter." AND ".$langFilter;
    } else {
        $filter = $catFilter.$langFilter;
    }

    $result = dbquery("
	SELECT blog_id, blog_cat, blog_subject, blog_image, blog_image_t1, blog_image_t2, blog_blog, blog_draft
	FROM ".DB_BLOG."
	".($filter ? "WHERE ".$filter : "")."
	ORDER BY blog_draft DESC, blog_sticky DESC, blog_datestamp DESC LIMIT $rowstart, $limit
	");

    $rows = dbrows($result);
    echo "<div class='clearfix m-t-10'>\n";
    echo "<span class='pull-right m-t-10'>".sprintf($locale['blog_0408'], $rows, $total_rows)."</span>\n";

    if (!empty($catOpts) > 0 && $total_rows > 0) {
        echo "<div class='pull-left m-t-5 m-r-10'>".$locale['blog_0458']."</div>\n";
        echo "<div class='dropdown pull-left m-r-10' style='position:relative'>\n";
        echo "<a class='dropdown-toggle btn btn-default btn-sm' style='width: 200px;' data-toggle='dropdown'>\n<strong>\n";
        if (isset($_GET['filter_cid']) && isset($catOpts[$_GET['filter_cid']])) {
            echo $catOpts[$_GET['filter_cid']];
        } else {
            echo $locale['blog_0459'];
        }
        echo " <span class='caret'></span></strong>\n</a>\n";
        echo "<ul class='dropdown-menu' style='max-height:180px; width:200px; overflow-y: scroll'>\n";
        foreach ($catOpts as $catID => $catName) {
            $active = isset($_GET['filter_cid']) && $_GET['filter_cid'] == $catID ? TRUE : FALSE;
            echo "<li".($active ? " class='active'" : "").">\n<a class='text-smaller' href='".clean_request("filter_cid=".$catID,
                                                                                                            array("section", "rowstart", "aid"),
                                                                                                            TRUE)."'>\n";
            echo $catName;
            echo "</a>\n</li>\n";
        }
        echo "</ul>\n";
        echo "</div>\n";
    }
    if ($total_rows > $rows) {
        echo makepagenav($rowstart, $limit, $total_rows, $limit, clean_request("", array("aid", "section"), TRUE)."&amp;");
    }
    echo "</div>\n";

    echo "<ul class='list-group m-t-10'>\n";
    if ($rows > 0) {
        while ($data2 = dbarray($result)) {
            echo "<li class='list-group-item'>\n";
            echo "<div class='pull-left m-r-10'>\n";
            $image_thumb = get_blog_image_path($data2['blog_image'], $data2['blog_image_t1'], $data2['blog_image_t2']);
            if (!$image_thumb) {
                $image_thumb = IMAGES."imagenotfound70.jpg";
            }
            echo thumbnail($image_thumb, '70px');
            echo "</div>\n";
            echo "<div class='overflow-hide'>\n";
            echo "<div><span class='strong text-dark'>".$data2['blog_subject']."</span><br/>\n";
            if (!empty($data2['blog_cat'])) {
                $blog_cat = str_replace(".", ",", $data2['blog_cat']);
                $result2 = dbquery("SELECT blog_cat_id, blog_cat_name
                            from ".DB_BLOG_CATS." WHERE blog_cat_id in ($blog_cat)
                            ");
                $rows2 = dbrows($result2);
                if ($rows2 > 0) {
                    echo "<div class='m-b-10'><strong>".$locale['blog_0407'].": </strong>\n";
                    $i = 1;
                    while ($cdata = dbarray($result2)) {
                        echo "<a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;cat_id=".$cdata['blog_cat_id']."&amp;section=blog_category'>";
                        echo $cdata['blog_cat_name'];
                        echo "</a>";
                        echo $i == $rows2 ? "" : ", ";
                        $i++;
                    }
                    echo "</div>\n";
                }
                echo "</div>\n";
            }
            $blogText = strip_tags(parse_textarea($data2['blog_blog']));
            echo fusion_first_words($blogText, '50');
            echo "<div class='block m-t-10'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;section=blog_form&amp;blog_id=".$data2['blog_id']."'>".$locale['blog_0420']."</a> -\n";
            echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;section=blog_form&amp;blog_id=".$data2['blog_id']."' onclick=\"return confirm('".$locale['blog_0451']."');\">".$locale['blog_0421']."</a> -\n";
            echo "<a target='_blank' href='".INFUSIONS."blog/blog.php?blog_id=".$data2['blog_id']."'>".$locale['view']."</a>\n";
            echo "</div>\n</div>\n";
            echo "</li>\n";
        }
    } else {
        echo "<div class='panel-body text-center'>\n";
        echo $locale['blog_0456'];
        echo "</div>\n";
    }
    echo "</ul>\n";

    if ($total_rows > $rows) {
        echo makepagenav($rowstart, $limit, $total_rows, $limit, clean_request("", array("aid", "section"), TRUE)."&amp;");
    }

}

/**
 * Returns nearest data unit
 * @param $total_bit
 * @return int
 */
function calculate_byte($total_bit) {
    $calc_opts = array(1 => 'Bytes (bytes)', 1000 => 'KB (Kilobytes)', 1000000 => 'MB (Megabytes)');
    foreach ($calc_opts as $byte => $val) {
        if ($total_bit / $byte <= 999) {
            return (int)$byte;
        }
    }

    return 1000000;
}

/**
 * Function to progressively return closest full image_path
 * @param $blog_image
 * @param $blog_image_t1
 * @param $blog_image_t2
 * @return string
 */
function get_blog_image_path($blog_image, $blog_image_t1, $blog_image_t2, $hiRes = FALSE) {
    return PHPFusion\Blog\Functions::get_blog_image_path($blog_image, $blog_image_t1, $blog_image_t2, $hiRes);
}
