<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: install.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
const UI_FRAMEWORK = 'bootstrap';
const UI_FRAMEWORK_VERSION = '5';
const UI_FRAMEWORK_VARIANT = 'tabler';
const BASEDIR = '';
const COOKIE_PATH = '/';
const IN_FUSION = TRUE;

require_once __DIR__.'/includes/autoloader.php';
require_once INCLUDES.'frameworks/framework_engine.php';
fusion_framework_boot('site');
// Start the installer
PHPFusion\Installer\InstallCore::getInstallInstance()->installPhpfusion();
