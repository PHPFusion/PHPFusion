<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: blog.php
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
require_once __DIR__.'/../../maincore.php';

if (!defined('BLOG_EXIST')) {
    redirect(BASEDIR."error.php?code=404");
}

$settings = fusion_get_settings();
$locale = fusion_get_locale('', BLOG_LOCALE);

require_once THEMES.'templates/header.php';
require_once INCLUDES."infusions_include.php";
require_once INFUSIONS."blog/classes/Functions.php";
require_once INFUSIONS."blog/classes/OpenGraphBlogs.php";
require_once INFUSIONS."blog/templates/blog.php";
require_once INCLUDES."infusions_include.php";

if ($settings['tinymce_enabled'] == 1) {
    $tinymce_list = [];
    $image_list = makefilelist(IMAGES, ".|..|");
    $image_filter = ['png', 'PNG', 'bmp', 'BMP', 'jpg', 'JPG', 'jpeg', 'gif', 'GIF', 'tiff', 'TIFF'];
    foreach ($image_list as $image_name) {
        $image_1 = explode('.', $image_name);
        $last_str = count($image_1) - 1;
        if (in_array($image_1[$last_str], $image_filter)) {
            $tinymce_list[] = ['title' => $image_name, 'value' => IMAGES.$image_name];
        }
    }
    $tinymce_list = json_encode($tinymce_list);
}

$blog_settings = get_settings("blog");

$blog_settings['blog_pagination'] = !empty($blog_settings['blog_pagination']) ? $blog_settings['blog_pagination'] : 12;

if (file_exists(INFUSIONS.'rss_feeds_panel/feeds/rss_blog.php')) {
    add_to_head('<link rel="alternate" type="application/rss+xml" title="'.fusion_get_locale('blog_1000').' - RSS Feed" href="'.fusion_get_settings('siteurl').'infusions/rss_feeds_panel/feeds/rss_blog.php"/>');
}

set_title($locale['blog_1000']);

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => INFUSIONS.'blog/blog.php', 'title' => $locale['blog_1000']]);
$_GET['cat_id'] = isset($_GET['cat_id']) && isnum($_GET['cat_id']) ? $_GET['cat_id'] : NULL;
$result = NULL;

$info = [
    'blog_title'            => $locale['blog_1000'],
    'blog_updated'          => '',
    'blog_image'            => '',
    'blog_language'         => LANGUAGE,
    'blog_categories'       => get_blogCatsData(),
    'blog_categories_index' => get_blogCatsIndex(),
    'blog_last_updated'     => 0,
    'blog_max_rows'         => 0,
    'blog_rows'             => 0,
    'blog_nav'              => ''
];

$info['allowed_filters'] = [
    'recent'  => $locale['blog_2001']
];

if (fusion_get_settings('comments_enabled') == 1) {
    $info['allowed_filters']['comment'] = $locale['blog_2002'];
}

if (fusion_get_settings('ratings_enabled') == 1) {
    $info['allowed_filters']['rating'] = $locale['blog_2003'];
}

$info['blog_categories'][0][0] = [
    'blog_cat_id'       => 0,
    'blog_cat_parent'   => 0,
    'blog_cat_name'     => $locale['global_080'],
    'blog_cat_image'    => '',
    'blog_cat_language' => LANGUAGE,
    'blog_cat_link'     => "<a href='".INFUSIONS."blog/blog.php?cat_id=0&amp;filter=false'>".$locale['global_080']."</a>"
];

// controller: make filter types
$filter = array_keys($info['allowed_filters']);
$_GET['type'] = isset($_GET['type']) && in_array($_GET['type'], array_keys($info['allowed_filters'])) ? stripinput($_GET['type']) : '';

if (!empty($info['allowed_filters'])) {
    foreach ($info['allowed_filters'] as $type => $filter_name) {
        /**
         * Dynamic array filtration
         */
        $preserved_keys = [];
        if (!empty($_GET['cat_id'])) {
            $preserved_keys[] = "cat_id";
        }
        if (!empty($_GET['archive'])) {
            $preserved_keys[] = "archive";
        }
        if (!empty($_GET['month'])) {
            $preserved_keys[] = "month";
        }
        if (!empty($_GET['author'])) {
            $preserved_keys[] = "author";
        }

        $filter_link = clean_request("type=".$type, $preserved_keys, TRUE);
        $active = isset($_GET['type']) && $_GET['type'] === $type ? 1 : 0;
        $info['blog_filter'][$type] = ['title' => $filter_name, 'link' => $filter_link, 'active' => $active];

        unset($filter_link);
    }
}

