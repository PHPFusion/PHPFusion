<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: img_bbcode_include.php
| Author: Wooya
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

if (!function_exists("img_bbcode_callback")) {
	function img_bbcode_callback($matches) 
	{
		if (substr($matches[3], -1, 1) != "/") 
		{
			$avdata = @getimagesize("$matches[1]$matches[3]$matches[4]");
			if (isset($avdata["mime"]))
			{
				return "<span style='display: block; max-width: 300px; max-height: 300px; overflow: auto;' class='forum-img-wrapper'><img src='".$matches[1].str_replace(array("?","&amp;","&","="), "", $matches[3]).$matches[4]."' alt='".$matches[3].$matches[4]."' style='border:0px' class='forum-img' /></span>";
			}
			else
			{
				return "[img]$matches[1]$matches[3]$matches[4][/img] is not a valid Image.";
			}
		}
		else 
		{
			return "[img]$matches[1]$matches[3]$matches[4][/img] is not a valid Image.";
		}
		// End of BBCode IMG tag patch by Euforia33
	}

}
	
$text = preg_replace_callback("#\[img\]((http|ftp|https|ftps)://)(.*?)(\.(jpg|jpeg|gif|png|JPG|JPEG|GIF|PNG))\[/img\]#si", "img_bbcode_callback", $text);
?>