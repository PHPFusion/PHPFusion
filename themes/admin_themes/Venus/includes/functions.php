<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: functions.php
| Author: PHP-Fusion Inc.
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery.cookie.js'></script>");

// Dashboard template
function render_admin_dashboard() {
    if (isset($_GET['os']) or (isset($_GET['pagenum']) && $_GET['pagenum']) > 0) {
        render_admin_icons();
    } else {
        render_dashboard();
    }
}

function render_dashboard() {
    global $locale, $members, $forum, $download, $news, $blog, $articles, $weblinks, $photos, $global_comments, $global_ratings, $global_submissions, $link_type, $submit_data, $submit_type, $comments_type, $infusions_count, $global_infusions, $aidlink, $settings;

    $mobile = '12';
    $tablet = '6';
    $laptop = '6';
    $desktop = '3';
	
	// comments, ratings, submission types
	$comments_type = array(
		'N' => $locale['269'],
		'D' => $locale['268'],
		'P' => $locale['272'],
		'A' => $locale['270'],
		'B' => $locale['269b'],
		'C' => $locale['272a'],
		'PH' => $locale['261'],
	);
	
	$submit_type = array(
		'n' => $locale['269'],
		'd' => $locale['268'],
		'p' => $locale['272'],
		'a' => $locale['270'],
		'l' => $locale['271'],
		'b' => $locale['269b'],
	);
	
	$link_type = array(
		'N' => $settings['siteurl']."news.php?readmore=%s",
		'D' => $settings['siteurl']."downloads.php?download_id=%s",
		'P' => $settings['siteurl']."photogallery.php?photo_id=%s",
		'A' => $settings['siteurl']."articles.php?article_id=%s",
		'B' => $settings['siteurl']."blog.php?readmore=%s",
		'C' => $settings['siteurl']."viewpage.php?page_id=%s",
		'PH' => $settings['siteurl']."photogallery.php?photo_id=%s",
	);

    opentable($locale['250']);

        $panels = [
            'registered'   => ['link' => '', 'title' => 251],
            'cancelled'    => ['link' => 'status=5', 'title' => 263],
            'unactivated'  => ['link' => 'status=2', 'title' => 252],
            'security_ban' => ['link' => 'status=4', 'title' => 253]
        ];

        echo "<!--Start Members-->\n";
        echo "<div class='row' id='members'>\n";
            foreach ($panels as $panel => $block) {
                $block['link'] = empty($block['link']) ? $block['link'] : '&amp;'.$block['link'];
                echo "<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
                openside();
                    echo "<img class='pull-left m-r-10 dashboard-icon' src='".get_image('ac_Members')."' alt='".$locale['M']."'/>\n";
                    echo "<h4 class='text-right m-t-0 m-b-0'>".number_format($members[$panel])."</h4>\n";
                    echo "<span class='m-t-10 text-uppercase text-lighter text-smaller pull-right'><strong>".$locale[$block['title']]."</strong></span>\n";
                    $content_ = "<div class='text-right text-uppercase'>\n";
                    $content_ .= "<a class='text-smaller' href='".ADMIN."members.php".$aidlink.$block['link']."'>".$locale['255']." <i class='entypo right-open-mini'></i></a>\n";
                    $content_ .= "</div>\n";
                closeside(checkrights('M') ? $content_ : '');
                echo "</div>\n";
            }
        echo "</div>\n";
        echo "<!--End Members-->\n";
		
		$desktop = '4';
		
        echo "<div class='row' id='overview'>\n";
        echo "<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
		
                openside("", "well");
                    echo "<strong class='text-smaller text-uppercase'>".$locale['265']." ".$locale['258']."</strong>\n";
                    echo "<div class='clearfix m-t-10'>\n";
                        echo "<img class='img-responsive pull-right dashboard-icon' src='".get_image("ac_Forums")."'/>\n";
                        echo "<div class='pull-left display-inline-block m-r-10'>\n";
                            echo "<span class='text-smaller'>".$locale['265']."</span>\n<br/>\n";
                            echo "<h4 class='m-t-0'>".number_format($forum['count'])."</h4>\n";
                        echo "</div>\n";
                        echo "<div class='pull-left display-inline-block m-r-10'>\n";
                            echo "<span class='text-smaller'>".$locale['256']."</span>\n<br/>\n";
                            echo "<h4 class='m-t-0'>".number_format($forum['thread'])."</h4>\n";
                        echo "</div>\n";
                        echo "<div class='pull-left display-inline-block m-r-10'>\n";
                            echo "<span class='text-smaller'>".$locale['259']."</span>\n<br/>\n";
                            echo "<h4 class='m-t-0'>".number_format($forum['post'])."</h4>\n";
                        echo "</div>\n";
                        echo "<div class='pull-left display-inline-block m-r-10'>\n";
                            echo "<span class='text-smaller'>".$locale['260']."</span>\n<br/>\n";
                            echo "<h4 class='m-t-0'>".($forum['users'])."</h4>\n";
                        echo "</div>\n";
                    echo "</div>\n";
                closeside();
                echo "</div>\n";

                echo "<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
                openside("", "well");
                    echo "<strong class='text-smaller text-uppercase'>".$locale['269']." ".$locale['258']."</strong>\n";
                    echo "<div class='clearfix m-t-10'>\n";
                        echo "<img class='img-responsive pull-right dashboard-icon' src='".get_image("ac_News")."'/>\n";
                        echo "<div class='pull-left display-inline-block m-r-10'>\n";
                            echo "<span class='text-smaller'>".$locale['269']."</span>\n<br/>\n";
                            echo "<h4 class='m-t-0'>".number_format($news['news'])."</h4>\n";
                        echo "</div>\n";
                        echo "<div class='pull-left display-inline-block m-r-10'>\n";
                            echo "<span class='text-smaller'>".$locale['257']."</span>\n<br/>\n";
                            echo "<h4 class='m-t-0'>".number_format($news['comment'])."</h4>\n";
                        echo "</div>\n";
                        echo "<div class='pull-left display-inline-block m-r-10'>\n";
                            echo "<span class='text-smaller'>".$locale['254']."</span>\n<br/>\n";
                            echo "<h4 class='m-t-0'>".number_format($news['submit'])."</h4>\n";
                        echo "</div>\n";
                    echo "</div>\n";
                closeside();
                echo "</div>\n";
				
			   echo "<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
                openside("", "well");
                    echo "<strong class='text-smaller text-uppercase'>".$locale['BLOG']." ".$locale['258']."</strong>\n";
                    echo "<div class='clearfix m-t-10'>\n";
                        echo "<img class='img-responsive pull-right dashboard-icon' src='".get_image("ac_Blog")."'/>\n";
                        echo "<div class='pull-left display-inline-block m-r-10'>\n";
                            echo "<span class='text-smaller'>".$locale['269b']."</span>\n<br/>\n";
                            echo "<h4 class='m-t-0'>".number_format($blog['blog'])."</h4>\n";
                        echo "</div>\n";
                        echo "<div class='pull-left display-inline-block m-r-10'>\n";
                            echo "<span class='text-smaller'>".$locale['257']."</span>\n<br/>\n";
                            echo "<h4 class='m-t-0'>".number_format($blog['comment'])."</h4>\n";
                        echo "</div>\n";
                        echo "<div class='pull-left display-inline-block m-r-10'>\n";
                            echo "<span class='text-smaller'>".$locale['254']."</span>\n<br/>\n";
                            echo "<h4 class='m-t-0'>".number_format($blog['submit'])."</h4>\n";
                        echo "</div>\n";
                    echo "</div>\n";
                closeside();
                echo "</div>\n";
			
			$desktop = '3';
					
		         echo "<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
                openside("", "well");
                    echo "<strong class='text-smaller text-uppercase'>".$locale['268']." ".$locale['258']."</strong>\n";
                    echo "<div class='clearfix m-t-10'>\n";
                        echo "<img class='img-responsive pull-right dashboard-icon' src='".get_image("ac_Downloads")."'/>\n";
                        echo "<div class='pull-left display-inline-block m-r-10'>\n";
                            echo "<span class='text-smaller'>".$locale['268']."</span>\n<br/>\n";
                            echo "<h4 class='m-t-0'>".number_format($download['download'])."</h4>\n";
                        echo "</div>\n";
                        echo "<div class='pull-left display-inline-block m-r-10'>\n";
                            echo "<span class='text-smaller'>".$locale['257']."</span>\n<br/>\n";
                            echo "<h4 class='m-t-0'>".number_format($download['comment'])."</h4>\n";
                        echo "</div>\n";
                        echo "<div class='pull-left display-inline-block m-r-10'>\n";
                            echo "<span class='text-smaller'>".$locale['254']."</span>\n<br/>\n";
                            echo "<h4 class='m-t-0'>".number_format($download['submit'])."</h4>\n";
                        echo "</div>\n";
                    echo "</div>\n";
                closeside();
                echo "</div>\n";
				
                echo "<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
                openside("", "well");
                    echo "<strong class='text-smaller text-uppercase'>".$locale['270']." ".$locale['258']."</strong>\n";
                    echo "<div class='clearfix m-t-10'>\n";
                        echo "<img class='img-responsive pull-right dashboard-icon' src='".get_image("ac_Articles")."'/>\n";
                        echo "<div class='pull-left display-inline-block m-r-10'>\n";
                            echo "<span class='text-smaller'>".$locale['270']."</span>\n<br/>\n";
                            echo "<h4 class='m-t-0'>".number_format($articles['article'])."</h4>\n";
                        echo "</div>\n";
                        echo "<div class='pull-left display-inline-block m-r-10'>\n";
                            echo "<span class='text-smaller'>".$locale['257']."</span>\n<br/>\n";
                            echo "<h4 class='m-t-0'>".number_format($articles['comment'])."</h4>\n";
                        echo "</div>\n";
                        echo "<div class='pull-left display-inline-block m-r-10'>\n";
                            echo "<span class='text-smaller'>".$locale['254']."</span>\n<br/>\n";
                            echo "<h4 class='m-t-0'>".number_format($articles['submit'])."</h4>\n";
                        echo "</div>\n";
                    echo "</div>\n";
                closeside();
                echo "</div>\n";


                echo "<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
                 openside("", "well");
                    echo "<strong class='text-smaller text-uppercase'>".$locale['271']." ".$locale['258']."</strong>\n";
                    echo "<div class='clearfix m-t-10'>\n";
                        echo "<img class='img-responsive pull-right dashboard-icon' src='".get_image("ac_Web Links")."'/>\n";
                        echo "<div class='pull-left display-inline-block m-r-10'>\n";
                            echo "<span class='text-smaller'>".$locale['271']."</span>\n<br/>\n";
                            echo "<h4 class='m-t-0'>".number_format($weblinks['weblink'])."</h4>\n";
                        echo "</div>\n";
                        echo "<div class='pull-left display-inline-block m-r-10'>\n";
                            echo "<span class='text-smaller'>".$locale['254']."</span>\n<br/>\n";
                            echo "<h4 class='m-t-0'>".number_format($weblinks['submit'])."</h4>\n";
                        echo "</div>\n";
                    echo "</div>\n";
                closeside();
                echo "</div>";

                echo "<div class='col-xs-$mobile col-sm-$tablet col-md-$laptop col-lg-$desktop'>\n";
                openside("", "well");
                    echo "<strong class='text-smaller text-uppercase'>".$locale['272']." ".$locale['258']."</strong>\n";
                    echo "<div class='clearfix m-t-10'>\n";
                        echo "<img class='img-responsive pull-right dashboard-icon' src='".get_image("ac_Photo Albums")."'/>\n";
                        echo "<div class='pull-left display-inline-block m-r-10'>\n";
                            echo "<span class='text-smaller'>".$locale['261']."</span>\n<br/>\n";
                            echo "<h4 class='m-t-0'>".number_format($photos['photo'])."</h4>\n";
                        echo "</div>\n";
                        echo "<div class='pull-left display-inline-block m-r-10'>\n";
                            echo "<span class='text-smaller'>".$locale['257']."</span>\n<br/>\n";
                            echo "<h4 class='m-t-0'>".number_format($photos['comment'])."</h4>\n";
                        echo "</div>\n";
                        echo "<div class='pull-left display-inline-block m-r-10'>\n";
                            echo "<span class='text-smaller'>".$locale['254']."</span>\n<br/>\n";
                            echo "<h4 class='m-t-0'>".number_format($photos['submit'])."</h4>\n";
                        echo "</div>\n";
                    echo "</div>\n";
                closeside();
                echo "</div>\n";

        echo "</div>\n";

        echo "<div class='row'>\n";
            echo "<div class='col-xs-12 co-sm-6 col-md-6 col-lg-3'>\n";
                openside("<strong class='text-smaller text-uppercase'>".$locale['277']."</strong><span class='pull-right badge'>".number_format($global_comments['rows'])."</span>");
                if (count($global_comments['data']) > 0) {
                    foreach ($global_comments['data'] as $i => $comment_data) {
                        echo "<!--Start Comment Item-->\n";
                        echo "<div data-id='$i' class='comment_content clearfix p-t-10 p-b-10' ".($i > 0 ? "style='border-top:1px solid #ddd;'" : '')." >\n";
                        echo "<div class='pull-left display-inline-block' style='margin-top:5px; margin-bottom:10px;'>".display_avatar($comment_data, "27px", "", FALSE, "img-rounded m-r-5")."</div>\n";
                        echo "<div id='comment_action-$i' class='btn-group pull-right' style='position:absolute; right: 30px; margin-top:25px;'>\n
                            <a class='btn btn-xs btn-default' title='".$locale['274']."' href='".ADMIN."comments.php".$aidlink."&amp;ctype=".$comment_data['comment_type']."&amp;comment_item_id=".$comment_data['comment_item_id']."'><i class='entypo eye'></i></a>
                            <a class='btn btn-xs btn-default' title='".$locale['275']."' href='".ADMIN."comments.php".$aidlink."&amp;action=edit&amp;comment_id=".$comment_data['comment_id']."&amp;ctype=".$comment_data['comment_type']."&amp;comment_item_id=".$comment_data['comment_item_id']."'><i class='entypo pencil'></i></a>
                            <a class='btn btn-xs btn-default' title='".$locale['276']."' href='".ADMIN."comments.php".$aidlink."&amp;action=delete&amp;comment_id=".$comment_data['comment_id']."&amp;ctype=".$comment_data['comment_type']."&amp;comment_item_id=".$comment_data['comment_item_id']."'><i class='entypo trash'></i></a></div>\n";
                        echo "<strong>".(!empty($comment_data['user_id']) ? profile_link($comment_data['user_id'], $comment_data['user_name'], $comment_data['user_status']) : $comment_data['comment_name'])." </strong>\n";
                        echo "<span class='text-lighter'>".$locale['273']."</span> <a href='".sprintf($link_type[$comment_data['comment_type']], $comment_data['comment_item_id'])."'><strong>".$comments_type[$comment_data['comment_type']]."</strong></a>";
                        echo "<br/>\n".timer($comment_data['comment_datestamp'])."<br/>\n";
                        echo "<span class='text-smaller text-lighter'>".trimlink(parseubb($comment_data['comment_message']), 70)."</span>\n";
                        echo "</div>\n";
                        echo "<!--End Comment Item-->\n";
                    }
                    if (isset($global_comments['comments_nav'])) {
                        echo "<div class='clearfix'>\n";
                        echo "<span class='pull-right text-smaller'>".$global_comments['comments_nav']."</span>";
                        echo "</div>\n";
                    }
                } else {
                    echo "<div class='text-center'>".$global_comments['nodata']."</div>\n";
                }
                closeside();
            echo "</div>\n";

            echo "<div class='col-xs-12 co-sm-6 col-md-6 col-lg-3'>\n";
                openside("<strong class='text-smaller text-uppercase'>".$locale['278']."</strong><span class='pull-right badge'>".number_format($global_ratings['rows'])."</span>");
                if (count($global_ratings['data']) > 0) {
                    foreach ($global_ratings['data'] as $i => $ratings_data) {
                        echo "<!--Start Rating Item-->\n";
                        echo "<div class='comment_content clearfix p-t-10 p-b-10' ".($i > 0 ? "style='border-top:1px solid #ddd;'" : '')." >\n";
                        echo "<div class='pull-left display-inline-block' style='margin-top:5px; margin-bottom:10px;'>".display_avatar($ratings_data, "25px", "", FALSE, "img-rounded m-r-5")."</div>\n";
                        echo "<strong>".profile_link($ratings_data['user_id'], $ratings_data['user_name'], $ratings_data['user_status'])." </strong>\n";
                        echo "<span class='text-lighter'>".$locale['273a']." </span>\n";
                        echo "<a href='".sprintf($link_type[$ratings_data['rating_type']], $ratings_data['rating_item_id'])."'><strong>".$comments_type[$ratings_data['rating_type']]."</strong></a>";
                        echo "<span class='text-lighter m-l-10'>".str_repeat("<i class='entypo star'></i>", $ratings_data['rating_vote'])."</span>\n<br/>";
                        echo timer($ratings_data['rating_datestamp'])."<br/>\n";
                        echo "</div>\n";
                        echo "<!--End Rating Item-->\n";
                    }
                    if (isset($global_ratings['ratings_nav'])) {
                        echo "<div class='clearfix'>\n";
                            echo "<span class='pull-right text-smaller'>".$global_ratings['ratings_nav']."</span>";
                        echo "</div>\n";
                    }
                } else {
                    echo "<div class='text-center'>".$global_ratings['nodata']."</div>\n";
                }
                closeside();
            echo "</div>\n";

            echo "<div class='col-xs-12 co-sm-6 col-md-6 col-lg-3'>\n";
                openside("<strong class='text-smaller text-uppercase'>".$locale['279']."</strong><span class='pull-right badge'>".number_format($global_submissions['rows'])."</span>");
	if (count($global_submissions['data']) > 0) {
		foreach ($global_submissions['data'] as $i => $submit_data) {
			echo "<!--Start Submissions Item-->\n";
			echo "<div data-id='$i' class='submission_content clearfix p-t-10 p-b-10' ".($i > 0 ? "style='border-top:1px solid #ddd;'" : '')." >\n";
			echo "<div class='pull-left m-r-10 display-inline-block' style='margin-top:0px; margin-bottom:10px;'>".display_avatar($submit_data, '40px')."</div>\n";
			echo "<div class='btn-group pull-right' style='position:absolute; right: 30px; margin-top:35px;'>\n
				<a class='btn btn-xs btn-default' title='".$locale['286']."' href='".ADMIN."submissions.php".$aidlink."&amp;action=2&amp;t=".$submit_data['submit_type']."&amp;submit_id=".$submit_data['submit_id']."'><i class='entypo eye'></i></a>
				<a class='btn btn-xs btn-default' title='".$locale['287']."' href='".ADMIN."submissions.php".$aidlink."&amp;delete=".$submit_data['submit_id']."'><i class='entypo trash'></i></a></div>\n";
			echo "<strong>".profile_link($submit_data['user_id'], $submit_data['user_name'], $submit_data['user_status'])."</strong>\n";
			echo "<span class='text-smaller text-lighter'>".$locale['273b']." <strong>".$submit_type[$submit_data['submit_type']]."</strong></span>";
			echo "&nbsp;<span class='text-smaller'>".timer($submit_data['submit_datestamp'])."</span><br/>\n";
			echo "</div>\n";
			echo "<!--End Submissions Item-->\n";
		}
		if (isset($global_submissions['submissions_nav'])) {
			echo "<div class='clearfix'>\n";
			echo "<span class='pull-right text-smaller'>".$global_submissions['submissions_nav']."</span>";
			echo "</div>\n";
		}
	} else {
		echo "<div class='text-center'>".$global_submissions['nodata']."</div>\n";
	}
                closeside();
            echo "</div>\n";

            echo "<div class='col-xs-12 co-sm-6 col-md-6 col-lg-3'>\n";
                openside("<strong class='text-smaller text-uppercase'>".$locale['283']."</strong><span class='pull-right badge'>".number_format((int)$infusions_count)."</span>");
                $content = '';
                if ($infusions_count > 0) {
                    echo "<div class='comment_content'>\n";
                    if (!empty($global_infusions)) {
                        foreach ($global_infusions as $inf_id => $inf_data) {
                            echo "<span class='badge m-b-10 m-r-5'>".$inf_data['inf_title']."</span>\n";
                        }
                    }
                    echo "</div>\n";
                    $content = checkrights("I") ? "<div class='text-right text-uppercase'>\n<a class='text-smaller' href='".ADMIN."infusions.php".$aidlink."'>".$locale['285']."</a> <i class='entypo right-open-mini'></i></div>\n" : '';
                } else {
                    echo "<div class='text-center'>".$locale['284']."</div>\n";
                }
                closeside($content);
            echo "</div>\n";
        echo "</div>\n"; // .row
    closetable();
}