//  controller: make $filter_condition string
$filter_count = '';
$filter_condition = '';
$filter_join = '';
switch ($_GET['type']) {
    case 'recent':
        $filter_condition = 'b.blog_datestamp DESC';
        break;
    case 'comment':
        $filter_condition = 'count_comment DESC';
        break;
    case 'rating':
        $filter_condition = 'sum_rating DESC';
        break;
    default:
        $filter_condition = 'b.blog_datestamp DESC';
}

if (isset($_GET['readmore'])) {

    if (validate_blog($_GET['readmore'])) {

        $pattern = "SELECT %s(br.rating_vote) FROM ".DB_RATINGS." AS br WHERE br.rating_item_id = b.blog_id AND br.rating_type = 'B'";
        $sql_count = sprintf($pattern, 'COUNT');
        $sql_sum = sprintf($pattern, 'SUM');

        $sql = "SELECT b.*, bu.*,
            ($sql_sum) AS sum_rating,
            ($sql_count) AS count_votes,
            (SELECT COUNT(bc.comment_id) FROM ".DB_COMMENTS." AS bc WHERE bc.comment_item_id = b.blog_id AND bc.comment_type = 'B' AND bc.comment_hidden = '0') AS count_comment,
            b.blog_datestamp as last_updated
            FROM ".DB_BLOG." AS b
            LEFT JOIN ".DB_USERS." AS bu ON b.blog_name=bu.user_id
            ".(multilang_table("BL") ? "WHERE ".in_group('blog_language', LANGUAGE)." AND " : "WHERE ").groupaccess('blog_visibility')." AND blog_id=:blog_id AND blog_draft=:draft
            GROUP BY blog_id
        ";

        $param = [':blog_id' => intval($_GET['readmore']), ':draft' => 0];
        $result = dbquery($sql, $param);
        $info['blog_rows'] = dbrows($result);
        if ($info['blog_rows'] > 0) {
            include INCLUDES."comments_include.php";
            include INCLUDES."ratings_include.php";
            $item = dbarray($result);
            unset($item['user_password']);
            unset($item['user_algo']);
            unset($item['user_salt']);
            unset($item['user_admin_password']);
            unset($item['user_admin_algo']);
            unset($item['user_admin_salt']);

            $item += [
                'blog_subject'       => "<a class='blog_subject text-dark' href='".INFUSIONS."blog/blog.php?readmore=".$item['blog_id']."'>".$item['blog_subject']."</a>",
                'blog_blog'          => preg_replace("/<!?--\s*pagebreak\s*-->/i", "", $item['blog_blog']),
                'print_link'         => BASEDIR."print.php?type=B&amp;item_id=".$item['blog_id'],
                'blog_post_author'   => display_avatar($item, '25px', '', TRUE, 'img-rounded m-r-5').profile_link($item['user_id'], $item['user_name'], $item['user_status']),
                'blog_category_link' => "",
                'blog_post_time'     => $locale['global_049']." ".timer($item['blog_datestamp']),
                'blog_nav'           => ''
            ];

            $item['blog_blog'] = parse_textarea($item['blog_blog'], FALSE, FALSE, TRUE, FALSE, $item['blog_breaks'] == "y" ? TRUE : FALSE);
            $item['blog_extended'] = parse_textarea($item['blog_extended'], FALSE, FALSE, TRUE, FALSE, $item['blog_breaks'] == "y" ? TRUE : FALSE);

            $item['blog_image_link'] = '';
            $item['blog_thumb_1_link'] = '';
            $hiRes_image_path = get_blog_image_path($item['blog_image'], $item['blog_image_t1'], $item['blog_image_t2'], TRUE);
            $lowRes_image_path = get_blog_image_path($item['blog_image'], $item['blog_image_t1'], $item['blog_image_t2'], FALSE);
            if ($hiRes_image_path || $lowRes_image_path) {
                $item['blog_image'] = "<img class='img-responsive' src='".$hiRes_image_path."' alt='".$item['blog_subject']."' title='".$item['blog_subject']."'>";
                $item['blog_image_link'] = $hiRes_image_path;
                $item['blog_thumb_1_link'] = $lowRes_image_path;
                $item['blog_thumb_1'] = thumbnail($lowRes_image_path, '80px', $hiRes_image_path, TRUE);
                $item['blog_thumb_2'] = thumbnail($hiRes_image_path, '200px', $hiRes_image_path, TRUE);
            }

            // changed to multi.
            if (!empty($item['blog_cat'])) {
                $blog_cat = str_replace(".", ",", $item['blog_cat']);
                $result2 = dbquery("SELECT blog_cat_id, blog_cat_name from ".DB_BLOG_CATS." WHERE blog_cat_id in ($blog_cat)");
                $rows2 = dbrows($result2);
                if ($rows2 > 0) {
                    $i = 1;
                    while ($catData = dbarray($result2)) {
                        $item['blog_category_link'] .= "<a href='".INFUSIONS."blog/blog.php?cat_id=".$catData['blog_cat_id']."'>".$catData['blog_cat_name']."</a>";
                        $item['blog_category_link'] .= $i == $rows2 ? "" : ", ";
                        $i++;
                    }
                }
            }
            $user_contact = '';
            if (isset($item['user_skype']) && $item['user_skype']) {
                $user_contact .= "<strong>Skype:</strong> ".$item['user_skype'];
            }
            if (isset($item['user_icq']) && $item['user_icq']) {
                $user_contact .= "<strong>ICQ:</strong> ".$item['user_icq'];
            }
            $item['blog_author_info'] = "<h4 class='blog_author_info'>".$locale['about']." ".profile_link($item['user_id'], $item['user_name'], $item['user_status'])."</h4>";
            $item['blog_author_info'] .= sprintf($locale['testimonial_rank'], getgroupname($item['user_level']));
            $item['blog_author_info'] .= (isset($item['user_joined']) && $item['user_joined'] !== '') ? sprintf($locale['testimonial_join'], showdate('shortdate', $item['user_joined'])).". " : '';
            $item['blog_author_info'] .= (isset($item['user_location']) && $item['user_location'] !== '') ? sprintf($locale['testimonial_location'], $item['user_location']) : '. ';
            $item['blog_author_info'] .= (isset($item['user_web']) && $item['user_web']) ? sprintf($locale['testimonial_web'], $item['user_web']).". " : '';
            $item['blog_author_info'] .= (isset($item['user_contact']) && $item['user_contact'] !== '') ? sprintf($locale['testimonial_contact'], $user_contact).". " : '';
            $item['blog_author_info'] .= ($item['user_email'] && $item['user_hide_email'] == 0) ? sprintf($locale['testimonial_email'], "<a href='mailto:".$item['user_email']."'>".$item['user_email']."</a>") : '';

            // Edit and Delete link
            $item['admin_link'] = '';
            if (iADMIN && checkrights('BLOG')) {
                $item['admin_link'] = [
                    'edit'   => INFUSIONS."blog/blog_admin.php".$aidlink."&amp;action=edit&amp;section=blog_form&amp;blog_id=".$item['blog_id'],
                    'delete' => INFUSIONS."blog/blog_admin.php".$aidlink."&amp;action=delete&amp;section=blog_form&amp;blog_id=".$item['blog_id'],
                ];
            }

            $blog_pagecount = 1;
            $blog_extended = preg_split("/<!?--\s*pagebreak\s*-->/i", $item['blog_breaks'] == "y" ? nl2br($item['blog_extended']) : $item['blog_extended']);
            $blog_pagecount = count($blog_extended);
            $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? intval($_GET['rowstart']) : 0;

            if (is_array($blog_extended)) {
                $item['blog_extended'] = $blog_extended[$_GET['rowstart']];
            }

            if ($blog_pagecount > 1) {
                $item['blog_nav'] = makepagenav($_GET['rowstart'], 1, $blog_pagecount, 3, INFUSIONS."blog/blog.php?readmore=".$_GET['readmore']."&amp;")."\n";
            }

            if (empty($item['blog_extended'])) {
                $item['blog_extended'] = $item['blog_blog'];
            }

            \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => INFUSIONS."blog/blog.php?readmore=".$_GET['readmore'],
                'title' => $item['blog_subject']
            ]);

            set_title($locale['blog_1000'].$locale['global_201']);
            add_to_title($item['blog_subject']);

            if (!empty($item['blog_keywords'])) {
                set_meta("keywords", $item['blog_keywords']);
            }

            $info['blog_title'] = $item['blog_subject'];
            $info['blog_updated'] = $locale['global_049']." ".timer($item['blog_datestamp']);

            $item['blog_show_comments'] = \PHPFusion\Blog\Functions::get_blog_comments($item);
            $item['blog_show_ratings'] = \PHPFusion\Blog\Functions::get_blog_ratings($item);

            $info['blog_item'] = $item;

            if (!isset($_POST['post_comment']) && !isset($_POST['post_rating']) && isset($_GET['readmore']) && empty($_GET['rowstart'])) {
                dbquery("UPDATE ".DB_BLOG." SET blog_reads=blog_reads+1 WHERE blog_id=:read_more", [':read_more' => intval($_GET['readmore'])]);
                $item['blog_reads']++;
            }
            \PHPFusion\OpenGraphBlogs::ogBlog($_GET['readmore']);
        }

    } else {
        redirect(INFUSIONS."blog/blog.php");
    }

} else {

    set_title($locale['blog_1000']);
    if (isset($_GET['author']) && isnum($_GET['author'])) {
        $info['blog_max_rows'] = dbcount("(blog_id)", DB_BLOG,
            (multilang_table("BL") ? in_group('blog_language', LANGUAGE)." and" : "")." ".groupaccess('blog_visibility')."
                                         AND (blog_start='0' || blog_start<='".time()."') AND (blog_end='0' || blog_end>='".time()."')
                                         AND blog_draft='0' AND blog_name='".intval($_GET['author'])."'");

        $_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['blog_max_rows']) ? $_GET['rowstart'] : 0;

        if ($info['blog_max_rows'] > 0) {

            $author = fusion_get_user(intval($_GET['author']));
            $author_username = $author['user_name'];
            unset($author);

            \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => INFUSIONS."blog/blog.php?author=".$_GET['author'],
                'title' => $locale['global_070'].$author_username
            ]);

            if (isset($_GET['type']) && isset($info['allowed_filters'][$_GET['type']])) {
                \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
                    'link'  => clean_request("", ["author"], TRUE),
                    'title' => $info['allowed_filters'][$_GET['type']]
                ]);
            }

            $pattern = "SELECT %s(br.rating_vote) FROM ".DB_RATINGS." AS br WHERE br.rating_item_id = b.blog_id AND br.rating_type = 'B'";
            $sql_count = sprintf($pattern, 'COUNT');
            $sql_sum = sprintf($pattern, 'SUM');

            $sql = "SELECT b.*, bu.user_id, bu.user_name, bu.user_status, bu.user_avatar , bu.user_level, bu.user_joined,
                ($sql_sum) AS sum_rating,
                ($sql_count) AS count_votes,
                (SELECT COUNT(bc.comment_id) FROM ".DB_COMMENTS." AS bc WHERE bc.comment_item_id = b.blog_id AND bc.comment_type = 'B' AND bc.comment_hidden = '0') AS count_comment,
                MAX(b.blog_datestamp) AS last_updated
                FROM ".DB_BLOG." AS b
                INNER JOIN ".DB_USERS." AS bu ON b.blog_name=bu.user_id
                ".(multilang_table('BL') ? "WHERE ".in_group('blog_language', LANGUAGE)." AND " : "WHERE ").groupaccess('blog_visibility')."
                AND (blog_start=0 || blog_start<=".TIME.") AND (blog_end=0 || blog_end>=".TIME.") AND blog_draft=0 AND blog_name=:author_id
                GROUP BY blog_id
                ORDER BY blog_sticky DESC, ".$filter_condition." LIMIT :rowstart, :limit
            ";

            $param = [
                ':author_id' => intval($_GET['author']),
                ':rowstart'  => intval($_GET['rowstart']),
                ':limit'     => intval($blog_settings['blog_pagination'])
            ];
            $result = dbquery($sql, $param);
            $info['blog_rows'] = dbrows($result);
        }
    } // Category
    else if ($_GET['cat_id'] !== NULL && validate_blogCats($_GET['cat_id'])) {

        $catFilter = "and blog_cat =''";
        if (!empty($_GET['cat_id'])) {

            $res = dbquery("SELECT blog_cat_id, blog_cat_name FROM ".DB_BLOG_CATS." WHERE ".(multilang_column('BL') ? in_group('blog_cat_language', LANGUAGE)." AND " : '')." blog_cat_id=:blog_cat_id", [':blog_cat_id' => intval($_GET['cat_id'])]);
            if (dbrows($res) > 0) {
                $res = dbarray($res);
                \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
                    'link'  => INFUSIONS."blog/blog.php?cat_id=".intval($_GET['cat_id']),
                    'title' => $res['blog_cat_name']
                ]);

                add_to_title($locale['global_201'].$res['blog_cat_name']);
                $info['blog_title'] = $res['blog_cat_name'];
                $catFilter = "and ".in_group("blog_cat", intval($_GET['cat_id']));
            }
        } else {

            // Uncategorized blog
            \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => INFUSIONS."blog/blog.php?cat_id=".intval($_GET['cat_id']),
                'title' => $locale['global_080']
            ]);
            add_to_title($locale['global_201'].$locale['global_080']);
            $info['blog_title'] = $locale['global_080'];
        }

        if (isset($_GET['type']) && isset($info['allowed_filters'][$_GET['type']])) {
            \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => INFUSIONS."blog/blog.php?cat_id=".intval($_GET['cat_id'])."&amp;type=".$_GET['type'],
                'title' => $info['allowed_filters'][$_GET['type']]
            ]);
        }

        $max_rows_sql = "SELECT blog_id from ".DB_BLOG." ".(multilang_table("BL") ? "WHERE ".in_group('blog_language', LANGUAGE)." AND " : "WHERE ").groupaccess("blog_visibility")." AND (blog_start=0 || blog_start<=".TIME.") AND (blog_end=0 || blog_end>=".TIME.") AND blog_draft='0' $catFilter";
        $info['blog_max_rows'] = dbrows(dbquery($max_rows_sql));

        //xss
        $_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['blog_max_rows']) ? $_GET['rowstart'] : 0;

        if ($info['blog_max_rows']) {
            $pattern = "SELECT %s(br.rating_vote) FROM ".DB_RATINGS." AS br WHERE br.rating_item_id = b.blog_id AND br.rating_type = 'B'";
            $sql_count = sprintf($pattern, 'COUNT');
            $sql_sum = sprintf($pattern, 'SUM');
            $result = dbquery("SELECT b.*, bc.*,
                IF(b.blog_cat = 0, '".$locale['global_080']."', blog_cat_name) as blog_cat_name,
                bu.user_id, bu.user_name, bu.user_status, bu.user_avatar , bu.user_level, bu.user_joined,
                ($sql_sum) AS sum_rating,
                ($sql_count) AS count_votes,
                (SELECT COUNT(bcc.comment_id) FROM ".DB_COMMENTS." AS bcc WHERE bcc.comment_item_id = b.blog_id AND bcc.comment_type = 'B' AND bcc.comment_hidden = '0') AS count_comment,
                MAX(b.blog_datestamp) as last_updated
                FROM ".DB_BLOG." AS b
                LEFT JOIN ".DB_USERS." AS bu ON b.blog_name=bu.user_id
                LEFT JOIN ".DB_BLOG_CATS." AS bc ON b.blog_cat=bc.blog_cat_id
                ".(multilang_table("BL") ? "WHERE ".in_group('blog_language', LANGUAGE)." AND " : "WHERE ").groupaccess('blog_visibility')."
                ".$catFilter."
                AND (blog_start='0' || blog_start<='".time()."') AND (blog_end='0' || blog_end>='".time()."')
                AND blog_draft='0'
                GROUP BY b.blog_id
                ORDER BY blog_sticky DESC, ".$filter_condition." LIMIT ".intval($_GET['rowstart']).",".intval($blog_settings['blog_pagination'])
            );
            $info['blog_rows'] = dbrows($result);
        }
        \PHPFusion\OpenGraphBlogs::ogBlogCat($_GET['cat_id']);

    } // Front Page with Condition from Archive
    else {

        // Archives
        $archiveSql = "";
        if (isset($_GET['archive']) && isnum($_GET['archive']) && isset($_GET['month']) && isnum($_GET['month'])) {

            $start_time = mktime('0', '0', '0', intval($_GET['month']), 1, intval($_GET['archive']));
            $end_time = mktime('0', '0', '0', intval($_GET['month']) + 1, 1, intval($_GET['archive'])) - (3600 * 24);
            $archiveSql = "AND blog_datestamp >=".intval($start_time)." AND blog_datestamp <= ".intval($end_time);

            \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
                'link'  => clean_request("", ["archive", "month"], TRUE),
                'title' => showdate($locale['blog_archive'], $start_time),
            ]);

        }

        $info['blog_max_rows'] = dbcount("('blog_id')", DB_BLOG, (multilang_table("BL") ? in_group('blog_language', LANGUAGE)." AND " : '').groupaccess('blog_visibility')." AND (blog_start=0 || blog_start<=".TIME.") AND (blog_end=0 || blog_end>=".TIME.") AND blog_draft=0 ".$archiveSql);
        $_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['blog_max_rows']) ? intval($_GET['rowstart']) : 0;

        if (isset($_GET['type']) && !empty($archiveSql) && isset($info['allowed_filters'][$_GET['type']])) {
            \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
                "link"  => clean_request("", ["archive", "month"], TRUE),
                "title" => $info['allowed_filters'][$_GET['type']]
            ]);
        }

        if ($info['blog_max_rows'] > 0) {
            $pattern = "SELECT %s(br.rating_vote) FROM ".DB_RATINGS." br WHERE br.rating_item_id = b.blog_id AND br.rating_type = 'B'";
            $sql_count = sprintf($pattern, 'COUNT');
            $sql_sum = sprintf($pattern, 'SUM');

            $condition = "SELECT b.*, bu.user_id, bu.user_name, bu.user_status, bu.user_avatar, bu.user_level, bu.user_joined,
                ($sql_sum) AS sum_rating,
                ($sql_count) AS count_votes,
                (SELECT COUNT(bcc.comment_id) FROM ".DB_COMMENTS." AS bcc WHERE bcc.comment_item_id = b.blog_id AND bcc.comment_type = 'B' AND bcc.comment_hidden = '0') AS count_comment,
                MAX(b.blog_datestamp) AS last_updated
                FROM ".DB_BLOG." AS b
                LEFT JOIN ".DB_USERS." AS bu ON b.blog_name=bu.user_id
                {FILTER_JOIN}
                WHERE {MULTILANG_CONDITION} {VISIBILITY} AND (blog_start=0 || blog_start<=:start_time) AND (blog_end=0 || blog_end>=:end_time) AND blog_draft=0
                {ARCHIVE_CONDITION} GROUP BY b.blog_id
                ORDER BY blog_sticky DESC, {FILTER_CONDITION} LIMIT :rowstart, :limit
            ";

            $sql = strtr($condition, [
                '{FILTER_JOIN}'         => $filter_join,
                '{MULTILANG_CONDITION}' => (multilang_table('BL') ? in_group('blog_language', LANGUAGE)." AND " : ""),
                '{VISIBILITY}'          => groupaccess('blog_visibility'),
                '{ARCHIVE_CONDITION}'   => $archiveSql,
                '{FILTER_CONDITION}'    => $filter_condition,
            ]);

            $param = [
                ':start_time' => TIME,
                ':end_time'   => TIME,
                ':rowstart'   => intval($_GET['rowstart']),
                ':limit'      => intval($blog_settings['blog_pagination'])
            ];
            $result = dbquery($sql, $param);
            $info['blog_rows'] = dbrows($result);
        }
    }

    if (!empty($info['blog_rows'])) {
        while ($data = dbarray($result)) {
            // remove category image binding on item. each item is capable of housing hundreds of category.
            $blog_image = '';
            $hiRes_image_path = "";
            $lowRes_image_path = "";
            if ($data['blog_image']) {
                $hiRes_image_path = get_blog_image_path($data['blog_image'], $data['blog_image_t1'], $data['blog_image_t2'], TRUE);
                $lowRes_image_path = get_blog_image_path($data['blog_image'], $data['blog_image_t1'], $data['blog_image_t2'], FALSE);
                $blog_image = "<a href='".INFUSIONS."blog/blog.php?readmore=".$data['blog_id']."'>".thumbnail($lowRes_image_path, '150px')."</a>";
            }

            $blog_blog = parse_textarea($data['blog_blog'], FALSE, FALSE, TRUE, FALSE, $data['blog_breaks'] == 'y' ? TRUE : FALSE);
            $blog_extended = parse_textarea($data['blog_extended'], FALSE, FALSE, TRUE, FALSE, $data['blog_breaks'] == 'y' ? TRUE : FALSE);

            $cdata = [
                'blog_ialign'            => $data['blog_ialign'] == 'center' ? 'clearfix' : $data['blog_ialign'],
                'blog_anchor'            => "<a name='blog_".$data['blog_id']."' id='blog_".$data['blog_id']."'></a>",
                'blog_blog'              => preg_replace("/<!?--\s*pagebreak\s*-->/i", "", $blog_blog),
                'blog_extended'          => preg_replace("/<!?--\s*pagebreak\s*-->/i", "", $blog_extended),
                'blog_link'              => INFUSIONS."blog/blog.php?readmore=".$data['blog_id'],
                'blog_category_link'     => "",
                'blog_readmore_link'     => "<a href='".INFUSIONS."blog/blog.php?readmore=".$data['blog_id']."'>".$locale['blog_1006']."</a>",
                'blog_subject'           => stripslashes($data['blog_subject']),
                'blog_image'             => $blog_image,
                'blog_image_path'        => $hiRes_image_path,
                'blog_lowRes_image_path' => $lowRes_image_path,
                'blog_thumb'             => get_blog_image_path($data['blog_image'], $data['blog_image_t1'], $data['blog_image_t2'], FALSE),
                "blog_reads"             => format_word($data['blog_reads'], $locale['fmt_read']),
                "blog_comments"          => format_word($data['count_comment'], $locale['fmt_comment']),
                'blog_sum_rating'        => format_word($data['sum_rating'], $locale['fmt_rating']),
                'blog_count_votes'       => format_word($data['count_votes'], $locale['fmt_vote']),
                'blog_user_avatar'       => display_avatar($data, '35px', '', TRUE, 'img-rounded'),
                'blog_user_link'         => profile_link($data['user_id'], $data['user_name'], $data['user_status'], 'strong'),
            ];
            // refetch category per item and parse as string
            if (!empty($data['blog_cat'])) {
                $blog_cat = str_replace(".", ",", $data['blog_cat']);
                $result2 = dbquery("SELECT blog_cat_id, blog_cat_name, blog_cat_image from ".DB_BLOG_CATS." WHERE blog_cat_id in ($blog_cat)");
                $rows2 = dbrows($result2);
                if ($rows2 > 0) {
                    $i = 1;
                    while ($catData = dbarray($result2)) {
                        $cdata['blog_category_link'] .= "<a href='".INFUSIONS."blog/blog.php?cat_id=".$catData['blog_cat_id']."'>".$catData['blog_cat_name']."</a>";
                        $cdata['blog_category_link'] .= $i == $rows2 ? "" : ", ";
                        $cdata['blog_cat_image'] = $catData['blog_cat_image'];
                        $i++;
                    }
                }
            }

            $data = array_merge($data, $cdata);
            $info['blog_item'][$data['blog_id']] = $data;
        }
    }
}

