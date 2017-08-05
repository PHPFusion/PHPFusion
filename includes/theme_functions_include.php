<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: theme_functions_include.php
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
use PHPFusion\Database\DatabaseFactory;

if (!defined("IN_FUSION")) {
    die("Access Denied");
}

/**
 * Show PHP-Fusion Performance
 * @param bool $queries
 * @return string
 */
function showrendertime($queries = TRUE) {
    $locale = fusion_get_locale();
    $mysql_queries_count = DatabaseFactory::getConnection('default')->getGlobalQueryCount();
    if (fusion_get_settings('rendertime_enabled') == 1 || (fusion_get_settings('rendertime_enabled') == 2 && iADMIN)) {
        $res = showBenchmark();
        $res .= ($queries ? " | ".ucfirst($locale['global_173']).": $mysql_queries_count" : '');

        return $res;
    } else {
        return "";
    }
}

/**
 * Developer tools only (Translations not Required)
 *
 * @param bool $show_sql_performance
 *
 * @return bool|string
 */
function showBenchmark($show_sql_performance = FALSE) {
    $locale = fusion_get_locale();
    if ($show_sql_performance) {
        $query_log = DatabaseFactory::getConnection('default')->getQueryLog();
        $modal = openmodal('querylogsModal', "<h4><strong>Database Query Performance Logs</strong></h4>");
        $modal_body = '';
        $i = 0;
        $time = 0;
        if (!empty($query_log)) {
            foreach ($query_log as $connectionID => $sql) {
                $current_time = $sql[0];
                $modal_body .= "<div class='spacer-xs m-10'>\n";
                $modal_body .= "<h5><strong>SQL run#$i : ".($sql[0] > .1 ? "<span class='text-danger'>".$sql[0]."</span>" : "<span class='text-success'>".$sql[0]."</span>")." seconds</strong></h5>\n\r";
                $modal_body .= "[code]".$sql[1].($sql[2] ? " [Parameters -- ".implode(',', $sql[2])." ]" : '')."[/code]\n\r";
                $modal_body .= "<div>\n";
                $end_sql = end($sql[3]);
                $modal_body .= "<kbd>".$end_sql['file']."</kbd><span class='badge pull-right'>Line #".$end_sql['line'].", ".$end_sql['function']."</span>\n\r";
                $modal_body .= "</div>\n";
                $modal_body .= "</div>\n";
                $i++;
                $time = $current_time + $time;
            }
        }
        $modal .= parse_textarea($modal_body, FALSE, TRUE, FALSE);
        $modal .= modalfooter("<h4><strong>Total Time Expended in ALL SQL Queries: ".$time." seconds</strong></h4>", FALSE);
        $modal .= closemodal();
        add_to_footer($modal);
    }
    $render_time = substr((microtime(TRUE) - START_TIME), 0, 7).' seconds';
    $_SESSION['performance'][] = $render_time;
    if (count($_SESSION['performance']) > 5) {
        array_shift($_SESSION['performance']);
    }
    $average_speed = $render_time;
    $diff = 0;
    if (isset($_SESSION['performance'])) {
        $average_speed = substr(array_sum($_SESSION['performance']) / count($_SESSION['performance']), 0, 7);
        $previous_render = array_values(array_slice($_SESSION['performance'], -2, 1, TRUE));
        $diff = (float)$render_time - (!empty($previous_render) ? (float)$previous_render[0] : 0);
    }

    return sprintf($locale['global_172'], $render_time)." | ".sprintf($locale['global_175'], $average_speed." ($diff)");
}

function showMemoryUsage() {
    $locale = fusion_get_locale();
    $memory_allocated = parsebytesize(memory_get_peak_usage(TRUE));
    $memory_used = parsebytesize(memory_get_peak_usage(FALSE));

    return " | ".$locale['global_174'].": ".$memory_used."/".$memory_allocated;
}

function showcopyright($class = "", $nobreak = FALSE) {
    $link_class = $class ? " class='$class' " : "";
    $res = "Powered by <a href='https://www.php-fusion.co.uk'".$link_class.">PHP-Fusion</a> Copyright &copy; ".date("Y")." PHP-Fusion Inc";
    $res .= ($nobreak ? "&nbsp;" : "<br />\n");
    $res .= "Released as free software without warranties under <a href='http://www.fsf.org/licensing/licenses/agpl-3.0.html'".$link_class." target='_blank'>GNU Affero GPL</a> v3.\n";

    return $res;
}

function showcounter() {
    $locale = fusion_get_locale();
    $settings = fusion_get_settings();
    if ($settings['visitorcounter_enabled']) {
        return "<!--counter-->".number_format($settings['counter'])." ".($settings['counter'] == 1 ? $locale['global_170'] : $locale['global_171']);
    } else {
        return "";
    }
}

function showprivacypolicy() {
    $html = '';
    if (!empty(fusion_get_settings('privacy_policy'))) {
        $html .= "<a href='".BASEDIR."print.php?type=P' id='privacy_policy'>".fusion_get_locale('global_176')."</a>";
        $modal = openmodal('privacy_policy', $locale = fusion_get_locale('global_176'), ['button_id' => 'privacy_policy']);
        $modal .= parse_textarea(fusion_get_settings('privacy_policy'));
        $modal .= closemodal();
        add_to_footer($modal);
    }

    return $html;
}

/**
 * Creates an alert bar
 * @param        $title
 * @param string $text
 * @param array  $options
 * @return string
 */
if (!function_exists("alert")) {
    function alert($title, array $options = array()) {
        $options += array(
            "class"   => !empty($options['class']) ? $options['class'] : 'alert-danger',
            "dismiss" => !empty($options['dismiss']) && $options['dismiss'] == TRUE ? TRUE : FALSE
        );
        if ($options['dismiss'] == TRUE) {
            $html = "<div class='alert alert-dismissable ".$options['class']."'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>$title</div>";
        } else {
            $html = "<div class='alert ".$options['class']."'>$title</div>";
        }
        add_to_jquery("$('div.alert a').addClass('alert-link');");

        return $html;
    }
}

