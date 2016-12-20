<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: notify.inc.php
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

/* Updated 2014/July - https://github.com/sciactive/pnotify */
require_once __DIR__.'/../output_handling_include.php';
/**
 * Pop up notification
 * @param       $title
 * @param       $text
 * @param array $options
 */

function notify($title, $text, array $options = array()) {
    // init library
    $default_options = array(
        "sticky" => TRUE,
        "animation" => 1,
        "icon" => "notify_icon n-attention"
    );

    $options += $default_options;

    $sticky = ($options['sticky'] == TRUE) ? "hide:false," : "";

    switch ($options['animation']) {
        case 1:
            $animation = "animation: 'show',";
            break;
        case 2:
            $animation = "animation: 'fade',";
            break;
        case 3:
            $animation = "animation: 'slide',";
            break;
        default:
            $animation = "";
    }

    add_to_jquery("
		$(function(){
			new PNotify({
				title: '$title',
				text: '$text',
				icon: '".$options['icon']."',
				$animation
				width: 'auto',
				$sticky
				delay: '4500'
			});
		});
	");
}

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

        if ($status == "success") {
            // Success messages can be auto closed
            $messages .= "<div id='close-message'>\n";
        }
        $messages .= "<div class='admin-message alert alert-".$status." alert-dismissible' role='alert'>";
        $messages .= "<button type='button' class='close' data-dismiss='alert'><span aria-hidden='true'>&times;</span></button>";
        foreach ($notice as $id => $message) {
            $messages .= $message."<br />";
        }
        $messages .= "</div>\n";
        if ($status == "success") {
            $messages .= "</div>\n";
        }
    }

    return (string)$messages;
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
            (isset($_SESSION['notices']['persist'][$key]) && !empty($_SESSION['notices']['persist'][$key]))
        ) {
            return TRUE;
        }
    }

    return FALSE;
}

/**
 * Retrievs all notices
 * Retrievs all notices for the group identified by the key provided
 *
 * @param string|array $key the key(s) identifying a group or more holding notices, by default the page name in which the notice was set
 * @param boolean      $delete whether to delete or keep a notice message after it was accessed. This only works if the notice
 * was set or added while having $removeAfterAccess set to FALSE
 * @return array the notices for the group identified by the provided key
 */
function getNotices($key = FUSION_SELF, $delete = TRUE) {
    $key = is_array($key) ? $key : array($key); // key can be arrays or a string
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

    return (array)$notices;
}


function remove_notice($key = array('all', FUSION_SELF, FUSION_REQUEST)) {
    $key = is_array($key) ? $key : array($key); // key can be arrays or a string
    if (!empty($_SESSION['notices'])) {
        foreach ($_SESSION['notices'] as $type => $keys) {
            foreach ($key as $thiskey) {
                if (isset($keys[$thiskey]) && !empty($_SESSION['notices'][$type][$thiskey])) {
                    $_SESSION['notices'][$type][$thiskey] = array();
                }
            }
        }
    }
}

/**
 * Adds a notice message
 * Adds a notice message to the group identified by the key provided
 *
 * @param string  $status the status of the message
 * @param string  $value the message
 * @param string  $key the key identifying a group holding notices, by default the page name in which the notice was set
 * @param boolean $removeAfterAccess whether the notice should be automatically removed after it was displayed once,
 * if set to FALSE when getNotices() is called you have the option to keep the notice even after it was accesed
 */
function addNotice($status, $value, $key = FUSION_SELF, $removeAfterAccess = TRUE) {
    $type = $removeAfterAccess ? 'once' : 'persist';
    if (is_array($value)) {
        $return = "<ol style='list-style: decimal'>\n";
        foreach ($value as $text) {
            $return .= "<li>".$text."</li>";
        }
        $return .= "</ol>\n";
        $value = $return;
    }

    if (!isset($_SESSION['notices'][$type][$key][$status])) {
        $_SESSION['notices'][$type][$key][$status] = array();
    }
    if (array_search($value, $_SESSION['notices'][$type][$key][$status]) === FALSE) {
        $_SESSION['notices'][$type][$key][$status][] = $value;
        //die(print_p($_SESSION['notices']));
    }
}

/**
 * Sets a notice message
 * Sets a notice message for the whole group identified by the key provided, this will overwrite any other notices previously set
 *
 * @param string  $status the status of the message
 * @param string  $value the message
 * @param string  $key the key identifying a group holding notices, by default the page name in which the notice was set
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