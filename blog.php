<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: blog.php
| Author: Nick Jones (Digitanium)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__."/maincore.php";
require_once THEMES."templates/header.php";

include LOCALE.LOCALESET."blog.php";

// Predefined variables, do not edit these values
$i = 0;

// Number of blogs displayed
$items_per_page = $settings['blogperpage'];

add_to_title($locale['global_200'].$locale['blog_077']);

if (!isset($_GET['readmore']) || !isnum($_GET['readmore'])) {
	$rows = dbcount(
		"(blog_id)",
		DB_BLOG,
		groupaccess('blog_visibility')." AND (blog_start='0'||blog_start<=".time().")
										AND (blog_end='0'||blog_end>=".time().")
										AND blog_draft='0'"
	);
	if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }
	if ($rows) {
		$result = dbquery(
			"SELECT tn.*, tc.*, tu.user_id, tu.user_name, tu.user_status
			FROM ".DB_BLOG." tn
			LEFT JOIN ".DB_USERS." tu ON tn.blog_name=tu.user_id
			LEFT JOIN ".DB_BLOG_CATS." tc ON tn.blog_cat=tc.blog_cat_id
			".(multilang_table("BL") ? "WHERE blog_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('blog_visibility')." AND (blog_start='0'||blog_start<=".time().")
				AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0'
			GROUP BY blog_id
			ORDER BY blog_sticky DESC, blog_datestamp DESC LIMIT ".$_GET['rowstart'].",".$items_per_page
		);
		$numrows = dbrows($result);
		while ($data = dbarray($result)) {
			$i++;
			$comments = dbcount("(comment_id)", DB_COMMENTS." WHERE comment_type='N' AND comment_hidden='0' AND comment_item_id='".$data['blog_id']."'");
			$blog_cat_image = "";
			$blog_subject = "<a name='blog_".$data['blog_id']."' id='blog_".$data['blog_id']."'></a>".stripslashes($data['blog_subject']);
			$blog_cat_image = "<a href='".($settings['blog_image_link'] == 0 ? "".BASEDIR."blog_cats.php?cat_id=".$data['blog_cat'] : BASEDIR."blog.php?readmore=".$data['blog_id'] )."'>";
			if ($data['blog_image_t2'] && $settings['blog_image_frontpage'] == 0) {
				$blog_cat_image .= "<img src='".IMAGES_B_T.$data['blog_image_t2']."' alt='".$data['blog_subject']."' class='img-responsive blog-category' /></a>";
			} elseif ($data['blog_cat_image']) {
				$blog_cat_image .= "<img src='".get_image("bc_".$data['blog_cat_name'])."' alt='".$data['blog_cat_name']."' class='img-responsive blog-category' /></a>";
			} else {
				$blog_cat_image = "";
			}
			$blog_blog = preg_replace("/<!?--\s*pagebreak\s*-->/i", "", ($data['blog_breaks'] == "y" ? nl2br(stripslashes($data['blog_blog'])) : stripslashes($data['blog_blog'])));
			$blog_info = array(
				"blog_id" => $data['blog_id'],
				"user_id" => $data['user_id'],
				"user_name" => $data['user_name'],
				"user_status" => $data['user_status'],
				"blog_date" => $data['blog_datestamp'],
				"cat_id" => $data['blog_cat'],
				"cat_name" => $data['blog_cat_name'],
				"cat_image" => $blog_cat_image,
				"blog_subject" => $data['blog_subject'],
				"blog_ext" => $data['blog_extended'] ? "y" : "n",
				"blog_reads" => $data['blog_reads'],
				"blog_comments" => $comments,
				"blog_allow_comments" => $data['blog_allow_comments'],
				"blog_sticky" => $data['blog_sticky']
			);

			echo "<!--blog_prepost_".$i."-->\n";
			// Safe fallback for blog rendering if blog does not exist in your theme.
				render_blog($blog_subject, $blog_blog, $blog_info);
		}
		echo "<!--sub_blog_idx-->\n";
		if ($rows > $items_per_page) echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'],$items_per_page,$rows,3)."\n</div>\n";
	} else {
		opentable($locale['blog_077']);
		echo "<div style='text-align:center'><br />\n".$locale['blog_078']."<br /><br />\n</div>\n";
		closetable();
	}
} else {
	if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }
	$result = dbquery(
		"SELECT tn.*, tc.*, tu.user_id, tu.user_name, tu.user_status FROM ".DB_BLOG." tn
		LEFT JOIN ".DB_USERS." tu ON tn.blog_name=tu.user_id
		LEFT JOIN ".DB_BLOG_CATS." tc ON tn.blog_cat=tc.blog_cat_id
		".(multilang_table("BL") ? "WHERE blog_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('blog_visibility')." AND blog_id='".$_GET['readmore']."' AND blog_draft='0'
		LIMIT 1"
	);
	if (dbrows($result)) {
		include INCLUDES."comments_include.php";
		include INCLUDES."ratings_include.php";
		$data = dbarray($result);
		if (!isset($_POST['post_comment']) && !isset($_POST['post_rating'])) {
			$result2 = dbquery("UPDATE ".DB_BLOG." SET blog_reads=blog_reads+1 WHERE blog_id='".$_GET['readmore']."'");
			$data['blog_reads']++;
		}
		$blog_cat_image = "";
		$blog_subject = $data['blog_subject'];
		if ($data['blog_image_t1'] && $settings['blog_image_readmore'] == "0") {
			$img_size = @getimagesize(IMAGES_B.$data['blog_image']);
			$blog_cat_image = "<a href=\"javascript:;\" onclick=\"window.open('".$settings['siteurl']."/images/blog/".$data['blog_image']."','','scrollbars=yes,toolbar=no,status=no,resizable=yes,width=".($img_size[0]+20).",height=".($img_size[1]+20)."')\"><img src='".IMAGES_B_T.$data['blog_image_t1']."' alt='".$data['blog_subject']."' class='img-responsive blog-category' /></a>";
		} elseif ($data['blog_cat_image']) {
			$blog_cat_image = "<a href='blog_cats.php?cat_id=".$data['blog_cat']."'><img src='".get_image("bc_".$data['blog_cat_name'])."' alt='".$data['blog_cat_name']."' class='img-responsive blog-category' /></a>";
		}
		$blog_blog = preg_split("/<!?--\s*pagebreak\s*-->/i", $data['blog_breaks'] == "y" ? nl2br(stripslashes($data['blog_extended'] ? $data['blog_extended'] : $data['blog_blog'])) : stripslashes($data['blog_extended'] ? $data['blog_extended'] : $data['blog_blog']));    
		$pagecount = count($blog_blog);
		$blog_info = array(
			"blog_id" => $data['blog_id'],
			"user_id" => $data['user_id'],
			"user_name" => $data['user_name'],
			"user_status" => $data['user_status'],
			"blog_date" => $data['blog_datestamp'],
			"cat_id" => $data['blog_cat'],
			"cat_name" => $data['blog_cat_name'],
			"cat_image" => $blog_cat_image,
			"blog_subject" => $data['blog_subject'],
			"blog_ext" => "n",
			"blog_reads" => $data['blog_reads'],
			"blog_comments" => dbcount("(comment_id)", DB_COMMENTS, "comment_type='N' AND comment_item_id='".$data['blog_id']."' AND comment_hidden='0'"),
			"blog_allow_comments" => $data['blog_allow_comments'],
			"blog_sticky" => $data['blog_sticky']
		);
		add_to_title($locale['global_201'].$blog_subject);
		echo "<!--blog_pre_readmore-->";
		
		render_blog($blog_subject, $blog_blog[$_GET['rowstart']], $blog_info);
			
		echo "<!--blog_sub_readmore-->";
		if ($pagecount > 1) {
			echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], 1, $pagecount, 3, BASEDIR."blog.php?readmore=".$_GET['readmore']."&amp;")."\n</div>\n";
		}
		if ($data['blog_allow_comments']) { showcomments("B", DB_BLOG, "blog_id", $_GET['readmore'], BASEDIR."blog.php?readmore=".$_GET['readmore']); }
		if ($data['blog_allow_ratings']) { showratings("B", $_GET['readmore'], BASEDIR."blog.php?readmore=".$_GET['readmore']); }
	} else {
		redirect(FUSION_SELF);
	}
}

require_once THEMES."templates/footer.php";