if (!empty($info['blog_max_rows']) && ($info['blog_max_rows'] > $blog_settings['blog_pagination']) && !isset($_GET['readmore'])) {
    $page_nav_link = (!empty($_GET['type']) ? INFUSIONS."blog/blog.php?type=".$_GET['type']."&amp;" : '');

    if (!empty($_GET['cat_id']) && isnum($_GET['cat_id'])) {
        $page_nav_link = INFUSIONS."blog/blog.php?cat_id=".$_GET['cat_id'].(!empty($_GET['type']) ? "&amp;type=".$_GET['type'] : '')."&amp;";
    } else if (!empty($_GET['author']) && isnum($_GET['author'])) {
        $info['blog_max_rows'] = dbcount("('blog_id')", DB_BLOG, "blog_name='".intval($_GET['author'])."' AND ".groupaccess('blog_visibility'));
        $_GET['rowstart'] = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $info['blog_max_rows']) ? $_GET['rowstart'] : 0;

        $page_nav_link = INFUSIONS."blog/blog.php?author=".$_GET['author']."&amp;";
    }

    $info['blog_nav'] = makepagenav($_GET['rowstart'], $blog_settings['blog_pagination'], $info['blog_max_rows'], 3, $page_nav_link);
}

// Archive Menu
$sql = "SELECT YEAR(from_unixtime(blog_datestamp)) as blog_year, MONTH(from_unixtime(blog_datestamp)) AS blog_month, count(blog_id) AS blog_count
        FROM ".DB_BLOG." ".(multilang_table("BL") ? "WHERE ".in_group('blog_language', LANGUAGE)." AND " : "WHERE ").groupaccess('blog_visibility')." AND (blog_start=0 || blog_start<=".TIME.") AND (blog_end=0 || blog_end>=".TIME.") AND blog_draft=0 GROUP BY blog_year, blog_month ORDER BY blog_datestamp DESC";
