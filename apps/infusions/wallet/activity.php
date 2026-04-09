<?php
use PHPFusion\Infusions\Wallet\User_Fields\User_Transaction_View;

require_once __DIR__."/../../maincore.php";

require_once THEMES."templates/header.php";

fusion_load_script(INFUSIONS."wallet/templates/css/wallet.css", "css");

fusion_load_script(INFUSIONS."wallet/wallet.js");

$transaction_view = new User_Transaction_View();

$transaction_view->transaction_ref = get("id");

$transaction_view->token = get("token");

echo $transaction_view->view();

require_once THEMES."templates/footer.php";
