<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: theme_functions_include.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\BreadCrumbs;
use PHPFusion\Database\DatabaseFactory;
use PHPFusion\OutputHandler;
use PHPFusion\Panels;
use PHPFusion\SiteLinks;

defined('IN_FUSION') || exit;

/**
 * Show PHPFusion performance.
 *
 * @param bool $queries Show the number of queries used on the current page.
 *
 * @return string
 */
function showrendertime($queries = TRUE)
{
	$locale = fusion_get_locale();
	$db_connection = DatabaseFactory::getConnection('default');
	$mysql_queries_count = $db_connection::getGlobalQueryCount();
	if (fusion_get_settings('rendertime_enabled') == 1 || (fusion_get_settings('rendertime_enabled') == 2 && iADMIN)) {
		$res = showbenchmark();
		$res .= " | ";
		$res .= ($queries ? ucfirst($locale['global_173']) . ": " . $mysql_queries_count . " | " : '');

		return $res;
	} else {
		return "";
	}
}

/**
 * Show benchmark and database performance.
 * Developer tools only (Translations not Required)
 *
 * @param bool $show_sql_performance True to pop up SQL analysis modal
 * @param string $performance_threshold Results that is slower than this will be highlighted
 *
 * @return string
 */
function showbenchmark($show_sql_performance = FALSE, $performance_threshold = '0.01')
{
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
				$modal_body .= "<div class='spacer-xs m-10" . ($highlighted ? " alert alert-warning" : "") . "'>";
				$modal_body .= "<h5><strong>SQL run#$i : " . ($highlighted ? "<span class='text-danger'>" . $sql[0] . "</span>" : "<span class='text-success'>" . $sql[0] . "</span>") . " seconds</strong></h5>\r";
				$modal_body .= "[code]" . $sql[1] . ($sql[2] ? " [Parameters -- " . implode(',', $sql[2]) . " ]" : '') . "[/code]\r";
				$modal_body .= "<div>";
				$end_sql = end($sql[3]);
				$modal_body .= "<kbd>" . addslashes($end_sql['file']) . "</kbd><span class='badge pull-right'>Line #" . $end_sql['line'] . ", " . $end_sql['function'] . "</span> - <a class='pointer' data-toggle='collapse' data-target='#trace_$connectionID'>Toggle Backtrace</a>";
				if (is_array($sql[3])) {
					$modal_body .= "<div id='trace_$connectionID' class='alert alert-info collapse spacer-sm'>";
					foreach ($sql[3] as $id => $debug_backtrace) {
						$modal_body .= "<kbd>Stack Trace #$id - " . addslashes($debug_backtrace['file']) . " @ Line " . $debug_backtrace['line'] . "</kbd><br/>";
						if (!empty($debug_backtrace['args'][0])) {
							$debug_line = $debug_backtrace['args'][0];
							if (is_array($debug_backtrace['args'][0])) {
								$debug_line = "";
								foreach ($debug_backtrace['args'][0] as $line) {
									if (!is_array($line)) {
										$debug_line .= "<br/>" . $line;
									}
								}
							}

							$debug_param = "";
							if (!empty($debug_backtrace['args'][1])) {
								if (is_array($debug_backtrace['args'][1])) {
									$debug_param .= "array(" . PHP_EOL;
									foreach ($debug_backtrace['args'][1] as $key => $value) {
										$debug_param .= "&nbsp;&nbsp;&nbsp;&nbsp;[$key] => $value," . PHP_EOL;
									}
									$debug_param .= ");";
								} else {
									$debug_param .= $debug_backtrace['args'][1];
								}
							}
							$modal_body .= "Statement::: <code>" . addslashes($debug_line) . "</code>";
							$modal_body .= !empty($debug_param) ? "<br/>Parameters::: <code>" . $debug_param . "</code>" : '';
						}

					}
					$modal_body .= "</div>";
				}
				$modal_body .= "</div>";
				$modal_body .= "</div>";
				$i++;
				$time = $current_time + $time;
			}
		}
		$modal .= parse_text($modal_body, [
			'parse_smileys' => FALSE,
			'descript'      => FALSE,
			'parse_usres'   => FALSE,
		]);
		$modal .= modalfooter("<h4><strong>Total Time Expended in ALL SQL Queries: " . $time . " seconds</strong></h4>");
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

	return sprintf($locale['global_172'], $render_time) . " | " . sprintf($locale['global_175'], $average_speed . " ($diff)");
}

/**
 * Show memory usage
 *
 * @return string
 */
function showmemoryusage()
{
	$locale = fusion_get_locale();
	$memory_allocated = parsebytesize(memory_get_peak_usage(TRUE));
	$memory_used = parsebytesize(memory_get_peak_usage(FALSE));

	return $locale['global_174'] . ": " . $memory_used . "/" . $memory_allocated;
}

/**
 * Show the PHPFusion copyright.
 *
 * @param string $class The class attribute of the link.
 * @param false $nobreak If true <br> tag will be removed between copyright and license.
 *
 * @return string
 */
function showcopyright($class = "", $nobreak = FALSE)
{
	$link_class = $class ? " class='$class' " : "";

	$copyright = "Powered by <a href='https://phpfusion.com' " . $link_class . "target='_blank'>PHPFusion</a>. Copyright &copy; " . date("Y") . " PHP Fusion Inc. ";
	$copyright .= $nobreak ? "&nbsp;" : "<br />";
	$license = "Released as free software without warranties under <a href='https://www.gnu.org/licenses/agpl-3.0.html'" . $link_class . " target='_blank'>GNU Affero GPL</a> v3.";

	/*if (fusion_get_settings('license') == 'epal') {
		$license = "Published without warranties under <a href='https://www.phpfusion.com/licensing/?epal' ".$link_class." target='_blank'>EPAL</a>.";
	}*/

	return $copyright . $license;
}

/**
 * If the visitor counter is enabled in settings this function will return the number of visitors.
 *
 * @return string
 */
function showcounter()
{
	$locale = fusion_get_locale();
	$settings = fusion_get_settings();
	if ($settings['visitorcounter_enabled']) {
		return "<!--counter-->" . number_format($settings['counter'], 0, $settings['number_delimiter'], $settings['thousands_separator']) . " " . ($settings['counter'] == 1 ? $locale['global_170'] : $locale['global_171']);
	} else {
		return "";
	}
}

/**
 * Show popup with privacy policy text.
 *
 * @return string
 */
function showprivacypolicy()
{
	$html = '';
	if (!empty(fusion_get_settings('privacy_policy'))) {
		$html .= "<a href='" . BASEDIR . "print.php?type=P' id='privacy_policy'>" . fusion_get_locale('global_176') . "</a>";
		$modal = openmodal('privacy_policy', fusion_get_locale('global_176'), ['button_id' => 'privacy_policy']);
		$modal .= parse_text(\PHPFusion\QuantumFields::parseLabel(fusion_get_settings('privacy_policy')));
		$modal .= closemodal();
		add_to_footer($modal);
	}

	return $html;
}

/**
 * Show cookie notice popup until the visitor accepts it.
 *
 * @return string
 */
function showCookieNotice()
{
	$cookie_name = 'elite_cookie_notice';
	if (!empty($_COOKIE[$cookie_name])) {
		return '';
	}

	$policy_link = BASEDIR . 'print.php?type=P';
	$html = "<div id='eliteCookieNotice' role='dialog' aria-live='polite' aria-label='Cookie notice' style='position:fixed;right:24px;bottom:28px;z-index:1080;width:400px;max-width:calc(100vw - 32px);min-height:172px;padding:33px 30px 29px;border-radius:7px;background:var(--tblr-dark,#202020);color:var(--tblr-white,#fff);box-shadow:0 18px 45px rgba(0,0,0,.22);font-family:var(--tblr-font-sans-serif,inherit);'>";
	$html .= "<p style='margin:0 0 18px;color:var(--tblr-white,#fff);font-size:14px;font-weight:400;line-height:1.72;'>This website uses cookies to ensure you get the best experience on our website. <a href='" . $policy_link . "' target='_blank' rel='noopener' style='color:var(--tblr-white,#fff);text-decoration:underline;text-underline-offset:2px;'>Cookies Policy</a></p>";
	$html .= "<button type='button' id='eliteCookieNoticeOk' class='btn btn-light' style='display:inline-flex;align-items:center;justify-content:center;width:83px;height:42px;padding:0;border:0;border-radius:8px;background:var(--tblr-white,#fff);color:var(--tblr-body-color,#1f2937);font-size:12px;font-weight:400;line-height:1;text-transform:uppercase;box-shadow:none;'>GOT IT</button>";
	$html .= "</div>";
	$html .= "<script>
(function() {
	var notice = document.getElementById('eliteCookieNotice');
	var button = document.getElementById('eliteCookieNoticeOk');
	var cookieName = '" . $cookie_name . "';
	if (!notice || !button) {
		return;
	}
	if (document.cookie.split('; ').some(function(cookie) {
		return cookie.indexOf(cookieName + '=') === 0;
	})) {
		notice.remove();
		return;
	}
	button.addEventListener('click', function() {
		var expires = new Date();
		expires.setFullYear(expires.getFullYear() + 1);
		document.cookie = cookieName + '=1; expires=' + expires.toUTCString() + '; path=/; SameSite=Lax';
		notice.remove();
	});
}());
</script>";

	return $html;
}

if (!function_exists('alert')) {
	/**
	 * Creates an alert bar.
	 *
	 * @param string $title Text inside the alert.
	 * @param array $options
	 *
	 * @return string
	 */
	function alert($title, $options = [])
	{
		$options += [
			"class"   => !empty($options['class']) ? $options['class'] : 'alert-danger',
			"dismiss" => !empty($options['dismiss']) && $options['dismiss'] == TRUE,
		];
		if (function_exists('fusion_render_framework_component')
			&& in_array((fusion_framework_active()['key'] ?? ''), ['tailwind', 'bootstrap'], TRUE)) {
			$framework_alert = fusion_render_framework_component('alert', [
				'title' => $title,
				'options' => $options,
			]);
			if ($framework_alert !== '') return $framework_alert;
		}
		if ($options['dismiss'] == TRUE) {
			$html = "<div class='alert alert-dismissable " . $options['class'] . "'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>$title</div>";
		} else {
			$html = "<div class='alert " . $options['class'] . "'>$title</div>";
		}
		add_to_jquery("$('div.alert a').addClass('alert-link');");

		return $html;
	}
}