$archive_result = dbquery($sql);

if (dbrows($archive_result)) {
    while ($a_data = dbarray($archive_result)) {
        $active = isset($_GET['archive']) && isnum($_GET['archive']) && ($_GET['archive'] == $a_data['blog_year']) &&
        isset($_GET['month']) && isnum($_GET['month']) && ($_GET['month'] == $a_data['blog_month']) ? TRUE : FALSE;
        $month_locale = explode('|', $locale['months']);
        $info['blog_archive'][$a_data['blog_year']][$a_data['blog_month']] = [
            'title'  => $month_locale[$a_data['blog_month']],
            'link'   => INFUSIONS."blog/blog.php?archive=".$a_data['blog_year']."&amp;month=".$a_data['blog_month'].(isset($_GET['type']) && !empty($_GET['type']) ? "&amp;type=".$_GET['type'] : ""),
            'count'  => $a_data['blog_count'],
            'active' => $active
        ];
    }
}

// Author Menu
$sql = "SELECT b.blog_name, count(b.blog_id) AS blog_count, u.user_id, u.user_name, u.user_status FROM ".DB_BLOG." b INNER JOIN ".DB_USERS." u ON (b.blog_name = u.user_id) GROUP BY blog_name ORDER BY blog_name ASC";
$author_result = dbquery($sql);

