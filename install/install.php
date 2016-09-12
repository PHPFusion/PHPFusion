<?php
define('BASEDIR', '../');
require_once __DIR__.'/../includes/autoloader.php';
$fusion_page_head_tags = &\PHPFusion\OutputHandler::$pageHeadTags;
$fusion_page_footer_tags = &\PHPFusion\OutputHandler::$pageFooterTags;
$fusion_jquery_tags = &\PHPFusion\OutputHandler::$jqueryTags;
// Start the installer
PHPFusion\Installer\Install_Core::getInstance()->install_phpfusion();