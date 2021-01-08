<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: theme.php
| Author: JoiNNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

//Theme settings
require_once THEME."theme_settings.php";

define("THEME_BULLET", "<img src='".THEME."images/bullet.png' class='bullet' width='3' height='5' alt='>' />");
// Uncomment to enable/disable styles

// Disable Load Default CCS
// define('NO_DEFAULT_CSS', TRUE);

// Enable Fontawesome
// define('FONTAWESOME', TRUE);

// Enable Entypo
// define('ENTYPO', TRUE);

// Enable Bootstrap
// define('BOOTSTRAP', TRUE);

require_once THEME."includes/functions.php";
require_once INCLUDES."theme_functions_include.php";

function get_head_tags() {
    echo "<!--[if lte IE 7]><style type='text/css'>hr{height: 2px;} .button,button,input[type=submit]{padding: 2px 4px} #navigation h2{margin:0 -1px} .member_wrap,.subs{position: relative} .clearfix {display:inline-block;} * html .clearfix{height: 1px;}</style><![endif]-->\n";
    //Theme width class
    echo "<style type='text/css'>.theme-width {max-width: ".THEME_MAXWIDTH.";min-width: ".THEME_MINWIDTH."}</style>\n";
}

//Open Sans font
add_to_head("<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css' />");

