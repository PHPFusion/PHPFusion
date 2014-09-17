<?php

/* Updated 2014/July - https://github.com/sciactive/pnotify */
require_once INCLUDES."output_handling_include.php";
if (!defined('notification_ui')) {
	add_to_footer("<script type='text/javascript' src='".INCLUDES."notify/pnotify.js'></script>\n");
	add_to_head("<link href='".INCLUDES."notify/pnotify.custom.css' media='all' rel='stylesheet' type='text/css' />\n");
	define('notification_ui', TRUE);
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

/*
 *     var effect_in = $('#ui_effect_in').val(),
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