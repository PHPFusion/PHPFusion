<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: blog.php
| Author: Frederick MC Chan (Hien)
| Version : 9.00
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

if (!function_exists('render_main_blog')) {
	function render_main_blog($info) {
		/*
		 * Array
(
    [news_cat_id] => 0
    [news_cat_name] => News
    [news_cat_image] =>
    [news_cat_language] => English

    [news_categories] => Array
        (
            [1] => Array
                (
                    [link] => ../../infusions/news.php?cat_id=1
                    [name] => Bugs
                )

            [2] => Array
                (
                    [link] => ../../infusions/news.php?cat_id=2
                    [name] => Downloads
                )

            [3] => Array
                (
                    [link] => ../../infusions/news.php?cat_id=3
                    [name] => Games
                )

            [4] => Array
                (
                    [link] => ../../infusions/news.php?cat_id=4
                    [name] => Graphics
                )

            [5] => Array
                (
                    [link] => ../../infusions/news.php?cat_id=5
                    [name] => Hardware
                )

            [6] => Array
                (
                    [link] => ../../infusions/news.php?cat_id=6
                    [name] => Journal
                )

            [7] => Array
                (
                    [link] => ../../infusions/news.php?cat_id=7
                    [name] => Members
                )

            [8] => Array
                (
                    [link] => ../../infusions/news.php?cat_id=8
                    [name] => Mods
                )

            [9] => Array
                (
                    [link] => ../../infusions/news.php?cat_id=9
                    [name] => Movies
                )

            [10] => Array
                (
                    [link] => ../../infusions/news.php?cat_id=10
                    [name] => Network
                )

            [11] => Array
                (
                    [link] => ../../infusions/news.php?cat_id=11
                    [name] => News
                )

            [12] => Array
                (
                    [link] => ../../infusions/news.php?cat_id=12
                    [name] => PHP-Fusion
                )

            [13] => Array
                (
                    [link] => ../../infusions/news.php?cat_id=13
                    [name] => Security
                )

            [14] => Array
                (
                    [link] => ../../infusions/news.php?cat_id=14
                    [name] => Software
                )

            [15] => Array
                (
                    [link] => ../../infusions/news.php?cat_id=15
                    [name] => Themes
                )

            [16] => Array
                (
                    [link] => ../../infusions/news.php?cat_id=16
                    [name] => Windows
                )

        )

    [allowed_filters] => Array
        (
            [recent] => Most Recent News
            [comment] => Most Commented News
            [rating] => Highest Rating News
        )

    [news_filter] => Array
        (
            [../../infusions/news/news.php?type=recent] => Most Recent News
            [../../infusions/news/news.php?type=comment] => Most Commented News
            [../../infusions/news/news.php?type=rating] => Highest Rating News
        )

    [news_item_rows] => 2
    [news_last_updated] => 1439522774
    [news_items] => Array
        (
            [1] => Array
                (
                    [news_id] => 6
                    [news_subject] => Apple Reports Record Second Quarter Results
                    [news_url] => ../../infusions/news/news.php?readmore=6
                    [news_anchor] => <a name='news_6' id='news_6'></a>
                    [news_news] => Apple today announced financial results for its fiscal 2015 second quarter ended March 28. The company posted quarterly revenue of $58 billion and quarterly net profit of $13.6 billion, or $2.33 per diluted share. These results compare to revenue of $45.6 billion and net profit of $10.2 billion, or $1.66 per diluted share, in the year-ago quarter. Gross margin was 40.8 percent compared to 39.3 percent in the year-ago quarter. International sales accounted for 69 percent of the quarter’s revenue. “We are thrilled by the continued strength of iPhone, Mac, and the App Store, which drove our best March quarter results ever,” said Tim Cook, Apple’s CEO. “We’re seeing a higher rate of people switching to iPhone than we’ve experienced in previous cycles, and we’re off to an exciting start to the June quarter with the launch of Apple Watch.” Read more: apple.com/pr
                    [news_keywords] => Apple
                    [user_id] => 1
                    [user_name] => Admin
                    [user_status] => 0
                    [user_avatar] =>
                    [user_level] => -103
                    [news_date] => 1439522774
                    [cat_id] => 0
                    [cat_name] =>
                    [cat_image] => <a href='../../infusions/news/news.php?readmore=6'><img class='img-responsive' src='../../infusions/news/images/thumbs/o-computer-happy-facebook_t1.jpg' alt='Apple Reports Record Second Quarter Results' />
</a>
                    [news_image] => <a class='img-link' href='
					../../infusions/news/news.php?readmore=6
					'><img class='img-responsive' src='../../infusions/news/images/thumbs/o-computer-happy-facebook_t1.jpg' alt='Apple Reports Record Second Quarter Results' />
</a>

                    [news_image_src] => ../../infusions/news/images/o-computer-happy-facebook.jpg
                    [news_ext] => n
                    [news_reads] => 12
                    [news_comments] => 0
                    [news_sum_rating] => 0
                    [news_count_votes] => 0
                    [news_allow_comments] => 0
                    [news_allow_ratings] => 0
                    [news_sticky] => 0
                )

            [2] => Array
                (
                    [news_id] => 5
                    [news_subject] => Apple Announces New Environmental Initiatives in China
                    [news_url] => ../../infusions/news/news.php?readmore=5
                    [news_anchor] => <a name='news_5' id='news_5'></a>
                    [news_news] => Apple today announced that its board of directors has authorized an increase of more than 50 percent to the company’s program to return capital to shareholders. Under the expanded program, Apple plans to utilize a cumulative total of $200 billion of cash by the end of March 2017. As part of the revised program, the board has increased its share repurchase authorization to $140 billion from the $90 billion level announced last year. In addition, the company expects to continue to net-share-settle vesting restricted stock units. The board has also approved an increase of 11 percent to the company’s quarterly dividend, and has declared a dividend of $0.52 per share, payable on May 14, 2015, to shareholders of record as of the close of business on May 11. “We believe Apple has a bright future ahead, and the unprecedented size of our capital return program reflects that strong confidence,” said Tim Cook, Apple’s CEO. “While most of our program will focus on buying back shares, we know that the dividend is very important to many of our investors, so we’re raising it for the third time in less than three years.”
                    [news_keywords] => 123456
                    [user_id] => 1
                    [user_name] => Admin
                    [user_status] => 0
                    [user_avatar] =>
                    [user_level] => -103
                    [news_date] => 1439522246
                    [cat_id] => 0
                    [cat_name] =>
                    [cat_image] => <a href='../../infusions/news/news.php?readmore=5'><img class='img-responsive' src='../../infusions/news/images/thumbs/texting-phone-woman_1_t1.jpg' alt='Apple Announces New Environmental Initiatives in China' />
</a>
                    [news_image] => <a class='img-link' href='
					../../infusions/news/news.php?readmore=5
					'><img class='img-responsive' src='../../infusions/news/images/thumbs/texting-phone-woman_1_t1.jpg' alt='Apple Announces New Environmental Initiatives in China' />
</a>

                    [news_image_src] => ../../infusions/news/images/texting-phone-woman_1.jpg
                    [news_ext] => n
                    [news_reads] => 4
                    [news_comments] => 0
                    [news_sum_rating] => 0
                    [news_count_votes] => 0
                    [news_allow_comments] => 0
                    [news_allow_ratings] => 0
                    [news_sticky] => 0
                )

        )

)
		 */


		echo render_breadcrumbs();
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-9 overflow-hide'>\n";
		if (isset($_GET['readmore'])) {
			echo display_blog_item($info); // change this integration
		} else {
			echo display_blog_index($info);
		}
		echo "</div><div class='col-xs-12 col-sm-3'>\n";
		echo display_blog_menu($info);
		echo "</div>\n";
		echo "</div>\n";
	}
}

