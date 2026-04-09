<?php

use PHPFusion\Infusions\Wallet\Classes\Wallet_Payment;

require_once __DIR__.'/../../maincore.php';
require_once THEMES.'templates/header.php';
new Wallet_Payment();
require_once THEMES.'templates/footer.php';
