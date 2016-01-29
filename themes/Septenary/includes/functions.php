<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2014 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Name: Septenary Theme
| Filename: functions.php
| Version: 1.00
| Author: PHP-Fusion Mods UK
| Developer & Designer: Craig, Falcon
| Site: http://www.phpfusionmods.co.uk
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

define("THEME_BULLET", "<img src='".THEME."images/bullet.png' class='bullet'  alt='&raquo;' />");

function theme_output($output) {
	$search = array("@><img src='reply' alt='(.*?)' style='border:0px' />@si",
					"@><img src='newthread' alt='(.*?)' style='border:0px;?' />@si",
					"@><img src='web' alt='(.*?)' style='border:0;vertical-align:middle' />@si",
					"@><img src='pm' alt='(.*?)' style='border:0;vertical-align:middle' />@si",
					"@><img src='quote' alt='(.*?)' style='border:0px;vertical-align:middle' />@si",
					"@><img src='forum_edit' alt='(.*?)' style='border:0px;vertical-align:middle' />@si",
					"@<a href='".ADMIN."comments.php(.*?)&amp;ctype=(.*?)&amp;cid=(.*?)'>(.*?)</a>@si");
	$replace = array(' class="big button"><span class="reply-button icon"></span>$1',
					 ' class="big button"><span class="newthread-button icon"></span>$1',
					 ' class="button" rel="nofollow" title="$1"><span class="web-button icon"></span>Web',
					 ' class="button" title="$1"><span class="pm-button icon"></span>PM',
					 ' class="button" title="$1"><span class="quote-button icon"></span>$1',
					 ' class="negative button" title="$1"><span class="edit-button icon"></span>$1',
					 '<a href="'.ADMIN.'comments.php$1&amp;ctype=$2&amp;cid=$3" class="big button"><span class="settings-button icon"></span>$4</a>');
	$output = preg_replace($search, $replace, $output);
	return $output;
}

