<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: acp_theme.php
| Author: Frederick Chan (deviance)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once THEMES."admin_themes/Genesis/autoloader.php";

define('BOOTSTRAP', TRUE);
define('FONTAWESOME', TRUE);

\PHPFusion\Admins::getInstance()->setAdminBreadcrumbs();

function opentable($title, $class = FALSE) {
    \Genesis\Viewer\adminPanel::opentable($title, $class);
}

function closetable($title = FALSE, $class = FALSE) {
    \Genesis\Viewer\adminPanel::closetable($title, $class);
}

function openside($title = FALSE, $class = FALSE) {
    \Genesis\Viewer\adminPanel::openside($title, $class);
}

function closeside($title = FALSE, $class = FALSE) {
    \Genesis\Viewer\adminPanel::closeside($title, $class);
}

function render_admin_login() {
    \Genesis\Controller::Instance(FALSE)->do_login_panel();
}

function render_admin_panel() {
    \Genesis\Controller::Instance(FALSE)->do_admin_panel();
}

function render_admin_dashboard() {
    \Genesis\Controller::Instance(FALSE)->do_admin_dashboard();
}
