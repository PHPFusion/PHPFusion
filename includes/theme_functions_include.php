<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: theme_functions_include.php
| Author: Core Development Team (coredevs@phpfusion.com)
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
use PHPFusion\OutputHandler;
use PHPFusion\Panels;

defined('IN_FUSION') || exit;

/**
 * Show PHPFusion Performance
 *
 * @param bool $queries
 *
 * @return string
 */
function showrendertime($queries = TRUE) {
    $locale = fusion_get_locale();
    $db_connection = DatabaseFactory::getConnection('default');
    $mysql_queries_count = $db_connection::getGlobalQueryCount();
    if (fusion_get_settings('rendertime_enabled') == 1 || (fusion_get_settings('rendertime_enabled') == 2 && iADMIN)) {
        $res = showBenchmark();
        $res .= " | ";
        $res .= ($queries ? ucfirst($locale['global_173']).": ".$mysql_queries_count." | " : '');

        return $res;
    } else {
        return "";
    }
}

/**
 * Show benchmark and database performance.
 * Developer tools only (Translations not Required)
 *
 * @param bool   $show_sql_performance  True to pop up SQL analysis modal
 * @param string $performance_threshold Results that is slower than this will be highlighted
 *
 * @return string
 */
function showBenchmark($show_sql_performance = FALSE, $performance_threshold = '0.01') {
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
                $highlighted = $current_time > $performance_threshold;
                $modal_body .= "<div class='spacer-xs m-10".($highlighted ? " alert alert-warning" : "")."'>\n";
                $modal_body .= "<h5><strong>SQL run#$i : ".($highlighted ? "<span class='text-danger'>".$sql[0]."</span>" : "<span class='text-success'>".$sql[0]."</span>")." seconds</strong></h5>\n\r";
                $modal_body .= "[code]".$sql[1].($sql[2] ? " [Parameters -- ".implode(',', $sql[2])." ]" : '')."[/code]\n\r";
                $modal_body .= "<div>\n";
                $end_sql = end($sql[3]);
                $modal_body .= "<kbd>".addslashes($end_sql['file'])."</kbd><span class='badge pull-right'>Line #".$end_sql['line'].", ".$end_sql['function']."</span> - <a class='pointer' data-toggle='collapse' data-target='#trace_$connectionID'>Toggle Backtrace</a>\n";
                if (is_array($sql[3])) {
                    $modal_body .= "<div id='trace_$connectionID' class='alert alert-info collapse spacer-sm'>";
                    foreach ($sql[3] as $id => $debug_backtrace) {
                        $modal_body .= "<kbd>Stack Trace #$id - ".addslashes($debug_backtrace['file'])." @ Line ".$debug_backtrace['line']."</kbd><br/>";
                        if (!empty($debug_backtrace['args'][0])) {
                            $debug_line = $debug_backtrace['args'][0];
                            if (is_array($debug_backtrace['args'][0])) {
                                $debug_line = "";
                                foreach ($debug_backtrace['args'][0] as $line) {
                                    if (!is_array($line)) {
                                        $debug_line .= "<br/>".$line;
                                    }
                                }
                            }

                            $debug_param = "";
                            if (!empty($debug_backtrace['args'][1])) {
                                if (is_array($debug_backtrace['args'][1])) {
                                    $debug_param .= "<br/>array(";
                                    foreach ($debug_backtrace['args'][1] as $key => $value) {
                                        $debug_param .= "<br/><span class='m-l-15'>[$key] => $value,</span>";
                                    }
                                    $debug_param .= "<br/>);";
                                } else {
                                    $debug_param .= $debug_backtrace['args'][1];
                                }
                            }
                            $modal_body .= "Statement::: <code>".addslashes($debug_line)."</code><br/>Parameters::: <code>".($debug_param ?: "--")."</code><br/>";
                        }

                    }
                    $modal_body .= "</div>\n";
                }
                $modal_body .= "</div>\n";
                $modal_body .= "</div>\n";
                $i++;
                $time = $current_time + $time;
            }
        }
        $modal .= parse_textarea($modal_body, FALSE, TRUE, TRUE, NULL, FALSE, FALSE);
        $modal .= modalfooter("<h4><strong>Total Time Expended in ALL SQL Queries: ".$time." seconds</strong></h4>", FALSE);
        $modal .= closemodal();
        add_to_footer($modal);
    }
    $render_time = substr((microtime(TRUE) - START_TIME), 0, 7);
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

/**
 * Show memory usage
 *
 * @return string
 */
function showMemoryUsage() {
    $locale = fusion_get_locale();
    $memory_allocated = parsebytesize(memory_get_peak_usage(TRUE));
    $memory_used = parsebytesize(memory_get_peak_usage(FALSE));

    return $locale['global_174'].": ".$memory_used."/".$memory_allocated;
}

/**
 * Show PHPFusion copyright.
 *
 * @param string $class
 * @param false  $nobreak
 *
 * @return string
 */
function showcopyright($class = "", $nobreak = FALSE) {
    $link_class = $class ? " class='$class' " : "";
    $res = "Powered by <a href='https://phpfusion.com'".$link_class.">PHPFusion</a> Copyright &copy; ".date("Y")." PHP Fusion Inc";
    $res .= ($nobreak ? "&nbsp;" : "<br />\n");
    $res .= "Released as free software without warranties under <a href='https://www.gnu.org/licenses/agpl-3.0.html'".$link_class." target='_blank'>GNU Affero GPL</a> v3.\n";

    return $res;
}

/**
 * Visitor counter
 *
 * @return string
 */
function showcounter() {
    $locale = fusion_get_locale();
    $settings = fusion_get_settings();
    if ($settings['visitorcounter_enabled']) {
        return "<!--counter-->".number_format($settings['counter'], 0, $settings['number_delimiter'], $settings['thousands_separator'])." ".($settings['counter'] == 1 ? $locale['global_170'] : $locale['global_171']);
    } else {
        return "";
    }
}

/**
 * Show privacy policy
 *
 * @return string
 */