function render_admin_icons() {
    global $admin_icons, $admin_images, $locale, $aidlink, $settings;

    $admin_title = str_replace("[SITENAME]", $settings['sitename'], $locale['200']);
    opentable($admin_title);
	
    echo "<div class='row'>\n";
    if (count($admin_icons['data']) > 0) {
        foreach ($admin_icons['data'] as $i => $data) {
            echo "<div class='icon-wrapper col-xs-6 col-sm-3 col-md-2 col-lg-2' style='height: 135px;'>\n";
            if ($admin_images) {
                echo "<div class='icon-container'>\n";
                echo "<a href='".$data['admin_link'].$aidlink."'><img src='".(file_exists(ADMIN."images/".$data['admin_image']) ? ADMIN."images/".$data['admin_image'] :  ADMIN."images/notfound.png")."' alt='".$data['admin_title']."'/>\n</a>\n";
                
				echo "<div class='overflow-hide'>\n";
                echo "<a class='icon-title' href='".$data['admin_link'].$aidlink."'>".$data['admin_title']."</a>\n";
                echo "</div>\n";
                echo "</div>\n";
            } else {
                echo "<span class='small'>".THEME_BULLET." <a href='".$data['admin_link'].$aidlink."'>".$data['admin_title']."</a></span>";
            }
            echo "</div>\n";
        }
    }
    echo "</div>\n";
    closetable();
}


