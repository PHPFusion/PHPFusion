<?php
define('BASEDIR', '../');
require_once __DIR__.'/../includes/autoloader.php';
// Start the installer
PHPFusion\Installer\Install_Core::getInstance()->install_phpfusion();