// Get the widget settings for the theme settings table
if (!function_exists('get_theme_settings')) {
    function get_theme_settings($theme_folder) {
        $settings_arr = array();
        $set_result = dbquery("SELECT settings_name, settings_value FROM ".DB_SETTINGS_THEME." WHERE settings_theme='".$theme_folder."'");
        if (dbrows($set_result)) {
            while ($set_data = dbarray($set_result)) {
                $settings_arr[$set_data['settings_name']] = $set_data['settings_value'];
            }

            return $settings_arr;
        } else {
            return FALSE;
        }
    }
}

/**
 * Java script that transform html table sortable
 * @param $table_id - table ID
 * @return string
 */
function fusion_sort_table($table_id) {
    add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/tablesorter/jquery.tablesorter.min.js'></script>\n");
    add_to_jquery("
	$('#".$table_id."').tablesorter();
	");

    return "tablesorter";
}

if (!function_exists("label")) {
    function label($label, array $options = array()) {
        $options += array(
            "class" => !empty($array['class']) ? $array['class'] : "",
            "icon" => !empty($array['icon']) ? "<i class='".$array['icon']."'></i> " : "",
        );

        return "<span class='label ".$options['class']."'>".$options['icon'].$label."</span>\n";
    }
}
if (!function_exists("badge")) {
    function badge($label, array $options = array()) {
        $options += array(
            "class" => !empty($array['class']) ? $array['class'] : "",
            "icon" => !empty($array['icon']) ? "<i class='".$array['icon']."'></i> " : "",
        );

        return "<span class='badge ".$options['class']."'>".$options['icon'].$label."</span>\n";
    }
}
if (!function_exists("openmodal") && !function_exists("closemodal") && !function_exists("modalfooter")) {

    /**
     * To get the best results for Modal z-index overlay, try :
     * ob_start();
     * ... insert and echo ...
     * add_to_footer(ob_get_contents()).ob_end_clean();
     */

    /**
     * Generate modal
     * @param       $id - unique CSS id
     * @param       $title - modal title
     * @param array $options
     * @return string
     */
    function openmodal($id, $title, $options = array()) {
        $locale = fusion_get_locale();
        $options += array(
            'class' => !empty($options['class']) ?: 'modal-lg',
            'button_id' => "",
            "button_class" => "",
            'static' => FALSE,
        );

        $modal_trigger = "";
        if (!empty($options['button_id']) || !empty($options['button_class'])) {
            $modal_trigger = !empty($options['button_id']) ? "#".$options['button_id'] : ".".$options['button_class'];
        }

        if ($options['static'] && !empty($modal_trigger)) {
            PHPFusion\OutputHandler::addToJQuery("$('".$modal_trigger."').bind('click', function(e){ $('#".$id."-Modal').modal({backdrop: 'static', keyboard: false}).modal('show'); e.preventDefault(); });");
        } elseif ($options['static'] && empty($options['button_id'])) {
            PHPFusion\OutputHandler::addToJQuery("$('#".$id."-Modal').modal({	backdrop: 'static',	keyboard: false }).modal('show');");
        } elseif ($modal_trigger && empty($options['static'])) {
            PHPFusion\OutputHandler::addToJQuery("$('".$modal_trigger."').bind('click', function(e){ $('#".$id."-Modal').modal('show'); e.preventDefault(); });");
        } else {
            PHPFusion\OutputHandler::addToJQuery("$('#".$id."-Modal').modal('show');");
        }
        $html = '';
        $html .= "<div class='modal' id='$id-Modal' tabindex='-1' role='dialog' aria-labelledby='$id-ModalLabel' aria-hidden='true'>\n";
        $html .= "<div class='modal-dialog ".$options['class']."'>\n";
        $html .= "<div class='modal-content'>\n";
        if ($title) {
            $html .= "<div class='modal-header'>";
            $html .= ($options['static'] ? "" : "<button type='button' class='btn pull-right btn-default' data-dismiss='modal'><i class='fa fa-times'></i> ".$locale['close']."</button>\n");
            $html .= "<h4 class='modal-title text-dark' id='$id-title'>$title</h4>\n";
            $html .= "</div>\n";
        }
        $html .= "<div class='modal-body'>\n";

        return $html;
    }

    /**
     * Adds a modal footer in between openmodal and closemodal.
     * @param            $content
     * @param bool|FALSE $dismiss
     * @return string
     */
    function modalfooter($content, $dismiss = FALSE) {
        $html = "</div>\n<div class='modal-footer'>\n";
        $html .= $content;
        if ($dismiss) {
            $html .= "<button type='button' class='btn btn-default pull-right' data-dismiss='modal'>".fusion_get_locale('close')."</button>";
        }

        return $html;
    }

    /**
     * Close the modal
     * @return string
     */
    function closemodal() {
        return "</div>\n</div>\n</div>\n</div>\n";
    }
}

if (!function_exists("progress_bar")) {
    /**
     * Render a progress bar
     * @param      $num - str or array
     * @param bool $title - str or array
     * @param bool $class
     * @param bool $height
     * @param bool $reverse
     * @param bool $as_percent
     * @param bool $disabled
     * @return string
     */
    function progress_bar($num, $title = FALSE, $class = FALSE, $height = FALSE, $reverse = FALSE, $as_percent = TRUE, $disabled = FALSE, $hide_info = FALSE, $class_ = 'm-b-10') {
        $height = ($height) ? $height : '20px';
        if (!function_exists('bar_color')) {
            function bar_color($num, $reverse) {
                $auto_class = $reverse ? "progress-bar-success" : "progress-bar-danger";
                if ($num > 71) {
                    $auto_class = ($reverse) ? 'progress-bar-danger' : 'progress-bar-success';
                } elseif ($num > 55) {
                    $auto_class = ($reverse) ? 'progress-bar-warning' : 'progress-bar-info';
                } elseif ($num > 25) {
                    $auto_class = ($reverse) ? 'progress-bar-info' : 'progress-bar-warning';
                } elseif ($num < 25) {
                    $auto_class = ($reverse) ? 'progress-bar-success' : 'progress-bar-danger';
                }

                return $auto_class;
            }
        }
        $_barcolor = array('progress-bar-success', 'progress-bar-info', 'progress-bar-warning', 'progress-bar-danger');
        $_barcolor_reverse = array(
            'progress-bar-success',
            'progress-bar-info',
            'progress-bar-warning',
            'progress-bar-danger'
        );
        $html = '';
        if (is_array($num)) {
            $i = 0;
            $chtml = "";
            $cTitle = "";
            $cNum = "";
            foreach ($num as $value) {

                $int = intval($num);

                if ($disabled == TRUE) {
                    $value = "&#x221e;";
                } else {
                    $value = $value > 0 ? $value.' ' : '0 ';
                    $value .= $as_percent ? '%' : '';
                }

                $c2Title = "";

                if (is_array($title)) {
                    $c2Title = $title[$i];
                } else {
                    $cTitle = $title;
                }

                $auto_class = ($reverse) ? $_barcolor_reverse[$i] : $_barcolor[$i];
                $classes = (is_array($class)) ? $class[$i] : $auto_class;

                $cNum .= "<div class='progress display-inline-block m-0' style='width:20px; height: 10px; '>\n";
                $cNum .= "<span class='progress-bar ".$classes."' style='width:100%'></span></div>\n";
                $cNum .= "<div class='display-inline-block m-r-5'>".$c2Title." ".$value."</div>\n";
                $chtml .= "<div title='".$title."' class='progress-bar ".$classes."' role='progressbar' aria-valuenow='$value' aria-valuemin='0' aria-valuemax='100' style='width: $int%'>\n";
                $chtml .= "</div>\n";
                $i++;
            }
            $html .= ($hide_info == FALSE ? "<div class='text-right m-b-10'><span class='pull-left'>$cTitle</span><span class='clearfix'>$cNum </span></div>\n" : "");
            $html .= "<div class='progress ".$class_."' style='height: ".$height."'>\n";
            $html .= $chtml;
            $html .= "</div>\n";
            $html .= "</div>\n";
        } else {
            $int = intval($num);
            if ($disabled == TRUE) {
                $num = "&#x221e;";
            } else {
                $num = $num > 0 ? $num.' ' : '0 ';
                $num .= $as_percent ? '%' : '';
            }

            $auto_class = bar_color($int, $reverse);
            $class = (!$class) ? $auto_class : $class;

            $html .= ($hide_info === FALSE ? "<div class='text-right m-b-10'><span class='pull-left'>$title</span><span class='clearfix'>$num</span></div>\n" : "");
            $html .= "<div class='progress ".$class_."' style='height: ".$height."'>\n";
            $html .= "<div class='progress-bar ".$class."' role='progressbar' aria-valuenow='$num' aria-valuemin='0' aria-valuemax='100' style='width: $int%'>\n";
            $html .= "</div></div>\n";
        }

        return $html;
    }
}

if (!function_exists("check_panel_status")) {
    function check_panel_status($side) {
        $settings = fusion_get_settings();
        $exclude_list = "";
        if ($side == "left") {
            if ($settings['exclude_left'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_left']);
            }
        } elseif ($side == "upper") {
            if ($settings['exclude_upper'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_upper']);
            }
        } elseif ($side == "aupper") {
            if ($settings['exclude_aupper'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_aupper']);
            }
        } elseif ($side == "lower") {
            if ($settings['exclude_lower'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_lower']);
            }
        } elseif ($side == "blower") {
            if ($settings['exclude_blower'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_blower']);
            }
        } elseif ($side == "right") {
            if ($settings['exclude_right'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_right']);
            }
        } elseif ($side == "user1") {
            if ($settings['exclude_user1'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_user1']);
            }
        } elseif ($side == "user2") {
            if ($settings['exclude_user2'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_user2']);
            }
        } elseif ($side == "user3") {
            if ($settings['exclude_user3'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_user3']);
            }
        } elseif ($side == "user4") {
            if ($settings['exclude_user4'] != "") {
                $exclude_list = explode("\r\n", $settings['exclude_user4']);
            }
        }

        if (is_array($exclude_list)) {
            $script_url = explode("/", $_SERVER['PHP_SELF']);
            $url_count = count($script_url);
            $base_url_count = substr_count(BASEDIR, "/") + 1;
            $match_url = "";
            while ($base_url_count != 0) {
                $current = $url_count - $base_url_count;
                $match_url .= "/".$script_url[$current];
                $base_url_count--;
            }
            if (!in_array($match_url, $exclude_list) && !in_array($match_url.(FUSION_QUERY ? "?".FUSION_QUERY : ""), $exclude_list)) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return TRUE;
        }
    }
}

if (!function_exists("showbanners")) {
    /*
     * Displays the system settings banner
     */
    function showbanners($display = "") {
        ob_start();
        if ($display == 2) {
            if (fusion_get_settings("sitebanner2")) {
                eval("?>".stripslashes(fusion_get_settings("sitebanner2"))."<?php ");
            }
        } else {
            if (fusion_get_settings("sitebanner1")) {
                eval("?>".stripslashes(fusion_get_settings("sitebanner1"))."<?php ");
            }
        }
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }
}

if (!function_exists("showlogo")) {
    function showlogo($class = 'logo') {
        echo "<div class='".$class."'><a href='".BASEDIR.fusion_get_settings('opening_page')."' title='".fusion_get_settings('sitename')."'><img src='".BASEDIR.fusion_get_settings('sitebanner')."' alt='Logo'/></a></div>";
    }
}

if (!function_exists("showsublinks")) {

    /**
     * Displays Site Links Navigation Bar
     * @param string $sep - Custom seperator text
     * @param string $class - Class
     * @param array $options
     *
     * Notice: There is a more powerful method now that offers more powerful manipulation methods
     * that non oo approach cannot ever achieve using cache and the new mutator method
     * SiteLinks::setSubLinks($sep, $class, $options)->showsublinks(); for normal usage
     * @return string
     */
    function showsublinks($sep = "", $class = "navbar-default", array $options = array()) {
        $options += [
            'seperator' => $sep,
            'navbar_class' => $class,
        ];
        return \PHPFusion\SiteLinks::setSubLinks($options)->showSubLinks();
    }

}

if (!function_exists("showsubdate")) {
    function showsubdate() {
        global $settings;

        return ucwords(showdate($settings['subheaderdate'], time()));
    }
}

if (!function_exists("newsposter")) {
    function newsposter($info, $sep = "", $class = "") {
        $locale = fusion_get_locale();
        $res = "";
        $link_class = $class ? " class='$class' " : "";
        $res = THEME_BULLET." <span ".$link_class.">".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."</span> ";
        $res .= $locale['global_071'].showdate("newsdate", $info['news_date']);
        $res .= $info['news_ext'] == "y" || $info['news_allow_comments'] ? $sep."\n" : "\n";

        return "<!--news_poster-->".$res;
    }
}

if (!function_exists("newsopts")) {
    function newsopts($info, $sep, $class = "") {
        $locale = fusion_get_locale();
        $res = "";
        $link_class = $class ? " class='$class' " : "";
        if (!isset($_GET['readmore']) && $info['news_ext'] == "y") {
            $res = "<a href='".INFUSIONS."news/news.php?readmore=".$info['news_id']."'".$link_class.">".$locale['global_072']."</a> ".$sep." ";
        }
        if ($info['news_allow_comments'] && fusion_get_settings('comments_enabled') == "1") {
            $res .= "<a href='".INFUSIONS."news/news.php?readmore=".$info['news_id']."#comments'".$link_class.">".$info['news_comments'].($info['news_comments'] == 1 ? $locale['global_073b'] : $locale['global_073'])."</a> ".$sep." ";
        }
        if ($info['news_ext'] == "y" || ($info['news_allow_comments'] && fusion_get_settings('comments_enabled') == "1")) {
            $res .= $info['news_reads'].$locale['global_074']."\n ".$sep;
        }
        $res .= "<a href='print.php?type=N&amp;item_id=".$info['news_id']."'><img src='".get_image("printer")."' alt='".$locale['global_075']."' style='vertical-align:middle;border:0;' /></a>\n";

        return "<!--news_opts-->".$res;
    }
}

if (!function_exists("newscat")) {
    function newscat($info, $sep = "", $class = "") {
        $locale = fusion_get_locale();
        $res = "";
        $link_class = $class ? " class='$class' " : "";
        $res .= $locale['global_079'];
        if ($info['cat_id']) {
            $res .= "<a href='news_cats.php?cat_id=".$info['cat_id']."'$link_class>".$info['cat_name']."</a>";
        } else {
            $res .= "<a href='news_cats.php?cat_id=0'$link_class>".$locale['global_080']."</a>";
        }

        return "<!--news_cat-->".$res." $sep ";
    }
}

if (!function_exists("articleposter")) {
    function articleposter($info, $sep = "", $class = "") {
        $locale = fusion_get_locale();
        $res = "";
        $link_class = $class ? " class='$class' " : "";
        $res = THEME_BULLET." ".$locale['global_070']."<span ".$link_class.">".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."</span>\n";
        $res .= $locale['global_071'].showdate("newsdate", $info['article_date']);
        $res .= ($info['article_allow_comments'] && fusion_get_settings('comments_enabled') == "1" ? $sep."\n" : "\n");

        return "<!--article_poster-->".$res;
    }
}

if (!function_exists("articleopts")) {
    function articleopts($info, $sep) {
        $locale = fusion_get_locale();
        $res = "";
        if ($info['article_allow_comments'] && fusion_get_settings('comments_enabled') == "1") {
            $res = "<a href='articles.php?article_id=".$info['article_id']."#comments'>".$info['article_comments'].($info['article_comments'] == 1 ? $locale['global_073b'] : $locale['global_073'])."</a> ".$sep."\n";
        }
        $res .= $info['article_reads'].$locale['global_074']." ".$sep."\n";
        $res .= "<a href='print.php?type=A&amp;item_id=".$info['article_id']."'><img src='".get_image("printer")."' alt='".$locale['global_075']."' style='vertical-align:middle;border:0;' /></a>\n";

        return "<!--article_opts-->".$res;
    }
}

if (!function_exists("articlecat")) {
    function articlecat($info, $sep = "", $class = "") {
        $locale = fusion_get_locale();
        $res = "";
        $link_class = $class ? " class='$class' " : "";
        $res .= $locale['global_079'];
        if ($info['cat_id']) {
            $res .= "<a href='articles.php?cat_id=".$info['cat_id']."'$link_class>".$info['cat_name']."</a>";
        } else {
            $res .= "<a href='articles.php?cat_id=0'$link_class>".$locale['global_080']."</a>";
        }

        return "<!--article_cat-->".$res." $sep ";
    }
}

if (!function_exists("itemoptions")) {
    function itemoptions($item_type, $item_id) {
        $locale = fusion_get_locale();
        $res = "";
        if ($item_type == "N") {
            if (iADMIN && checkrights($item_type)) {
                $res .= "<!--article_news_opts--> &middot; <a href='".INFUSIONS."news/news_admin.php".fusion_get_aidlink()."&amp;action=edit&amp;news_id=".$item_id."'><img src='".get_image("edit")."' alt='".$locale['global_076']."' title='".$locale['global_076']."' style='vertical-align:middle;border:0;' /></a>\n";
            }
        } elseif ($item_type == "A") {
            if (iADMIN && checkrights($item_type)) {
                $res .= "<!--article_admin_opts--> &middot; <a href='".INFUSIONS."articles/articles_admin.php".fusion_get_aidlink()."&amp;action=edit&amp;article_id=".$item_id."'><img src='".get_image("edit")."' alt='".$locale['global_076']."' title='".$locale['global_076']."' style='vertical-align:middle;border:0;' /></a>\n";
            }
        }

        return $res;
    }
}

if (!function_exists("panelbutton")) {
    function panelbutton($state, $bname) {
        $bname = preg_replace("/[^a-zA-Z0-9\s]/", "_", $bname);
        if (isset($_COOKIE["fusion_box_".$bname])) {
            if ($_COOKIE["fusion_box_".$bname] == "none") {
                $state = "off";
            } else {
                $state = "on";
            }
        }

        return "<img src='".get_image("panel_".($state == "on" ? "off" : "on"))."' id='b_".$bname."' class='panelbutton' alt='' onclick=\"javascript:flipBox('".$bname."')\" />";
    }
}

if (!function_exists("panelstate")) {
    function panelstate($state, $bname, $element = "div") {
        $bname = preg_replace("/[^a-zA-Z0-9\s]/", "_", $bname);
        if (isset($_COOKIE["fusion_box_".$bname])) {
            if ($_COOKIE["fusion_box_".$bname] == "none") {
                $state = "off";
            } else {
                $state = "on";
            }
        }

        return "<$element id='box_".$bname."'".($state == "off" ? " style='display:none'" : "").">\n";
    }
}

if (!function_exists('opensidex')) {
    function opensidex($title, $state = "on") {
        openside($title, TRUE, $state);
    }
}

if (!function_exists('closesidex')) {
    function closesidex() {
        closeside();
    }
}
if (!function_exists('tablebreak')) {
    function tablebreak() {
        return TRUE;
    }
}

/**
 * @param array  $userdata
 *                          Indexes:
 *                          - user_id
 *                          - user_name
 *                          - user_avatar
 *                          - user_status
 * @param string $size A valid size for CSS max-width and max-height.
 * @param string $class Classes for the link
 * @param bool   $link FALSE if you want to display the avatar without link. TRUE by default.
 * @param string $img_class Classes for the image
 * @return string
 */
if (!function_exists('display_avatar')) {
    function display_avatar(array $userdata, $size, $class = '', $link = TRUE, $img_class = 'img-thumbnail') {
        if (empty($userdata)) {
            $userdata = array();
        }
        $userdata += array(
            'user_id' => 0,
            'user_name' => '',
            'user_avatar' => '',
            'user_status' => ''
        );

        if (!$userdata['user_id']) {
            $userdata['user_id'] = 1;
        }
        $link = fusion_get_settings('hide_userprofiles') == TRUE ? (iMEMBER ? $link : FALSE) : $link;
        $class = ($class) ? "class='$class'" : '';
        // Need a full path - or else Jquery script cannot use this function.
        //$default_avatar = fusion_get_settings('site_path')."images/avatars/no-avatar.jpg";
        $default_avatar = fusion_get_settings('siteurl')."images/avatars/no-avatar.jpg";
        //$default_avatar = IMAGES.'avatars/no-avatar.jpg';
        $user_avatar = fusion_get_settings('siteurl')."images/avatars/".$userdata['user_avatar'];
        //$user_avatar = IMAGES.'avatars/'.$userdata['user_avatar'];
        //$user_avatar = fusion_get_settings('site_path')."images/avatars/".$userdata['user_avatar'];
        $hasAvatar = $userdata['user_avatar'] && file_exists(IMAGES."avatars/".$userdata['user_avatar']) && $userdata['user_status'] != '5' && $userdata['user_status'] != '6';
        $imgTpl = "<img class='img-responsive $img_class' alt='".$userdata['user_name']."' data-pin-nopin='true' style='display:inline; width:$size; max-height:$size;' src='%s'>";
        $img = sprintf($imgTpl, $hasAvatar ? $user_avatar : $default_avatar);
        return $link ? sprintf("<a $class title='".$userdata['user_name']."' href='".BASEDIR."profile.php?lookup=".$userdata['user_id']."'>%s</a>", $img) : $img;
    }
}

if (!function_exists('colorbox')) {
    function colorbox($img_path, $img_title, $responsive = TRUE, $class = '', $as_text = FALSE) {
        if (!defined('COLORBOX')) {
            define('COLORBOX', TRUE);
            add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
            add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");
            add_to_jquery("$('a[rel^=\"colorbox\"]').colorbox({ current: '',width:'80%',height:'80%'});");
        }
        $class = ($class ? " $class" : '');
        if ($responsive) {
            $class = " class='img-responsive $class' ";
        } else {
            $class = (!empty($class) ? " class='$class' " : '');
        }

        return "<a target='_blank' href='$img_path' title='$img_title' rel='colorbox'>".($as_text ? $img_title : "<img src='$img_path'".$class."alt='$img_title'/>")."</a>";
    }
}

/**
 * Thumbnail function
 * @param      $src
 * @param      $size
 * @param bool $url
 * @param bool $colorbox
 * @param bool $responsive
 * @return string
 */
if (!function_exists("thumbnail")) {
    function thumbnail($src, $size, $url = FALSE, $colorbox = FALSE, $responsive = TRUE, $class = "m-2") {
        $_offset_w = 0;
        $_offset_h = 0;
        if (!$responsive && $src) {
            // get the size of the image and centrally aligned it
            $image_info = @getimagesize($src);
            $width = $image_info[0];
            $height = $image_info[1];
            $_size = explode('px', $size);
            if ($width > $_size[0]) {
                $_offset_w = floor($width - $_size[0]) * 0.5;
            } // get surplus and negative by half.
            if ($height > $_size[0]) {
                $_offset_h = ($height - $_size[0]) * 0.5;
            } // get surplus and negative by half.
        }
        $html = "<div style='max-height:".$size."; max-width:".$size."' class='display-inline-block image-wrap thumb text-center overflow-hide ".$class."'>\n";
        $html .= $url || $colorbox ? "<a ".($colorbox && $src ? "class='colorbox'" : '')."  ".($url ? "href='".$url."'" : '')." >" : '';
        if ($src && file_exists($src) && !is_dir($src) || stristr($src, "?")) {
            $html .= "<img ".($responsive ? "class='img-responsive'" : '')." src='$src'/ ".(!$responsive && ($_offset_w || $_offset_h) ? "style='margin-left: -".$_offset_w."px; margin-top: -".$_offset_h."px' " : '')." />\n";
        } else {
            $size = str_replace('px', '', $size);
            $html .= "<img src='holder.js/".$size."x".$size."/text:'/>\n";
        }
        $html .= $url || $colorbox ? "</a>" : '';
        $html .= "</div>\n";
        if ($colorbox && $src && !defined('colorbox')) {
            define('colorbox', TRUE);
            add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
            add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");
            add_to_jquery("$('.colorbox').colorbox();");
        }

        return $html;
    }
}

if (!function_exists("lorem_ipsum")) {
    function lorem_ipsum($length) {
        $text = "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum aliquam felis nunc, in dignissim metus suscipit eget. Nunc scelerisque laoreet purus, in ullamcorper magna sagittis eget. Aliquam ac rhoncus orci, a lacinia ante. Integer sed erat ligula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Fusce ullamcorper sapien mauris, et tempus mi tincidunt laoreet. Proin aliquam vulputate felis in viverra.</p>\n";
		$text .= "<p>Duis sed lorem vitae nibh sagittis tempus sed sed enim. Mauris egestas varius purus, a varius odio vehicula quis. Donec cursus interdum libero, et ornare tellus mattis vitae. Phasellus et ligula velit. Vivamus ac turpis dictum, congue metus facilisis, ultrices lorem. Cras imperdiet lacus in tincidunt pellentesque. Sed consectetur nunc vitae fringilla volutpat. Mauris nibh justo, luctus eu dapibus in, pellentesque non urna. Nulla ullamcorper varius lacus, ut finibus eros interdum id. Proin at pellentesque sapien. Integer imperdiet, sapien nec tristique laoreet, sapien lacus porta nunc, tincidunt cursus risus mauris id quam.</p>\n";
		$text .= "<p>Ut vulputate mauris in facilisis euismod. Ut id libero vitae neque laoreet placerat a id mi. Integer ornare risus placerat, interdum nisi sed, commodo ligula. Integer at ipsum id magna blandit volutpat. Sed euismod mi odio, vitae molestie diam ornare quis. Aenean id ligula finibus, convallis risus a, scelerisque tellus. Morbi quis pretium lectus. In convallis hendrerit sem. Vestibulum sed ultricies massa, ut tempus risus. Nunc aliquam at tellus quis lobortis. In hac habitasse platea dictumst. Vestibulum maximus, nibh at tristique viverra, eros felis ultrices nunc, et efficitur nunc augue a orci. Phasellus et metus mauris. Morbi ut ex ut urna tincidunt varius eu id diam. Aenean vestibulum risus sed augue vulputate, a luctus ligula laoreet.</p>\n";
		$text .= "<p>Nam tempor sodales mi nec ullamcorper. Mauris tristique ligula augue, et lobortis turpis dictum vitae. Aliquam leo massa, posuere ac aliquet quis, ultricies eu elit. Etiam et justo et nulla cursus iaculis vel quis dolor. Phasellus viverra cursus metus quis luctus. Nulla massa turpis, porttitor vitae orci sed, laoreet consequat urna. Etiam congue turpis ac metus facilisis pretium. Nam auctor mi et auctor malesuada. Mauris blandit nulla quis ligula cursus, ut ullamcorper dui posuere. Fusce sed urna id quam finibus blandit tempus eu tellus. Vestibulum semper diam id ante iaculis iaculis.</p>\n";
		$text .= "<p>Fusce suscipit maximus neque, sed consectetur elit hendrerit at. Sed luctus mi in ex auctor mollis. Suspendisse ac elementum tellus, ut malesuada purus. Mauris condimentum elit at dolor eleifend iaculis. Aenean eget faucibus mauris. Pellentesque fermentum mattis imperdiet. Donec mattis nisi id faucibus finibus. Vivamus in eleifend lorem, vel dictum nisl. Morbi ut mollis arcu.</p>\n";

        return trim_text($text, $length);
    }
}

if (!function_exists("timer")) {
    function timer($updated = FALSE) {
        $locale = fusion_get_locale();
        if (!$updated) {
            $updated = time();
        }
        $updated = stripinput($updated);
        $current = time();
        $calculated = $current - $updated;
        $second = 1;
        $minute = $second * 60;
        $hour = $minute * 60;
        $day = 24 * $hour;
        $month = days_current_month() * $day;
        $year = (date("L", $updated) > 0) ? 366 * $day : 365 * $day;
        if ($calculated < 1) {
            return "<abbr class='atooltip' data-toggle='tooltip' data-placement='top' title='".showdate('longdate', $updated)."'>".$locale['just_now']."</abbr>\n";
        }
        //	$timer = array($year => $locale['year'], $month => $locale['month'], $day => $locale['day'], $hour => $locale['hour'], $minute => $locale['minute'], $second => $locale['second']);
        //	$timer_b = array($year => $locale['year_a'], $month => $locale['month_a'], $day => $locale['day_a'], $hour => $locale['hour_a'], $minute => $locale['minute_a'], $second => $locale['second_a']);
        $timer = array(
            $year => $locale['fmt_year'],
            $month => $locale['fmt_month'],
            $day => $locale['fmt_day'],
            $hour => $locale['fmt_hour'],
            $minute => $locale['fmt_minute'],
            $second => $locale['fmt_second']
        );
        foreach ($timer as $arr => $unit) {
            $calc = $calculated / $arr;
            if ($calc >= 1) {
                $answer = round($calc);
                //	$string = ($answer > 1) ? $timer_b[$arr] : $unit;
                $string = \PHPFusion\Locale::format_word($answer, $unit, array('add_count' => FALSE));
                return "<abbr class='atooltip' data-toggle='tooltip' data-placement='top' title='".showdate('longdate', $updated)."'>".$answer." ".$string." ".$locale['ago']."</abbr>";
            }
        }

        return NULL;
    }
}

if (!function_exists("days_current_month")) {
    function days_current_month() {
        $year = showdate("%Y", time());
        $month = showdate("%m", time());

        return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
    }
}

if (!function_exists("countdown")) {
    function countdown($time) {
        $locale = fusion_get_locale();
        $updated = stripinput($time);
        $second = 1;
        $minute = $second * 60;
        $hour = $minute * 60;
        $day = 24 * $hour;
        $month = days_current_month() * $day;
        $year = (date("L", $updated) > 0) ? 366 * $day : 365 * $day;
        $timer = array(
            $year => $locale['year'],
            $month => $locale['month'],
            $day => $locale['day'],
            $hour => $locale['hour'],
            $minute => $locale['minute'],
            $second => $locale['second']
        );
        $timer_b = array(
            $year => $locale['year_a'],
            $month => $locale['month_a'],
            $day => $locale['day_a'],
            $hour => $locale['hour_a'],
            $minute => $locale['minute_a'],
            $second => $locale['second_a']
        );
        foreach ($timer as $arr => $unit) {
            $calc = $updated / $arr;
            if ($calc >= 1) {
                $answer = round($calc);
                $string = ($answer > 1) ? $timer_b[$arr] : $unit;

                return "<abbr class='atooltip' data-toggle='tooltip' data-placement='top' title='~".showdate('newsdate', $updated + time())."'>$answer ".$string."</abbr>";
            }
        }
        if (!isset($answer)) {
            return "<abbr class='atooltip' data-toggle='tooltip' data-placement='top' title='".showdate('newsdate', time())."'>".$locale['now']."</abbr>";
        }
    }
}

if (!function_exists("opencollapse")
    && !function_exists("opencollapsebody")
    && !function_exists("closecollapsebody")
    && !function_exists("collapse_header_link")
    && !function_exists("collapse_footer_link")
    && !function_exists("closecollapse")
) {
    /**
     * Accordion template
     * @param $id - unique accordion id name
     * @return string
     */
    function opencollapse($id) {
        return "<div class='panel-group' id='".$id."' role='tablist' aria-multiselectable='true'>\n";
    }

    function opencollapsebody($title, $unique_id, $grouping_id, $active = 0, $class = FALSE) {
        $html = "<div class='panel panel-default'>\n";
        $html .= "<div class='panel-heading clearfix'>\n";
        $html .= "<div class='overflow-hide'>\n";
        $html .= "<span class='display-inline-block strong'><a ".collapse_header_link($grouping_id, $unique_id, $active, $class).">".$title."</a></span>\n";
        $html .= "</div>\n";
        $html .= "</div>\n";
        $html .= "<div ".collapse_footer_link($grouping_id, $unique_id, $active).">\n"; // body.
        return $html;
    }

    function closecollapsebody() {
        $html = "</div>\n"; // panel container
        $html .= "</div>\n"; // panel default

        return $html;
    }

    function collapse_header_link($id, $title, $active, $class = '') {
        $active = ($active) ? '' : 'collapsed';
        $title_id_cc = preg_replace('/[^A-Z0-9-]+/i', "-", $title);

        return "class='$class $active' data-toggle='collapse' data-parent='#".$id."' href='#".$title_id_cc."-".$id."' aria-expanded='true' aria-controls='".$title_id_cc."-".$id."'";
    }

    function collapse_footer_link($id, $title, $active, $class = '') {
        $active = ($active) ? 'in' : '';
        $title_id_cc = preg_replace('/[^A-Z0-9-]+/i', "-", $title);

        return "id='".$title_id_cc."-".$id."' class='panel-collapse collapse ".$active." ".$class."' role='tabpanel' aria-labelledby='headingOne'";
    }

    function closecollapse() {
        return "</div>\n";
    }
}

if (!function_exists("tab_active")
    && !function_exists("opentab")
    && !function_exists("opentabbody")
    && !function_exists("closetabbody")
    && !function_exists("closetab")
) {

    class FusionTabs {

        private $id = '';
        private $remember = FALSE;
        private $cookie_prefix = 'tab_js';
        private $cookie_name = '';
        private $tab_info = [];
        private $link_mode = FALSE;


        public static function tab_active($array, $default_active, $getname = FALSE) {
            if (!empty($getname)) {
                $section = isset($_GET[$getname]) && $_GET[$getname] ? $_GET[$getname] : $default_active;
                $count = count($array['title']);
                if ($count > 0) {
                    for ($i = 0; $i < $count; $i++) {
                        $id = $array['id'][$i];
                        if ($section == $id) {
                            return $id;
                        }
                    }
                } else {
                    return $default_active;
                }
            } else {
                $id = $array['id'][$default_active];
                return $id;;
            }
        }

        public function set_remember($value) {
            $this->remember = $value;
        }

        public function opentab($tab_title, $link_active_arrkey, $id, $link = FALSE, $class = FALSE, $getname = 'section', array $cleanup_GET = []) {
            $this->id = $id;
            $this->cookie_name = $this->cookie_prefix.'-'.$id;
            $this->tab_info = $tab_title;
            $this->link_mode = $link;

            $getArray = array($getname);
            if (!empty($cleanup_GET)) {
                $getArray = array_merge_recursive($cleanup_GET, $getArray);
            }
            if (empty($link) && $this->remember) {
                if (isset($_COOKIE[$this->cookie_name])) {
                    $link_active_arrkey = str_replace('tab-', '', $_COOKIE[$this->cookie_name]);
                }
            }
            $html = "<div class='nav-wrapper'>\n";
            $html .= "<ul id='$id' class='nav ".($class ? $class : 'nav-tabs')."'>\n";
            foreach ($tab_title['title'] as $arr => $v) {
                $v_title = $v;
                $tab_id = $tab_title['id'][$arr];
                $icon = (isset($tab_title['icon'][$arr])) ? $tab_title['icon'][$arr] : "";
                $link_url = '#';
                if ($link) {
                    $link_url = $link.(stristr($link, '?') ? '&' : '?').$getname."=".$tab_id; // keep all request except GET array
                    if ($link === TRUE) {
                        $link_url = clean_request($getname.'='.$tab_id.(defined('ADMIN_PANEL') ? "&aid=".$_GET['aid'] : ""), $getArray, FALSE);
                    }
                    $html .= ($link_active_arrkey == $tab_id) ? "<li class='active'>\n" : "<li>\n";
                } else {
                    $html .= ($link_active_arrkey == "".$tab_id) ? "<li class='active'>\n" : "<li>\n";
                }
                $html .= "<a class='pointer' ".(!$link ? "id='tab-".$tab_id."' data-toggle='tab' data-target='#".$tab_id."'" : "href='$link_url'")." role='tab'>\n".($icon ? "<i class='".$icon."'></i>" : '')." ".$v_title." </a>\n";
                $html .= "</li>\n";
            }
            $html .= "</ul>\n";
            $html .= "<div id='tab-content-$id' class='tab-content'>\n";
            if (empty($link) && $this->remember) {
                \PHPFusion\OutputHandler::addToJQuery("
                $('#".$id." > li').on('click', function() {
                    var cookieName = '".$this->cookie_name."';
                    var cookieValue = $(this).find(\"a[role='tab']\").attr('id');
                    Cookies.set(cookieName, cookieValue);
                });
                var cookieName = 'tab_js-".$id."';
                if (Cookies.get(cookieName)) {
                    $('#".$id."').find('#'+Cookies.get(cookieName)).click();
                }
                ");
            }

            return (string)$html;
        }

        /*
         * Deprecated $tab_title.
         * Deprecated $link
         *
         * Commit title:
         * Using globals without adding parameter to pass $id set on previous opentabs() to next opentabbody()
         */
        public function opentabbody($id, $link_active_arrkey = FALSE, $key = FALSE) {
            $key = $key ? $key : 'section';
            if (isset($_GET[$key]) && $this->link_mode) {
                if ($link_active_arrkey == $id) {
                    $status = 'in active';
                } else {
                    $status = '';
                }
            } else {
                if (!$this->link_mode) {
                    if ($this->remember) {
                        if (isset($_COOKIE[$this->cookie_name])) {
                            $link_active_arrkey = str_replace('tab-', '', $_COOKIE[$this->cookie_name]);
                        }
                    }
                }
                $status = ($link_active_arrkey == $id ? " in active" : '');

            }
            return "<div class='tab-pane fade".$status."' id='".$id."'>\n";
        }

        public function closetab(array $options = array()) {
            $locale = fusion_get_locale();
            $default_options = array(
                "tab_nav" => FALSE,
            );
            $options += $default_options;
            if ($options['tab_nav'] == TRUE) {
                $nextBtn = "<a class='btn btn-warning btnNext pull-right' >".$locale['next']."</a>";
                $prevBtn = "<a class='btn btn-warning btnPrevious m-r-10'>".$locale['previous']."</a>";
                add_to_jquery("
				$('.btnNext').click(function(){
				  $('.nav-tabs > .active').next('li').find('a').trigger('click');
				});
				$('.btnPrevious').click(function(){
				  $('.nav-tabs > .active').prev('li').find('a').trigger('click');
				});
			");
                echo "<div class='clearfix'>\n".$prevBtn.$nextBtn."</div>\n";
            }

            return "</div>\n</div>\n";
        }

        public function closetabbody() {
            return "</div>\n";
        }
    }

    $fusion_tabs = new FusionTabs();

    /**
     * Current Tab Active Selector
     *
     * @param      $array          - multidimension array consisting of keys 'title', 'id', 'icon'
     * @param      $default_active - 0 if link_mode is false, $_GET if link_mode is true
     * @param bool $getname        - set getname and turn tabs into link that listens to getname
     *
     * @return string
     * @todo: options base
     */
    function tab_active($array, $default_active, $getname = FALSE) {
        return \FusionTabs::tab_active($array, $default_active, $getname);
    }

    /**
     * Render Tab Links
     *
     * @param               $tab_title          entire array consisting of ['title'], ['id'], ['icon']
     * @param               $link_active_arrkey tab_active() function or the $_GET request to match the $tab_title['id']
     * @param               $id                 unique ID
     * @param bool|FALSE    $link               default false for jquery, true for php (will reload page)
     * @param bool|FALSE    $class              the class for the nav
     * @param string        $getname            the get request
     * @param array         $cleanup_GET        the request key that needs to be deleted
     * @param bool|FALSE    $remember           set to true to automatically remember tab using cookie.
     *                                          Example:
     *                                          $tab_title['title'][] = "Tab 1";
     *                                          $tab_title['id'][] = "tab1";
     *
     * $tab_title['title'][] = "Tab 2";
     * $tab_title['id'][] = "tab2";
     *
     * $tab_active = tab_active($tab_title, 0);
     *
     * Jquery:
     * echo opentab($tab_title, $tab_active, 'myTab', FALSE, 'nav-pills', 'ref', ['action', 'subaction']);
     *
     * PHP:
     * echo opentab($tab_title, $_GET['ref'], 'myTab', TRUE, 'nav-pills', 'ref', ['action', 'subaction']);
     *
     * @return string
     */
    function opentab($tab_title, $link_active_arrkey, $id, $link = FALSE, $class = FALSE, $getname = "section", array $cleanup_GET = [], $remember = FALSE) {
        global $fusion_tabs;

        return $fusion_tabs->opentab($tab_title, $link_active_arrkey, $id, $link, $class, $getname, $cleanup_GET, $remember);
    }

    /**
     * @param      $tab_title               deprecated, however this function is replaceable, and the params are accessible.
     * @param      $tab_id
     * @param bool $link_active_arrkey
     * @param bool $link                    deprecated, however this function is replaceable, and the params are accessible.
     * @param bool $key
     *
     * @return mixed
     */
    function opentabbody($tab_title, $tab_id, $link_active_arrkey = FALSE, $link = FALSE, $key = FALSE) {
        global $fusion_tabs;

        return $fusion_tabs->opentabbody($tab_id, $link_active_arrkey, $key);
    }

    function closetabbody() {
        global $fusion_tabs;

        return $fusion_tabs->closetabbody();
    }

    function closetab(array $options = array()) {
        global $fusion_tabs;

        return $fusion_tabs->closetab($options);
    }
}

if (!function_exists("display_ratings")) {
    /* Standard ratings display */
    function display_ratings($total_sum, $total_votes, $link = FALSE, $class = FALSE, $mode = '1') {
        $locale = fusion_get_locale();
        $start_link = $link ? "<a class='comments-item ".$class."' href='".$link."'>" : '';
        $end_link = $link ? "</a>\n" : '';
        $average = $total_votes > 0 ? number_format($total_sum / $total_votes, 2) : 0;
        $str = $mode == 1 ? $average.$locale['global_094'].format_word($total_votes, $locale['fmt_rating']) : "$average/$total_votes";
        if ($total_votes > 0) {
            $answer = $start_link."<i title='".$locale['ratings']."' class='fa fa-star-o m-l-0'></i>".$str.$end_link;
        } else {
            $answer = $start_link."<i title='".sprintf($locale['global_089a'], $locale['global_077'])."' class='fa fa-star-0 high-opacity m-l-0'></i>".$str.$end_link;
        }

        return $answer;
    }
}

if (!function_exists("display_comments")) {
    /* Standard comment display */
    function display_comments($news_comments, $link = FALSE, $class = FALSE, $mode = '1') {
        $locale = fusion_get_locale();
        $start_link = $link ? "<a class='comments-item ".$class."' href='".$link."' {%title%} >" : '';
        $end_link = $link ? "</a>\n" : '';
        $str = $mode == 1 ? format_word($news_comments, $locale['fmt_comment']) : $news_comments;
        if ($news_comments > 0) {
            $start_link = strtr($start_link, ['{%title%}' => "title='".$locale['global_073']."'"]);
        } else {
            $start_link = strtr($start_link, ['{%title%}' => "title='".sprintf($locale['global_089'], $locale['global_077'])."'"]);
        }

        return $start_link.$str.$end_link;
    }
}

if (!function_exists("fusion_confirm_exit")) {
    /* JS form exit confirmation if form has changed */
    function fusion_confirm_exit() {
        PHPFusion\OutputHandler::addToJQuery("
			$('form').change(function() {
				window.onbeforeunload = function() {
					return true;
				}
				$(':button').bind('click', function() {
					window.onbeforeunload = null;
				});
			});
		");
    }
}
