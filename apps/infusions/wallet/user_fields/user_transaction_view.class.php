<?php
namespace PHPFusion\Infusions\Wallet\User_Fields;

use PHPFusion\Infusions\Wallet\Classes\Wallet;
use PHPFusion\Infusions\Wallet\Classes\Wallet_Transaction;

/**
 * Class User_Transaction_View
 *
 * @package PHPFusion\Infusions\Wallet\User_Fields
 */
class User_Transaction_View {

    public $transaction_ref = "";
    public $token = "";

    /**
     * @return string|null
     */
    public function view() {
        $settings = fusion_get_settings();

        $wallet_settings = Wallet::walletSettings();

        if (iMEMBER) {
            $transaction = new Wallet_Transaction();

            if ($transaction->getRef($this->transaction_ref)) {

                $transaction_data = $transaction->transactionData();

                $transaction_data["transaction_datestamp_time"] = showdatetime($transaction_data["transaction_datestamp"]);

                // User bill
                $transact_user = fusion_get_user($transaction_data["transaction_user"]);
                // Ordered by
                $transact_user_name = $transact_user["user_firstname"]." ".$transact_user["user_lastname"];
                $address = explode("|", $transact_user["user_address"]);
                if (count($address) === 6) {
                    [$address, $address_2, $country, $region, $city, $postcode] = $address;
                    $transaction_data["transaction_user_address"] = "$address, $address_2,<br>$city, $region<br/>$country $postcode";
                }

                // Paid user
                $paid_user = [
                    "user_firstname" => "",
                    "user_lastname" => "",
                    "user_name" => "",
                ];

                $nature = "Payments pending";

                $user_real_name = "Payments pending";

                if ($transaction_data["transaction_paid_user"]) {

                    $paid_user = fusion_get_user($transaction_data["transaction_paid_user"]);

                    $user_real_name = $paid_user["user_firstname"]." ".$paid_user["user_lastname"];

                    $nature = "Payments received from $user_real_name";

                    if ($transaction_data["transaction_paid_user"] == fusion_get_userdata("user_id")) {

                        $nature = "Purchase payment sent";

                    }
                }

                $transaction_data["transaction_paid_name"] = $user_real_name;

                $transaction_data["transaction_paid_username"] = $paid_user["user_name"];

                $transaction_data["transaction_nature"] = $nature;

                $transaction_data["store_name"] = $wallet_settings["store_name"];

                if ($transaction_data["transaction_number"] === $this->token) {

                    $orders = $transaction->getOrders();

                    // need to gen a wallet link
                    add_to_jquery(/** @lang JavaScript */ "
                    $(document).on('click', '.print-invoice', function(e) {
                    e.preventDefault();
                    walletJs.printInvoice('invoice-print');
                    });
                    ");

                    return fusion_render(INFUSIONS."wallet/templates/", "transaction-details.twig", [
                        "transaction" => $transaction_data,
                        "orders" => $orders,
                    ], true);
                }
            } else {
                // redirect back to wallet profile.
                add_notice("danger", "Invalid wallet transaction request. No transaction could be found.", BASEDIR . "edit_profile.php");
                redirect(BASEDIR."edit_profile.php");
            }
        } else {
            add_notice("danger", "You need to login to view transactions.", BASEDIR . $settings["opening_page"]);
            redirect(BASEDIR.$settings["opening_page"]);
        }
        return NULL;
    }


}