function showprivacypolicy() {
    $html = '';
    if (!empty(fusion_get_settings('privacy_policy'))) {
        $html .= "<a href='".BASEDIR."print.php?type=P' id='privacy_policy'>".fusion_get_locale('global_176')."</a>";
        $modal = openmodal('privacy_policy', fusion_get_locale('global_176'), ['button_id' => 'privacy_policy']);
        $modal .= parse_textarea(\PHPFusion\QuantumFields::parse_label(fusion_get_settings('privacy_policy')));
        $modal .= closemodal();
        add_to_footer($modal);
    }

    return $html;
}

if (!function_exists("alert")) {
    /**
     * Creates an alert bar
     *
     * @param string $title
     * @param array  $options
     *
     * @return string
     */
    function alert($title, $options = []) {
        $options += [
            "class"   => !empty($options['class']) ? $options['class'] : 'alert-danger',
            "dismiss" => !empty($options['dismiss']) && $options['dismiss'] == TRUE
        ];
        if ($options['dismiss'] == TRUE) {
            $html = "<div class='alert alert-dismissable ".$options['class']."'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>$title</div>";
        } else {
            $html = "<div class='alert ".$options['class']."'>$title</div>";
        }
        add_to_jquery("$('div.alert a').addClass('alert-link');");

        return $html;
    }
}

