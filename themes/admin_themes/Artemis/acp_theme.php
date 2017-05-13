<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Artemis Interface
| The Artemis Project - 2014 - 2016 (c)
| Network Data Model Development
| Filename: Artemis_ACP/acp_request.php
| Author: Guidlsquare , enVision Sdn Bhd
| Copyright patent 0517721 IPO
| Author's all rights reserved.
+--------------------------------------------------------+
| Released under PHP-Fusion EPAL
+--------------------------------------------------------*/
require_once INCLUDES."theme_functions_include.php";
require_once THEMES."admin_themes/Artemis/autoloader.php";
define('BOOTSTRAP', TRUE);
define('FONTAWESOME', TRUE);
\PHPFusion\Admins::getInstance()->setAdminBreadcrumbs();
function opentable($title, $class = FALSE) {
    \Artemis\Viewer\adminPanel::opentable($title, $class);
}

function closetable($title = FALSE, $class = FALSE) {
    \Artemis\Viewer\adminPanel::closetable($title, $class);
}

function openside($title = FALSE, $class = FALSE) {
    \Artemis\Viewer\adminPanel::openside($title, $class);
}

function closeside($title = FALSE, $class = FALSE) {
    \Artemis\Viewer\adminPanel::closeside($title, $class);
}

function render_admin_login() {
    \Artemis\Controller::Instance(FALSE)->do_login_panel();
}

function render_admin_panel() {
    \Artemis\Controller::Instance(FALSE)->do_admin_panel();
}

function render_admin_dashboard() {
    \Artemis\Controller::Instance(FALSE)->do_admin_dashboard();
}