function admin_nav($style=false) {
	global $aidlink, $locale, $pages;
	$admin_icon = array(
		'0' => 'entypo gauge',
		'1' => 'entypo docs',
		'2' => 'entypo user',
		'3' => 'entypo drive',
		'4' => 'entypo cog',
		'5' => 'entypo magnet'
	);

	if (!$style) {
		// horizontal navigation with dropdown menu.
		
		$html = "<ul class='admin-horizontal-link'>\n";
		for ($i = 0; $i < 6; $i++) {
			$active = (isset($_GET['pagenum']) && $_GET['pagenum'] == $i || !isset($_GET['pagenum']) && admin_active() == $i) ? 1 : 0;
            $html .= "<li class='".($active ? 'active panel' : 'panel')."' >\n";
			$html .= "<li><a href='".ADMIN.$aidlink."&amp;pagenum=$i' alt='".$locale['ac0'.$i]."'><i class='".$admin_icon[$i]."'></i> <span class='hidden-xs hidden-sm hidden-md'>".$locale['ac0'.$i]."</a></span></li>\n";
		}
		$html .= "</ul>\n";
	} else {
		$html = "<ul id='adl' class='admin-vertical-link'>\n";
		for ($i = 0; $i < 6; $i++) {
			$result = dbquery("SELECT * FROM ".DB_ADMIN." WHERE admin_page='".$i."' AND admin_link !='reserved' ORDER BY admin_title ASC");
			$active = (isset($_GET['pagenum']) && $_GET['pagenum'] == $i || !isset($_GET['pagenum']) && admin_active() == $i) ? 1 : 0;

			$html .= "<li class='".($active ? 'active panel' : 'panel')."' >\n";
			if ($i == 0) {
				$html .= "<a class='adl-link' href='".ADMIN."index.php".$aidlink."&amp;pagenum=0'><i class='".$admin_icon[$i]."'></i> ".$locale['ac0'.$i]." ".($i > 0 ? "<span class='adl-drop pull-right'></span>" : '')."</a>\n";
			} else {
				$html .= "<a class='adl-link ".($active ? '' : 'collapsed')."' data-parent='#adl' data-toggle='collapse' href='#adl-$i'><i class='".$admin_icon[$i]."'></i> ".$locale['ac0'.$i]." ".($i > 0 ? "<span class='adl-drop pull-right'></span>" : '')."</a>\n";
				$html .= "<div id='adl-$i' class='collapse ".($active ? 'in' : '')."'>\n";
				if (dbrows($result)>0) {
					$html .= "<ul class='admin-submenu'>\n";
					while ($data = dbarray($result)) {
						$secondary_active = FUSION_SELF == $data['admin_link'] ? 'active' : '';
						$html .= checkrights($data['admin_rights']) ? "<li ".($secondary_active ? "class='active'" : '')."><a href='".ADMIN.$data['admin_link'].$aidlink."'> <img style='max-width:30px;' class='pull-right m-l-10' src='".(file_exists(ADMIN."images/".$data['admin_image']) ? ADMIN."images/".$data['admin_image'] :  ADMIN."images/notfound.png")."'/><div style='margin-top: 7px;'> ".$data['admin_title']."</div></a></li>\n" : '';
					}
					$html .= "</ul>\n";
				}
				$html .= "</div>\n";
				$html .= "</li>\n";
			}
		}
		$html .= "</ul>\n";
	}
	return $html;
}

