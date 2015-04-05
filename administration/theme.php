<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: theme.php
| Author: Frederick MC Chan (Hien)
| Co-Author: PHP-Fusion Development Team
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
require_once THEMES."templates/admin_header.php";
require_once LOCALE.LOCALESET."admin/theme.php";

opentable($locale['theme_1000']);
$theme_admin = new \PHPFusion\Atom\Admin();
if ($theme_admin::get_edit_status()) {
	$tab_title['title'][] = $locale['theme_1009'];
	$tab_title['id'][] = 'edt';
	$tab_title['icon'][] = '';
	$active_tab = tab_active($tab_title, 0);
} else {
	$tab_title['title'][] = $locale['theme_1010'];
	$tab_title['id'][] = 'its';
	$tab_title['icon'][] = '';
	$tab_title['title'][] = $locale['theme_1011'];
	$tab_title['id'][] = 'upt';
	$tab_title['icon'][] = '';
	$active_set = isset($_POST['upload']) ? 1 : 0;
	$active_tab = tab_active($tab_title, $active_set);
}
echo opentab($tab_title, $active_tab, 'theme_tab');
if ($theme_admin::get_edit_status()) {
	echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $active_tab);
	echo "<div class='m-t-20'>\n";
	$theme_admin::theme_editor();
	echo "</div>\n";
	echo closetabbody();
} else {
	echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $active_tab);
	echo "<div class='m-t-20'>\n";
	$theme_admin::list_theme();
	echo "</div>\n";
	echo closetabbody();
	echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $active_tab);
	echo "<div class='m-t-20'>\n";
	$theme_admin::theme_uploader();
	echo "</div>\n";
	echo closetabbody();
}
echo closetab();
closetable();

require_once THEMES."templates/footer.php";
?>