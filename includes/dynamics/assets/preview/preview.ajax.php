<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: preview.ajax.php
| Author: Frederick MC CHan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

require_once "../../../../maincore.php";
if (isset($_POST['fusion_token']) && $defender->verify_tokens($_POST['id'],0)) {
	echo "<div class='editor-preview-wrapper m-t-10'>\n";
	if ($_POST['editor'] == 'html_input') {
		$text = stripslash(nl2br(parsesmileys($_POST['text'])));
		echo $text;
	} elseif ($_POST['editor'] == 'bbcode') {
		$text = parseubb(parsesmileys($_POST['text']));
		echo $text;
	}
	echo "</div>\n";
}
?>