function openside($title = FALSE, $class = FALSE) {
	echo "<div class='panel panel-default $class'>\n";
	echo ($title) ? "<div class='panel-heading'>$title</div>\n" : '';
	echo "<div class='panel-body'>\n";
}
function closeside($title = FALSE) {
	echo "</div>\n";
	echo ($title) ? "<div class='panel-footer'>$title</div>\n" : '';
	echo "</div>\n";
}

function opentable($title) {
	echo "<div class='panel panel-default box-shadow' style='border:none;'>\n<div class='panel-body'>\n";
	echo "<h3 class='m-b-20'>".$title."</h3>\n";
}

function closetable() {
	echo "</div>\n</div>\n";
}

function admin_active() {
	$pages = array(1 => FALSE, 2 => FALSE, 3 => FALSE, 4 => FALSE, 5 => FALSE);
	$index_link = FALSE;

	$result = dbquery("SELECT admin_title, admin_page, admin_rights, admin_link FROM ".DB_ADMIN." ORDER BY admin_page DESC, admin_title ASC");
	$rows = dbrows($result);
	$admin_url = array();
	while ($data = dbarray($result)) {
		if ($data['admin_link'] != "reserved" && checkrights($data['admin_rights'])) {
		$admin_pages[$data['admin_page']][$data['admin_title']] = $data['admin_link'];
		}
	}
	
	foreach($admin_pages as $key =>$data) {
		if (in_array(FUSION_SELF, $data)) {
			return $key;
		}
	}
	return '0';
}

