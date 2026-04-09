<?php

use PHPFusion\Infusions\Wallet\Classes\Wallet;
use PHPFusion\Infusions\Wallet\Classes\Wallet_Transaction;

(defined('IN_FUSION') || exit);

if (!defined("no_profile_button")) {
    define("no_profile_button", TRUE);
}

function do_credit_transfer() {
    $wallet_settings = Wallet::walletSettings();
    if (check_post("transfer_coin")) {
        if ($wallet_settings["coin_u2u_transfer"]) {
            if (check_post("agree_transfer")) {

                if ($password = sanitizer("password", "", "password")) {

                    $userdata = fusion_get_userdata();

                    $passAuth = new \PHPFusion\PasswordAuth();

                    $passAuth->currentAlgo = $userdata["user_algo"];

                    $passAuth->currentPasswordHash = $userdata["user_password"];

                    $passAuth->currentSalt = $userdata["user_salt"];

                    $passAuth->inputPassword = $password;

                    if ($passAuth->isValidCurrentPassword(FALSE)) {

                        if ($transfer_amt = sanitizer("transfer_amount", "", "transfer_amount")) {

                            $transfer_amt = (float)format_float($transfer_amt);

                            $user_wallet = Wallet::getInstance()->getUserWallet($userdata["user_id"]);

                            if ($user_wallet["gold_balance"] >= $transfer_amt && fusion_safe()) {

                                // transfer possible
                                if ($transfer_to = (int)sanitizer("transfer_to", "", "transfer_to")) {

                                    if ($transfer_to != $userdata["user_id"]) {

                                        $target = fusion_get_user($transfer_to);

                                        // cannot use this because the wallet account is incomplete
                                        $target_wallet = dbarray(dbquery("SELECT * FROM ".DB_USER_WALLET." WHERE user_id=:uid", [":uid" => $target["user_id"]]));

                                        if ($target["user_id"] && $target_wallet["wallet_id"]) {

                                            // can transfer
                                            $new_amount_a = (float)$user_wallet["gold_balance"] - (float)$transfer_amt;

                                            $new_amount_b = (float)$target_wallet["gold_balance"] + (float)$transfer_amt;

                                            $transaction_time = time();

                                            // Creates a transaction
                                            $transaction = new Wallet_Transaction();

                                            $transaction->datestamp = $transaction_time;

                                            $transaction->user = $userdata["user_id"];

                                            $transaction->checkout_url = ""; // WIP file here to renew domains

                                            $transaction->require_checkout = FALSE;

                                            $transaction->order_data[1] = [
                                                'order_item_id'             => 1,
                                                'order_item_type'           => 'COINT',
                                                'order_item_value'          => number_format($transfer_amt, 4),
                                                'order_item_quantity'       => 1,
                                                'order_title'               => "Transfer gold coins send to ".$target["user_name"],
                                                'order_description'         => "Wallet gold coins transfer payment send to ".$target["user_name"],
                                                'order_options'             => "",
                                                "order_currency"            => "COINS",
                                                "order_paid"                => 1,
                                                "order_paid_datestamp"      => $transaction_time,
                                                "order_paid_user"           => $userdata["user_id"],
                                                "order_completed"           => 1,
                                                "order_completed_user"      => $userdata["user_id"],
                                                "order_completed_datestamp" => $transaction_time,
                                                "order_item_cycle"          => 0,
                                                "order_item_interval"       => 0,
                                            ];

                                            $wallet_section = (int)dbresult(dbquery("SELECT ufr.field_cat_id
                                            FROM ".DB_USER_FIELDS." uf
                                            INNER JOIN ".DB_USER_FIELD_CATS." ufc ON uf.field_cat=ufc.field_cat_id
                                            INNER JOIN ".DB_USER_FIELD_CATS." ufr ON ufr.field_cat_id=ufc.field_parent
                                            WHERE uf.field_name='user_transaction'"), 0);

                                            $wallet_bbcode_link = "[url=".rtrim(fusion_get_settings("siteurl"), "/")."/edit_profile.php?section=$wallet_section]profile settings page[/url]";


                                            $mail_subject = "Coin transfer successful";
                                            $mail_description = "Dear ".$userdata["user_name"].",\n\n[b]Your gold coin transfer of ".number_format($transfer_amt, 2)." coin(s) to ".$target["user_name"]." was successful[/b].\n\n";
                                            $mail_description .= "The transfer was performed on ".date("d M Y H:i:s", $transaction_time)."\n\nPlease check your $wallet_bbcode_link to see your new wallet balance. If you need help in this regard, you can contact the site administrator for further assistance.";

                                            if ($transaction_id = $transaction->save(FALSE, $mail_subject, $mail_description)) {

                                                send_pm($userdata["user_id"], 0, $mail_subject, $mail_description);

                                                $tdata["transaction_id"] = $transaction_id;
                                                $tdata["transaction_title"] = "Gold coin transfer";
                                                $tdata["transaction_description"] = "Transfer gold coins send to ".$target["user_name"];
                                                $tdata["transaction_currency"] = "coins";
                                                $tdata["transaction_method"] = "coins";
                                                $tdata["transaction_type"] = "Wallet Transfer";
                                                $tdata["transaction_status"] = 1;
                                                $tdata["transaction_datestamp"] = $transaction_time;
                                                $tdata["transaction_paid_datestamp"] = $transaction_time;
                                                $tdata["transaction_paid_user"] = $userdata["user_id"];

                                                $transaction->updateTransactionPayment($tdata);

                                                dbquery("UPDATE ".DB_USER_WALLET." SET gold_balance=:new_amt WHERE user_id=:uid", [":uid" => $userdata["user_id"], ":new_amt" => $new_amount_a]);

                                                // New transaction for receiving payment
                                                $transaction2 = new Wallet_Transaction();
                                                $transaction2->datestamp = $transaction_time;
                                                $transaction2->user = $target["user_id"];
                                                $transaction2->checkout_url = ""; // WIP file here to renew domains
                                                $transaction2->require_checkout = FALSE;

                                                $transaction2->order_data[1] = [
                                                    'order_item_id'             => 1,
                                                    'order_item_type'           => 'COINP',
                                                    'order_item_value'          => number_format($transfer_amt, 4),
                                                    'order_item_quantity'       => 1,
                                                    'order_title'               => "Transfer gold coins received from ".$userdata["user_name"],
                                                    'order_description'         => "Wallet gold coin transfer payment from ".$userdata["user_name"],
                                                    "order_currency"            => "COINS",
                                                    'order_options'             => "",
                                                    "order_paid"                => 1,
                                                    "order_paid_datestamp"      => $transaction_time,
                                                    "order_paid_user"           => $userdata["user_id"],
                                                    "order_completed"           => 1,
                                                    "order_completed_user"      => $userdata["user_id"],
                                                    "order_completed_datestamp" => $transaction_time,
                                                    "order_item_cycle"          => 0,
                                                    "order_item_interval"       => 0,
                                                ];

                                                $mail_subject = "Gold coin transfer received";
                                                $mail_description = "Dear ".$target["user_name"].",\n\n[b]Your gold coin transfer of ".number_format($transfer_amt, 2)." coin(s) from ".$userdata["user_name"]." was successful[/b].\n\n";
                                                $mail_description .= "The transfer was performed on ".date("d M Y H:i:s", $transaction_time)."\n\nPlease check your $wallet_bbcode_link to see your new wallet balance. If you need help in this regard, you can contact the site administrator for further assistance.";

                                                if ($transaction_id = $transaction2->save(FALSE, $mail_subject, $mail_description)) {

                                                    // Send PM
                                                    send_pm($target["user_id"], 0, $mail_subject, $mail_description);

                                                    $vdata["transaction_title"] = "Gold coin transfer";
                                                    $vdata["transaction_description"] = "Transaction gold coin transfer from ".$userdata["user_name"];
                                                    $vdata["transaction_method"] = "coins";
                                                    $vdata["transaction_id"] = $transaction_id;
                                                    $vdata["transaction_status"] = 1;
                                                    $vdata["transaction_type"] = "Wallet Transfer";
                                                    $vdata["transaction_datestamp"] = $transaction_time;
                                                    $vdata["transaction_paid_datestamp"] = $transaction_time;
                                                    $vdata["transaction_paid_user"] = $userdata["user_id"];

                                                    $transaction2->updateTransactionPayment($vdata);

                                                    dbquery("UPDATE ".DB_USER_WALLET." SET gold_balance=:new_amt WHERE user_id=:uid", [":uid" => $target["user_id"], ":new_amt" => $new_amount_b]);

                                                    add_notice("success", "<strong>$transfer_amt coins</strong> was successfully transferred to user <strong>" . $target["user_name"] . "</strong>");

                                                    redirect(FUSION_REQUEST);
                                                }
                                            }
                                        } else {
                                            add_notice("danger", "Recepient does not have an active wallet account yet.");
                                        }
                                    } else {
                                        add_notice("danger", "You cannot transfer to yourself. Coin transfer is aborted.");
                                    }
                                } else {
                                    add_notice("danger", "Please select a valid recepient to recieve your coins.");
                                }
                            } else {

                                add_notice("danger", "Insufficient credit. You cannot send more than what you currently own.");
                            }
                        } else {
                            add_notice("danger", "Please specify how coins quantity to be transferred.");
                        }
                    } else {
                        add_notice("danger", "Incorrect account password.");
                    }
                } else {
                    add_notice("danger", "Incorrect account password.");
                }
            } else {
                add_notice("danger", "You need to agree with the terms of use before you can make a credit transfer.");
            }
        } else {
            add_notice("danger", "Wallet User to User Gold coin transfer is currently closed until further notice. We apologise for the inconvenience.");
        }
    }
}

function get_coin_packages() {

    $wallet_settings = get_settings("wallet");
    // market capital
    $current_market_capital = dbresult(dbquery("SELECT SUM(gold_balance) 'balance' FROM ".DB_USER_WALLET), 0);

    $package_info = [];

    if ($wallet_settings["coin_max_float"] >= $current_market_capital && $wallet_settings["coin_charging"]) {

        $result = dbquery("SELECT p.*,        
           (
               SELECT p1.package_promotion_bonus
               FROM fusion_coin_packages p1
               WHERE p1.package_promotion = '1' AND (p1.package_promotion_start >= '".time()."' AND p1.package_promotion_end <= '".time()."' AND p1.package_id = p.package_id)
               OR p1.package_promotion=1 AND (p1.package_promotion_start = '' AND p1.package_promotion_end = '' AND p1.package_id = p.package_id)
               
           ) 'package_promotion_bonus'      
           FROM ".DB_COIN_PACKS." p WHERE p.package_status=1 ORDER BY p.package_price");

        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $package_info[$data["package_id"]] = [
                    "quantity" => $data["package_coin_quantity"],
                    "bonus"    => $data["package_promotion_bonus"]
                ];
            }
        }
    }

    return $package_info;
}

function show_credit_topup() {
    $wallet_settings = Wallet::walletSettings();
    if (check_post("purchase_coin")) {
        if ($wallet_settings["coin_charging"]) {
            if (fusion_safe() && iMEMBER) {

                if (check_post("agree_purchase")) {

                    if ($package_id = post("topup")) {

                        // now amount.
                        $id = "topup";

                        $coin_package = get_coin_packages();

                        if (isset($coin_package[$package_id])) {

                            $coin_package = $coin_package[$package_id];

                            if ($coin_package["quantity"]>= $wallet_settings["coin_min_purchase"]) {

                                $bonus = "";

                                if ($coin_package["bonus"] > 0) {
                                    $bonus = "+ ".number_format($coin_package["bonus"], 2);
                                }

                                $config = [
                                    "display_amount"    => FALSE,
                                    "no_credits"        => TRUE,
                                    "amount_label"      => "PHPFusion Gold Coins",
                                    "order_title"       => "PHPFusion Gold Coins",
                                    "order_description" => "Purchase of PHPFusion Gold Coins",
                                    "order_item_type"   => "COINP",
                                    "items"             => [
                                        0 => [
                                            "id"          => $package_id,
                                            "type"        => "COINP",
                                            "title"       => "PHPFusion Gold Coins",
                                            "description" => "Top up purchase of ".format_word($coin_package["quantity"], "gold coin|gold coins"),
                                            "price"       => 1,
                                            "tax"         => 0,
                                            "shipping"    => 0,
                                            "quantity"    => $coin_package["quantity"],
                                            "currency"    => "USD",
                                        ]
                                    ],
                                    "order_amount"      => 1,
                                    "order_quantity"    => $coin_package["quantity"],
                                    "order_currency"    => "USD",
                                    "order_tax"         => 0,
                                    "return_url"        => rtrim(fusion_get_settings("siteurl"), "/")."/infusions/wallet/api/checkout/wallet-topup.php",
                                    "transaction_ref"   => "",
                                    // "display_amount_field" => FALSE,
                                    // "display_amount"       => FALSE,
                                    // "amount_label"         => "Donation",
                                    "delimiter"         => 4,
                                    "label"             => FALSE,
                                    "reverse_display"   => FALSE,
                                ];

                                require_once INFUSIONS."wallet/wallet_include.php";

                                $html = openmodal($id, "Please select payment method", ["static" => TRUE, "class" => "modal-md"]);
                                $html .= "<div style=\"margin:0 -30px;\">
                                <svg style=\"position: absolute;\">
                                    <clipPath id=\"clip\">
                                        <path d=\"M1175.65 35.7644C962.846 -46.1285 865.751 35.6288 643.768 54.6084C421.786 73.588 391.68 33.0436 226.292 14.4975C60.9034 -4.0486 11.1228 90.8729 -67.9998 110.654C-67.9998 198.586 -67.9998 516 -67.9998 516L1508.19 516L1508.19 8.45359C1508.19 8.45359 1388.84 117.807 1175.65 35.7644Z\"></path>
                                    </clipPath>
                                </svg>
                                   <div class=\"wave-wrapper\">
                                    <div class=\"wave-2\"></div>
                                    <div class=\"wave-3\"></div>
                                    <div class=\"wave-1\"></div>
                                </div>";
                                $html .= "<div style='padding:30px;position: relative;'>";
                                // order template
                                $html .= "<div class='text-center'>
                            <div class='wallet-icon'>
                                <div class='wallet-icon-inner'>                                                             
                                    <img src='".INFUSIONS."wallet/images/fusiongold.svg' alt='' class='img-responsive'>
                                </div>     
                            </div>
                            <div class='wallet-label'><img src='".INFUSIONS."wallet/images/fusiongold.svg' alt='' class='img-responsive'> ".number_format($coin_package["quantity"], 0)." $bonus</div>
                            </div>                                                
                            <h4 class='text-center'>$".number_format($coin_package["quantity"], 2)."</h4>
                            <h5 class='text-center m-t-0'>A cheaper way to make payment in PHP-Fusion</h5>                         
                            ";

                                $html .= display_wallet($config);

                                $html .= "</div></div>";

                                $html .= closemodal();

                                add_to_footer($html);

                            } else {
                                add_notice("danger", "You need to select at least a minimum of " . format_word($wallet_settings["coin_min_purchase"], "gold coin|gold coins") . " for every purchase.");
                            }

                        } else {
                            add_notice("danger", "Please select a valid top up amount.");

                            redirect(FUSION_REQUEST);
                        }
                    } else {
                        add_notice("danger", "Please select a top up amount.");

                        redirect(FUSION_REQUEST);
                    }
                } else {
                    add_notice("danger", "You need to read and agree with coin shop terms of use");

                    redirect(FUSION_REQUEST);
                }
            } else {
                add_notice("danger", "You are not authorized to perform this transaction.");
                redirect(FUSION_REQUEST);
            }
        } else {
            add_notice("danger", "PHPFusion gold coin shop is currently closed. Please check back later. We apologise for the inconvenience.");
        }
    }
}

// Display user field input
if ($profile_method == "input") {

    if (iMEMBER) {
        $wallet_settings = Wallet::walletSettings();

        if (defined("ADMIN_PANEL")) {
            $user_fields = "<div class='well'>Wallet Payment Window</div>";
        } else {

            do_credit_transfer();

            show_credit_topup();

            $form_id = random_string(8, TRUE);

            $form_id_2 = random_string(8, TRUE);

            $coin_bonuses = get_coin_packages();

            $coin_packs = [];

            $i = 0;

            foreach ($coin_bonuses as $package_id => $coin) {
                //$coin_packs[$amount] = format_word($amount, "coin|coins").($percent? " (+ Free ".number_format($amount * $percent, 2)." coins voucher)" : "");
                if ($coin["quantity"] >= $wallet_settings["coin_min_purchase"]) {
                    $coin_packs[$package_id] = "<label class=\"option-item\">
                    <input name='topup' type='radio' class='checkbox' value='$package_id' ".($i == 1 ? "checked" : "").">
                    <div class='option-inner'>
                      <div class='tickmark'></div>
                      <div class='icon'>
                       <img src='".INFUSIONS."wallet/images/fusiongold_stacked.svg' alt=''>
                      </div>
                      <div class='name'>".format_word($coin["quantity"], "Gold Coin|Gold Coins").($coin["bonus"] ? "<br/><small> (+ ".number_format($coin["bonus"], 2)." Free Coins)</small>" : "")."</div>
                    </div>
                    </label>";
                    $i++;
                }
            }
            //            print_p($coin_packs);

            //print_P($_POST);
            fusion_load_script(DYNAMICS."assets/mask/jquery.mask.min.js");
            add_to_jquery("$('#transfer_amount').mask('000,000,000,000.00', {'reverse':true});");

            $wallet_balance = (int)fusion_get_userdata("user_balance");
            //print_P($wallet_settings);
            //print_p($wallet_settings["coin_u2u_transfer"]);

            $user_fields = "<div class='row'>"
                ."<div class='col-xs-12 col-sm-6'>"
                // payment window
                ."<div class='list-group'>"
                ."<div class='list-group-item field-group-item'>"
                ."<div class='line-compressed br-b-1 p-b-15'><h4 class='m-0 profile_category_name'>Transfer Coins</h4><span class='text-smaller'>You can transfer your coins to other members here. For security reasons, you need to enter your account password to perform credit transfer.</span></div>."
                .openform($form_id, "post")
                .form_user_select("transfer_to", "Transfer To (Recepient)", post("transfer_to"), ["inline" => FALSE, "inner_width" => "100%", "required" => TRUE])
                .form_text("transfer_amount", "Coin Amount", post("transfer_amount"), [
                    "inline"       => FALSE,
                    "placeholder"  => (float)number_format($wallet_balance, 2),
                    "required"     => TRUE,
                    "append"       => TRUE,
                    "append_value" => "Coins",
                    "type"         => "text",
                    "number_max"   => $wallet_balance
                ])
                .form_text("password", "Password", "", ["inline" => FALSE, "type" => "password", "required" => TRUE])
                .form_checkbox("agree_transfer", "I agree to abide with the terms of the my wallet account.", "", ["reverse_label" => TRUE])
                .form_button("transfer_coin", "Transfer Coin", "transfer_coin", ["class" => "btn-primary", "deactivate" => (!$wallet_settings["coin_u2u_transfer"])])
                .closeform()
                ."</div>"
                ."</div>"

                ."</div><div class='col-xs-12 col-sm-6'>"

                // Top up coins
                ."<div class='list-group'>"
                ."<div class='list-group-item field-group-item'>"
                ."<div class='line-compressed br-b-1 p-b-15'><h4 class='m-0 profile_category_name'>Coin Shop</h4><span class='text-smaller'>Purchase coins to top up your wallet credits. Now you can buy more to earn more coins. The more you top up, the more coins you'll get.</span></div>."
                .openform($form_id_2, "post")
                ."<div class='coin-shop'>"
                .implode("", $coin_packs)
                ."</div>"
                .form_checkbox("agree_purchase", "I agree to abide with the terms of the my wallet account.", "", ["reverse_label" => TRUE])
                .form_button("purchase_coin", "Purchase Coin", "purchase_coin", ["class" => "btn-primary", "deactivate" => (!$wallet_settings["coin_charging"])])
                .closeform()
                ."</div>"
                ."</div>"
                ."</div></div>";

        }
    }

}
