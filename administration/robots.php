<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: robots.php
| Author: MarcusG
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../maincore.php";
pageAccess('ROB');
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/robots.php";

add_breadcrumb(array('link'=>ADMIN.'administrators.php'.$aidlink, 'title'=>$locale['400']));

function openFile($file, $mode, $input = "") {
	if ($mode == "READ") {
		if (file_exists($file)) {
			$handle = fopen($file, "rb");
			$output = fread($handle, filesize($file));
			return $output; // output file text
		} else {
			return FALSE; // failed.
		}
	} elseif ($mode == "WRITE") {
		$handle = fopen($file, "wb");
		if (!fwrite($handle, $input)) {
			return FALSE; // failed.
		} else {
			return TRUE; //success.
		}
	} else {
		return FALSE; // failed.
	}
	fclose($handle);
}

if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "su") {
		echo "<div id='close-message'>\n<div class='admin-message alert alert-info m-t-10'>".$locale['412']."</div>\n</div>\n";
	}
}
if (isset($_POST['save_robots'])) {
	$error = 0;
	$file = BASEDIR."robots.txt";
	$robots_content = form_sanitizer($_POST['robots_content'], '', 'robots_content');
	if (!is_writable($file)) {
		$defender->stop();
		$defender->addNotice($locale['414']);
	}
	if ($error == 0 && !defined('FUSION_NULL')) {
		if (openFile($file, "WRITE", stripslash($robots_content))) {
			redirect(FUSION_SELF.$aidlink."&amp;status=su");
		} else {
			$defender->stop();
			$defender->addNotice($locale['415']);
		}
	}
}
if (isset($_POST['set_default'])) {
	$error = 0;
	$file = BASEDIR."robots.txt";
	$robots_content = "User-agent: *\n";
	$robots_content .= "Disallow: /administration/\n";
	$robots_content .= "Disallow: /locale/\n";
	$robots_content .= "Disallow: /themes/\n";
	$robots_content .= "Disallow: /print.php\n";
	if (!is_writable($file)) {
		$defender->stop();
		$defender->addNotice($locale['414']);
	}
	if ($error == 0 && !defined('FUSION_NULL')) {
		if (openFile($file, "WRITE", $robots_content)) {
			redirect(FUSION_SELF.$aidlink."&amp;status=su");
		} else {
			$defender->stop();
			$defender->addNotice($locale['415']);
		}
	}
}
opentable($locale['400']);
$file = BASEDIR."robots.txt";
if (!file_exists($file)) {
	$defender->stop();
	$defender->addNotice($locale['411']);
}
echo openform('robotsform', 'post', FUSION_SELF.$aidlink, array('max_tokens' => 1));
echo "<table class='table table-responsive tbl-border center'>\n<tbody>\n";
echo "<tr>\n";
echo "<td class='tbl2' style='text-align:center;font-weight:bold;'>".$locale['420']."</td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1' style='text-align:center;'><a href='http://www.robotstxt.org/' target='_blank'>".$locale['421']."</a></td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1' style='text-align:center;'>\n";
echo form_textarea('robots_content', '', openFile($file, 'READ'));
echo "</td>\n";
echo "</tr>\n<tr>\n";
echo "<td class='tbl1' style='text-align:center;'>";
echo form_button('save_robots', $locale['422'], $locale['422'], array('class' => 'btn-primary m-r-10'));
echo form_button('set_default', $locale['423'], $locale['423'], array('class' => 'btn-primary'));
add_to_jquery("
    $('#set_default').bind('click', function() { confirm('".$locale['410']."'); });
    ");
echo "</td>\n";
echo "</tr>\n";
echo "</tbody>\n</table>\n";
echo closeform();
closetable();
require_once THEMES."templates/footer.php";