function render_admin_login() {
    global $locale, $aidlink, $userdata, $settings;

    echo "<section class='login-bg'>\n";
    echo "<aside class='block-container'>\n";
    echo "<div class='block'>\n";
    echo "<div class='block-content clearfix' style='font-size:13px;'>\n";
    echo "<h6><strong>".$locale['280']."</strong></h6>\n";
    echo "<img src='".IMAGES."php-fusion-icon.png' class='pf-logo position-absolute' alt='PHP-Fusion'/>";
    echo "<p class='fusion-version text-right mid-opacity text-smaller'>".$locale['version'].$settings['version']."</p>";
    echo "<div class='row m-0'>\n<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>";

	$admin_password = '';
	$login_error = "";
	$form_action = "";

	add_to_head("<link rel='stylesheet' href='".THEMES."admin_themes/Venus/admin_login.css' type='text/css' />");
	echo "<aside class='block-container'>\n";
	echo "<div class='block'>\n";
	echo "<div class='block-content clearfix' style='font-size:14px;'>\n";
	echo "<h6><strong>".$locale['280']."</strong></h6>\n";
	echo "<img class='pf-logo' src='".IMAGES."php-fusion-icon.png' class='position-absolute'/>";
	echo "<div class='row m-0'>\n<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>";
	
	$form_action = FUSION_SELF.$aidlink == ADMIN."index.php".$aidlink ? FUSION_SELF.$aidlink."&amp;pagenum=0" : FUSION_SELF."?".FUSION_QUERY;
	echo "<form name='admin-login-form' method='post' action='".$form_action."'>\n";
	openside('');
		echo "<div class='m-t-10 clearfix row'>\n";
		echo "<div class='col-xs-3 col-sm-3 col-md-3 col-lg-3'>\n";
		echo "<div class='pull-right'>\n";
		echo display_avatar($userdata, '90px');
		echo "</div>\n";
		echo "</div>\n<div class='col-xs-9 col-sm-9 col-md-8 col-lg-7'>\n";
		echo "<h5><strong>".$locale['welcome'].", ".(ucwords($userdata['user_name']))."</strong><br/>".getuserlevel($userdata['user_level'])."</h5>";
		echo "<div class='clearfix'>\n";
		echo "".$locale['281']." : <input type='text' class='textbox' value='' name='admin_password' />\n";
		echo "</div>\n";
		echo "</div>\n";
		echo "</div>\n";
	closeside();
	echo "<input type='submit' class='btn-primary btn-block' value='".$locale['login']."' name='admin_login' />";
	echo "</form>\n";

    echo "</form></div>\n</div>\n"; // .col-*, .row
    echo "</div>\n"; // .block-content
    echo "</div>\n"; // .block
    echo "<div class='copyright-note clearfix m-t-10'>".showcopyright()."</div>\n";
    echo "</aside>\n";
    echo "</section>\n";
}

