<?php

use PHPFusion\Infusions\Wallet\Classes\Wallet_Model;
use PHPFusion\OutputHandler;

require_once __DIR__.'/../../../../maincore.php';
require_once INCLUDES.'ajax_include.php';
$fusion_head_tags = &OutputHandler::$pageHeadTags;
$html = $fusion_head_tags;
// we need the footer code.
$html .= "<div class='well text-center'><strong>Oops! Your order session has expired.</strong><br/>Please refresh the page and try again.</div>";
$html = 'Token Error';
if (fusion_safe()) {

    $_package_id = post('package_id', FILTER_VALIDATE_INT);
    $_response = post('response', FILTER_VALIDATE_INT);

    $html = 'user aborted payment';
    // User aborted payment and return to this page
    if ($_package_id && $_response == 200) {

        $result = dbquery("SELECT * FROM ".DB_COIN_PACKS." WHERE package_id=:id AND package_status=:ons AND (package_promotion_start=0||package_promotion_start<=:time00) AND (package_promotion_end=0||package_promotion_end>=:time01)", [
            ':id'    => (int)$_package_id,
            ':ons'   => (int)1,
            'time00' => (int)time(),
            'time01' => (int)time(),
        ]);
        $html = 'transaction not found';
        if (dbrows($result)) {
            $data = dbarray($result);
            $currency = Wallet_Model::walletSettings('coin_base_currency');
            //Illegal Transaction - Price Error
            $current_pack_price = post('package_price');
            if ($data['package_price'] != $current_pack_price) {
                echo json_encode(['response' => 'Price differential error. Aborted']);
                exit();
            }
            $bonus = $data['package_promotion'] && $data['package_promotion_bonus'] ? '+ '.number_format($data['package_promotion_bonus'], 0).' Coin(s) bonus' : '';
            $order_description = format_word($data['package_coin_quantity'], 'coin|coins', ['html' => FALSE]).' '.$bonus;

            $html = '<div class="panel panel-default"><div class="panel-heading">
            <div class="clearfix"><div class="pull-right"><h4 class="strong text-dark m-0">$'.number_format($data['package_price'], 2).' '.$currency.'</h4></div>
            <h3 class="strong text-dark m-0"><i class="fad fa-sack fa-lg m-r-10"></i>'.$order_description.'</h3></div>
            </div><div class="panel-body">';

            $config = [
                'display_amount' => FALSE,
                'no_credits'     => TRUE,
                'amount_label'   => 'Top Up PHPFusion Coins',
                'items'          => [
                    0 => [
                        'id'          => $data['package_id'],
                        'type'        => 'COINP',
                        'title'       => "Top up PHPFusion Coins",
                        'description' => $order_description,
                        'price'       => $data['package_price'],
                        'tax'         => 0,
                        'shipping'    => 0,
                        'quantity'    => 1,
                        'currency'    => $currency,
                    ]
                ],
                'return_url'     => fusion_get_settings('siteurl').'infusions/wallet/top_up.php'
            ];

            require_once WALLET.'wallet_include.php';
            $html .= display_wallet($config);
            $html .= '</div></div>';

            $fusion_jquery_tags = &OutputHandler::$jqueryTags;
            $fusion_footer_tags = &OutputHandler::$pageFooterTags;

            $html .= $fusion_footer_tags;

            if (!empty($fusion_jquery_tags)) {
                $html .= "<script>$(function(){".$fusion_jquery_tags."});</script>\n";
            }
            $html .= "<script>focusInput();</script>";
        }
    }
}
//echo json_encode(['response' => $html]);
echo $html;
exit();