if (!function_exists('get_theme_settings')) {
    /**
     * Get the widget settings for the theme settings table
     *
     * @param $theme_folder
     *
     * @return array|false
     */
    function get_theme_settings($theme_folder) {
        $settings_arr = [];
        $set_result = dbquery("SELECT settings_name, settings_value FROM ".DB_SETTINGS_THEME." WHERE settings_theme=:themeset", [':themeset' => $theme_folder]);
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
 *
 * @param $table_id - table ID
 *
 * @return string
 */
function fusion_sort_table($table_id) {
    if (!defined('TABLE_SORTER')) {
        define('TABLE_SORTER', TRUE);
        add_to_footer("<script type='text/javascript' src='".INCLUDES."jquery/tablesorter/jquery.tablesorter.min.js'></script>\n");
    }
    add_to_jquery("$('#".$table_id."').tablesorter();");

    return "tablesorter";
}

if (!function_exists("label")) {
    /**
     * Creates label
     *
     * @param string $label
     * @param array  $options
     *
     * @return string
     */
    function label($label, $options = []) {
        $options += [
            "class" => !empty($options['class']) ? $options['class'] : 'label-default',
            "icon"  => !empty($options['icon']) ? "<i class='".$options['icon']."'></i> " : '',
        ];

        return "<span class='label ".$options['class']."'>".$options['icon'].$label."</span>\n";
    }
}

if (!function_exists("badge")) {
    /**
     * Creates badge
     *
     * @param string $label
     * @param array  $options
     *
     * @return string
     */
    function badge($label, $options = []) {
        $options += [
            "class" => !empty($options['class']) ? $options['class'] : '',
            "icon"  => !empty($options['icon']) ? "<i class='".$options['icon']."'></i> " : '',
        ];

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
     *
     * @param string $id    Unique modal ID
     * @param string $title Modal title
     * @param array  $options
     *
     * @return string
     */
    function openmodal($id, $title, $options = []) {
        $locale = fusion_get_locale();
        $options += [
            'class'        => !empty($options['class']) ?: 'modal-lg',
            'button_id'    => '',
            'button_class' => '',
            'static'       => FALSE,
            'hidden'       => FALSE,  // force a modal to be hidden at default, you will need a jquery trigger $('#your_modal_id').modal('show'); manually
        ];

        $modal_trigger = '';
        if (!empty($options['button_id']) || !empty($options['button_class'])) {
            $modal_trigger = !empty($options['button_id']) ? "#".$options['button_id'] : ".".$options['button_class'];
        }

        if ($options['static'] && !empty($modal_trigger)) {
            OutputHandler::addToJQuery("$('".$modal_trigger."').bind('click', function(e){ $('#".$id."-Modal').modal({backdrop: 'static', keyboard: false}).modal('show'); e.preventDefault(); });");
        } else if ($options['static'] && empty($options['button_id'])) {
            OutputHandler::addToJQuery("$('#".$id."-Modal').modal({	backdrop: 'static',	keyboard: false }).modal('show');");
        } else if ($modal_trigger && empty($options['static'])) {
            OutputHandler::addToJQuery("$('".$modal_trigger."').bind('click', function(e){ $('#".$id."-Modal').modal('show'); e.preventDefault(); });");
        } else {
            if (!$options['hidden']) {
                OutputHandler::addToJQuery("$('#".$id."-Modal').modal('show');");
            }
        }
        $html = '';
        $html .= "<div class='modal' id='$id-Modal' tabindex='-1' role='dialog' aria-labelledby='$id-ModalLabel' aria-hidden='true'>\n";
        $html .= "<div class='modal-dialog ".$options['class']."' role='document'>\n";
        $html .= "<div class='modal-content'>\n";
        if ($title) {
            $html .= "<div class='modal-header'>";
            $html .= "<div class='modal-title pull-left' id='$id-title'>$title</div>\n";
            $html .= ($options['static'] ? '' : "<button type='button' class='btn btn-default btn-sm pull-right' data-dismiss='modal'><i class='fa fa-times'></i> ".$locale['close']."</button>\n");
            $html .= "</div>\n";
        }
        $html .= "<div class='modal-body'>\n";

        return $html;
    }

    /**
     * Adds a modal footer in between openmodal and closemodal.
     *
     * @param string $content
     * @param bool   $dismiss
     *
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
     *
     * @return string
     */
    function closemodal() {
        return "</div>\n</div>\n</div>\n</div>\n";
    }
}

if (!function_exists("progress_bar")) {
    /**
     * Render a progress bar
     *
     * @param int|int[]            $num   Max of 100
     * @param string|string[]|null $title Label for the progress bar
     * @param array                $options
     *                                    class           additional class for the progress bar
     *                                    height          the height of the progress bar in px
     *                                    reverse         set to true to have the color counting reversed
     *                                    disable         set to true to have the progress bar disabled status
     *                                    hide_info       set to true to hide the information in the progress bar rendering
     *                                    progress_class  have it your custom progress bar class with your own custom class
     *
     * @return string
     */
    function progress_bar($num, $title = NULL, $options = []) {
        $default_options = [
            'class'          => '',
            'height'         => '',
            'reverse'        => FALSE,
            'as_percent'     => TRUE,
            'disabled'       => FALSE,
            'hide_info'      => FALSE,
            'progress_class' => ''
        ];
        $options += $default_options;

        $height = ($options['height']) ? $options['height'] : '20px';
        if (!function_exists('bar_color')) {
            function bar_color($num, $reverse) {
                $auto_class = $reverse ? "progress-bar-success" : "progress-bar-danger";
                if ($num > 71) {
                    $auto_class = ($reverse) ? 'progress-bar-danger' : 'progress-bar-success';
                } else if ($num > 55) {
                    $auto_class = ($reverse) ? 'progress-bar-warning' : 'progress-bar-info';
                } else if ($num > 25) {
                    $auto_class = ($reverse) ? 'progress-bar-info' : 'progress-bar-warning';
                } else if ($num < 25) {
                    $auto_class = ($reverse) ? 'progress-bar-success' : 'progress-bar-danger';
                }

                return $auto_class;
            }
        }
        $_barcolor = ['progress-bar-success', 'progress-bar-info', 'progress-bar-warning', 'progress-bar-danger'];
        $_barcolor_reverse = [
            'progress-bar-success',
            'progress-bar-info',
            'progress-bar-warning',
            'progress-bar-danger'
        ];
        $html = '';
        if (is_array($num)) {
            $i = 0;
            $chtml = "";
            $cTitle = "";
            $cNum = "";
            foreach ($num as $value) {

                $int = intval($num);

                if ($options['disabled'] == TRUE) {
                    $value = "&#x221e;";
                } else {
                    $value = $value > 0 ? $value.' ' : '0 ';
                    $value .= $options['as_percent'] ? '%' : '';
                }

                $c2Title = "";

                if (is_array($title)) {
                    $c2Title = $title[$i];
                } else {
                    $cTitle = $title;
                }

                $auto_class = ($options['reverse']) ? $_barcolor_reverse[$i] : $_barcolor[$i];
                $classes = (is_array($options['class'])) ? $options['class'][$i] : $auto_class;

                $cNum .= "<div class='progress display-inline-block m-0' style='width:20px; height: 10px; '>\n";
                $cNum .= "<span class='progress-bar ".$classes."' style='width:100%'></span></div>\n";
                $cNum .= "<div class='display-inline-block m-r-5'>".$c2Title." ".$value."</div>\n";
                $chtml .= "<div title='".$c2Title."' class='progress-bar ".$classes."' role='progressbar' aria-valuenow='$value' aria-valuemin='0' aria-valuemax='100' style='width: $int%'>\n";
                $chtml .= "</div>\n";
                $i++;
            }
            $html .= ($options['hide_info'] == FALSE ? "<div class='text-right m-b-10'><span class='pull-left'>$cTitle</span><span class='clearfix'>$cNum </span></div>\n" : "");
            $html .= "<div class='progress ".$options['progress_class']."' style='height: ".$height."'>\n";
            $html .= $chtml;
            $html .= "</div>\n";
        } else {
            $int = intval($num);
            if ($options['disabled'] == TRUE) {
                $num = "&#x221e;";
            } else {
                $num = $num > 0 ? $num.' ' : '0 ';
                $num .= $options['as_percent'] ? '%' : '';
            }

            $auto_class = bar_color($int, $options['reverse']);
            $class = (!$options['class']) ? $auto_class : $options['class'];

            $html .= ($options['hide_info'] === FALSE ? "<div class='text-right m-b-10'><span class='pull-left'>$title</span><span class='clearfix'>$num</span></div>\n" : "");
            $html .= "<div class='progress ".$options['progress_class']."' style='height: ".$height."'>\n";
            $html .= "<div class='progress-bar ".$class."' role='progressbar' aria-valuenow='$num' aria-valuemin='0' aria-valuemax='100' style='width: $int%'>\n";
            $html .= "</div></div>\n";
        }

        return $html;
    }
}

if (!function_exists("check_panel_status")) {
    /**
     * Check panel exclusions in certain page, which will be dropped sooner or later
     * Because we will need page composition database soon
     *
     * @param string $side
     *
     * @return bool
     */
    function check_panel_status($side) {
        return Panels::check_panel_status($side);
    }
}

if (!function_exists("showbanners")) {
    /**
     * Displays the system settings banner
     *
     * @param null $display
     *
     * @return string
     */
    function showbanners($display = NULL) {
        $settings = fusion_get_settings();

        ob_start();
        if ($display == 2) {
            if ($settings['sitebanner2']) {
                if ($settings['allow_php_exe']) {
                    eval("?>".stripslashes($settings['sitebanner2'])."<?php ");
                } else {
                    echo parse_textarea($settings['sitebanner2'], FALSE, FALSE, TRUE, NULL, TRUE);
                }
            }
        } else {
            if ($settings['sitebanner1']) {
                if ($settings['allow_php_exe']) {
                    eval("?>".stripslashes($settings['sitebanner1'])."<?php ");
                } else {
                    echo parse_textarea($settings['sitebanner1'], FALSE, FALSE, TRUE, NULL, TRUE);
                }
            }
        }
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }
}

if (!function_exists("showlogo")) {
    /**
     * Show site logo
     *
     * @param string $class
     *
     * @return string
     */
    function showlogo($class = 'logo') {
        return "<div class='".$class."'><a href='".BASEDIR.fusion_get_settings('opening_page')."' title='".fusion_get_settings('sitename')."'><img src='".BASEDIR.fusion_get_settings('sitebanner')."' alt='Logo'></a></div>";
    }
}

if (!function_exists("showsublinks")) {

    /**
     * Displays Site Links Navigation Bar
     *
     * @param string $sep   - Custom seperator text
     * @param string $class - Class
     * @param array  $options
     *
     * Notice: There is a more powerful method now that offers more powerful manipulation methods
     * that non oo approach cannot ever achieve using cache and the new mutator method
     * SiteLinks::setSubLinks($sep, $class, $options)->showsublinks(); for normal usage
     *
     * @return string
     */
    function showsublinks($sep = "", $class = "navbar-default", $options = []) {
        $options += [
            'seperator'    => $sep,
            'navbar_class' => $class,
        ];
        return \PHPFusion\SiteLinks::setSubLinks($options)->showSubLinks();
    }

}

if (!function_exists("panelbutton")) {
    /**
     * Show the collapse or expand button for panels which are collapsible
     *
     * @param string $state
     * @param string $bname
     *
     * @return string
     */
    function panelbutton($state, $bname) {
        $bname = preg_replace("/[^a-zA-Z0-9\s]/", "_", $bname);
        if (isset($_COOKIE["fusion_box_".$bname])) {
            if ($_COOKIE["fusion_box_".$bname] == "none") {
                $state = "off";
            } else {
                $state = "on";
            }
        }

        return "<img src='".get_image("panel_".($state == "on" ? "off" : "on"))."' id='b_".$bname."' class='panelbutton pointer' alt='panelstate' onclick=\"flipBox('".$bname."')\" />";
    }
}

if (!function_exists("panelstate")) {
    /**
     * Checks the state of a panel
     *
     * @param string $state
     * @param string $bname
     * @param string $element
     *
     * @return string
     */
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

if (!function_exists('display_avatar')) {
    /**
     * Display user avatar
     *
     * @param array  $userdata
     *                              Indexes:
     *                              - user_id
     *                              - user_name
     *                              - user_avatar
     *                              - user_status
     * @param string $size          A valid size for CSS max-width and max-height.
     * @param string $class         Classes for the link
     * @param bool   $link          False if you want to display the avatar without link. TRUE by default.
     * @param string $img_class     Classes for the image
     * @param string $custom_avatar Custom default avatar
     *
     * @return string
     */
    function display_avatar($userdata, $size, $class = '', $link = TRUE, $img_class = '', $custom_avatar = '') {
        if (empty($userdata)) {
            $userdata = [
                'user_name' => fusion_get_locale('user_anonymous')
            ];
        }

        $userdata += [
            'user_id'     => 0,
            'user_name'   => '',
            'user_avatar' => '',
            'user_status' => ''
        ];

        $link = fusion_get_settings('hide_userprofiles') == TRUE ? (iMEMBER ? $link : FALSE) : $link;
        $link = $userdata['user_id'] !== 0 ? $link : FALSE;
        $class = ($class) ? "class='$class'" : '';

        $hasAvatar = $userdata['user_avatar'] && file_exists(IMAGES."avatars/".$userdata['user_avatar']) && $userdata['user_status'] != '5' && $userdata['user_status'] != '6';
        $name = !empty($userdata['user_name']) ? $userdata['user_name'] : 'Guest';

        if ($hasAvatar) {
            // Check if remote load avatar will break image paths or not
            //$user_avatar = fusion_get_settings('siteurl')."images/avatars/".$userdata['user_avatar'];
            //$imgTpl = "<img class='avatar img-responsive $img_class' alt='".$name."' data-pin-nopin='true' style='display:inline; width:$size; max-height:$size;' src='%s'>";
            //$img = sprintf($imgTpl, $user_avatar);
            $imgTpl = "<img class='avatar img-responsive $img_class' alt='".$name."' data-pin-nopin='true' style='display:inline; width:$size; max-height:$size;' src='%s'>";
            $img = sprintf($imgTpl, IMAGES."avatars/".$userdata['user_avatar']);
        } else {
            if (!empty($custom_avatar) && file_exists($custom_avatar)) {
                $imgTpl = "<img class='avatar img-responsive $img_class' alt='".$name."' data-pin-nopin='true' style='display:inline; width:$size; max-height:$size;' src='%s'>";
                $img = sprintf($imgTpl, $custom_avatar);
            } else {
                $color = string_to_color_code($userdata['user_name']);
                $font_color = get_color_brightness($color) > 130 ? '000' : 'fff';

                if (function_exists('mb_substr') && function_exists('mb_strtoupper')) {
                    $first_char = mb_substr($userdata['user_name'], 0, 1, 'UTF-8');
                    $first_char = mb_strtoupper($first_char, 'UTF-8');
                } else {
                    $first_char = substr($userdata['user_name'], 0, 1);
                    $first_char = strtoupper($first_char);
                }

                $size_int = (int)filter_var($size, FILTER_SANITIZE_NUMBER_INT);
                $img = '<div class="display-inline-block va avatar '.$img_class.'" style="width:'.$size.';max-height:'.$size.';"><svg viewBox="0 0 '.$size_int.' '.$size_int.'" preserveAspectRatio="xMidYMid meet"><rect fill="#'.$color.'" stroke-width="0" y="0" x="0" width="'.$size.'" height="'.$size.'"/><text class="m-t-5" font-size="'.($size_int - 5).'" fill="#'.$font_color.'" x="50%" y="50%" text-anchor="middle" dy="0.325em">'.$first_char.'</text></svg></div>';
            }
        }

        return $link ? sprintf("<a $class title='".$userdata['user_name']."' href='".BASEDIR."profile.php?lookup=".$userdata['user_id']."'>%s</a>", $img) : $img;
    }
}

/**
 * String to color code
 *
 * @param string $text
 *
 * @return string
 */
function string_to_color_code($text) {
    $min_brightness = 50; // integer between 0 and 100
    $spec = 3; // integer between 2-10, determines how unique each color will be

    $hash = sha1(md5(sha1($text)));
    $colors = [];
    for ($i = 0; $i < 3; $i++) {
        $colors[$i] = max([round(((hexdec(substr($hash, $spec * $i, $spec))) / hexdec(str_pad('', $spec, 'F'))) * 255), $min_brightness]);
    }

    if ($min_brightness > 0) {
        while (array_sum($colors) / 3 < $min_brightness) {
            for ($i = 0; $i < 3; $i++) {
                $colors[$i] += 10;
            }
        }
    }

    $output = '';

    for ($i = 0; $i < 3; $i++) {
        $output .= str_pad(dechex($colors[$i]), 2, 0, STR_PAD_LEFT);
    }

    return $output;
}

/**
 * Get color brightness by given HEX code
 *
 * @param string $hex
 *
 * @return float
 */
function get_color_brightness($hex) {
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    return (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
}

if (!function_exists('colorbox')) {
    /**
     * Display image in colorbox
     *
     * @param string $img_path
     * @param string $img_title
     * @param bool   $responsive
     * @param string $class
     * @param false  $as_text
     *
     * @return string
     */
    function colorbox($img_path, $img_title, $responsive = TRUE, $class = '', $as_text = FALSE) {
        if (!defined('COLORBOX')) {
            define('COLORBOX', TRUE);
            $colorbox_css = file_exists(THEME.'colorbox/colorbox.css') ? THEME.'colorbox/colorbox.css' : INCLUDES.'jquery/colorbox/colorbox.css';
            add_to_head("<link rel='stylesheet' href='$colorbox_css' type='text/css' media='screen' />");
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

if (!function_exists("thumbnail")) {
    /**
     * Show image thumbnail
     *
     * @param string $src
     * @param string $size
     * @param bool   $url
     * @param bool   $colorbox
     * @param bool   $responsive
     * @param string $class
     *
     * @return string
     */
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
        $html .= $url || $colorbox ? "<a ".($colorbox && $src ? "class='colorbox' " : '').($url ? "href='".$url."'" : '')." >" : '';
        if ($src && file_exists($src) && !is_dir($src) || stristr($src, "?")) {
            $html .= "<img ".($responsive ? "class='img-responsive' " : '')."src='$src'".(!$responsive && ($_offset_w || $_offset_h) ? " style='margin-left: -".$_offset_w."px; margin-top: -".$_offset_h."px' " : '')." alt='thumbnail'/>\n";
        } else {
            $size = str_replace('px', '', $size);

            if (!defined('HOLDERJS')) {
                define('HOLDERJS', TRUE);
                add_to_footer("<script src='".INCLUDES."jquery/holder.min.js'></script>");
            }

            $html .= "<img src='holder.js/".$size."x".$size."/text:' alt='thumbnail'/>\n";
        }
        $html .= $url || $colorbox ? "</a>" : '';
        $html .= "</div>\n";
        if ($colorbox && $src && !defined('COLORBOX')) {
            define('COLORBOX', TRUE);
            add_to_head("<link rel='stylesheet' href='".INCLUDES."jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
            add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");
            add_to_jquery("$('.colorbox').colorbox({width: '75%', height: '75%'});");
        }

        return $html;
    }
}

if (!function_exists("lorem_ipsum")) {
    /**
     * Generate random lorem ipsum text by given length.
     *
     * @param int $length
     *
     * @return string
     */
    function lorem_ipsum($length) {
        $text = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum aliquam felis nunc, in dignissim metus suscipit eget. Nunc scelerisque laoreet purus, in ullamcorper magna sagittis eget. Aliquam ac rhoncus orci, a lacinia ante. Integer sed erat ligula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Fusce ullamcorper sapien mauris, et tempus mi tincidunt laoreet. Proin aliquam vulputate felis in viverra.";
        $text .= "Duis sed lorem vitae nibh sagittis tempus sed sed enim. Mauris egestas varius purus, a varius odio vehicula quis. Donec cursus interdum libero, et ornare tellus mattis vitae. Phasellus et ligula velit. Vivamus ac turpis dictum, congue metus facilisis, ultrices lorem. Cras imperdiet lacus in tincidunt pellentesque. Sed consectetur nunc vitae fringilla volutpat. Mauris nibh justo, luctus eu dapibus in, pellentesque non urna. Nulla ullamcorper varius lacus, ut finibus eros interdum id. Proin at pellentesque sapien. Integer imperdiet, sapien nec tristique laoreet, sapien lacus porta nunc, tincidunt cursus risus mauris id quam.";
        $text .= "Ut vulputate mauris in facilisis euismod. Ut id libero vitae neque laoreet placerat a id mi. Integer ornare risus placerat, interdum nisi sed, commodo ligula. Integer at ipsum id magna blandit volutpat. Sed euismod mi odio, vitae molestie diam ornare quis. Aenean id ligula finibus, convallis risus a, scelerisque tellus. Morbi quis pretium lectus. In convallis hendrerit sem. Vestibulum sed ultricies massa, ut tempus risus. Nunc aliquam at tellus quis lobortis. In hac habitasse platea dictumst. Vestibulum maximus, nibh at tristique viverra, eros felis ultrices nunc, et efficitur nunc augue a orci. Phasellus et metus mauris. Morbi ut ex ut urna tincidunt varius eu id diam. Aenean vestibulum risus sed augue vulputate, a luctus ligula laoreet.";
        $text .= "Nam tempor sodales mi nec ullamcorper. Mauris tristique ligula augue, et lobortis turpis dictum vitae. Aliquam leo massa, posuere ac aliquet quis, ultricies eu elit. Etiam et justo et nulla cursus iaculis vel quis dolor. Phasellus viverra cursus metus quis luctus. Nulla massa turpis, porttitor vitae orci sed, laoreet consequat urna. Etiam congue turpis ac metus facilisis pretium. Nam auctor mi et auctor malesuada. Mauris blandit nulla quis ligula cursus, ut ullamcorper dui posuere. Fusce sed urna id quam finibus blandit tempus eu tellus. Vestibulum semper diam id ante iaculis iaculis.";
        $text .= "Fusce suscipit maximus neque, sed consectetur elit hendrerit at. Sed luctus mi in ex auctor mollis. Suspendisse ac elementum tellus, ut malesuada purus. Mauris condimentum elit at dolor eleifend iaculis. Aenean eget faucibus mauris. Pellentesque fermentum mattis imperdiet. Donec mattis nisi id faucibus finibus. Vivamus in eleifend lorem, vel dictum nisl. Morbi ut mollis arcu.";

        return trim_text($text, $length);
    }
}

if (!function_exists("timer")) {
    /**
     * Show time ago from timestamp.
     *
     * @param int $time
     *
     * @return string
     */
    function timer($time = NULL) {
        $locale = fusion_get_locale();
        if (!$time) {
            $time = time();
        }
        $time = stripinput($time);
        $current = time();
        $calculated = $current - $time;
        $second = 1;
        $minute = $second * 60;
        $hour = $minute * 60;
        $day = 24 * $hour;
        $month = days_current_month() * $day;
        $year = (date("L", $time) > 0) ? 366 * $day : 365 * $day;
        if ($calculated < 1) {
            return "<abbr class='atooltip' data-toggle='tooltip' data-placement='top' title='".showdate('longdate', $time)."'>".$locale['just_now']."</abbr>\n";
        }

        $timer = [
            $year   => $locale['timer_year'],
            $month  => $locale['timer_month'],
            $day    => $locale['timer_day'],
            $hour   => $locale['timer_hour'],
            $minute => $locale['timer_minute'],
            $second => $locale['timer_second']
        ];

        foreach ($timer as $arr => $unit) {
            $calc = $calculated / $arr;
            if ($calc >= 1) {
                $answer = round($calc);
                $string = \PHPFusion\Locale::format_word($answer, $unit, ['add_count' => FALSE]);
                $text = strtr($locale['timer'], [
                    '[DAYS]'   => $answer." ".$string,
                    '[AGO]'    => $locale['ago'],
                    '[ANSWER]' => $answer,
                    '[STRING]' => $string
                ]);
                return "<abbr class='atooltip' data-toggle='tooltip' data-placement='top' title='".showdate('longdate', $time)."'>".$text."</abbr>";
            }
        }

        return NULL;
    }
}

if (!function_exists("days_current_month")) {
    /**
     * Days in the current month
     *
     * @return int
     */
    function days_current_month() {
        $year = showdate("%Y", time());
        $month = showdate("%m", time());

        return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
    }
}

if (!function_exists("countdown")) {
    /**
     * Counts how many days remain until the specified date.
     *
     * @param $time
     *
     * @return string|null
     */
    function countdown($time) {
        $locale = fusion_get_locale();
        $updated = $time - time();
        $second = 1;
        $minute = $second * 60;
        $hour = $minute * 60;
        $day = 24 * $hour;
        $month = days_current_month() * $day;
        $year = (date("L", $updated) > 0) ? 366 * $day : 365 * $day;
        $timer = [
            $year   => $locale['year'],
            $month  => $locale['month'],
            $day    => $locale['day'],
            $hour   => $locale['hour'],
            $minute => $locale['minute'],
            $second => $locale['second']
        ];
        $timer_b = [
            $year   => $locale['year_a'],
            $month  => $locale['month_a'],
            $day    => $locale['day_a'],
            $hour   => $locale['hour_a'],
            $minute => $locale['minute_a'],
            $second => $locale['second_a']
        ];
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

        return NULL;
    }
}

if (!function_exists("opencollapse")
    && !function_exists("opencollapsebody")
    && !function_exists("closecollapsebody")
    && !function_exists("closecollapse")
) {
    /**
     * Create accordion
     *
     * @param string $id unique accordion ID
     *
     * @return string
     */
    function opencollapse($id) {
        return '<div class="panel-group" id="'.$id.'-accordion" role="tablist" aria-multiselectable="true">';
    }

    /**
     * Create collapsing panel
     *
     * @param string $title
     * @param string $unique_id
     * @param string $grouping_id
     * @param bool   $active
     * @param string $class
     *
     * @return string
     */
    function opencollapsebody($title, $unique_id, $grouping_id, $active = FALSE, $class = NULL) {
        $html = '<div class="panel panel-default '.$class.'">';

        $html .= '<div class="panel-heading" role="tab" id="'.$unique_id.'-collapse-heading">';
        $html .= '<h4 class="panel-title">';
        $html .= '<a role="button" data-toggle="collapse" data-parent="#'.$grouping_id.'-accordion" href="#'.$unique_id.'-collapse" aria-expanded="true" aria-controls="'.$unique_id.'-collapse">'.$title.'</a>';
        $html .= '</h4>';
        $html .= '</div>';

        $html .= '<div id="'.$unique_id.'-collapse" class="panel-collapse collapse'.($active ? ' in' : '').'" role="tabpanel" aria-labelledby="'.$unique_id.'-collapse-heading">';
        $html .= '<div class="panel-body">';
        return $html;
    }

    /**
     * Close collapsing panel
     *
     * @return string
     */
    function closecollapsebody() {
        $html = '</div>'; // .panel-body
        $html .= '</div>'; // .panel-collapse
        $html .= '</div>'; // .panel-default

        return $html;
    }

    /**
     * Close accordion
     *
     * @return string
     */
    function closecollapse() {
        return '</div>';
    }
}

if (!function_exists("tab_active")
    && !function_exists("opentab")
    && !function_exists("opentabbody")
    && !function_exists("closetabbody")
    && !function_exists("closetab")
) {

    class FusionTabs {
        private $remember = FALSE;
        private $cookie_prefix = 'tab_js';
        private $cookie_name = '';
        private $link_mode = FALSE;

        public static function tab_active($array, $default_active, $getname = NULL) {
            if (!empty($getname)) {
                $section = get($getname) ?: $default_active;
                //$section = isset($_GET[$getname]) && $_GET[$getname] ? $_GET[$getname] : $default_active;
                $count = count($array['title']);

                if ($count > 0) {
                    foreach ($array["id"] as $tab_id) {
                        if ($section == $tab_id) {
                            return $tab_id;
                        }

                    }
                }

                return $default_active;
            }

            return $array['id'][$default_active];
        }

        /**
         * @param      $array
         * @param      $default_active
         * @param bool $getname
         *
         * @return int
         */
        public static function tabIndex($array, $default_active, $getname = FALSE) {
            if (!empty($getname)) {
                $section = get($getname) ?: $default_active;
                //$section = isset($_GET[$getname]) && $_GET[$getname] ? $_GET[$getname] : $default_active;
                $count = count($array['title']);
                if ($count > 0) {
                    for ($tabCount = 0; $tabCount < $count; $tabCount++) {
                        $tab_id = $array['id'][$tabCount];
                        if ($section == $tab_id) {
                            return $tabCount;
                        }
                    }
                }
            }
            return $default_active;
        }

        public function set_remember($value) {
            $this->remember = $value;
        }

        public function opentab($tab_title, $link_active_arrkey, $id, $link = FALSE, $class = FALSE, $getname = 'section', array $cleanup_GET = []) {
            $this->cookie_name = $this->cookie_prefix.'-'.$id;
            $this->link_mode = $link;

            $getArray = [$getname];
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

                        $keep_filtered = FALSE;
                        if (in_array("*", $cleanup_GET)) {
                            $getArray = [];
                            $keep_filtered = TRUE;
                        }

                        $link_url = clean_request($getname.'='.$tab_id.(defined('ADMIN_PANEL') ? "&aid=".$_GET['aid'] : ""), $getArray, $keep_filtered);
                    }

                    $active = ($link_active_arrkey == $tab_id) ? ' active' : '';
                } else {
                    $active = ($link_active_arrkey == "".$tab_id) ? ' active' : '';
                }

                $bs4_li = defined('BOOTSTRAP4') ? 'nav-item' : '';
                $html .= '<li class="'.$bs4_li.$active.'">';
                $bs4 = defined('BOOTSTRAP4') ? ' nav-link' : '';
                $html .= "<a class='pointer".$bs4.$active."' ".(!$link ? "id='tab-".$tab_id."' aria-controls='#".$tab_id."' data-toggle='tab' data-target='#".$tab_id."'" : "href='$link_url'")." role='tab'>\n".($icon ? "<i class='".$icon."'></i>" : '')." ".$v_title." </a>\n";
                $html .= "</li>\n";
            }
            $html .= "</ul>\n";
            $html .= "<div id='tab-content-$id' class='tab-content'>\n";
            if (empty($link) && $this->remember) {
                if (!defined('JS_COOKIES')) {
                    define('JS_COOKIES', TRUE);
                    OutputHandler::addToFooter('<script type="text/javascript" src="'.INCLUDES.'jscripts/js.cookie.min.js"></script>');
                }
                OutputHandler::addToJQuery("
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
        public function opentabbody($id, $link_active_arrkey = NULL, $key = NULL) {
            $key = $key ? $key : 'section';

            $bootstrap = defined('BOOTSTRAP4') ? ' show' : '';

            if (isset($_GET[$key]) && $this->link_mode) {
                if ($link_active_arrkey == $id) {
                    $status = 'in active'.$bootstrap;
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
                $status = ($link_active_arrkey == $id ? " in active".$bootstrap : '');

            }
            return "<div class='tab-pane fade".$status."' id='".$id."'>\n";
        }

        public function closetabbody() {
            return "</div>\n";
        }

        public function closetab($options = []) {
            $locale = fusion_get_locale();
            $default_options = [
                "tab_nav" => FALSE,
            ];
            $options += $default_options;
            if ($options['tab_nav'] == TRUE) {
                $nextBtn = "<a class='btn btn-warning btnNext pull-right' >".$locale['next']."</a>";
                $prevBtn = "<a class='btn btn-warning btnPrevious m-r-10'>".$locale['previous']."</a>";
                OutputHandler::addToJQuery("
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
    }

    /**
     * Current tab active selector
     *
     * @param array  $array          multidimension array consisting of keys 'title', 'id', 'icon'
     * @param int    $default_active 0 if link_mode is false, $_GET if link_mode is true
     * @param string $getname        set getname and turn tabs into link that listens to getname
     *
     * @return string
     */
    function tab_active($array, $default_active, $getname = NULL) {
        return \FusionTabs::tab_active($array, $default_active, $getname);
    }

    /**
     * Get current active tab index
     *
     * @param      $array
     * @param      $default_active
     * @param bool $getname
     *
     * @return int
     */
    function tab_index($array, $default_active, $getname = FALSE) {
        return FusionTabs::tabIndex($array, $default_active, $getname);
    }


    /**
     * Render Tab Links
     *
     * @param array  $tab_title          entire array consisting of ['title'], ['id'], ['icon']
     * @param string $link_active_arrkey tab_active() function or the $_GET request to match the $tab_title['id']
     * @param string $id                 unique ID
     * @param bool   $link               default false for jquery, true for php (will reload page)
     * @param string $class              the class for the nav
     * @param string $getname            the get request
     * @param array  $cleanup_GET        the request key that needs to be deleted
     * @param bool   $remember           set to true to automatically remember tab using cookie.
     *                                   Example:
     *                                   $tab_title['title'][] = "Tab 1";
     *                                   $tab_title['id'][] = "tab1";
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
     * echo opentab($tab_title, $_GET['ref'], 'myTab', TRUE, 'nav-pills', 'ref', ['*']); // clear all
     *
     * @return string
     */
    function opentab($tab_title, $link_active_arrkey, $id, $link = FALSE, $class = NULL, $getname = "section", $cleanup_GET = [], $remember = FALSE) {
        $fusion_tabs = new FusionTabs();
        if ($remember) {
            $fusion_tabs->set_remember(TRUE);
        }

        return $fusion_tabs->opentab($tab_title, $link_active_arrkey, $id, $link, $class, $getname, $cleanup_GET);
    }

    /**
     * Creates tab body
     *
     * @param string $tab_title deprecated, however this function is replaceable, and the params are accessible.
     * @param string $tab_id
     * @param string $link_active_arrkey
     * @param bool   $link      deprecated, however this function is replaceable, and the params are accessible.
     * @param string $key
     *
     * @return string
     */
    function opentabbody($tab_title, $tab_id, $link_active_arrkey = NULL, $link = FALSE, $key = NULL) {
        $fusion_tabs = new FusionTabs();

        return $fusion_tabs->opentabbody($tab_id, $link_active_arrkey, $key);
    }

    /**
     * Close tab body
     *
     * @return string
     */
    function closetabbody() {
        $fusion_tabs = new FusionTabs();

        return $fusion_tabs->closetabbody();
    }

    /**
     * Close tab
     *
     * @param array $options
     *
     * @return string
     */
    function closetab($options = []) {
        $fusion_tabs = new FusionTabs();

        return $fusion_tabs->closetab($options);
    }
}

if (!function_exists("display_ratings")) {
    /**
     * Standard ratings display
     *
     * @param int    $total_sum
     * @param int    $total_votes
     * @param string $link
     * @param string $class
     * @param int    $mode
     *
     * @return string
     */
    function display_ratings($total_sum, $total_votes, $link = NULL, $class = NULL, $mode = 1) {
        $locale = fusion_get_locale();
        $start_link = $link ? "<a class='comments-item ".$class."' href='".$link."'>" : '';
        $end_link = $link ? "</a>\n" : '';
        $average = $total_votes > 0 ? number_format($total_sum / $total_votes, 2) : 0;
        $str = $mode == 1 ? $average.$locale['global_094'].format_word($total_votes, $locale['fmt_rating']) : "$average/$total_votes";
        if ($total_votes > 0) {
            $answer = $start_link."<i title='".$locale['ratings']."' class='fa fa-star-o m-l-0'></i>".$str.$end_link;
        } else {
            $answer = $start_link."<i title='".sprintf($locale['global_089a'], $locale['global_077'])."' class='fa fa-star-o high-opacity m-l-0'></i> ".$str.$end_link;
        }

        return $answer;
    }
}

if (!function_exists("display_comments")) {
    /**
     * Standard comment display
     *
     * @param int    $total_sum
     * @param string $link
     * @param string $class
     * @param int    $mode
     *
     * @return string
     */
    function display_comments($total_sum, $link = NULL, $class = NULL, $mode = 1) {
        $locale = fusion_get_locale();
        $start_link = $link ? "<a class='comments-item ".$class."' href='".$link."' {%title%} >" : '';
        $end_link = $link ? "</a>\n" : '';
        $str = $mode == 1 ? format_word($total_sum, $locale['fmt_comment']) : $total_sum;
        if ($total_sum > 0) {
            $start_link = strtr($start_link, ['{%title%}' => "title='".$locale['global_073']."'"]);
        } else {
            $start_link = strtr($start_link, ['{%title%}' => "title='".sprintf($locale['global_089'], $locale['global_077'])."'"]);
        }

        return $start_link.$str.$end_link;
    }
}

if (!function_exists("fusion_confirm_exit")) {
    /**
     * JS form exit confirmation if form has changed
     */
    function fusion_confirm_exit() {
        OutputHandler::addToJQuery("
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

if (!function_exists('social_media_links')) {
    /**
     * Return a list of social media sharing services where an url can be shared
     * Requires the loading of Font Awesome which can be enabled in theme settings.
     *
     * @param string $url
     * @param array  $options
     *
     * @return string
     */
    function social_media_links($url, $options = []) {

        $default = [
            "facebook" => TRUE,
            "twitter"  => TRUE,
            "reddit"   => TRUE,
            "vk"       => TRUE,
            "whatsapp" => TRUE,
            "telegram" => TRUE,
            "linkedin" => TRUE,
            "class"    => "",
            "template" => '<a class="m-5 {%class%}" href="{%url%}" title="{%name%}" target="_blank" rel="nofollow noopener"><i class="{%icon%} fa-2x"></i></a>'
        ];

        $options += $default;

        $services = [];

        if ($options['facebook'] == 1) {
            $services['facebook'] = [
                'name' => 'Facebook',
                'icon' => 'fab fa-facebook-square',
                'url'  => 'https://www.facebook.com/sharer.php?u='
            ];
        }

        if ($options['twitter'] == 1) {
            $services['twitter'] = [
                'name' => 'Twitter',
                'icon' => 'fab fa-twitter-square',
                'url'  => 'https://twitter.com/intent/tweet?url='
            ];
        }

        if ($options['reddit'] == 1) {
            $services['reddit'] = [
                'name' => 'Reddit',
                'icon' => 'fab fa-reddit-square',
                'url'  => 'https://www.reddit.com/submit?url='
            ];
        }

        if ($options['vk'] == 1) {
            $services['vk'] = [
                'name' => 'VK',
                'icon' => 'fab fa-vk',
                'url'  => 'https://vk.com/share.php?url='
            ];
        }

        if ($options['whatsapp'] == 1) {
            $services['whatsapp'] = [
                'name' => 'WhatsApp',
                'icon' => 'fab fa-whatsapp',
                'url'  => 'https://api.whatsapp.com/send?text='
            ];
        }

        if ($options['telegram'] == 1) {
            $services['telegram'] = [
                'name' => 'Telegram',
                'icon' => 'fab fa-telegram',
                'url'  => 'https://telegram.me/share/url?url='
            ];
        }

        if ($options['linkedin'] == 1) {
            $services['linkedin'] = [
                'name' => 'LinkedIn',
                'icon' => 'fab fa-linkedin',
                'url'  => 'https://www.linkedin.com/shareArticle?mini=true&url=',
            ];
        }

        $html = '';
        if (!empty($services) && is_array($services)) {
            foreach ($services as $service) {
                $html .= strtr($options["template"], [
                    "{%class%}" => $options["class"],
                    "{%url%}"   => $service["url"].$url,
                    "{%name%}"  => $service["name"],
                    "{%icon%}"  => $service["icon"]
                ]);
            }
        }

        return $html;
    }
}

/**
 * Adds a whitespace if value is present
 *
 * @param $value
 *
 * @return string
 */
function whitespace($value) {
    if ($value) {
        return " ".$value;
    }
    return "";
}