function render_admin_panel() {
	global $locale, $userdata, $defender, $pages, $aidlink, $settings, $enabled_languages, $_errorHandler;

	$admin_password = '';
	$login_error = "";
	
	if (!check_admin_pass($admin_password) && !stristr($_SERVER['PHP_SELF'], $settings['site_path']."infusions")) {
		
		render_admin_login();
		
	} else {
		echo "<div id='admin-panel' ".(isset($_COOKIE['Venus']) && $_COOKIE['Venus'] ? "class='in'" : '')." >\n";
		include THEMES."admin_themes/Venus/includes/header.php";
		echo "<!-- begin leftnav -->\n";
		echo "<div id='acp-left' class='pull-left off-canvas affix ".(isset($_COOKIE['Venus']) && $_COOKIE['Venus'] ? 'in' : '')."'  style='width:250px; height:100%;'>\n"; // collapse to top menu on sm and xs
		echo "<div class='panel panel-default admin' style='border:0px; box-shadow: none;'><div class='panel-body clearfix'>\n";
		echo "<div class='pull-left m-r-5'>\n".display_avatar($userdata, '50px')."</div>\n";
		echo "<div class='p-t-10'><strong>\n".ucfirst($userdata['user_name'])."</strong>\n<br/>".getuserlevel($userdata['user_level'])."</div></div>\n";
		echo "</div>\n";
		echo admin_nav(1); // Reminder Can also use echo \PHPFusion\Admins::getInstance()->vertical_admin_nav();
		echo "</div>\n";
		echo "<!--end leftnav -->\n";
		echo "<!-- begin main content -->\n";
		echo "<div id='acp-main' class='display-block acp ".(isset($_COOKIE['Venus']) && $_COOKIE['Venus'] ? 'in' : '')."' style='margin-top:50px; min-height:1125px; width:100%; height:100%; vertical-align:top;'>\n";
		echo "<div id='acp-content' class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";
		echo render_breadcrumbs();
		echo CONTENT;
		echo "</div>\n";
		echo "<footer>";
		echo "Venus Admin Theme &copy; ".date("Y")." created by <a href='https://www.php-fusion.co.uk'><strong>PHP-Fusion Inc.</strong></a>\n";
		echo showcopyright();
		
		if ($settings['rendertime_enabled']) {
			echo "<br /><br />";
			echo showrendertime()." - ".showMemoryUsage();
		}
		
		if (iADMIN && checkrights("ERRO") && count($_errorHandler) > 0) {
			echo "<div class='well text-center m-t-20'>".str_replace("[ERROR_LOG_URL]", ADMIN."errors.php".$aidlink, $locale['err_101'])."</div>\n";
		}
		
		echo "</footer>";
		echo "</div>\n";
		echo "<!-- end main content -->\n";
		echo "</div>\n";
	}
}