function render_page($license = FALSE) {
    global $aidlink, $locale, $settings, $main_style;

    // Check if a locale file is available that match the selected locale.
    if (file_exists(THEME."locale/".LANGUAGE.".php")) {
        // Load the locale file matching selection.
        include THEME."locale/".LANGUAGE.".php";
    } else {
        // Load the default locale file.
        include THEME."locale/English.php";
    }

    add_handler("theme_output");

    //Header
    $links = showsublinks("", "link");
    //Home link as icon
    if (HOME_ICON == 1) {
        $links = preg_replace("#(<li class=')link( first-link)?( current-link)?('><a href=')(\.\./|\.\./\.\./)?(".$settings['opening_page']."|index.php)?('.*)#si", "$1link home$2$4$5$6$7", $links);
    }
    //add class last-link
    $links = preg_replace("#(.*<li class=')link( current-link)?( home)?('.*)#si", "$1link last-link$2$3$4", $links);
    echo "<div id='header' class='clearfix'>";
    echo "<div id='mainheader' class='center clearfix'>";

    echo "<form id='top-search' class='search-wrap' name='top-search' action='".BASEDIR."search.php' method='get'>";
    echo "<div class='search-input-wrap textbox flleft'>";
    //Search based on the website area you are on
    include THEME."includes/header_search_includes.php";
    if (!empty($stype) && !empty($text)) {
        echo "<span id='search_area' class='search button mini flleft'>".$text." &nbsp;X</span>";
        echo "<input type='hidden' value='".$stype."' id='search_type' name='stype' />";
    }
    echo "<span id='placeholder' class='flleft'>".$locale['search']."</span><input id='sinput' type='text' style='' class='textbox-search flleft' value='' name='stext' /></div>";
    echo "<button type='submit' class='button search flleft'><img src='".THEME."images/search.png' alt='".$locale['search']."' /></button>";
    echo "</form>";
    echo "<div id='logo'>".showbanners()."</div>";

    //Winter Mode
    if (WINTER == 1) {
        echo "<span class='top-snow'></span>";
        add_to_head("<link type='text/css' href='".THEME."css/winter.css' rel='stylesheet' media='screen' />");
    }
    echo "</div><div id='subheader'><div class='menu theme-width center'>".$links."</div></div></div>";

    echo "<div id='main' class='$main_style theme-width center clearfix'>";

    //Panels structure
    echo "<div class='upper'>".AU_CENTER."</div>\n";
    echo(LEFT ? "<div id='side-border-left' class='sides flleft'>\n".LEFT."</div>\n" : "");
    echo(RIGHT ? "<div id='side-border-right' class='sides flright'>\n".RIGHT."</div>\n" : "");
    //Main structure
    echo "<div id='main-bg'><div id='container'>";
    echo U_CENTER.CONTENT.L_CENTER."</div>\n";
    echo "</div>\n";
    echo "<div class='clear'></div>\n";
    echo "<div class='lower'>".BL_CENTER."</div>\n";
    echo "</div>\n";
    //Footer
    echo "<div id='footer'>";
    //Scroll to top link
    echo "<div class='scroll-top center'><a href='#header' id='top-link' class='scroll clean' title='".$locale['scroll_top']."'></a></div>";
    echo "<div class='footer center theme-width'>
            <div class='footernav center clearfix'>";
    //Footer links
    require_once THEME."includes/footer_links.php";
    echo "</div></div>";
    echo "</div>";

    //Subfooter
    echo "<div id='subfooter'>";
    echo "<div id='copyright'><div class='flleft'><img width='40' src='".THEME."images/8ight.png' alt='Logo' /></div><div class='flleft' style='width: 40%; padding-left: 10px'>".(!$license ? showcopyright() : "")."<br />Theme designed by <a href='http://phpfusion.com'>JoiNNN</a></div>";
    echo "<div class='flright alright' style='width: 40%;'>".stripslashes($settings['footer'])."</div></div>";

    echo "<div class='subfooter clearfix'>";
    echo "<div class='flleft' style='width: 50%'>";
    if ($settings['rendertime_enabled'] == 1 || ($settings['rendertime_enabled'] == 2 && iADMIN)) {
        echo showrendertime();
    }
    echo "</div>";
    echo "<div class='flright alright' style='width: 50%;'>".showcounter()."</div>";
    echo "</div></div>";


    //javaScripts
    add_to_footer("<script type='text/javascript' src='".THEME."js/jquery.cookie.js'></script>");
    add_to_footer("<script type='text/javascript' src='".THEME."js/jquery.mousewheel.js'></script>");
    add_to_footer("<script type='text/javascript' src='".THEME."js/scrolltopcontrol.js'></script>");
    //Script for search in header
    add_to_footer("<script type='text/javascript'>
    $(document).ready(function() {
    //Header search - area script
    $('#search_area').click( function() {
        $(this).fadeTo('slow',0,function(){
            $(this).animate({'width': '0', 'padding': '0'}, 300, function(){
                $(this).remove()
            });
        });
    $('#search_type').attr({'disabled': 'disabled'}) //disabling(instead of removing) keeps input field value after page reload
    });
    $('#search_type').removeAttr('disabled') //if is disabled, stays disabled after page reload. Remove attribute on page load

    //Header search - placeholder script
    var text = $('#placeholder').html();
    if ($('#sinput').attr('value') != '') {
        $('#placeholder').html('') //remove placeholder text if other value is kept after page reload
    }
    //remove placeholder text when typing
    $('#sinput').bind('input propertychange', function() {
        if ($(this).attr('value') != '') {
            $('#placeholder').html('') //remove
        } else {
            $('#placeholder').html(text) //add it back if no text input
        }
    });

    //Verticaly align the logo
    $('.main-logo').css({'top': '50%', 'margin-top': '-' + $('.main-logo').height()/2 + 'px'});
    });
    </script>");

    //Show warning if Theme Control Panel is not infused and user has access to Infusions
    if (TCPINFUSED == 0 && checkrights("I")) {
        //Render the warning
        replace_in_output("<!--error_handler-->", "<!--error_handler--><div class='admin-message tcp-warn'><span id=\'tcp-warn\'></span>".str_replace(['[link]', '[/link]'], ["<a href='".ADMIN."infusions.php".(isset($aidlink) ? $aidlink : "")."'>", '</a>'], $locale['tcp_warning'])."</div>");
        //Message close script
        add_to_footer("<script type='text/javascript'>
    $(document).ready(function() {
    $('#tcp-warn').click( function() {
        $('.tcp-warn').fadeTo('slow',0.01,function(){
            $(this).slideUp('slow',function(){
                $(this).hide()
            });
        $.cookie('showWarn', 'hide', {expires: 1, path:'/' });
        });
    });
    var showWarn = $.cookie('showWarn');
    if (showWarn == 'hide') {
        $('.tcp-warn').hide();
    }
    });
    </script>");
    }
}

//Render News
function render_news($subject, $news, $info) {
    global $locale, $settings;
    opentable($subject, "post", $info, "N");
    echo "<ul class='news-info'>\n";
    //Author
    echo "<li class='author'>".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."</li>\n";
    //Date
    echo "<li class='dated'>".showdate("%d %b %Y", $info['news_date'])."</li>\n";
    //Category
    echo "<li class='cat'>\n";
    if ($info['cat_id']) {
        echo "<a href='".BASEDIR."news_cats.php?cat_id=".$info['cat_id']."'>".$info['cat_name']."</a>\n";
    } else {
        echo "<a href='".BASEDIR."news_cats.php?cat_id=0'>".$locale['global_080']."</a>";
    }
    echo "</li>\n";
    //Reads
    if ($info['news_ext'] == "y" || ($info['news_allow_comments'] && $settings['comments_enabled'] == "1")) {
        echo "<li class='reads'>\n";
        echo $info['news_reads'].$locale['global_074'];
        echo "</li>\n";
    }
    //Comments
    if ($info['news_allow_comments'] && $settings['comments_enabled'] == "1") {
        echo "<li class='comments'><a ".(isset($_GET['readmore']) ? "class='scroll'" : "")." href='".BASEDIR."news.php?readmore=".$info['news_id']."#comments'>".$info['news_comments']."".($info['news_comments'] == 1 ? $locale['global_073b'] : $locale['global_073'])."</a></li>\n";
    }
    echo "</ul>\n";
    //The message
    echo $info['cat_image'].$news;

    //Read more button
    if (!isset($_GET['readmore']) && $info['news_ext'] == "y") {
        echo "<div class='flright'><a href='".BASEDIR."news.php?readmore=".$info['news_id']."' class='button'><img alt='".$locale['global_072']."' class='rightarrow icon' src='".THEME."images/blank.gif' />".$locale['global_072']."</a></div>\n";
    }
    closetable();
}

//Render Articles
function render_article($subject, $article, $info) {
    global $locale, $settings;
    opentable($subject, "article", $info, "A");
    echo "<ul class='article-info'>\n";
    //Author
    echo "<li class='author'>".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."</li>\n";
    //Date
    echo "<li class='dated'>".showdate("%d %b %Y", $info['article_date'])."</li>\n";
    //Category
    echo "<li class='cat'>\n";
    if ($info['cat_id']) {
        echo "<a href='".BASEDIR."articles.php?cat_id=".$info['cat_id']."'>".$info['cat_name']."</a>\n";
    } else {
        echo "<a href='".BASEDIR."articles.php?cat_id=0'>".$locale['global_080']."</a>";
    }
    echo "</li>\n";
    //Reads
    echo "<li class='reads'>".$info['article_reads'].$locale['global_074']."</li>\n";
    //Comments
    if ($info['article_allow_comments'] && $settings['comments_enabled'] == "1") {
        echo "<li class='comments'><a class='scroll' href='".BASEDIR."articles.php?article_id=".$info['article_id']."#comments'>".$info['article_comments'].($info['article_comments'] == 1 ? $locale['global_073b'] : $locale['global_073'])."</a></li>\n";
    }
    echo "</ul>\n";
    //The message
    echo ($info['article_breaks'] == "y" ? nl2br($article) : $article)."\n";
    closetable();
}

//Render comments
function render_comments($c_data, $c_info) {
    global $locale, $settings;
    if ($c_info['admin_link'] !== FALSE) {
        echo "<div class='comment_admin floatfix' style='margin-bottom: 15px'><div class='flright'>".$c_info['admin_link']."</div></div>\n";
    }
    if (!empty($c_data)) {
        echo "<div class='user-comments floatfix'>\n";
        $c_makepagenav = '';
        if ($c_info['c_makepagenav'] !== FALSE) {
            echo $c_makepagenav = "<div style='text-align:center;margin-bottom:5px;'>".$c_info['c_makepagenav']."</div>\n";
        }
        foreach ($c_data as $data) {
            echo "<div id='c".$data['comment_id']."' class='comment'>\n";
            //User avatar
            if ($settings['comments_avatar'] == "1") {
                echo "<span class='user_avatar'>".$data['user_avatar']."</span>\n";
                $noav = "";
            } else {
                $noav = "noavatar";
            }
            echo "<div class='tbl1 comment_wrap $noav'>";
            //Pointer tip
            if ($settings['comments_avatar'] == "1") {
                echo "<div class='pointer'><span></span></div>\n";
            }
            //Options
            echo "<div class='comment-info'>";
            if ($data['edit_dell'] !== FALSE) {
                echo "<div class='actions flright'>".$data['edit_dell']."\n</div>\n";
            }
            //Info
            echo "<a class='scroll' href='".FUSION_REQUEST."#c".$data['comment_id']."'>#".$data['i']."</a> |\n";
            echo "<span class='comment-name'>".$data['comment_name']."</span>\n";
            echo "<span class='small'>".$data['comment_datestamp']."</span></div>\n";
            //The message
            echo "<div class='comment-msg'>".$data['comment_message']."</div></div></div>\n";
        }

        echo $c_makepagenav;

        echo "</div>\n";
    } else {
        echo "<div class='nocomments-message spacer'>".$locale['c101']."</div>\n";
    }
}

function itemoptions2($item_type, $item_id, $info) {
    global $locale, $aidlink;
    $res = "";
    if ($item_type == "N") {
        //Edit
        if (iADMIN && checkrights($item_type)) {
            $res .= "<span class='edit'><!--article_news_opts--> <a href='".ADMIN."news.php".$aidlink."&amp;action=edit&amp;news_id=".$item_id."'><img src='".get_image("edit")."' alt='".$locale['global_076']."' title='".$locale['global_076']."' width='16' height='16' style='border:0;' /></a></span>\n";
        }
        //Print
        $res .= "<!--news_opts--><span class='print'><a href='print.php?type=N&amp;item_id=".$info['news_id']."'><img src='".get_image("printer")."' alt='".$locale['global_075']."' title='".$locale['global_075']."' width='20' height='16' style='border:0;' /></a></span>\n";
    } else if ($item_type == "A") {
        //Edit
        if (iADMIN && checkrights($item_type)) {
            $res .= "<span class='edit'><!--article_admin_opts--> <a href='".ADMIN."articles.php".$aidlink."&amp;action=edit&amp;article_id=".$item_id."'><img src='".get_image("edit")."' alt='".$locale['global_076']."' title='".$locale['global_076']."' width='16' height='16' style='border:0;' /></a></span>\n";
        }
        //Print
        $res .= "<!--article_opts--><span class='print'><a href='print.php?type=A&amp;item_id=".$info['article_id']."'><img src='".get_image("printer")."' alt='".$locale['global_075']."' title='".$locale['global_075']."' width='20' height='16' style='border:0;' /></a></span>\n";
    }
    return $res;
}

//Content Panels
function opentable($title, $custom_class = "", $info = [], $type = "") {
    global $p_data;
    //News and Articles IDs and Options(Edit/Print)
    $id = "";
    $options = "";
    if ($type == "N") {
        $id = "id='news-".$info['news_id']."'";
        $options = itemoptions2("N", $info['news_id'], $info);
    } else if ($type == "A") {
        $id = "id='article-".$info['article_id']."'";
        $options = itemoptions2("A", $info['article_id'], $info);
    }

    //Wrapp panel in div with class based on panel name
    $class = !empty($p_data['panel_filename']) ? $p_data['panel_filename'] : '';
    if ($class == "" && $custom_class == "") {
        //Panel with no custom class and no panel name file
        $class = "panel";
    } else if ($class == "" && $custom_class != "") {
        //Panel with custom class and no panel name file
        $class = $custom_class." panel";
    } else {
        //Panel with panel name file
        $class = str_replace("_panel", " panel", $class);
    }

    echo "<div ".$id." class='".$class."'>
            <h1 class='maincap'>
                        <span class='title'>".$title."</span>";
    if ($info != "") {
        echo "<span class='options'>";
        echo $options;
        echo "</span>";
    }
    echo "</h1>\n";
    echo "<div class='contentbody clearfix'>";
}

function closetable() {
    echo "</div></div>\n";
}

//Side panels
function openside($title, $collapse = FALSE, $state = "on") {
    global $panel_collapse, $p_data;
    $panel_collapse = $collapse;
    $boxname = '';
    //Wrapp panel in div with class based on panel name
    $class = $p_data['panel_filename'];
    if ($class == "") {
        $class = "panel";
    } else {
        $class = str_replace("_panel", " panel", $class);
    }

    echo "<div class='".$class."'>";
    echo "<h2 class='panelcap'><span class='title'>".$title."</span>\n";
    if ($collapse == TRUE) {
        $boxname = str_replace(" ", "", $title);
        echo "<span class='switcherbutton flright'>".panelbutton($state, $boxname)."</span>\n";
    }
    echo "</h2>
<div class='panelbody clearfix'>\n";
    if ($collapse == TRUE) {
        echo panelstate($state, $boxname);
    }
}

function closeside() {
    global $panel_collapse;

    echo "</div>\n";
    echo "</div>\n";
    if ($panel_collapse == TRUE) {
        echo "</div>\n";
    }
}
