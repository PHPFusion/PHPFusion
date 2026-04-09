<?php
/**
 * Standard Verification API
 * Include transaction file for delivery and marking will be automatically included by the system
 */

use PHPFusion\Infusions\Wallet\Classes\Wallet;
use ThemeFactory\Core;
use ThemePack\Fusion\MainFrame;

define("FUSION_ALLOW_REMOTE", true);

if (!defined("STOP_REDIRECT")) {
    define("STOP_REDIRECT", true);
}

require_once __DIR__."/../../maincore.php";
require_once THEMES."templates/header.php";

echo Wallet::getInstance()->displayConfirmation(TRUE);

require_once THEMES."templates/footer.php";