if (dbrows($author_result)) {
    while ($at_data = dbarray($author_result)) {
        $active = isset($_GET['author']) && $_GET['author'] == $at_data['blog_name'] ? 1 : 0;
        $info['blog_author'][$at_data['blog_name']] = [
            'title'  => $at_data['user_name'],
            'link'   => INFUSIONS."blog/blog.php?author=".$at_data['blog_name'],
            'count'  => $at_data['blog_count'],
            'active' => $active
        ];
    }
}

render_main_blog($info);

require_once THEMES.'templates/footer.php';

/**
 * Returns Blog Category Hierarchy Tree Data
 *
 * @return array
 */
function get_blogCatsData() {
    return \PHPFusion\Blog\Functions::get_blogCatsData();
}

/**
 * Get Blog Hierarchy Index
 *
 * @return array
 */
function get_blogCatsIndex() {
    return PHPFusion\Blog\Functions::get_blogCatsIndex();
}

/**
 * Validate Blog ID
 *
 * @param $blog_id
 *
 * @return int
 */
function validate_blog($blog_id) {
    return PHPFusion\Blog\Functions::validate_blog($blog_id);
}

/**
 * Validate Blog Cat Id
 *
 * @param $blog_cat_id
 *
 * @return int
 */
function validate_blogCats($blog_cat_id) {
    return PHPFusion\Blog\Functions::validate_blogCat($blog_cat_id);
}

/**
 * Get the closest image available
 *
 * @param      $image
 * @param      $thumb1
 * @param      $thumb2
 * @param bool $hires - true for image, false for thumbnail
 *
 * @return bool|string
 */
function get_blog_image_path($image, $thumb1, $thumb2, $hires = FALSE) {
    return \PHPFusion\Blog\Functions::get_blog_image_path($image, $thumb1, $thumb2, $hires);
}