if (!function_exists('display_blog_item')) {
	function display_blog_item($info) {
		global $locale;
		ob_start();
		$data = $info['blog_item'];
		if ($data['admin_link']) {
			$admin_actions = $data['admin_link'];
			echo "<div class='btn-group pull-right'>\n";
			echo "<a class='btn btn-default btn-sm' href='".$admin_actions['edit']."'>".$locale['edit']."</a>\n";
			echo "<a class='btn btn-default btn-sm' href='".$admin_actions['delete']."'>".$locale['delete']."</a>\n";
			echo "</div>\n";
		}
		echo "<h2 class='strong m-t-0 m-b-20'>".$data['blog_subject']."</h2>";
		echo "<div class='m-b-20'>".$data['blog_post_author']." ".$data['blog_post_time']." ".$data['blog_post_cat']."</div>\n";
		echo "<div class='clearfix m-b-20'>\n";
		if ($data['blog_image']) {
			echo "<div class='m-10 m-l-0 ".$data['blog_ialign']."'>".$data['blog_thumb_2']."</div>";
		}
		echo $data['blog_extended'];
		echo "</div>\n";
		if ($info['blog_nav']) {
			echo "<div class='clearfix m-b-20'><div class='pull-right'>";
			echo $info['blog_nav'];
			echo "</div>\n</div>\n";
		}
		echo "<div class='m-b-20 well'>".$data['blog_author_info']."</div>";
		if ($data['blog_allow_comments']) {
			showcomments("B", DB_BLOG, "blog_id", $_GET['readmore'], INFUSIONS."blog/blog.php?readmore=".$_GET['readmore']);
		}

		if ($data['blog_allow_ratings']) {
			showratings("B", $_GET['readmore'], INFUSIONS."blog/blog.php?readmore=".$_GET['readmore']);
		}
		$str = ob_get_contents();
		ob_end_clean();
		return $str;
	}
}

