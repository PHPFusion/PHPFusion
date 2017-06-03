<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: print.php
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
require_once dirname(__FILE__).'/maincore.php';
$settings = fusion_get_settings();
$locale = fusion_get_locale('', LOCALE.LOCALESET."print.php");

if (fusion_get_settings('maintenance') == "1") {
    if (fusion_get_settings('maintenance_level') < fusion_get_userdata('user_level') or empty(fusion_get_userdata('user_level'))) {
        if (fusion_get_settings('site_seo')) {
            redirect(FUSION_ROOT.BASEDIR."maintenance.php");
        } else {
            redirect(BASEDIR."maintenance.php");
        }
    }
}

if (iMEMBER) {
    dbquery("UPDATE ".DB_USERS." SET user_lastvisit='".time()."', user_ip='".USER_IP."', user_ip_type='".USER_IP_TYPE."' WHERE user_id='".fusion_get_userdata('user_id')."'");
}

echo "<!DOCTYPE html>\n";
echo "<html lang='".$locale['xml_lang']."'>\n";
echo "<head>\n<title>".$settings['sitename']."</title>\n";
echo "<meta charset=".$locale['charset']."' />\n";
echo "<meta name='description' content='".$settings['description']."' />\n";
echo "<meta name='keywords' content='".$settings['keywords']."' />\n";
echo "<style type='text/css'>
	* { background: transparent !important; color: #444 !important; text-shadow: none; }
	body { font-family:Verdana,Tahoma,Arial,Sans-Serif;font-size:14px; }
	hr { display:block; height:1px; border:0; border-top:1px solid #ccc; margin:1em 0; padding:0; }
	.small { font-family:Verdana,Tahoma,Arial,Sans-Serif;font-size:12px; }
	.small2 { font-family:Verdana,Tahoma,Arial,Sans-Serif;font-size:12px;color:#666; }
	a, a:visited { color: #444 !important; text-decoration: underline; }
	a:after { content: ' (' attr(href) ')'; }
	abbr:after { content: ' (' attr(title) ')'; }
	pre, blockquote { border: 1px solid #999; page-break-inside: avoid; }
	img { page-break-inside: avoid; }
	@page { margin: 0.5cm; }
	p, h2, h3 { orphans: 3; widows: 3; }
	h2, h3 { page-break-after: avoid; }
</style>\n";
echo "</head>\n<body>\n";

$item_id = isset($_GET['item_id']) && isnum($_GET['item_id']) ? $_GET['item_id'] : 0;

if (isset($_GET['type'])) {
    switch ($_GET['type']) {
		case "FQ":
            if (!infusion_exists('faq')) {
                redirect(BASEDIR."error.php?code=404");
            }
            $result = dbquery("
			SELECT
				ta.faq_question, ta.faq_answer, ta.faq_breaks, ta.faq_datestamp,
				tu.user_id, tu.user_name, tu.user_status
            FROM ".DB_FAQS." ta
            LEFT JOIN ".DB_USERS." tu ON ta.faq_name=tu.user_id
            WHERE ta.faq_id='".intval($item_id)."' AND ta.faq_status='1' AND ".groupaccess("ta.faq_visibility")."
			LIMIT 0,1");
            $res = FALSE;
            if (dbrows($result)) {
                $data = dbarray($result);
				$res = TRUE;
				$faq = str_replace("<--PAGEBREAK-->", "", parse_textarea($data['faq_answer']));
				if ($data['faq_breaks'] == "y") {
					$faq = nl2br($faq);
				}
				echo "<strong>".$data['faq_question']."</strong><br />\n";
				echo "<span class='small'>".$locale['400'].$data['user_name'].$locale['401'].ucfirst(showdate("longdate", $data['faq_datestamp']))."</span>\n";
				echo "<hr />".$faq."\n";
            }
            if (!$res) {
                redirect($settings['opening_page']);
            }
            break;
		case "A":
            if (!infusion_exists('articles')) {
                redirect(BASEDIR."error.php?code=404");
            }
            $result = dbquery("
			SELECT
				ta.article_subject, ta.article_article, ta.article_breaks, ta.article_datestamp,
				tu.user_id, tu.user_name, tu.user_status
            FROM ".DB_ARTICLES." ta
            INNER JOIN ".DB_ARTICLE_CATS." tac ON ta.article_cat=tac.article_cat_id
            LEFT JOIN ".DB_USERS." tu ON ta.article_name=tu.user_id
            WHERE ta.article_id='".intval($item_id)."' AND ta.article_draft='0' AND tac.article_cat_status='1' AND ".groupaccess("ta.article_visibility")." AND ".groupaccess("tac.article_cat_visibility")."
			LIMIT 0,1");
            $res = FALSE;
            if (dbrows($result)) {
                $data = dbarray($result);
				$res = TRUE;
				$article = str_replace("<--PAGEBREAK-->", "", parse_textarea($data['article_article']));
				if ($data['article_breaks'] == "y") {
					$article = nl2br($article);
				}
				echo "<strong>".$data['article_subject']."</strong><br />\n";
				echo "<span class='small'>".$locale['400'].$data['user_name'].$locale['401'].ucfirst(showdate("longdate", $data['article_datestamp']))."</span>\n";
				echo "<hr />".$article."\n";
            }
            if (!$res) {
                redirect($settings['opening_page']);
            }
            break;
        case "N":
            if (!infusion_exists('news')) {
                redirect(BASEDIR."error.php?code=404");
            }
            $result = dbquery("SELECT tn.news_subject, tn.news_news, tn.news_extended, tn.news_breaks, tn.news_datestamp, tn.news_visibility,
            tu.user_id, tu.user_name, tu.user_status
            FROM ".DB_NEWS." tn
            LEFT JOIN ".DB_USERS." tu ON tn.news_name=tu.user_id
            WHERE news_id='".intval($item_id)."' AND news_draft='0'");
            $res = FALSE;
            if (dbrows($result) != 0) {
                $data = dbarray($result);
                if (checkgroup($data['news_visibility'])) {
                    $res = TRUE;
                    $news = parse_textarea($data['news_news']);
                    if ($data['news_breaks'] == "y") {
                        $news = nl2br($news);
                    }
                    if ($data['news_extended']) {
                        $news_extended = parse_textarea($data['news_extended']);
                        if ($data['news_breaks'] == "y") {
                            $news_extended = nl2br($news_extended);
                        }
                    } else {
                        $news_extended = "";
                    }
                    echo "<strong>".$data['news_subject']."</strong><br />\n";
                    echo "<span class='small'>".$locale['400'].$data['user_name'].$locale['401'].ucfirst(showdate("longdate",$data['news_datestamp']))."</span>\n";
                    echo "<hr />".$news."\n";
                    if ($news_extended) {
                        echo "<hr />\n<strong>".$locale['402']."</strong>\n<hr />\n$news_extended\n";
                    }
                }
            }
            if (!$res) {
                redirect($settings['opening_page']);
            }
            break;
        case "B":
            if (!infusion_exists('blog')) {
                redirect(BASEDIR."error.php?code=404");
            }
            $result = dbquery("SELECT tn.blog_subject, tn.blog_blog, tn.blog_extended, tn.blog_breaks, tn.blog_datestamp, tn.blog_visibility,
            tu.user_id, tu.user_name, tu.user_status
            FROM ".DB_BLOG." tn
            LEFT JOIN ".DB_USERS." tu ON tn.blog_name=tu.user_id
            WHERE blog_id='".intval($item_id)."' AND blog_draft='0'");
            $res = FALSE;
            if (dbrows($result) != 0) {
                $data = dbarray($result);
                if (checkgroup($data['blog_visibility'])) {
                    $res = TRUE;
                    $blog = parse_textarea($data['blog_blog']);
                    if ($data['blog_breaks'] == "y") {
                        $blog = nl2br($blog);
                    }
                    if ($data['blog_extended']) {
                        $blog_extended = parse_textarea($data['blog_extended']);
                        if ($data['blog_breaks'] == "y") {
                            $blog_extended = nl2br($blog_extended);
                        }
                    } else {
                        $blog_extended = "";
                    }
                    echo "<strong>".$data['blog_subject']."</strong><br />\n";
                    echo "<span class='small'>".$locale['400'].$data['user_name'].$locale['401'].ucfirst(showdate("longdate",$data['blog_datestamp']))."</span>\n";
                    echo "<hr />".$blog."\n";
                    if ($blog_extended) {
                        echo "<hr />\n<strong>".$locale['403']."</strong>\n<hr />\n$blog_extended\n";
                    }
                }
            }
            if (!$res) {
                redirect($settings['opening_page']);
            }
            break;
        case "F":
            if (!infusion_exists('forum')) {
                redirect(BASEDIR."error.php?code=404");
            }
            if ((isset($_GET['post']) && isnum($_GET['post'])) && (isset($_GET['nr']) && isnum($_GET['nr']))) {
                $result = dbquery("SELECT fp.post_message, fp.post_datestamp, fp.post_edittime, fp.post_author as post_author, fp.post_edituser,
                fu.user_name AS user_name, fu.user_status AS user_status, fe.user_name AS edit_name, fe.user_status AS edit_status,
                ft.thread_subject, ff.forum_access
                FROM ".DB_FORUM_THREADS." ft
                INNER JOIN ".DB_FORUM_POSTS." fp ON ft.thread_id = fp.thread_id
                INNER JOIN ".DB_FORUMS." ff ON ff.forum_id = ft.forum_id
                INNER JOIN ".DB_USERS." fu ON fu.user_id = fp.post_author
                LEFT JOIN ".DB_USERS." fe ON fe.user_id = fp.post_edituser
                WHERE ft.thread_id='".intval($item_id)."' AND fp.post_id = ".$_GET['post']);
                $res = FALSE;
                if (dbrows($result)) {
                    $data = dbarray($result);
                    if (checkgroup($data['forum_access'])) {
                        $res = TRUE;
                        echo $locale['500']." <strong>".$settings['sitename']." :: ".$data['thread_subject']."</strong><hr /><br />\n";
                        echo "<div style='margin-left:20px'>\n";
                        echo "<div style='float:left'>".$locale['501'].$data['user_name'].$locale['502'].showdate("forumdate",$data['post_datestamp'])."</div><div style='float:right'>#".$_GET['nr']."</div><div style='float:none;clear:both'></div><hr />\n";
                        echo nl2br(parseubb(parsesmileys($data['post_message'])));
                        if ($data['edit_name'] != "") {
                            echo "<div style='margin-left:20px'>\n<hr />\n";
                            echo $locale['503'].$data['edit_name'].$locale['502'].showdate("forumdate", $data['post_edittime']);
                            echo "</div>\n";
                        }
                        echo "</div>\n";
                        echo "<br />\n";
                    }
                }
                if (!$res) {
                    redirect($settings['opening_page']);
                }
            } else {
                $posts_per_page = 20;
                if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) {
                    $_GET['rowstart'] = 0;
                }
                $result = dbquery("SELECT fp.post_message, fp.post_datestamp, fp.post_edittime, fp.post_author, fp.post_edituser,
                fu.user_name AS user_name, fu.user_status AS user_status, fe.user_name AS edit_name, fe.user_status AS edit_status,
                ft.thread_subject, ff.forum_access
                FROM ".DB_FORUM_THREADS." ft
                INNER JOIN ".DB_FORUM_POSTS." fp ON ft.thread_id = fp.thread_id
                INNER JOIN ".DB_FORUMS." ff ON ff.forum_id = ft.forum_id
                INNER JOIN ".DB_USERS." fu ON fu.user_id = fp.post_author
                LEFT JOIN ".DB_USERS." fe ON fe.user_id = fp.post_edituser
                WHERE ft.thread_id='".intval($item_id)."'
                ORDER BY fp.post_datestamp
                LIMIT ".$_GET['rowstart'].",$posts_per_page");
                $res = FALSE;
                $i = 0;
                if (dbrows($result)) {
                    while ($data = dbarray($result)) {
                        if (checkgroup($data['forum_access'])) {
                            $res = TRUE;
                            if ($i == 0) {
                                echo $locale['500']." <strong>".$settings['sitename']." :: ".$data['thread_subject']."</strong><hr /><br />\n";
                            }
                            echo "<div style='margin-left:20px'>\n";
                            echo "<div style='float:left'>".$locale['501'].$data['user_name'].$locale['502'].showdate("forumdate",$data['post_datestamp'])."</div><div style='float:right'>#".($i + 1)."</div><div style='float:none;clear:both'></div><hr />\n";
                            echo nl2br(parseubb(parsesmileys($data['post_message'])));
                            if ($data['edit_name'] != '') {
                                echo "<div style='margin-left:20px'>\n<hr />\n";
                                echo $locale['503'].$data['edit_name'].$locale['502'].showdate("forumdate", $data['post_edittime']);
                                echo "</div>\n";
                            }
                            echo "</div>\n";
                            echo "<br />\n";
                            $i++;
                        }
                    }
                }
                if (!$res) {
                    redirect($settings['opening_page']);
                }
            }
            break;
        case "T":
            if ($settings['enable_terms'] == 1) {
                echo "<strong>".$settings['sitename']." ".$locale['600']."</strong><br />\n";
                echo "<small>".$locale['601']." ".ucfirst(showdate("longdate", $settings['license_lastupdate']))."<small>\n";
                echo "<hr />".parse_textarea($settings['license_agreement'])."\n";
            } else {
                redirect($settings['opening_page']);
            }
            break;
        case "P":
            echo "<strong>".$settings['sitename']." ".$locale['700']."</strong><br />\n";
            echo "<hr />".parse_textarea($settings['privacy_policy'])."\n";
            break;
    }
} else {
    redirect($settings['opening_page']);
}
echo "</body>\n</html>";
if (ob_get_length() !== FALSE) {
    ob_end_flush();
}