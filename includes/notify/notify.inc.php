<?php
/* Updated 2014/July - https://github.com/sciactive/pnotify */
require_once __DIR__.'/../output_handling_include.php';

if (!defined('NOTIFICATION_UI')) {
	define('NOTIFICATION_UI', TRUE);
	add_to_head("<link href='".INCLUDES."notify/pnotify.custom.css' media='all' rel='stylesheet' type='text/css' />\n");
	add_to_footer("<script type='text/javascript' src='".INCLUDES."notify/pnotify.js'></script>\n");
}

function notify($title, $text, $opts = FALSE) {
	// init library
	if (!is_array($opts)) {
		$sticky = "";
		$anime = "";
		$icon = "notify_icon n-attention";
	} else {
		$sticky = (array_key_exists("sticky", $opts)) ? "hide:false," : "";
		$icon = (array_key_exists("icon", $opts)) ? $opts['icon'] : "notify_icon n-attention";
		$animation = (array_key_exists("animate", $opts)) ? $opts['animate'] : "";
		if ($animation == "1") {
			$anime = "animation: 'show',";
		} elseif ($animation == "2") {
			$anime = "animation: 'fade',";
		} elseif ($animation == "3") {
			$anime = "animation: 'slide',";
		} else {
			// reset
			$anime = "";
		}
	}
	add_to_jquery("
		$(function(){
			new PNotify({
				title: '$title',
				text: '$text',
				icon: '$icon',
				$anime
				width: 'auto',
				$sticky
				delay: '3000'
			});
		});
		");
}

/********************************************
 * Code below is under development, feel free
 * to improve/change if necessary
 *
 * TODO: function to remove a notice
 ********************************************/

/**
 * Renders notices
 * Formats and renders notices
 *
 * @param array $notices the array contaning notices
 * @return string the notices formatted as HTML
 */
function renderNotices($notices) {
	$messages = "";
		foreach ($notices as $status => $notice) {
            // Success messages can be auto closed
			if ($status == "success") {$messages .= "<div id='close-message'>\n";}
			$messages .= "<div class='admin-message alert alert-".$status." m-t-10'>";
			foreach ($notice as $id => $message) {
				$messages .= $message."<br />";
			}
			$messages .= "</div>\n";
			if ($status == "success") {$messages .= "</div>\n";}
		}

	return $messages;
}

/**
 * Check for notices
 * Checks whether a group identified by the key provided has any notices
 *
 * @param string $key the key(s) identifying a group or more holding notices, by default the page name in which the notice was set
 * @return bool TRUE if the group has any notices, FALSE otherwise
 */
function hasNotice($key = FUSION_SELF) {
	if (!empty($_SESSION['notices'])) {
		if ((isset($_SESSION['notices']['once'][$key]) && !empty($_SESSION['notices']['once'][$key])) ||
			(isset($_SESSION['notices']['persist'][$key]) &&  !empty($_SESSION['notices']['persist'][$key]))) { return TRUE; } 
	}

	return FALSE;
}

/**
 * Retrievs all notices
 * Retrievs all notices for the group identified by the key provided
 *
 * @param string|array $key the key(s) identifying a group or more holding notices, by default the page name in which the notice was set
 * @param boolean $delete whether to delete or keep a notice message after it was accessed. This only works if the notice
 * was set or added while having $removeAfterAccess set to FALSE
 * @return array the notices for the group identified by the provided key
 */
function getNotices($key = FUSION_SELF, $delete = TRUE) {
	$key = is_array($key) ? $key : array($key);
	$notices = array();
	if (!empty($_SESSION['notices'])) {
		foreach ($_SESSION['notices'] as $type => $keys) {
			foreach ($key as $thiskey) {
				if (isset($keys[$thiskey])) {
					$notices = array_merge_recursive($notices, $keys[$thiskey]);
					if ($delete) $_SESSION['notices'][$type][$thiskey] = array();
				}
			}
		}
	}

	unset($_SESSION['notices']['once']);

	return $notices;
}

/**
 * Adds a notice message
 * Adds a notice message to the group identified by the key provided
 *
 * @param string $status the status of the message
 * @param string $value the message
 * @param string $key the key identifying a group holding notices, by default the page name in which the notice was set
 * @param boolean $removeAfterAccess whether the notice should be automatically removed after it was displayed once,
 * if set to FALSE when getNotices() is called you have the option to keep the notice even after it was accesed
 */
function addNotice($status, $value, $key = FUSION_SELF, $removeAfterAccess = TRUE) {
	$type = $removeAfterAccess ? 'once' : 'persist';
	if (isset($_SESSION['notices'][$type][$key][$status])) {
		array_push($_SESSION['notices'][$type][$key][$status], $value);
	} else {
		$_SESSION['notices'][$type][$key][$status] = array($value);
	}
}

/**
 * Sets a notice message
 * Sets a notice message for the whole group identified by the key provided, this will overwrite any other notices previously set
 *
 * @param string $status the status of the message
 * @param string $value the message
 * @param string $key the key identifying a group holding notices, by default the page name in which the notice was set
 * @param boolean $removeAfterAccess whether the notice should be automatically removed after it was displayed once.
 * If set to FALSE when getNotices() is called you have the option to keep the notice even after it was accesed.
 */
function setNotice($status, $value, $key = FUSION_SELF, $removeAfterAccess = TRUE) {
	$type = $removeAfterAccess ? 'once' : 'persist';
	$_SESSION['notices'][$type][$key] = array($status => array($value));
}

/*
 *	 var effect_in = $('#ui_effect_in').val(),
	easing_in = $('#ui_easing_in').val(),
	effect_out = $('#ui_effect_out').val(),
	easing_out = $('#ui_easing_out').val(),
	speed = $('#ui_speed').val();
	if (effect_out == 'same') effect_out = effect_in;
	if (easing_out == 'same') easing_out = easing_in;
	if (speed.match(/^\d+$/)) speed = parseInt(speed);
	var options_in = {
	easing: easing_in
	},
	options_out = {
	easing: easing_out
	};
	if (effect_in == 'scale') options_in.percent = 100;
	if (effect_out == 'scale') options_out.percent = 0;
	$.pnotify({
	title: 'jQuery UI Effect',
	text: 'I use an effect from jQuery UI.',
	animate_speed: speed,
	animation: {
	'effect_in': effect_in,
	'options_in': options_in,
	'effect_out': effect_out,
	'options_out': options_out
	}
	});
 */
?>