if (!function_exists('display_blog_index')) {
	function display_blog_index($info) {
		global $locale;
		ob_start();
		if (!empty($info['blog_item'])) {
			foreach($info['blog_item'] as $blog_id => $data) {
				echo "
					<div class='clearfix m-b-20'>
						<div class='row'>
							<div class='col-xs-12 col-sm-4'>
								<div class='pull-left m-r-5'>".$data['blog_user_avatar']."</div>
								<div class='overflow-hide'>
									".$data['blog_user_link']." <br/>
									<span class='m-r-10 text-lighter'><i class='fa fa-comment-o fa-fw'></i> ".$data['blog_comments']."</span><br/>
									<span class='m-r-10 text-lighter'><i class='fa fa-star-o fa-fw'></i> ".$data['blog_count_votes']."</span><br/>
									<span class='m-r-10 text-lighter'><i class='fa fa-eye fa-fw'></i> ".$data['blog_reads']."</span><br/>
								</div>
							</div>
							<div class='col-xs-12 col-sm-8'>
								<h2 class='strong m-b-20 m-t-0'><a class='text-dark' href='".$data['blog_link']."'>".$data['blog_subject']."</a></h2>
								<i class='fa fa-clock-o m-r-5'></i> ".$locale['global_049']." ".timer($data['blog_datestamp'])." ".$locale['in']." ".$data['blog_category_link']."
								".($data['blog_cat_image'] ? "<div class='blog-image m-10 ".$data['blog_ialign']."'>".$data['blog_cat_image']."</div>" : '')."
								<div class='m-t-20'>".$data['blog_blog']."<br/>".$data['blog_readmore_link']."</div>
							</div>
						</div>
						<hr>
					</div>
				";
			}
		} else {
			echo "<div class='well text-center'>".$locale['blog_3000']."</div>\n";
		}
		
		$str = ob_get_contents();
		ob_end_clean();
		return $str;
	}
}

/**
 * Recursive Menu Generator
 * @param     $info
 * @param int $cat_id
 * @param int $level
 * @return string
 */

if (!function_exists('blog_cat_menu')) {
	function blog_cat_menu($info, $cat_id = 0, $level = 0) {
		$html = '';
		if (!empty($info[$cat_id])) {
			foreach($info[$cat_id] as $blog_cat_id => $cdata) {
				$active = ($blog_cat_id == $_GET['cat_id'] && $_GET['cat_id'] !=='') ? 1 : 0;
				$html .= "<li ".($active ? "class='active strong'" : '')." >".str_repeat('&nbsp;', $level)." ".$cdata['blog_cat_link']."</li>\n";
				if ($active && $blog_cat_id !=0) {
					if (!empty($info[$blog_cat_id])) {
						$html .= blog_cat_menu($info, $blog_cat_id, $level++);
					}
				}
			}
		}
		return $html;
	}
}

if (!function_exists('display_blog_menu')) {
	function display_blog_menu($info) {
		global $locale;
		ob_start();
		echo "<ul class='m-b-40'>\n";
		foreach($info['blog_filter'] as $filter_key => $filter) {
			echo "<li ".(isset($_GET['type']) && $_GET['type'] == $filter_key ? "class='active strong'" : '')." ><a href='".$filter['link']."'>".$filter['title']."</a></li>\n";
		}
		echo "</ul>\n";

		echo "<div class='text-bigger strong text-dark m-b-20 m-t-20'><i class='fa fa-list m-r-10'></i> ".$locale['blog_1003']."</div>\n";
		echo "<ul class='m-b-40'>\n";
		$blog_cat_menu = blog_cat_menu($info['blog_categories']);
		if (!empty($blog_cat_menu)) {
			echo $blog_cat_menu;
		} else {
			echo "<li>".$locale['blog_3001']."</li>\n";
		}
		echo "</ul>\n";

		echo "<div class='text-bigger strong text-dark m-t-20 m-b-20'><i class='fa fa-calendar m-r-10'></i> ".$locale['blog_1004']."</div>\n";
		echo "<ul class='m-b-40'>\n";
		if (!empty($info['blog_archive'])) {
			$current_year = 0;
			foreach($info['blog_archive'] as $year => $archive_data) {
				if ($current_year !== $year) {
					echo "<li class='text-dark strong'>".$year."</li>\n";
				}
				if (!empty($archive_data)) {
					foreach($archive_data as $month => $a_data) {
						echo "<li ".($a_data['active'] ? "class='active strong'" : '').">
						<a href='".$a_data['link']."'>".$a_data['title']."</a> <span class='badge m-l-10'>".$a_data['count']."</span>
						</li>\n";
					}
				}
				$current_year = $year;
			}
		} else {
			echo "<li>".$locale['blog_3002']."</li>\n";
		}
		echo "</ul>\n";

		echo "<div class='text-bigger strong text-dark m-t-20 m-b-20'><i class='fa fa-users m-r-10'></i> ".$locale['blog_1005']."</div>\n";
		echo "<ul class='m-b-40'>\n";
		if (!empty($info['blog_author'])) {
			foreach($info['blog_author'] as $author_id => $author_info) {
				echo "<li ".($author_info['active'] ? "class='active strong'" : '').">
					<a href='".$author_info['link']."'>".$author_info['title']."</a> <span class='badge m-l-10'>".$author_info['count']."</span>
					</li>\n";
			}
		} else {
			echo "<li>".$locale['blog_3003']."</li>\n";
		}
		echo "</ul>\n";
		$str = ob_get_contents();
		ob_end_clean();
		return $str;
	}
}
