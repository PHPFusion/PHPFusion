<?php


//if (!defined('IN_FUSION')) {
//    header("error.php?code=401");
//}

// works with edit_profile.php only
use PHPFusion\Infusions\Wallet\Classes\Wallet_View;
require_once __DIR__."/../../maincore.php";
require_once THEMES."templates/header.php";

$wallet = Wallet_View::getInstance();
$user_fields = $wallet->View();
$user_fields_section = $wallet->getPages();
$user_fields_nav = $wallet->getPageNav();
//
echo $user_fields;
//
//print_p($user_fields_section);
//print_p($user_fields_nav);

require_once THEMES."templates/footer.php";
