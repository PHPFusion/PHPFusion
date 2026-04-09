<?php
/**
 * Wallet Administration
 */

use PHPFusion\Infusions\Wallet\Classes\Admin\Wallet_Admin;

require_once "../../../maincore.php";

require_once THEMES."templates/admin_header.php";

$WalletAdmin = new Wallet_Admin();

$WalletAdmin->__view();

require_once THEMES."templates/footer.php";
