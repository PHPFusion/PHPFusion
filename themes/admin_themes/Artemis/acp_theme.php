<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: acp_theme.php
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
require_once THEMES."admin_themes/Artemis/autoloader.php";

define('BOOTSTRAP', TRUE);
define('FONTAWESOME', TRUE);

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
