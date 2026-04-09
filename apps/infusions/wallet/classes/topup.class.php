<?php

namespace PHPFusion\Infusions\Wallet\Classes;

use PHPFusion\Template;

/**
 * Class Charge
 * A top up viewer page controller
 *
 * @package PHPFusion\Infusions\Wallet\Classes\Topup
 */
class TopUp extends Wallet_Model {

    public $pocket = [];

    public function setMenu() {
        return [
            'section' => [
                [
                    'id'     => 'top-up',
                    'parent' => '0',
                    'title'  => 'Coin Top Up',
                    'link'   => INFUSIONS.'wallet/wallet.php?ref=charge&amp;sref=top_up',
                    'icon'   => '',
                ],
                [
                    'id'     => 'redeem',
                    'parent' => '0',
                    'title'  => 'Redeem Coupon',
                    'link'   => INFUSIONS.'wallet/wallet.php?ref=charge&amp;sref=coupon',
                    'icon'   => '',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function getCoinPacks() {
        $list = [];
        $result = dbquery("SELECT * FROM ".DB_COIN_PACKS." WHERE (package_promotion_start=0||package_promotion_start<=:time00)
        AND (package_promotion_end=0||package_promotion_end>=:time01) AND package_status=1 ORDER BY package_coin_quantity ASC", [
            ':time00' => TIME,
            ':time01' => TIME
        ]);
        if (dbrows($result)) {
            while ($data = dbarray($result)) {

                $list[$data['package_id']] = [
                    "package_id"            => $data['package_id'],
                    "package_coin_quantity" => format_word(number_format($data['package_coin_quantity'], 0), 'Coin|Coins'),
                    'package_bonus'         => $data['package_promotion'] && $data['package_promotion_bonus'] ? '+ '.number_format($data['package_promotion_bonus'], 0).' coin bonus' : '',
                    "package_currency"      => $this::walletSettings('coin_base_currency'),
                    "package_price"         => number_format($data['package_price'], 2)
                ];
            }

        }
        return $list;
    }

    private function getCardNo() {
        $salt = fusion_encode($this->pocket['email']);
        $chunked_1 = substr($salt, 0, 4);
        $chunked_2 = substr($salt, 4, 8);
        $chunked_3 = substr($salt, 8, 12);

        return $chunked_1.'-'.$chunked_2.'-'.$chunked_3;
    }

    private function getValidThru($format) {
        $user_joined = fusion_get_user($this->pocket['user_id'], 'user_joined');
        $date = new \DateTime();
        $date->setTimestamp($user_joined);
        return $date->format($format);
    }

    public function topup() {
        $wallet_css_file = auto_file(WALLET."templates/css/wallet.css");
        $js_time = filemtime($wallet_css_file);
        add_to_head("<link rel='stylesheet' type='text/css' href='$wallet_css_file?v=$js_time'/>");
        add_to_title('Top up wallet');
        $twig = twig_init(INFUSIONS.'wallet/templates', TRUE);
        $coins_pack = $this->getCoinPacks();
        //print_P($coins_pack);
        $info = [
            'locale'           => fusion_get_locale(),
            'coins_packs'      => $coins_pack,
            'store_link'       => fusion_get_settings('siteurl').'edit_profile.php?ref=wallet&section=topup',
            'privacy_link'     => fusion_get_settings('siteurl').'legal/privacy.php',
            'tos_link'         => fusion_get_settings('siteurl').'legal/tos.php',
            'balance'          => format_word($this->pocket['balance'], 'coin|coins'),
            'first_name'       => $this->pocket['first_name'],
            'last_name'        => $this->pocket['last_name'],
            'valid_thru_month' => $this->getValidThru('m'),
            'valid_thru_year'  => $this->getValidThru('y'),
            'site_path'        => fusion_get_settings('site_path'),
            'form_id'          => 'coinfrm',
            'form_token'       => fusion_get_token('coinfrm'),
        ];

        return $twig->render('charge.twig', $info);
    }


    public function viewPage() {

        $locale = fusion_get_locale();
        add_to_title($locale['global_201']."Top Up Coin Account");
        add_to_head("<link rel='stylesheet' type='text/css' href='".WALLET."templates/css/wallet.css'/>");

        $tpl = Template::getInstance("wallet-packs");
        $tpl->set_file([WALLET.'images/']);

        $tpl->set_template(__DIR__.'/../templates/charge_topup.html');
        $tpl->set_tag("account_email", fusion_get_userdata('user_email'));
        $tpl->set_tag("coin_balance", format_word($this->pocket['balance'], "coin|coins", ['html' => FALSE]));
        $tpl->set_tag("coin_transaction", WALLET.'wallet.php?ref=overview&sref=topup');

        $result = dbquery("SELECT * FROM ".DB_COIN_PACKS." WHERE (package_promotion_start=0||package_promotion_start<=:time00)
        AND (package_promotion_end=0||package_promotion_end>=:time01) ORDER BY package_coin_quantity", [
            ':time00' => TIME,
            ':time01' => TIME
        ]);
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                if ($data['package_status']) {
                    $tpl->set_block('coin', [
                        "package_id"            => $data['package_id'],
                        "package_coin_quantity" => number_format($data['package_coin_quantity'], 0),
                        'package_bonus'         => $data['package_promotion'] && $data['package_promotion_bonus'] ? '+ '.number_format($data['package_promotion_bonus'], 0).' coin bonus' : '',
                        "package_currency"      => $this::walletSettings('coin_base_currency'),
                        "package_price"         => number_format($data['package_price'], 0)
                    ]);
                }
            }
        }

        $form_id = 'ctopup';
        $form_id2 = 'token2';
        //$remote_page_hash = fusion_get_settings('site_path').'infusions/wallet/classes/ajax/package.modal.php';
        $fusion_token = fusion_get_token($form_id, 20);
        $fusion_token2 = fusion_get_token($form_id2, 20);

        // now top up trigger modal
        add_to_jquery("
        $('.topup').bind('click', function(e) {
            var package = $(this).data('package');
            $.ajax({
                'method':'get',
                'dataType': 'json',
                'async': false,
                'url' : '".fusion_get_settings('site_path')."infusions/wallet/classes/ajax/package.json.php',
                'data' : {
                    'q': package,
                    'form_id': '$form_id2' ,
                    'fusion_token': '$fusion_token2'
                },

                'success': function(e){
                    if (e.response == 200) {
                        var eData = $.extend(e, {'form_id': '$form_id' , 'fusion_token': '$fusion_token' });
                        // trigger modal open
                        $.ajax({
                            'method':'post',
                            'dataType':'json',
                            'async': false,
                            'url': '".fusion_get_settings('site_path')."infusions/wallet/classes/ajax/package.modal.php',
                            'data': eData,
                            'success': function(e) {
                                $('#coin-purchase-Modal .modal-body').html(e.response);
                                $('#coin-purchase-Modal').modal('show');
                            },
                            'error': function(e) {
                                console.log('Top Up Error');
                            }
                        });
                    } // endif
                },
                'error': function(e){
                }
            });
        });
        ");

        $modal = openmodal("coin-purchase", "<h4 class='strong m-0'>PHP-Fusion Coin Store</h4>", ['button_class' => 'top-up']);
        $modal .= closemodal();

        add_to_footer($modal);

        return $tpl->get_output();
    }

}