if (!function_exists('get_theme_settings')) {
	/**
	 * Get the theme settings from database.
	 *
	 * @param string $theme_folder The name of the theme folder.
	 *
	 * @return array|bool
	 */
	function get_theme_settings($theme_folder)
	{
		$settings_arr = [];
		$set_result = dbquery("SELECT settings_name, settings_value FROM " . DB_SETTINGS_THEME . " WHERE settings_theme=:themeset", [':themeset' => $theme_folder]);
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
 * JavaScript that makes HTML table sortable.
 * Put in class column
 * @param string $table_id Table ID
 *
 * @return string
 */
function fusion_sort_table($table_id)
{
	fusion_load_script(INCLUDES."jquery/tablesorter/jquery.tablesorter.min.js");
	add_to_jquery("$('#" . $table_id . "').tablesorter();");

	return "tablesorter";
}

if (!function_exists('label')) {
	/**
	 * Creates label.
	 *
	 * @param string $label
	 * @param array $options
	 *
	 * @return string
	 */
	function label($label, $options = [])
	{
		$options += [
			"class" => !empty($options['class']) ? $options['class'] : 'label-default',
			"icon"  => !empty($options['icon']) ? "<i class='" . $options['icon'] . "'></i> " : '',
		];
		if (function_exists('fusion_render_framework_component')
			&& in_array((fusion_framework_active()['key'] ?? ''), ['tailwind', 'bootstrap'], TRUE)) {
			$framework_label = fusion_render_framework_component('badge', [
				'kind' => 'label',
				'label' => $label,
				'options' => $options,
			]);
			if ($framework_label !== '') return $framework_label;
		}

		return "<span class='label " . $options['class'] . "'>" . $options['icon'] . $label . "</span>";
	}
}

if (!function_exists('badge')) {
	/**
	 * Creates badge.
	 *
	 * @param string $label
	 * @param array $options
	 *
	 * @return string
	 */
	function badge($label, $options = [])
	{
		$options += [
			"class" => !empty($options['class']) ? $options['class'] : '',
			"icon"  => !empty($options['icon']) ? "<i class='" . $options['icon'] . "'></i> " : '',
		];
		if (function_exists('fusion_render_framework_component')
			&& in_array((fusion_framework_active()['key'] ?? ''), ['tailwind', 'bootstrap'], TRUE)) {
			$framework_badge = fusion_render_framework_component('badge', [
				'kind' => 'badge',
				'label' => $label,
				'options' => $options,
			]);
			if ($framework_badge !== '') return $framework_badge;
		}

		return "<span class='badge " . $options['class'] . "'>" . $options['icon'] . $label . "</span>";
	}
}

if (!function_exists('openmodal') &&
	!function_exists('closemodal') &&
	!function_exists('modalfooter')
) {
	/**
	 * To get the best results for Modal z-index overlay, try :
	 * ob_start();
	 * ... insert and echo ...
	 * add_to_footer(ob_get_contents()).ob_end_clean();
	 */

	/**
	 * Generate modal.
	 *
	 * @param string $id Unique modal ID.
	 * @param string $title Modal title.
	 * @param array $options
	 *
	 * @return string
	 */
	function openmodal($id, $title, $options = [])
	{
		$locale = fusion_get_locale();
		$options += [
			'class'        => 'modal-lg',
			'close'        => FALSE,
			'button_id'    => '',
			'button_class' => '',
			'static'       => FALSE,
			'hidden'       => FALSE,
			'size'         => 3,
			'position'     => 'middle',
		];

		$modal_trigger = '';
		if (!empty($options['button_id']) || !empty($options['button_class']) || !empty($options['button_name'])) {
			if (!empty($options['button_id'])) {
				$modal_trigger = '#' . $options['button_id'];
			} elseif (!empty($options['button_class'])) {
				$modal_trigger = '.' . $options['button_class'];
			} elseif (!empty($options['button_name'])) {
				$modal_trigger = '[name="' . $options['button_name'] . '"]';
			}
		}

		if (function_exists('fusion_render_framework_component')
			&& in_array((fusion_framework_active()['key'] ?? ''), ['tailwind', 'bootstrap'], TRUE)) {
			$framework_modal = fusion_render_framework_component('modal', [
				'id' => $id,
				'modal' => 'open',
				'header_content' => $title,
				'trigger' => $modal_trigger,
				'options' => $options,
			]);
			if ($framework_modal !== '') {
				return $framework_modal;
			}
		}

		// === jQuery modal triggers (still works in Bootstrap 5 if jQuery loaded) ===
		if ($options['static'] && $modal_trigger) {
			OutputHandler::addToJQuery("$('" . $modal_trigger . "').on('click', function(e){ $('#" . $id . "_Modal').modal({backdrop: 'static', keyboard: false}).modal('show'); e.preventDefault(); });");
		} else if ($options['static']) {
			OutputHandler::addToJQuery("$('#" . $id . "_Modal').modal({backdrop: 'static', keyboard: false}).modal('show');");
		} else if ($modal_trigger) {
			OutputHandler::addToJQuery("$('" . $modal_trigger . "').on('click', function(e){ $('#" . $id . "_Modal').modal('show'); e.preventDefault(); });");
		} else if ($options['hidden'] == FALSE) {
			OutputHandler::addToJQuery("$('#" . $id . "_Modal').modal('show');");
		}


		// === modal size ===
		if ($options['size'] == 4) {
			$options['class'] = 'modal-xl';
		} elseif ($options['size'] == 3) {
			$options['class'] = 'modal-lg';
		} else if ($options['size'] == 2) {
			$options['class'] = 'modal-md';
		} else if ($options['size'] == 1) {
			$options['class'] = 'modal-sm';
		}

		// === Handle modal position ===
		$position_class = match ($options['position']) {
			'top' => 'modal-dialog-top',
			'bottom' => 'modal-dialog-bottom',
			default => 'modal-dialog-centered',
		};

		// === Bootstrap 5-compliant modal ===
		$html = "<div class='modal fade' id='{$id}_Modal' tabindex='-1' aria-labelledby='{$id}_title' aria-hidden='true'>";
		$html .= "<div class='modal-dialog {$options['class']} {$position_class}' role='document'>";
		$html .= "<div class='modal-content'>";

		if ($title) {
			$html .= "<div class='modal-header '>";
			$html .= "<h5 class='modal-title' id='{$id}_title'>{$title}</h5>";
			if (!$options['static'] || $options['close']) {
				$html .= "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='{$locale['close']}'>
                        <i class='fa fa-times me-2'></i> {$locale['close']}
                      </button>";
			}
			$html .= "</div>";
		}

		$html .= "<div class='modal-body'>\n";

		return $html;
	}


	/**
	 * Adds a modal footer in between openmodal and closemodal.
	 *
	 * @param string $content
	 * @param bool $dismiss
	 *
	 * @return string
	 */
	function modalfooter($content, $dismiss = FALSE)
	{
		if (function_exists('fusion_render_framework_component')
			&& in_array((fusion_framework_active()['key'] ?? ''), ['tailwind', 'bootstrap'], TRUE)) {
			$framework_footer = fusion_render_framework_component('modal', [
				'modal' => 'footer',
				'footer_content' => $content,
				'static' => !$dismiss,
			]);
			if ($framework_footer !== '') {
				return $framework_footer;
			}
		}

		$html = "</div><div class='modal-footer'>";
		$html .= $content;
		if ($dismiss) {
			$html .= "<button type='button' class='btn btn-default pull-right' data-dismiss='modal'>" . fusion_get_locale('close') . "</button>";
		}

		return $html;
	}

	/**
	 * Close the modal.
	 *
	 * @return string
	 */
	function closemodal()
	{
		if (function_exists('fusion_render_framework_component')
			&& in_array((fusion_framework_active()['key'] ?? ''), ['tailwind', 'bootstrap'], TRUE)) {
			$framework_close = fusion_render_framework_component('modal', ['modal' => 'close']);
			if ($framework_close !== '') {
				return $framework_close;
			}
		}

		return "</div></div></div></div>";
	}
}

function stars($value)
{
	// 1. Convert 0-100 score to 0-5 stars
	$rating = $value / 20;
	$output = "<div class='d-inline-flex align-items-center' title='Score: $value%'>";
	$output .= "<div class='text-warning me-2' style='letter-spacing: 2px;'>";

	for ($i = 1; $i <= 5; $i++) {
		if ($rating >= $i) {
			// Full Star
			$output .= '<i class="fas fa-star text-warning"></i>';
		} elseif ($rating >= ($i - 0.5)) {
			// Half Star
			$output .= '<i class="fas fa-star-half-alt text-warning"></i>';
		} else {
			// Empty Star (Grey)
			$output .= '<i class="far fa-star text-muted"></i>';
		}
	}

	$output .= "</div>";

	// 2. Add the small percentage text (75% of original font size)
	$output .= "<span class='text-muted' style='font-size: 0.75em;'>($value%)</span>";
	$output .= "</div>";

	return $output;
}

if (!function_exists('progress_bar')) {
	/**
	 * Render a progress bar (supports stars, percent, inline layout, etc.)
	 *
	 * @param int|int[] $num
	 * @param string|string[] $title
	 * @param array $options
	 *
	 * @return string
	 */
	function progress_bar($num, $title = NULL, $options = [])
	{
		$default_options = [
			'class'          => '',
			'bar_class'      => '',
			'label_class'    => '',
			'height'         => '20px',
			'reverse'        => FALSE,
			'as_percent'     => TRUE,
			'as_star'        => FALSE,
			'as_unit'        => FALSE,  // Use "8/10" format
			'max_unit'       => 100,    // Default max for unit calculation
			'disabled'       => FALSE,
			'hide_info'      => FALSE,
			'progress_class' => '',
			'inline'         => FALSE,
			'width'          => '100%',
			'title_tooltip'  => '',
			'bar_tooltip'    => TRUE,
			'stacked'        => FALSE,  // Added stacked progress bar configuration option
		];
		
		$options += $default_options;
		
		// Quick fix for ['height' => true] fallback to string unit
		if ($options['height'] === true) {
			$options['height'] = '20px';
		}
		if (function_exists('fusion_render_framework_component')
			&& in_array((fusion_framework_active()['key'] ?? ''), ['tailwind', 'bootstrap'], TRUE)) {
			$framework_progress = fusion_render_framework_component('progress', [
				'value' => $num,
				'title' => $title,
				'options' => $options,
			]);
			if ($framework_progress !== '') return $framework_progress;
		}
		
		if (!function_exists('bar_color')) {
			function bar_color($num, $reverse)
			{
				$num = max(0, min(100, $num));
				if ($num > 80)
					return $reverse ? 'bg-danger' : 'bg-success';
				else if ($num > 60)
					return $reverse ? 'bg-warning' : 'bg-info';
				else if ($num > 40)
					return $reverse ? 'bg-info' : 'bg-warning';
				else
					return $reverse ? 'bg-success' : 'bg-danger';
			}
		}
		
		$_barcolor = ['bg-success', 'bg-info', 'bg-warning', 'bg-danger'];
		$_barcolor_reverse = ['bg-danger', 'bg-warning', 'bg-info', 'bg-success'];
		
		$html = '';
		
		$make_stars = function($percent) {
			if ($percent <= 0) return '0⭐';
			$stars = round($percent / 20 * 2) / 2;
			return rtrim($stars, '.0') . '⭐';
		};
		
		$get_display_val = function($current, $options) use ($make_stars) {
			if ($options['disabled']) return "&#x221e;";
			
			$max = (float)$options['max_unit'];
			$perc = ($max > 0) ? ($current / $max) * 100 : 0;
			
			if ($options['as_unit']) {
				return $current . ' / ' . $max;
			}
			if ($options['as_star']) {
				return $make_stars($perc);
			}
			return $current . ($options['as_percent'] ? '%' : '');
		};
		
		// 🧩 MULTI-BAR (Handles Standard Multi-bar & Stacked Mode)
		if (is_array($num)) {
			$i = 0;
			$chtml = "";
			$labels_html = "";
			
			// 🧮 Stacked calculation setup
			$total_sum = $options['stacked'] ? array_sum($num) : 0;
			
			foreach ($num as $key => $value) {
				$val = (float)$value;
				
				// Determine segment text and percentage calculation style
				if ($options['stacked']) {
					$segment_label = ucfirst($key);
					$perc = ($total_sum > 0) ? ($val / $total_sum) * 100 : 0;
					// For stacked segments, tooltips read best showing original value + its relative portion
					$display_value = $val . ' (' . round($perc, 1) . '%)';
				} else {
					$segment_label = is_array($title) ? ($title[$i] ?? '') : '';
					$max = (float)$options['max_unit'];
					$perc = ($max > 0) ? ($val / $max) * 100 : 0;
					$display_value = $get_display_val($val, $options);
				}
				
				$perc_clamped = max(0, min(100, $perc));
				
				$auto_class = $options['reverse'] ? $_barcolor_reverse[$i % 4] : $_barcolor[$i % 4];
				$bar_class = is_array($options['bar_class'])
					? ($options['bar_class'][$i] ?? $auto_class)
					: ($options['bar_class'] ?: $auto_class);
				
				$tooltip_attr = $options['bar_tooltip'] ? "data-bs-toggle='tooltip' title='{$segment_label}: {$display_value}'" : '';
				
				// Compile Legend Indicators
				$labels_html .= "<div class='d-inline-flex align-items-center me-3 mb-1'>
                              <div class='progress me-2' style='width:20px;height:10px'>
                                  <div class='progress-bar {$bar_class}' style='width:100%'></div>
                              </div>
                              <small>{$segment_label} <span class='text-muted'>({$val})</span></small>
                          </div>";
				
				// Compile Combined Stacked Bar Elements
				$chtml .= "<div class='progress-bar {$bar_class}' role='progressbar'
                      style='width:{$perc_clamped}%' {$tooltip_attr}></div>";
				$i++;
			}
			
			if ($options['inline']) {
				$html .= "<div class='d-inline-flex flex-column align-items-start me-3' style='width:{$options['width']}'>";
			}
			
			if (!$options['hide_info']) {
				$title_tooltip = $options['title_tooltip'] ? "data-bs-toggle='tooltip' title='{$options['title_tooltip']}'" : "";
				$main_title = is_array($title) ? "Progress" : $title;
				$html .= "<div class='d-flex flex-wrap justify-content-between align-items-center mb-2 w-100'>
                      <span class='fw-bold {$options['label_class']}' {$title_tooltip}>{$main_title}</span>
                      <span>{$labels_html}</span>
                    </div>";
			}
			
			$html .= "<div class='progress {$options['progress_class']} w-100' style='height:{$options['height']}'>
                  {$chtml}
                </div>";
			
			if ($options['inline']) $html .= "</div>";
			
		} // 🧩 SINGLE BAR
		else {
			$val = (float)$num;
			$max = (float)$options['max_unit'];
			$perc = ($max > 0) ? ($val / $max) * 100 : 0;
			$perc_clamped = max(0, min(100, $perc));
			
			$display_value = $get_display_val($val, $options);
			$bar_class = $options['bar_class'] ?: bar_color($perc_clamped, $options['reverse']);
			
			$title_tooltip = $options['title_tooltip'] ? "data-bs-toggle='tooltip' title='{$options['title_tooltip']}'" : '';
			$tooltip_attr = $options['bar_tooltip'] ? "data-bs-toggle='tooltip' title='{$display_value}'" : '';
			
			if ($options['inline']) {
				$html .= "<div class='d-inline-flex flex-column align-items-start me-3 mb-2 {$options['class']}' style='width:{$options['width']}'>";
			}
			
			if (!$options['hide_info'] && $title) {
				$html .= "<div class='d-flex align-items-center justify-content-between w-100 mb-1'>
                      <span class='{$options['label_class']}' {$title_tooltip}>{$title}</span>";
				
				if (!$options['as_star']) {
					$html .= "<span>{$display_value}</span>";
				}
				
				$html .= "</div>";
			}
			
			$html .= "<div class='progress {$options['progress_class']} w-100' style='height:{$options['height']}'>
                  <div class='progress-bar {$bar_class}' role='progressbar'
                       aria-valuenow='{$perc_clamped}' aria-valuemin='0' aria-valuemax='100'
                       style='width: {$perc_clamped}%' {$tooltip_attr}></div>
                </div>";
			
			if ($options['as_star'] && !$options['hide_info']) {
				$html .= "<div class='mt-1'><span>{$display_value}</span></div>";
			}
			
			if ($options['inline']) $html .= "</div>";
		}
		
		return $html;
	}

}

if (!function_exists('check_panel_status')) {
	/**
	 * Checks the panel status for given side.
	 *
	 * @param string $side Possible value: left, right, upper, aupper, lower, blower, user1, user2, user3, user4
	 *
	 * @return bool
	 */
	function check_panel_status($side)
	{
		return Panels::checkPanelStatus($side);
	}
}

if (!function_exists('showbanners')) {
	/**
	 * Display the site banner you specify through the Banner settings.
	 *
	 * @param int $display Possible value: 1, 2. If empty it shows banner 1.
	 *
	 * @return string
	 */
	function showbanners($display = NULL)
	{
		$settings = fusion_get_settings();

		ob_start();
		if ($display == 2) {
			if ($settings['sitebanner2']) {
				echo parse_text($settings['sitebanner2'], [
					'parse_smileys'        => FALSE,
					'parse_bbcode'         => FALSE,
					'default_image_folder' => NULL,
					'add_line_breaks'      => TRUE,
				]);
			}
		} else {
			if ($settings['sitebanner1']) {
				echo parse_text($settings['sitebanner1'], [
					'parse_smileys'        => FALSE,
					'parse_bbcode'         => FALSE,
					'default_image_folder' => NULL,
					'add_line_breaks'      => TRUE,
				]);
			}
		}
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}
}

if (!function_exists('showlogo')) {
	/**
	 * Show site logo.
	 *
	 * @param string $class CSS class.
	 *
	 * @return string
	 */
	function showlogo($class = 'logo')
	{
		return "<div class='" . $class . "'><a href='" . BASEDIR . fusion_get_settings('opening_page') . "' title='" . fusion_get_settings('sitename') . "'><img src='" . BASEDIR . fusion_get_settings('sitebanner') . "' alt='Logo'></a></div>";
	}
}

if (!function_exists('showsublinks')) {
	/**
	 * Displays Site Links navigation bar.
	 *
	 * @param string $sep Separator between links.
	 * @param string $class CSS class of the navbar.
	 * @param array $options
	 *
	 * Notice: There is a more powerful method now that offers more powerful manipulation methods
	 * that non oo approach cannot ever achieve using cache and the new mutator method
	 * SiteLinks::setSubLinks($sep, $class, $options)->showsublinks(); for normal usage
	 *
	 * @return string
	 */
	function showsublinks($sep = "", $class = "navbar-default", $options = [])
	{
		$options += [
			'seperator'    => $sep,
			'navbar_class' => $class,
		];
		return SiteLinks::setSubLinks($options)->showSubLinks();
	}
}

if (!function_exists('panelbutton')) {
	/**
	 * Show the collapse or expand a button for panels which are collapsible.
	 *
	 * @param string $state Panel state.
	 * @param string $bname Button name.
	 *
	 * @return string
	 */
	function panelbutton($state, $bname)
	{
		$bname = preg_replace("/[^a-zA-Z0-9\s]/", "_", $bname);
		if (isset($_COOKIE["fusion_box_" . $bname])) {
			if ($_COOKIE["fusion_box_" . $bname] == "none") {
				$state = "off";
			} else {
				$state = "on";
			}
		}

		return "<img src='" . get_image("panel_" . ($state == "on" ? "off" : "on")) . "' id='b_" . $bname . "' class='panelbutton pointer' alt='panelstate' onclick=\"flipBox('" . $bname . "')\" />";
	}
}

if (!function_exists('panelstate')) {
	/**
	 * Checks the state of a panel.
	 *
	 * @param string $state Panel state. Possible value: on, off
	 * @param string $bname Button name.
	 * @param string $element Element name.
	 *
	 * @return string
	 */
	function panelstate($state, $bname, $element = "div")
	{
		$bname = preg_replace("/[^a-zA-Z0-9\s]/", "_", $bname);
		if (isset($_COOKIE["fusion_box_" . $bname])) {
			if ($_COOKIE["fusion_box_" . $bname] == "none") {
				$state = "off";
			} else {
				$state = "on";
			}
		}

		return "<$element id='box_" . $bname . "'" . ($state == "off" ? " style='display:none'" : "") . ">";
	}
}

if (!function_exists('profile_link')) {
	/**
	 * User profile link.
	 *
	 * @param int $user_id
	 * @param string $user_name
	 * @param int $user_status
	 * @param string $class CSS class for the profile link.
	 * @param bool $display_link Allow clicking on the name, otherwise display only the name.
	 *
	 * @return string Link to the user's account along with the username correctly depending on the user's status.
	 */
	function profile_link($user_id, $user_name, $user_status, $class = "profile-link", $display_link = TRUE)
	{
		$locale = fusion_get_locale();
		$settings = fusion_get_settings();
		if ((in_array($user_status, [0, 3, 7]) || checkrights("M")) && (iMEMBER || $settings['hide_userprofiles'] == "0") && $display_link == TRUE && $user_id !== 0) {
			$link = '<a href="' . BASEDIR . 'profile.php?lookup=' . $user_id . '" class="' . $class . '">' . $user_name . '</a>';
		} else if ($user_status == "5" || $user_status == "6") {
			$link = $locale['user_anonymous'];
		} else {
			$link = $user_name;
		}

		return $link;
	}
}

if (!function_exists('display_avatar')) {
	/**
	 * Show user avatar.
	 *
	 * @param array $userdata User data with user_id, user_name , user_avatar, user_status
	 * @param string $size A size for CSS max-width and max-height.
	 * @param string $class CSS class for <a> tag.
	 * @param bool $link Wrap image with <a> tag.
	 * @param string $img_class CSS class for <img> tag.
	 * @param string $custom_avatar The path to own default avatar.
	 *
	 * @return string
	 */
	function display_avatar($userdata, $size, $class = '', $link = TRUE, $img_class = '', $custom_avatar = '')
	{
		if (empty($userdata)) {
			$userdata = [
				'user_name' => fusion_get_locale('user_anonymous'),
			];
		}

		$userdata += [
			'user_id'     => 0,
			'user_name'   => '',
			'user_avatar' => '',
			'user_status' => '',
		];

		$link = fusion_get_settings('hide_userprofiles') == TRUE ? (iMEMBER ? $link : FALSE) : $link;
		$link = $userdata['user_id'] !== 0 ? $link : FALSE;
		$class = ($class) ? "class='$class'" : '';

		$hasAvatar = $userdata['user_avatar'] && file_exists(IMAGES . "avatars/" . $userdata['user_avatar']) && $userdata['user_status'] != '5' && $userdata['user_status'] != '6';
		$name = !empty($userdata['user_name']) ? $userdata['user_name'] : 'Guest';

		$imgTpl = '<img class="avatar ' . $img_class . '" alt="' . $name . '" data-pin-nopin="true" style="width:' . $size . '; height:' . $size . '; object-fit:cover; display:block;flex-shrink:0;" src="%s">';
		if ($hasAvatar) {
			$img = sprintf($imgTpl, IMAGES . "avatars/" . $userdata['user_avatar']);
		} else {
			if (!empty($custom_avatar) && file_exists($custom_avatar)) {
				$img = sprintf($imgTpl, $custom_avatar);
			} else {
				$color = string_to_color_code($name);
				$font_color = get_color_brightness($color) > 130 ? '000' : 'fff';

				if (function_exists('mb_substr') && function_exists('mb_strtoupper')) {
					$first_char = mb_substr($name, 0, 1, 'UTF-8');
					$first_char = mb_strtoupper($first_char, 'UTF-8');
				} else {
					$first_char = substr($name, 0, 1);
					$first_char = strtoupper($first_char);
				}

				$size_int = (int)filter_var($size, FILTER_SANITIZE_NUMBER_INT);
				$img = '<div class="d-inline-block avatar ' . $img_class . '" style="width:' . $size . ';height:' . $size . ';object-fit:cover;flex-shrink:0;"><svg viewBox="0 0 ' . $size_int . ' ' . $size_int . '" preserveAspectRatio="xMidYMid meet"><rect fill="#' . $color . '" stroke-width="0" y="0" x="0" width="' . $size . '" height="' . $size . '"/><text class="m-t-5" font-size="' . ($size_int - 5) . '" fill="#' . $font_color . '" x="50%" y="50%" text-anchor="middle" dy="0.325em">' . $first_char . '</text></svg></div>';
			}
		}

		return $link ? sprintf('<a ' . $class . ' title="' . $userdata['user_name'] . '" href="' . BASEDIR . 'profile.php?lookup=' . $userdata['user_id'] . '">%s</a>', $img) : $img;
	}
}

/**
 * Generate HEX color code from string.
 *
 * @param string $text Any string.
 *
 * @return string HEX color code.
 */
function string_to_color_code($text)
{
	$min_brightness = 50; // integer between 0 and 100
	$spec = 3;            // integer between 2-10, determines how unique each color will be

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
 * @param string $hex HEX color code.
 *
 * @return float
 */
function get_color_brightness($hex)
{
	$hex = str_replace('#', '', $hex);
	$r = hexdec(substr($hex, 0, 2));
	$g = hexdec(substr($hex, 2, 2));
	$b = hexdec(substr($hex, 4, 2));

	return (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
}

if (!function_exists('colorbox')) {
	/**
	 * Display image in colorbox.
	 *
	 * @param string $img_path The path to image.
	 * @param string $img_title Image title.
	 * @param bool $responsive Add img-responsive class.
	 * @param string $class CSS class.
	 * @param bool $as_text Show clickable text instead image.
	 *
	 * @return string
	 */
	function colorbox($img_path, $img_title, $responsive = TRUE, $class = '', $as_text = FALSE)
	{
		if (!defined('COLORBOX')) {
			define('COLORBOX', TRUE);
			$colorbox_css = file_exists(THEME . 'colorbox/colorbox.css') ? THEME . 'colorbox/colorbox.css' : INCLUDES . 'jquery/colorbox/colorbox.css';
			add_to_head("<link rel='stylesheet' href='$colorbox_css' type='text/css' media='screen' />");
			add_to_head("<script type='text/javascript' src='" . INCLUDES . "jquery/colorbox/jquery.colorbox.js'></script>");
			add_to_jquery("$('a[rel^=\"colorbox\"]').colorbox({ current: '',width:'80%',height:'80%'});");
		}
		$class = ($class ? " $class" : '');
		if ($responsive) {
			$class = " class='img-responsive $class' ";
		} else {
			$class = (!empty($class) ? " class='$class' " : '');
		}

		return "<a target='_blank' href='$img_path' title='$img_title' rel='colorbox'>" . ($as_text ? $img_title : "<img src='$img_path'" . $class . "alt='$img_title'/>") . "</a>";
	}
}

if (!function_exists('thumbnail')) {
	/**
	 * Show image thumbnail.
	 *
	 * @param string $src The path to image.
	 * @param string $size Image size.
	 * @param bool $url Make image clickable.
	 * @param bool $colorbox Allow colorbox().
	 * @param bool $responsive Add img-responsive class.
	 * @param string $class CSS class.
	 *
	 * @return string
	 */
	function thumbnail($src, $size, $url = FALSE, $colorbox = FALSE, $responsive = TRUE, $class = "m-2")
	{
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
		$html = "<div style='max-height:" . $size . "; max-width:" . $size . "' class='display-inline-block image-wrap thumb text-center overflow-hide " . $class . "'>";
		$html .= $url || $colorbox ? "<a " . ($colorbox && $src ? "class='colorbox' " : '') . ($url ? "href='" . $url . "'" : '') . " >" : '';
		if ($src && file_exists($src) && !is_dir($src) || stristr($src, "?")) {
			$html .= "<img " . ($responsive ? "class='img-responsive' " : '') . "src='$src'" . (!$responsive && ($_offset_w || $_offset_h) ? " style='margin-left: -" . $_offset_w . "px; margin-top: -" . $_offset_h . "px' " : '') . " alt='thumbnail'/>";
		} else {
			$size = str_replace('px', '', $size);

			if (!defined('HOLDERJS')) {
				define('HOLDERJS', TRUE);
				add_to_footer("<script src='" . INCLUDES . "jquery/holder.min.js'></script>");
			}

			$html .= "<img src='holder.js/" . $size . "x" . $size . "/text:' alt='thumbnail'/>";
		}
		$html .= $url || $colorbox ? "</a>" : '';
		$html .= "</div>";
		if ($colorbox && $src && !defined('COLORBOX')) {
			define('COLORBOX', TRUE);
			add_to_head("<link rel='stylesheet' href='" . INCLUDES . "jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
			add_to_head("<script type='text/javascript' src='" . INCLUDES . "jquery/colorbox/jquery.colorbox.js'></script>");
			add_to_jquery("$('.colorbox').colorbox({width: '75%', height: '75%'});");
		}

		return $html;
	}
}

if (!function_exists('lorem_ipsum')) {
	/**
	 * Generate random lorem ipsum text by given length.
	 *
	 * @param int $length String length.
	 *
	 * @return string
	 */
	function lorem_ipsum($length)
	{
		$text = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum aliquam felis nunc, in dignissim metus suscipit eget. Nunc scelerisque laoreet purus, in ullamcorper magna sagittis eget. Aliquam ac rhoncus orci, a lacinia ante. Integer sed erat ligula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Fusce ullamcorper sapien mauris, et tempus mi tincidunt laoreet. Proin aliquam vulputate felis in viverra.";
		$text .= "Duis sed lorem vitae nibh sagittis tempus sed sed enim. Mauris egestas varius purus, a varius odio vehicula quis. Donec cursus interdum libero, et ornare tellus mattis vitae. Phasellus et ligula velit. Vivamus ac turpis dictum, congue metus facilisis, ultrices lorem. Cras imperdiet lacus in tincidunt pellentesque. Sed consectetur nunc vitae fringilla volutpat. Mauris nibh justo, luctus eu dapibus in, pellentesque non urna. Nulla ullamcorper varius lacus, ut finibus eros interdum id. Proin at pellentesque sapien. Integer imperdiet, sapien nec tristique laoreet, sapien lacus porta nunc, tincidunt cursus risus mauris id quam.";
		$text .= "Ut vulputate mauris in facilisis euismod. Ut id libero vitae neque laoreet placerat a id mi. Integer ornare risus placerat, interdum nisi sed, commodo ligula. Integer at ipsum id magna blandit volutpat. Sed euismod mi odio, vitae molestie diam ornare quis. Aenean id ligula finibus, convallis risus a, scelerisque tellus. Morbi quis pretium lectus. In convallis hendrerit sem. Vestibulum sed ultricies massa, ut tempus risus. Nunc aliquam at tellus quis lobortis. In hac habitasse platea dictumst. Vestibulum maximus, nibh at tristique viverra, eros felis ultrices nunc, et efficitur nunc augue a orci. Phasellus et metus mauris. Morbi ut ex ut urna tincidunt varius eu id diam. Aenean vestibulum risus sed augue vulputate, a luctus ligula laoreet.";
		$text .= "Nam tempor sodales mi nec ullamcorper. Mauris tristique ligula augue, et lobortis turpis dictum vitae. Aliquam leo massa, posuere ac aliquet quis, ultricies eu elit. Etiam et justo et nulla cursus iaculis vel quis dolor. Phasellus viverra cursus metus quis luctus. Nulla massa turpis, porttitor vitae orci sed, laoreet consequat urna. Etiam congue turpis ac metus facilisis pretium. Nam auctor mi et auctor malesuada. Mauris blandit nulla quis ligula cursus, ut ullamcorper dui posuere. Fusce sed urna id quam finibus blandit tempus eu tellus. Vestibulum semper diam id ante iaculis iaculis.";
		$text .= "Fusce suscipit maximus neque, sed consectetur elit hendrerit at. Sed luctus mi in ex auctor mollis. Suspendisse ac elementum tellus, ut malesuada purus. Mauris condimentum elit at dolor eleifend iaculis. Aenean eget faucibus mauris. Pellentesque fermentum mattis imperdiet. Donec mattis nisi id faucibus finibus. Vivamus in eleifend lorem, vel dictum nisl. Morbi ut mollis arcu.";

		return trim_text($text, $length);
	}
}

if (!function_exists('get_timezones')) {
	function get_timezones($key = NULL)
	{
		static $timezone_array = [];
		if (empty($timezone_array)) {
			$json_file = @file_get_contents(INCLUDES . 'geomap/timezones.json', FALSE);
			$timezones_json = json_decode($json_file, TRUE);

			foreach ($timezones_json as $zone => $zone_city) {
				$date = new \DateTime('now', new \DateTimeZone($zone));
				$offset = $date->getOffset() / 3600;
				$timezone_array[$zone] = '(GMT' . ($offset < 0 ? $offset : '+' . $offset) . ') ' . $zone_city;
			}
		}

		return $key === NULL ? $timezone_array : ($timezone_array[$key] ?? NULL);
	}

}


if (!function_exists('format_time')) {
	/**
	 * Formats and display a datetime
	 *
	 * @param $value - datetime
	 * @return string
	 */
	function format_time($value)
	{
		$updated = $value ?? NULL;

		if ($updated && $updated != '0000-00-00 00:00:00') {
			$updated_time = strtotime($updated);
			$today = strtotime(date('Y-m-d'));
			$yesterday = strtotime('-1 day', $today);

			if (date('Y-m-d', $updated_time) === date('Y-m-d')) {
				return "Today, " . date('g:i A', $updated_time);
			} elseif (date('Y-m-d', $updated_time) === date('Y-m-d', $yesterday)) {
				return "Yesterday, " . date('g:i A', $updated_time);
			} else {
				return date('j M Y, g:i A', $updated_time);
			}
		}

		return '-';
	}
}

if (!function_exists('timer')) {
	/**
	 * Show time ago from timestamp.
	 *
	 * @param int $time Timestamp or if empty it use time().
	 *
	 * @return string
	 */
    function timer($time = NULL)
    {
        $locale = fusion_get_locale();
        if (!$time) {
            $time = time();
        }

        $time = stripinput($time);

        // FIX: If the input is a date string (like Y-m-d H:i:s), safely convert it to a Unix timestamp
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }

        // Extra safety fallback if strtotime fails completely on a corrupt string
        if (!$time) {
            return NULL;
        }

        $current = time();
        $calculated = $current - $time;
        $second = 1;
        $minute = $second * 60;
        $hour = $minute * 60;
        $day = 24 * $hour;
        $month = days_current_month() * $day;
        $year = (date("L", $time) > 0) ? 366 * $day : 365 * $day;

        if ($calculated < 1) {
            return "<abbr class='atooltip' data-toggle='tooltip' data-placement='top' title='" . showdate('longdate', $time) . "'>" . $locale['just_now'] . "</abbr>";
        }

        $timer = [
            $year   => $locale['timer_year'],
            $month  => $locale['timer_month'],
            $day    => $locale['timer_day'],
            $hour   => $locale['timer_hour'],
            $minute => $locale['timer_minute'],
            $second => $locale['timer_second'],
        ];

        foreach ($timer as $arr => $unit) {
            $calc = $calculated / $arr;
            if ($calc >= 1) {
                $answer = round($calc);
                $string = format_word($answer, $unit, ['add_count' => FALSE]);
                $text = strtr($locale['timer'], [
                    '[DAYS]'   => $answer . " " . $string,
                    '[AGO]'    => $locale['ago'],
                    '[ANSWER]' => $answer,
                    '[STRING]' => $string,
                ]);
                return "<abbr class='atooltip' data-toggle='tooltip' data-placement='top' title='" . showdate('longdate', $time) . "'>" . $text . "</abbr>";
            }
        }

        return NULL;
    }
}

if (!function_exists('days_current_month')) {
	/**
	 * Days in the current month.
	 *
	 * @return int
	 */
	function days_current_month()
	{
		$year = showdate("%Y", time());
		$month = showdate("%m", time());

		return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
	}
}

if (!function_exists('countdown')) {
	/**
	 * Counts how many days remain until the specified date.
	 *
	 * @param int $time Timestamp.
	 *
	 * @return string|null
	 */
    function countdown($time)
    {
        $locale = fusion_get_locale();

        // FIX 1: Convert string dates (e.g., '2026-06-15 12:00:00') into Unix timestamps instantly
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }

        // Safety fallback if the timestamp string parse fails completely
        if (!$time) {
            return NULL;
        }

        $current = time();
        $updated = $time - $current;

        $second = 1;
        $minute = $second * 60;
        $hour = $minute * 60;
        $day = 24 * $hour;
        $month = days_current_month() * $day;

        // FIX 2: Check leap year condition using the actual target time ($time), not the tiny relative offset ($updated)
        $year = (date("L", $time) > 0) ? 366 * $day : 365 * $day;

        $timer = [
            $year   => $locale['year'],
            $month  => $locale['month'],
            $day    => $locale['day'],
            $hour   => $locale['hour'],
            $minute => $locale['minute'],
            $second => $locale['second'],
        ];
        $timer_b = [
            $year   => $locale['year_a'],
            $month  => $locale['month_a'],
            $day    => $locale['day_a'],
            $hour   => $locale['hour_a'],
            $minute => $locale['minute_a'],
            $second => $locale['second_a'],
        ];

        foreach ($timer as $arr => $unit) {
            $calc = $updated / $arr;
            if ($calc >= 1) {
                $answer = round($calc);
                $string = ($answer > 1) ? $timer_b[$arr] : $unit;

                // Pass the actual absolute target timestamp ($time) into showdate() for an accurate title tool-tip description
                return "<abbr class='atooltip' data-toggle='tooltip' data-placement='top' title='~" . showdate('newsdate', $time) . "'>$answer " . $string . "</abbr>";
            }
        }

        if (!isset($answer)) {
            return "<abbr class='atooltip' data-toggle='tooltip' data-placement='top' title='" . showdate('newsdate', $current) . "'>" . $locale['now'] . "</abbr>";
        }

        return NULL;
    }
}

if (!function_exists('opencollapse')
	&& !function_exists('opencollapsebody')
	&& !function_exists('closecollapsebody')
	&& !function_exists('closecollapse')
) {
	/**
	 * Create the BS5 Accordion wrapper.
	 *
	 * @param string $id Unique accordion ID.
	 *
	 * @return string
	 */
	function opencollapse($id)
	{
		if (function_exists('fusion_render_framework_component')
			&& in_array((fusion_framework_active()['key'] ?? ''), ['tailwind', 'bootstrap'], TRUE)) {
			$framework_collapse = fusion_render_framework_component('collapse', [
				'callback' => 'opencollapse',
				'id' => $id,
			]);
			if ($framework_collapse !== '') {
				return $framework_collapse;
			}
		}

		// BS5 uses .accordion instead of .panel-group
		return "<div class='accordion' id='{$id}-accordion'>";
	}

	/**
	 * Create collapsing BS5 Accordion item.
	 *
	 * @param string $title Panel title.
	 * @param string $unique_id Panel ID (used to link header/body).
	 * @param string $grouping_id Parent's accordion ID (for data-bs-parent).
	 * @param bool $active Panel state (TRUE for open).
	 * @param array $options
	 * @return string
	 */
	function opencollapsebody($title, $unique_id, $grouping_id, $active = FALSE, $options = [])
	{

		$active_class = $active ? ' show' : '';
		$button_class = $active ? '' : ' collapsed';
		$aria_expanded = $active ? 'true' : 'false';
		$button_title = $active ? 'Close' : 'Expand';
		$button_title_reverse = $active ? 'Expand' : 'Close';

		$default_options = [
			'class'         => '',
			'heading_size'  => 4,
			'custom_header' => '', // customization
			'type'          => '',
		];

		$options += $default_options;

		if (function_exists('fusion_render_framework_component')
			&& in_array((fusion_framework_active()['key'] ?? ''), ['tailwind', 'bootstrap'], TRUE)) {
			$framework_body = fusion_render_framework_component('collapse', [
				'callback' => 'opencollapsebody',
				'id' => $unique_id,
				'group_id' => $grouping_id,
				'title' => $title,
				'active' => $active,
				'class' => $options['class'],
				'heading_size' => $options['heading_size'],
				'custom_header' => $options['custom_header'],
				'type' => $options['type'],
			]);
			if ($framework_body !== '') {
				return $framework_body;
			}
		}

		$html = "<div class='accordion-item {$options['class']}'>";

		if ($options['custom_header']) {

			$html .= $options['custom_header'];

		} elseif ($options['type'] == 'admin_header') {

			$html .= "<div class='d-flex align-items-center p-3 py-2 bg-light-subtle'>
               <div class='card-title mb-0'>{$title}</div>
               <button class='btn p-2 ms-auto btn-sm fw-bolder adm-btn {$button_class}'
                  type='button'
                  data-bs-toggle='collapse'
                  data-bs-target='#{$unique_id}-collapse'
                  aria-expanded='{$aria_expanded}'
                  aria-controls='{$unique_id}-collapse'
                  data-label-expand='{$button_title}'
                  data-label-close='{$button_title_reverse}'>{$button_title}</button>
            </div>";

			$active_class .= ' bg-light-subtle pt-3';

			// Use a standard function() for jQuery to ensure 'this' refers to the button
			if (!defined('COLLAPSED_C')) {
				define('COLLAPSED_C', TRUE);
				add_to_jquery("
                $('.adm-btn').on('click', function() {
                    const btn = $(this);
                    const expandTxt = btn.data('label-expand');
                    const closeTxt = btn.data('label-close');
                    
                    // Small delay to check class after BS5 animation starts
                    setTimeout(() => {
                        if (btn.hasClass('collapsed')) {
                            btn.text(expandTxt);
                        } else {
                            btn.text(closeTxt);
                        }
                    }, 10);
                });
				");
			}

		} else {
			// Accordion Header (H2 element is mandatory here per BS5 recommendation)
			$html .= "<h2 class='accordion-header' id='{$unique_id}-collapse-heading'>";
			// Accordion Button (Role changed to 'button', data attributes updated)
			$html .= "<button class='accordion-button{$button_class}' type='button' data-bs-toggle='collapse' data-bs-target='#{$unique_id}-collapse' aria-expanded='{$aria_expanded}' aria-controls='{$unique_id}-collapse'>{$title}</button>";
			$html .= "</h2>"; // Close .accordion-header
		}

		// Accordion Collapse Body
		$html .= "<div id='{$unique_id}-collapse' class='accordion-collapse collapse{$active_class}' aria-labelledby='{$unique_id}-collapse-heading' data-bs-parent='#{$grouping_id}-accordion'>";
		// Accordion Body (New BS5 class)
		$html .= "<div class='accordion-body'>";

		return $html;
	}

	/**
	 * Close collapsing BS5 panel.
	 *
	 * @return string
	 */
	function closecollapsebody()
	{
		if (function_exists('fusion_render_framework_component')
			&& in_array((fusion_framework_active()['key'] ?? ''), ['tailwind', 'bootstrap'], TRUE)) {
			$framework_body = fusion_render_framework_component('collapse', ['callback' => 'closecollapsebody']);
			if ($framework_body !== '') {
				return $framework_body;
			}
		}

		// Close .accordion-body
		$html = "</div>";
		// Close .accordion-collapse
		$html .= "</div>";
		// Close .accordion-item
		$html .= "</div>";

		return $html;
	}

	/**
	 * Close BS5 accordion.
	 *
	 * @return string
	 */
	function closecollapse()
	{
		if (function_exists('fusion_render_framework_component')
			&& in_array((fusion_framework_active()['key'] ?? ''), ['tailwind', 'bootstrap'], TRUE)) {
			$framework_collapse = fusion_render_framework_component('collapse', ['callback' => 'closecollapse']);
			if ($framework_collapse !== '') {
				return $framework_collapse;
			}
		}

		return "</div>"; // .accordion
	}
}

if (!function_exists('tab_active')
	&& !function_exists('opentab')
	&& !function_exists('opentabbody')
	&& !function_exists('closetabbody')
	&& !function_exists('closetab')
) {
	class FusionTabs
	{
		private $remember = FALSE;
		private $cookie_prefix = 'tab_js';
		private $cookie_name = '';
		private $link_mode = FALSE;
		private static $instance = NULL;

		/**
		 * @param $id
		 *
		 * @return static
		 */
		public static function getInstance($id)
		{
			if (empty(self::$instance[$id])) {
				self::$instance[$id] = new static();
			}
			return self::$instance[$id];
		}

		/**
		 * Current active tab selector.
		 *
		 * @param array $array Multidimension array consisting of keys title, id, icon.
		 * @param int $default_active 0 if link_mode is false, $_GET if link_mode is true.
		 * @param string $getname Set getname and turn tabs into link that listens to getname.
		 *
		 * @return string
		 */
		public static function tabActive($array, $default_active, $getname = NULL)
		{
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
		 * Get current active tab index
		 *
		 * @param array $array
		 * @param string $default_active
		 * @param bool $getname
		 *
		 * @return int
		 */
		public static function tabIndex($array, $default_active, $getname = FALSE)
		{
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

		/**
		 * Automatically remember tab using cookie.
		 *
		 * @param bool $value
		 */
		public function setRemember($value)
		{
			$this->remember = $value;
		}

		/**
		 * Render tab links.
		 *
		 * @param array $tab_title Multidimension array consisting of keys title, id, icon.
		 * @param string $link_active_arrkey tab_active() function or the $_GET request to match the $tabs['id'].
		 * @param string $id Unique ID.
		 * @param bool $link False for jquery, true for php (will reload page).
		 * @param string $class CSS class for the nav.
		 * @param string $getname Set getname and turn tabs into the link that listens to getname.
		 * @param array $cleanup_get The request key that needs to be deleted.
		 *
		 * Example:
		 * $tabs['title'][] = "Tab 1";
		 * $tabs['id'][] = "tab1";
		 * $tabs['title'][] = "Tab 2";
		 * $tabs['id'][] = "tab2";
		 * $tab_active = tab_active($tabs, 0);
		 *
		 * Jquery:
		 * echo opentab($tabs, $tab_active, 'myTab', FALSE, 'nav-pills', 'ref', ['action', 'subaction']);
		 *
		 * PHP:
		 * echo opentab($tabs, $_GET['ref'], 'myTab', TRUE, 'nav-pills', 'ref', ['action', 'subaction']);
		 * echo opentab($tabs, $_GET['ref'], 'myTab', TRUE, 'nav-pills', 'ref', ['*']); // clear all
		 *
		 * @return string
		 */
//        public function openTab($tab_title, $link_active_arrkey, $id, $link = FALSE, $class = FALSE, $getname = 'section', array $cleanup_get = [], $wrapper_class = '') {
//            $this->cookie_name = $this->cookie_prefix.'-'.$id;
//
//            $this->link_mode = $link;
//
//            $getArray = [$getname];
//            if (!empty($cleanup_get)) {
//                $getArray = array_merge_recursive($cleanup_get, $getArray);
//            }
//
//            if (empty($link) && $this->remember) {
//                if (isset($_COOKIE[$this->cookie_name])) {
//                    $link_active_arrkey = str_replace('tab-', '', $_COOKIE[$this->cookie_name]);
//                }
//            }
//            $html = "<div class='nav-wrapper".whitespace(($wrapper_class ?? ''))."'>";
//            $html .= "<ul id='$id' class='nav ".(!empty($class) ? $class : 'nav-tabs')."'>";
//            foreach ($tab_title['title'] as $arr => $v) {
//                $v_title = $v;
//                $tab_id = $tab_title['id'][$arr];
//
//                $icon = (isset($tab_title['icon'][$arr])) ? $tab_title['icon'][$arr] : '';
//
//                $link_url = '#';
//                if ($link) {
//                    $link_url = $link.(stristr($link, '?') ? '&' : '?').$getname."=".$tab_id; // keep all request except GET array
//                    if ($link === TRUE) {
//
//                        $keep_filtered = FALSE;
//                        if (in_array("*", $cleanup_get)) {
//                            $getArray = [];
//                            $keep_filtered = TRUE;
//                        }
//
//                        $link_url = clean_request($getname.'='.$tab_id.(check_get('aid') ? "&aid=".get('aid') : ""), $getArray, $keep_filtered);
//                    }
//
//                    $active = ($link_active_arrkey == $tab_id) ? ' active' : '';
//                } else {
//                    $active = ($link_active_arrkey == "".$tab_id) ? ' active' : '';
//                }
//
//                $html .= '<li class="nav-item">';
//                $html .= "<a class='nav-link pointer".$active."' ".(!$link ? "id='tab-".$tab_id."' data-bs-toggle='tab' data-bs-target='#".$tab_id."'" : "href='$link_url'")." role='tab'><div class='d-flex align-items-center gap-2'>".($icon ? $icon : '')." ".$v_title."</div></a>";
//                $html .= "</li>";
//            }
//            $html .= "</ul>";
//            $html .= "<div id='tab-content-$id' class='tab-content'>";
//            if (empty($link) && $this->remember) {
//                if (!defined('JS_COOKIES')) {
//                    define('JS_COOKIES', TRUE);
//                    OutputHandler::addToFooter('<script type="text/javascript" src="'.INCLUDES.'jscripts/js.cookie.min.js"></script>');
//                }
//                OutputHandler::addToJQuery("
//                    $('#".$id." > li').on('click', function() {
//                        var cookieName = '".$this->cookie_name."';
//                        var cookieValue = $(this).find(\"a[role='tab']\").attr('id');
//                        Cookies.set(cookieName, cookieValue);
//                    });
//                    var cookieName = 'tab_js-".$id."';
//                    if (Cookies.get(cookieName)) {
//                        var tabElement = $('#'+Cookies.get(cookieName));
//                        if (tabElement.length) {
//                            var tab = new bootstrap.Tab(tabElement[0]);
//                            tab.show();
//                        }
//                    }
//                    ");
//            }
//
//            return $html;
//        }

		/**
		 * Creates tab body.
		 *
		 * @param string $id Tab id from $tabs['id'].
		 * @param string $link_active_arrkey tab_active() function or the $_GET request to match the $tabd['id'].
		 * @param string $key Set getname and turn tabs into link that listens to getname.
		 *
		 * @return string
		 */
		public function openTabBody($id, $link_active_arrkey = NULL, $key = 'section')
		{
			$bootstrap = ' show'; // Always use BS5 show class

			if (isset($_GET[$key]) && $this->link_mode) {
				if ($link_active_arrkey == $id) {
					$status = ' active' . $bootstrap;
				} else {
					$status = '';
				}
			} else {
				if (!$this->link_mode) {
					// bug because the remember is not part of the same instance.
					if ($this->remember) {

						if (isset($_COOKIE[$this->cookie_name])) {

							$link_active_arrkey = str_replace('tab-', '', $_COOKIE[$this->cookie_name]);
						}
					}
				}
				$status = ($link_active_arrkey == $id ? " active" . $bootstrap : '');
			}

			return "<div class='tab-pane fade {$status}' id='{$id}'><!-- opentab -->";
		}

		public function openTabWarapper($id, $wrapper_body_class = '') {
			$html = "<div class='" . ($wrapper_body_class ?: 'card-body') . "'>\n<div id='tab-content-{$id}' class='tab-content'>\n";
			$html .= "<!--Start tab content-->";
			return $html;
		}
		
		public function openTab(
			$tab_title,
			$link_active_arrkey,
			$id,
			$link = FALSE,
			$class = FALSE,
			$getname = 'section',
			array $cleanup_get = [],
			$wrapper_class = '',
			$wrapper_header_class = '',
			$wrapper_body_class = '',
			int $max_tabs = 0, // NEW PARAMETER — number of visible tabs before "More"
			$has_wrapper = true,
			$header_action = ''
		)
		{

			$this->cookie_name = $this->cookie_prefix . '-' . $id;
			$this->link_mode = $link;

			$getArray = [$getname];
			if (!empty($cleanup_get)) {
				$getArray = array_merge_recursive($cleanup_get, $getArray);
			}

			if (empty($link) && $this->remember) {
				if (isset($_COOKIE[$this->cookie_name])) {
					$link_active_arrkey = str_replace('tab-', '', $_COOKIE[$this->cookie_name]);
				}
			}

			$html = "<div class='nav-wrapper" . whitespace(($wrapper_class ?: 'card')) . "'>";
			$html .= '<div class="' . (!empty($wrapper_header_class) ? $wrapper_header_class : 'card-header') . '">';
			$html .= "<ul id='$id' class='nav " . (!empty($class) ? $class : "nav-tabs card-header-tabs") . " '>";

			$total_tabs = (isset($tab_title['title']) && is_array($tab_title['title'])) ? count($tab_title['title']) : 0;

			$visible_tabs = $max_tabs > 0 ? min($max_tabs, $total_tabs) : $total_tabs;

			$has_more = $max_tabs > 0 && $total_tabs > $max_tabs;

			$count = 0;
			foreach ($tab_title['title'] as $arr => $v) {
				$count++;
				$v_title = $v;
				$tab_id = $tab_title['id'][$arr];
				$icon = (!empty($tab_title['icon'][$arr])) ? $tab_title['icon'][$arr] : '';
				$class = (!empty($tab_title['class'][$arr])) ? ' ' . $tab_title['class'][$arr] : '';

				if (!empty($icon)) {
					$icon = get_svg($icon);
				}

				// Build link or tab target
				$link_url = '#';
				if ($link) {
					$link_url = $link . (stristr($link, '?') ? '&' : '?') . $getname . "=" . $tab_id;
					if ($link === TRUE) {
						$keep_filtered = FALSE;
						if (in_array("*", $cleanup_get)) {
							$getArray = [];
							$keep_filtered = TRUE;
						}
						$link_url = clean_request($getname . '=' . $tab_id . (check_get('aid') ? "&aid=" . get('aid') : ""), $getArray, $keep_filtered);
					}
					$active = ($link_active_arrkey == $tab_id) ? ' active' : '';
				} else {
					$active = ($link_active_arrkey == "" . $tab_id) ? ' active' : '';
				}

				// If exceeds max_tabs, begin dropdown structure
				if ($has_more && $count == $visible_tabs + 1) {

					$html .= "<li class='nav-item dropdown{$class}'>
                    <a class='nav-link dropdown-toggle' id='{$id}Dropdown' data-bs-toggle='dropdown' href='#' role='button' aria-expanded='false'>
                        More
                    </a>
                    <ul class='dropdown-menu dropdown-menu-end' aria-labelledby='{$id}Dropdown'>";
				}

				// Append tab item (normal or dropdown)
				if ($has_more && $count > $visible_tabs) {
					$html .= "<li><a class='dropdown-item" . ($active ? ' active' : '') . "' " . (!$link ? "id='tab-" . $tab_id . "' data-bs-toggle='tab' data-bs-target='#" . $tab_id . "'" : "href='$link_url'") . ">" . ($icon ? $icon . ' ' : '') . $v_title . "</a></li>";
				} else {
					$html .= "<li class='nav-item{$class}'>";
					$html .= "<a class='nav-link pointer" . $active . "' " . (!$link ? "id='tab-" . $tab_id . "' data-bs-toggle='tab' data-bs-target='#" . $tab_id . "'" : "href='$link_url'") . " role='tab'><div class='d-flex align-items-center gap-2'>" . ($icon ?: '') . " " . $v_title . "</div></a>";
					$html .= "</li>";
				}
			}

			// Close dropdown if used
			if ($has_more) {
				$html .= "</ul></li>";
			}

			$html .= "</ul>\n";
			$html .= $header_action;
			$html .= "</div>\n";
			
			if ($has_wrapper) {
				$html .= "<div class='" . ($wrapper_body_class ?: 'card-body') . "'><div id='tab-content-{$id}' class='tab-content'>";
				$html .= "<!--Start tab content-->";
			} else {
				$html .= "</div>\n";
			}
			
			// Cookie remember JS
			if (empty($link) && $this->remember) {
				fusion_load_script(INCLUDES . 'jscripts/js.cookie.min.js');
				OutputHandler::addToJQuery("
                    $('#" . $id . " > li').on('click', function() {
                        var cookieName = '" . $this->cookie_name . "';
                        var cookieValue = $(this).find(\"a[role='tab']\").attr('id');
                        Cookies.set(cookieName, cookieValue);
                    });
                    var cookieName = 'tab_js-" . $id . "';
                    if (Cookies.get(cookieName)) {
                        var tabElement = $('#'+Cookies.get(cookieName));
                        if (tabElement.length) {
                            var tab = new bootstrap.Tab(tabElement[0]);
                            tab.show();
                        }
                    }
                ");
			}
			
			return $html;
		}


		/**
		 * Close tab body.
		 *
		 * @return string
		 */
		public function closeTabBody()
		{
			return "</div><!-- close .tab-pane -->";
		}

		/**
		 * Close tab.
		 *
		 * @param array $options
		 *          'tab_nav'   -   Add previous and next
		 *
		 * @return string
		 */
		public function closeTab($options = [])
		{
			$locale = fusion_get_locale();
			$default_options = [
				"tab_nav" => FALSE,
				"has_wrapper" => TRUE
			];
			$options += $default_options;
			$html = "";
			
			if ($options['tab_nav'] == TRUE) {
				$nextBtn = "<a class='btn btnNext ms-auto'>" . $locale['next'] . "</a>";
				$prevBtn = "<a class='btn btnPrevious'>" . $locale['previous'] . "</a>";
				OutputHandler::addToJQuery("
                $('.btnNext').click(function(){
                  $('.nav-tabs > .active').next('li').find('a').trigger('click');
                });
                $('.btnPrevious').click(function(){
                  $('.nav-tabs > .active').prev('li').find('a').trigger('click');
                });
            ");
				$html .= "<div class='d-flex w-100'>" . $prevBtn . $nextBtn . "</div>";
			}
			if ($options['has_wrapper']) {
				$html .= "</div>";
			}
			$html .= "</div></div>";
			
			return $html;
		}
	}

	/**
	 * Current active tab selector.
	 *
	 * @param array $array Multidimension array consisting of keys title, id, icon.
	 * @param int $default_active 0 if link_mode is false, $_GET if link_mode is true.
	 * @param string $getname Set getname and turn tabs into link that listens to getname.
	 *
	 * @return string
	 */
	function tab_active($array, $default_active, $getname = NULL)
	{
		return \FusionTabs::tabActive($array, $default_active, $getname);
	}

	/**
	 * Get current active tab index
	 *
	 * @param array $array
	 * @param string $default_active
	 * @param bool $getname
	 *
	 * @return int
	 */
	function tab_index($array, $default_active, $getname = FALSE)
	{
		return FusionTabs::tabIndex($array, $default_active, $getname);
	}

	/**
	 * Render tab links.
	 *
	 * @param array $tab_array Multidimension array consisting of keys title, id, icon.
	 * @param string $link_active_arrkey tab_active() function or the $_GET request to match the $tab_title['id'].
	 * @param string $instance_id Unique ID.
	 * @param bool $link False for jquery, true for php (will reload page).
	 * @param string $class CSS class for the nav.
	 * @param string $getname Set getname and turn tabs into the link that listens to getname.
	 * @param array $cleanup_get The request key that needs to be deleted.
	 * @param bool $remember Set to true to automatically remember tab using cookie.
	 * @param string $wrapper_class CSS class for the nav wrapper
	 *
	 * Example:
	 * $tabs['title'][] = "Tab 1";
	 * $tabs['id'][] = "tab1";
	 * $tabs['title'][] = "Tab 2";
	 * $tabs['id'][] = "tab2";
	 * $tab_active = tab_active($tabs, 0);
	 *
	 * Jquery:
	 * echo opentab($tabs, $tab_active, 'myTab', FALSE, 'nav-pills', 'ref', ['action', 'subaction']);
	 *
	 * PHP:
	 * echo opentab($tabs, $_GET['ref'], 'myTab', TRUE, 'nav-pills', 'ref', ['action', 'subaction']);
	 * echo opentab($tabs, $_GET['ref'], 'myTab', TRUE, 'nav-pills', 'ref', ['*']); // clear all
	 *
	 * @return string
	 */
	function opentab($tab_array, $link_active_arrkey, $instance_id, $link = FALSE, $class = NULL, $getname = "section", $cleanup_get = [], $remember = FALSE, $wrapper_class = '', $wrapper_header_class = '', $wrapper_body_class = '', $max_tabs = 0, $has_wrapper = true, $header_action = '')
	{
		if (function_exists('fusion_render_framework_component')
			&& in_array((fusion_framework_active()['key'] ?? ''), ['tailwind', 'bootstrap'], TRUE)) {
			$framework_tabs = fusion_render_framework_component('tabs', [
				'part' => 'header',
				'tabs' => $tab_array,
				'active_id' => (string)$link_active_arrkey,
				'id' => $instance_id,
				'link' => $link,
				'class' => $class ?? '',
				'getname' => $getname,
				'cleanup_get' => $cleanup_get,
				'remember' => $remember,
				'wrapper_class' => $wrapper_class,
				'wrapper_header_class' => $wrapper_header_class,
				'wrapper_body_class' => $wrapper_body_class,
				'max_tabs' => $max_tabs,
				'has_wrapper' => $has_wrapper,
				'header_action' => $header_action,
			]);
			if ($framework_tabs !== '') {
				return $framework_tabs;
			}
		}

		$fusion_tabs = FusionTabs::getInstance($instance_id);
		if ($remember) {
			$fusion_tabs->setRemember(TRUE);
		}

		return $fusion_tabs->openTab($tab_array, $link_active_arrkey, $instance_id, $link, $class, $getname, $cleanup_get, $wrapper_class, $wrapper_header_class, $wrapper_body_class, $max_tabs, $has_wrapper, $header_action);
	}

	/**
	 * Creates tab body.
	 *
	 * @param string $instance_id
	 * @param string $tab_id Tab id from $tabs['id'].
	 * @param string $link_active_arrkey tab_active() function or the $_GET request to match the $tabd['id'].
	 * @param bool $link Deprecated, however this function is replaceable, and the params are accessible.
	 * @param string $key Set getname and turn tabs into link that listens to getname.
	 *
	 * @return string
	 */
	function opentabbody($instance_id, $tab_id, $link_active_arrkey = NULL, $link = FALSE, $key = NULL)
	{
		if (function_exists('fusion_render_framework_component')
			&& in_array((fusion_framework_active()['key'] ?? ''), ['tailwind', 'bootstrap'], TRUE)) {
			$tab_options = [
				'part' => 'openbody',
				'id' => $tab_id,
				'group_id' => $instance_id,
				'active_id' => $link_active_arrkey,
				'key' => $key ?? 'section',
			];
			if ($link_active_arrkey !== NULL) {
				$tab_options['active'] = (string)$link_active_arrkey === (string)$tab_id;
			}
			$framework_body = fusion_render_framework_component('tabs', $tab_options);
			if ($framework_body !== '') {
				return $framework_body;
			}
		}

		$fusion_tabs = FusionTabs::getInstance($instance_id);
		return $fusion_tabs->openTabBody($tab_id, $link_active_arrkey, $key);
	}
	
	/**
	 * @param $instance_id
	 * @param $wrapper_body_class
	 * @return string
	 */
	function opentabwrapper($instance_id, $wrapper_body_class) {
		if (function_exists('fusion_render_framework_component')
			&& in_array((fusion_framework_active()['key'] ?? ''), ['tailwind', 'bootstrap'], TRUE)) {
			$framework_wrapper = fusion_render_framework_component('tabs', [
				'part' => 'openwrapper',
				'group_id' => $instance_id,
				'class' => $wrapper_body_class,
			]);
			if ($framework_wrapper !== '') {
				return $framework_wrapper;
			}
		}

		$fusion_tabs = FusionTabs::getInstance($instance_id);
		return $fusion_tabs->openTabWarapper($instance_id, $wrapper_body_class);
	}
	
	function closetabwrapper() {
		if (function_exists('fusion_render_framework_component')
			&& in_array((fusion_framework_active()['key'] ?? ''), ['tailwind', 'bootstrap'], TRUE)) {
			$framework_wrapper = fusion_render_framework_component('tabs', ['part' => 'closewrapper']);
			if ($framework_wrapper !== '') {
				return $framework_wrapper;
			}
		}

		return "</div>\n</div>\n<!--End wrapper-->";
	}
	
	/**
	 * Close tab body.
	 *
	 * @return string
	 */
	function closetabbody()
	{
		if (function_exists('fusion_render_framework_component')
			&& in_array((fusion_framework_active()['key'] ?? ''), ['tailwind', 'bootstrap'], TRUE)) {
			$framework_body = fusion_render_framework_component('tabs', ['part' => 'closebody']);
			if ($framework_body !== '') {
				return $framework_body;
			}
		}

		$fusion_tabs = new FusionTabs();

		return $fusion_tabs->closeTabBody();
	}

	/**
	 * Close tab.
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	function closetab($options = [])
	{
		if (function_exists('fusion_render_framework_component')
			&& in_array((fusion_framework_active()['key'] ?? ''), ['tailwind', 'bootstrap'], TRUE)) {
			$options += [
				'tab_nav' => FALSE,
				'has_wrapper' => TRUE,
			];
			$locale = fusion_get_locale();
			$framework_tabs = fusion_render_framework_component('tabs', [
				'part' => 'footer',
				'tab_nav' => $options['tab_nav'],
				'has_wrapper' => $options['has_wrapper'],
				'locale' => [
					'previous' => $locale['previous'] ?? 'Previous',
					'next' => $locale['next'] ?? 'Next',
				],
			]);
			if ($framework_tabs !== '') {
				return $framework_tabs;
			}
		}

		$fusion_tabs = new FusionTabs();

		return $fusion_tabs->closeTab($options);
	}
}

if (!function_exists('display_ratings')) {
	/**
	 * Display ratings.
	 *
	 * @param int $total_sum Total number of ratings.
	 * @param int $total_votes Total number of votes.
	 * @param string $link Make item clickable.
	 * @param string $class CSS class for the link.
	 * @param int $mode Show 2 out of 10 or 2/10 rating. Possible value: 1, 2.
	 *
	 * @return string
	 */
	function display_ratings($total_sum, $total_votes, $link = NULL, $class = NULL, $mode = 1)
	{
		$locale = fusion_get_locale();
		$start_link = $link ? "<a class='comments-item " . $class . "' href='" . $link . "'>" : '';
		$end_link = $link ? "</a>" : '';
		$average = $total_votes > 0 ? number_format($total_sum / $total_votes, 2) : 0;
		$str = $mode == 1 ? $average . $locale['global_094'] . format_word($total_votes, $locale['fmt_rating']) : "$average/$total_votes";
		if ($total_votes > 0) {
			$answer = $start_link . "<i title='" . $locale['ratings'] . "' class='fa fa-star-o m-l-0'></i>" . $str . $end_link;
		} else {
			$answer = $start_link . "<i title='" . sprintf($locale['global_089a'], $locale['global_077']) . "' class='fa fa-star-o high-opacity m-l-0'></i> " . $str . $end_link;
		}

		return $answer;
	}
}

if (!function_exists('display_comments')) {
	/**
	 * Display comments.
	 *
	 * @param int $total_sum Total number of comments.
	 * @param string $link Make item clickable.
	 * @param string $class CSS class for the link.
	 * @param int $mode Show 2 out of 10 or 2/10 comments. Possible value: 1, 2.
	 *
	 * @return string
	 */
	function display_comments($total_sum, $link = NULL, $class = NULL, $mode = 1)
	{
		$locale = fusion_get_locale();
		$start_link = $link ? "<a class='comments-item " . $class . "' href='" . $link . "' {%title%} >" : '';
		$end_link = $link ? "</a>" : '';
		$str = $mode == 1 ? format_word($total_sum, $locale['fmt_comment']) : $total_sum;
		if ($total_sum > 0) {
			$start_link = strtr($start_link, ['{%title%}' => "title='" . $locale['global_073'] . "'"]);
		} else {
			$start_link = strtr($start_link, ['{%title%}' => "title='" . sprintf($locale['global_089'], $locale['global_077']) . "'"]);
		}

		return $start_link . $str . $end_link;
	}
}

if (!function_exists('fusion_confirm_exit')) {
	/**
	 * JS form exit confirmation if form has changed.
	 */
	function fusion_confirm_exit()
	{
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
	 * Return a list of social media sharing services where an url can be shared.
	 * Requires the loading of Font Awesome which can be enabled in theme settings.
	 *
	 * @param string $url The URL to share.
	 * @param array $options
	 *
	 * @return string
	 */
	function social_media_links($url, $options = [])
	{

		$default = [
			"facebook" => TRUE,
			"twitter"  => TRUE,
			"reddit"   => TRUE,
			"vk"       => TRUE,
			"whatsapp" => TRUE,
			"telegram" => TRUE,
			"linkedin" => TRUE,
			"class"    => "",
			"template" => '<a class="m-5 {%class%}" href="{%url%}" title="{%name%}" target="_blank" rel="nofollow noopener"><i class="{%icon%} fa-2x"></i></a>',
		];

		$options += $default;

		$services = [];

		if ($options['facebook'] == 1) {
			$services['facebook'] = [
				'name' => 'Facebook',
				'icon' => 'fab fa-facebook-square',
				'url'  => 'https://www.facebook.com/sharer.php?u=',
			];
		}

		if ($options['twitter'] == 1) {
			$services['twitter'] = [
				'name' => 'Twitter',
				'icon' => 'fab fa-twitter-square',
				'url'  => 'https://twitter.com/intent/tweet?url=',
			];
		}

		if ($options['reddit'] == 1) {
			$services['reddit'] = [
				'name' => 'Reddit',
				'icon' => 'fab fa-reddit-square',
				'url'  => 'https://www.reddit.com/submit?url=',
			];
		}

		if ($options['vk'] == 1) {
			$services['vk'] = [
				'name' => 'VK',
				'icon' => 'fab fa-vk',
				'url'  => 'https://vk.com/share.php?url=',
			];
		}

		if ($options['whatsapp'] == 1) {
			$services['whatsapp'] = [
				'name' => 'WhatsApp',
				'icon' => 'fab fa-whatsapp',
				'url'  => 'https://api.whatsapp.com/send?text=',
			];
		}

		if ($options['telegram'] == 1) {
			$services['telegram'] = [
				'name' => 'Telegram',
				'icon' => 'fab fa-telegram',
				'url'  => 'https://telegram.me/share/url?url=',
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
					"{%url%}"   => $service["url"] . $url,
					"{%name%}"  => $service["name"],
					"{%icon%}"  => $service["icon"],
				]);
			}
		}

		return $html;
	}
}

/**
 * Load any function and return its value.
 *
 * @param string $function Function name.
 * @params miexd  ...$args Zero or more parameters to be passed, depending on function.
 *
 * @return mixed|string
 */
function fusion_get_function($function)
{
	$function_args = func_get_args();
	if (count($function_args) > 1) {
		unset($function_args[0]);
	}
	// Attempt to check if this function prints anything
	ob_start();
	$func = call_user_func_array($function, $function_args);
	$content = ob_get_clean();
	// If it does not print return the function results
	if (empty($content)) {
		return $func;
	}

	return $content;
}

if (!function_exists('render_breadcrumbs')) {
	/**
	 * Render breadcrumbs.
	 *
	 * @param string $key Instance key.
	 *
	 * @return string
	 */
	function render_breadcrumbs($key = 'default')
	{
		$breadcrumbs = BreadCrumbs::getInstance($key);
		$crumbs = $breadcrumbs->toArray();

		if (function_exists('fusion_render_framework_component')) {
			$framework_breadcrumbs = fusion_render_framework_component('breadcrumbs', [
				'breadcrumbs' => $crumbs,
				'class' => $breadcrumbs->getCssClasses(),
				'aria_label' => 'Breadcrumb',
			]);

			if ($framework_breadcrumbs !== '') {
				return $framework_breadcrumbs;
			}
		}

		$html = '<nav aria-label="breadcrumb">';
		$html .= '<ol class="' . $breadcrumbs->getCssClasses() . '">';
		$last_key = array_key_last($crumbs);

		foreach ($crumbs as $index => $crumb) {
			$is_active = !$crumb['link'] || $index === $last_key;
			$html .= '<li class="breadcrumb-item ' . $crumb['class'] . ($is_active ? ' active" aria-current="page"' : '"') . '>';
			if ($crumb['link'] && $index !== $last_key) {
				$html .= '<a title="' . $crumb['title'] . '" href="' . $crumb['link'] . '">' . $crumb['title'] . '</a>';
			} else {
				$html .= $crumb['title'];
			}
			$html .= '</li>';
		}
		$html .= '</ol>';
		$html .= '</nav>';

		return $html;
	}
}

if (!function_exists('render_favicons')) {
	/**
	 * Show meta tags for favicons.
	 *
	 * @param string $folder The folder where the icons are.
	 *
	 * @return string
	 */
	function render_favicons($folder = IMAGES . 'favicons/')
	{
		$html = '';
		// Generator - https://realfavicongenerator.net/
		if (is_dir($folder)) {
			$html .= '<link rel="apple-touch-icon" sizes="180x180" href="' . $folder . 'apple-touch-icon.png">';
			$html .= '<link rel="icon" type="image/png" sizes="32x32" href="' . $folder . 'favicon-32x32.png">';
			$html .= '<link rel="icon" type="image/png" sizes="16x16" href="' . $folder . 'favicon-16x16.png">';
			$html .= '<link rel="manifest" href="' . $folder . 'site.webmanifest">';
			//$html .= '<link rel="mask-icon" href="' . $folder . 'safari-pinned-tab.svg" color="#262626">';
			$html .= '<meta name="msapplication-TileColor" content="#262626">';
		}

		return $html;
	}
}

if (!function_exists('render_user_tags')) {
	/**
	 * Render user tags template.
	 *
	 * @param array $data User data.
	 * @param string $tooltip The tooltip string.
	 *
	 * @return string
	 */
	function render_user_tags($data, $tooltip)
	{
		$locale = fusion_get_locale();

		if (!defined('USERPOPOVER')) {
			define('USERPOPOVER', TRUE);
			add_to_jquery("$('[data-toggle=\"user-tooltip\"]').popover();");
		}

		$avatar = !empty($data['user_avatar']) ? '<div class="pull-left m-r-10">' . display_avatar($data, '32px', '', FALSE, 'icon-sm') . '</div>' : '';
		$title = '<div class="user-tooltip">' . $avatar . '<div class="clearfix">' . profile_link($data['user_id'], $data['user_name'], $data['user_status']) . '<br><span class="user_level">' . getuserlevel($data['user_level']) . '</span></div>';
		$content = $tooltip . '<a class="btn btn-block btn-primary" href="' . BASEDIR . 'messages.php?msg_send=' . $data['user_id'] . '">' . $locale['send_message'] . '</a>';
		$html = '<a class="strong pointer" tabindex="0" role="button" data-html="true" data-trigger="focus" data-placement="top" data-toggle="user-tooltip" title=\'' . $title . '\' data-content=\'' . $content . '\'>';
		$html .= '<span class="user-label">@' . $data['user_name'] . '</span>';
		$html .= '</a>';

		return $html;
	}
}

/**
 * Get the current theme framework
 *
 * @return string
 */
function fusion_theme_framework()
{
	return 'BOOTSTRAP5';

	$level = ['BOOTSTRAP6', 'BOOTSTRAP5', 'BOOTSTRAP4', 'BOOTSTRAP'];
	foreach ($level as $framework) {
		if (defined($framework)) {
			return $framework;
		}
	}

	return 'default';
}

/**
 * @param array $options
 *
 * @return string
 */
function confirm($options = [])
{
	static $confirm_html;

	$options += [
		'method'      => 'POST',
		'confirm_url' => FORM_REQUEST,
		'capture'     => 'all',
		'url'         => '', // URL that returns the confirm HTML
		'href'        => '', // final delete action
		'data'        => [],
	];

	if (!empty($options['data'])) {
		array_walk($options['data'], function($a, $b) use (&$options_data) {
			$options_data[] = "data-$b='$a'";
		}, $options_data);
	}

	if (!function_exists('build_hidden_inputs')) {
		function build_hidden_inputs($name, $value)
		{
			$html = '';

			if (is_array($value)) {
				foreach ($value as $k => $v) {
					$newName = $name . '[' . $k . ']';
					$html .= build_hidden_inputs($newName, $v);
				}
			} else {
				$escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
				$escapedName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
				$html .= "<input type='hidden' name='{$escapedName}' value='{$escapedValue}'>";
			}

			return $html;
		}
	}

	if (empty($confirm_html)) {
		// Start modal
		// This will only render once per page no matter how many links attaches it.
		$confirm_html = openmodal('fusionConfirm', 'Please confirm this action', ['size' => 2, 'hidden' => TRUE]);
		$confirm_html .= openform('confirm', $options['method'], $options['confirm_url']);
		$confirm_html .= "<div id='fusionConfirmMessage'></div>";

		$hiddenFields = '';
		if ($options['capture'] === 'all') {
			foreach ($_REQUEST as $k => $v) {
				$hiddenFields .= build_hidden_inputs($k, $v);
			}
		} else if (is_array($options['capture'])) {
			foreach ($options['capture'] as $k) {
				if (isset($_REQUEST[$k])) {
					$hiddenFields .= build_hidden_inputs($k, $_REQUEST[$k]);
				}
			}
		}

		$confirm_html .= form_checkbox('confirm_check', 'I understand the consequences of this action', '', [
			//'reverse_label' => TRUE,
			'class' => 'm-0',
		]);

		$confirm_html .= $hiddenFields;
		// Modal footer
		$footer_html = form_hidden('_confirm', '', 1, ['input_id' => '_confirm']);
		$footer_html
			.= form_button('proceed', 'Proceed', 'proceed', [
			'svg'        => 'check',
			'input_id'   => 'fusionConfirmOk',
			'class'      => 'btn-danger btn-remove me-2',
			'deactivate' => TRUE,
		]);
		$footer_html .= form_button('cancel', 'Cancel', 'cancel', ['data' => ['bs-dismiss' => 'modal'], 'type' => 'button']);

		$confirm_html .= modalfooter($footer_html);
		// Close modal
		$confirm_html .= closeform() . closemodal();
		add_to_footer($confirm_html);

	}

	add_to_jquery("
       
       $(document).on('click', '[data-toggle=\"confirm-delete\"]', function(e) {
                e.preventDefault();
            
                let btn   = $(this);
                let fetchUrl = btn.data('url');   // URL that returns the confirm HTML
                let deleteUrl = btn.attr('href'); // final delete action
                let id = btn.data('id');
                            
                // Load confirm message via AJAX
                $.get(fetchUrl, {action: 'confirm_delete', id: id }, function(response) {
                    $('#fusionConfirmMessage').html(response);
            
                    // disable proceed until checkbox ticked (if included in response)
                    $('#fusionConfirmOk').prop('disabled', true);
            
                    // bind proceed button
                    
                    $('#fusionConfirmOk').off('click').on('click', function(e) {
                        e.preventDefault();
                        $('#fusionConfirm_Modal').modal('hide');
            
                        // Continue with original action
                        if (btn.is('a[href]')) {
                            alert('href');
                            // Let the browser follow the link
                            window.location.href = deleteUrl;
                            
                        } else if (btn.is('button') || btn.is('input[type=submit]')) {
                            // Find and submit the parent form
                            btn.closest('form').trigger('submit');
                        }
                    });
            
                    // open modal
                    $('#fusionConfirm_Modal').modal('show');
                });
            });
            
            // enable button when confirm checkbox is checked
            $(document).on('change', '#confirm_check', function() {
            if ($(this).is(':checked')) {
                $('#fusionConfirmOk')
                        .prop('disabled', false)
                        .removeClass('disabled');
                } else {
                    $('#fusionConfirmOk')
                        .prop('disabled', true)
                        .addClass('disabled');
                }
            });
              
        ");

	return "href='{$options['href']}' data-toggle='confirm-delete' data-url='{$options['url']}' " . (!empty($options_data) ? implode(' ', $options_data) : '');

}

/**
 * Universal jquery-confirm generator.
 * Attaches a confirm dialog to any element and redirects on confirm.
 *
 * Usage:
 *   <a href="delete.php?id=1" class="rm-subject" data-msg="Remove Subject A?">Delete</a>
 *   fusionconfirm('.rm-subject');
 *
 * @param string $selector jQuery selector, e.g. '.btnDelete', '#approveLink'
 * @param string|null $message Optional default message
 * @param string|null $redirectUrl Optional default redirect URL (href will override)
 * @param array $options Override jquery-confirm options
 */
function fusionconfirm(
	string $selector,
	string $message = NULL,
	string $redirectUrl = NULL,
	array $options = []
) {
	// Default config
	$defaults = [
		'title'              => 'Please confirm this action',
		'confirm_text'       => 'Yes, Proceed',
		'cancel_text'        => 'Cancel',
		'theme'              => 'modern',
		'type'               => 'red',
		'boxWidth'           => '400px',
		'useBootstrap'       => FALSE,
		'animation'          => 'scale',
		'closeAnimation'     => 'scale',
		'animateFromElement' => FALSE,
		'backgroundDismiss'  => FALSE,
		'draggable'          => TRUE,
		'closeIcon'          => TRUE,
		'escapeKey'          => TRUE,
		'dialogClass'        => '',
		'confirm_btn_class'  => 'btn-red',
		'cancel_btn_class'   => 'btn-default',
		'form_name'          => '', // Added for form support
	];

	$cfg = $options + $defaults;

	// Standard Escaping
	$title = htmlspecialchars($cfg['title'], ENT_QUOTES);
	$defaultMsg = htmlspecialchars($message ?: '', ENT_QUOTES);
	$confirmText = htmlspecialchars($cfg['confirm_text'], ENT_QUOTES);
	$cancelText = htmlspecialchars($cfg['cancel_text'], ENT_QUOTES);
	$dialogClass = htmlspecialchars($cfg['dialogClass'], ENT_QUOTES);
	$confirmClass = htmlspecialchars($cfg['confirm_btn_class'], ENT_QUOTES);
	$cancelClass = htmlspecialchars($cfg['cancel_btn_class'], ENT_QUOTES);
	$redirectJson = json_encode($redirectUrl);
	$formName = htmlspecialchars($cfg['form_name'], ENT_QUOTES);

	fusion_load_script(INCLUDES . 'jquery/jquery-confirm/jquery-confirm.min.css', 'css');
	fusion_load_script(INCLUDES . 'jquery/jquery-confirm/jquery-confirm.min.js');

	add_to_jquery("
    $(document).off('click', '{$selector}');
    $(document).on('click', '{$selector}', function(e) {
        e.preventDefault();
        var \$el = $(this);
        var msg = \$el.data('msg') || '{$defaultMsg}';
        var url = \$el.data('url') || \$el.attr('href') || {$redirectJson};
        var formName = '{$formName}';

        $.confirm({
            title: '{$title}',
            content: msg,
            theme: '{$cfg['theme']}',
            type: '{$cfg['type']}',
            boxWidth: '{$cfg['boxWidth']}',
            useBootstrap: " . ($cfg['useBootstrap'] ? 'true' : 'false') . ",
            animation: '{$cfg['animation']}',
            closeAnimation: '{$cfg['closeAnimation']}',
            backgroundDismiss: " . ($cfg['backgroundDismiss'] ? 'true' : 'false') . ",
            buttons: {
                confirm: {
                    text: '{$confirmText}',
                    btnClass: '{$confirmClass}',
                    action: function() {
                        // 1. Identify the target form: either by config 'formName' or closest parent
                        var formSelector = formName !== '' ? 'form[name=\"' + formName + '\"], #' + formName : null;
                        var \$form = formSelector ? \$(formSelector).first() : \$el.closest('form');
                    
                        if (\$form.length) {
                            // 2. Inject the button's name and value so PHP receives \$_POST['rm_user']
                            var btnName = \$el.attr('name');
                            var btnVal  = \$el.attr('value');
                    
                            if (btnName) {
                                // Remove any previously injected temp inputs to keep DOM clean
                                \$form.find('input[data-fusion-temp=\"true\"]').remove();
                                
                                // Append the hidden input to the form
                                \$('<input>').attr({
                                    type: 'hidden',
                                    name: btnName,
                                    value: btnVal,
                                    'data-fusion-temp': 'true'
                                }).appendTo(\$form);
                            }
                    
                            // 3. Submit the form
                            \$form.submit();
                        } else if (url && url !== '#' && !\$el.is(':submit')) {
                            // 4. Fallback: If no form exists, treat as a standard redirect
                            window.location.href = url;
                        }
                    }
                },
                cancel: {
                    text: '{$cancelText}',
                    btnClass: '{$cancelClass}'
                }
            }
        });
    });
    ");

}

/**
 * @param $value
 * @param $color_index
 *
 * @return string
 */
function display_badge($value, $color_index)
{
    $value = (string)$value;
    // Cast to string to ensure it matches your case definitions
    $color_index = (string)$color_index;

	$color = match ($color_index) {
		'0' => 'bg-secondary-subtle text-secondary-emphasis',
		'1' => 'bg-success-subtle text-success-emphasis',
		'2' => 'bg-danger-subtle text-danger-emphasis',
		'3' => 'bg-warning-subtle text-warning-emphasis',
		'4' => 'bg-info-subtle text-info-emphasis',
        default => 'bg-secondary-subtle text-secondary-emphasis' // Safety net
	};

	return badge($value, ['class' => $color]);
}
