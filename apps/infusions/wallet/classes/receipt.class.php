<?php
namespace PHPFusion\Infusions\Wallet\Classes;

use PHPFusion\Template;

/**
 * Class Receipt
 *
 * @package PHPFusion\Infusions\Wallet\Classes
 */
class Receipt {

    private function getCustomerWalletInfo($customer_wallet_id) {
        $wallet = Wallet::getInstance();
        return $wallet->getUserWallet($customer_wallet_id);
    }

    /**
     * Display Receipt
     * @param $transaction_ref
     //* @param $wallet_info
     *
     * @return string|null
     * @throws \Exception
     */
    public function displayOrderReceipt($transaction_ref) {

        $settings = Wallet::getInstance()::walletSettings();

        $value = stripinput($transaction_ref);

        $result = dbquery("SELECT * FROM ".DB_WALLET_TRANSACTIONS." WHERE transaction_ref=:ref", [':ref' => $value]);

        if (dbrows($result)) {

            $data = dbarray($result);

            $wallet_info = $this->getCustomerWalletInfo($data["transaction_user"]);

            $html = Template::getInstance('order_receipt');
            $html->set_template(__DIR__.'/../templates/order_receipt.html');
            $html->set_file([IMAGES]);
            $html->set_tag('store_logo', BASEDIR.fusion_get_settings('sitebanner'));
            $html->set_tag('store_name', $settings['store_name']);
            $html->set_tag('item_amount', number_format($data['transaction_item_total'], $settings['coin_currency_number_delim'], $settings['coin_currency_decimal_delim'], $settings['coin_currency_thousand_delim']));
            $html->set_tag('tax_amount', number_format($data['transaction_tax'], $settings['coin_currency_number_delim'], $settings['coin_currency_decimal_delim'], $settings['coin_currency_thousand_delim']));
            $html->set_tag('payment_status', $data['transaction_status'] == TRANSACTION_PAID ? '<strong class="text-success">Paid</strong>' : '<strong class="text-warning">Pending for payment</strong>');
            if ($data['transaction_shipping'] > 0) {
                $html->set_block('shipping_amount', [
                    'currency' => $settings['transaction_currency'],
                    'text'     => number_format($data['transaction_shipping'], $settings['coin_currency_number_delim'], $settings['coin_currency_decimal_delim'], $settings['coin_currency_thousand_delim'])]);
            }
            $html->set_tag('amount', number_format($data['transaction_amount'], $settings['coin_currency_number_delim'], $settings['coin_currency_decimal_delim'], $settings['coin_currency_thousand_delim']));
            $html->set_tag('invoice_ref', $data['transaction_ref']);
            $html->set_tag('currency', $data['transaction_currency']);
            $html->set_tag('date', showdate('longdate', $data['transaction_datestamp']));

            // User Particulars
            if ($wallet_info['first_name'] && $wallet_info['last_name']) {
                $html->set_block('name', [
                    'first_name' => $wallet_info['first_name'],
                    'last_name'  => $wallet_info['last_name']
                ]);
            }

            if (!empty($wallet_info['company']) && !empty($wallet_info['company_no'])) {
                $html->set_block('company', [
                    'company'    => $wallet_info['company'],
                    'company_no' => $wallet_info['company_no']
                ]);
            }

            if ($wallet_info['address']) {
                $html->set_block('add', [
                    'address'   => $wallet_info['address'],
                    'address_2' => $wallet_info['address_2'],
                    'address_3' => $wallet_info['address_3'],
                    'postcode'  => $wallet_info['postcode'],
                    'region'    => $wallet_info['region'],
                    'country'   => $wallet_info['country'],
                ]);
            }
            if ($wallet_info['mobile']) {
                $html->set_block('co', [
                    'mobile'    => $wallet_info['mobile'],
                    'mobile_cc' => $wallet_info['mobile_cc']
                ]);
            }
            if ($wallet_info['email']) {
                $html->set_block('email', [
                    'email' => $wallet_info['email'],
                ]);
            }

            foreach ($wallet_info as $key => $value) {
                $html->set_tag($key, $value);
            }

            // orders id - transaction_oid
            $cresult = dbquery("SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_ref=:inv_num AND order_paid=1 ORDER BY order_id ASC", [':inv_num'=>$data['transaction_number']]);
            if (dbrows($cresult)) {
                while ($cdata = dbarray($cresult)) {
                    $cdata['order_description'] = nl2br($cdata['order_description']);
                    $cdata['order_datestamp'] = showdate('longdate', $cdata['order_datestamp']);
                    $cdata['order_paid_datestamp'] = showdate('longdate', $cdata['order_paid_datestamp']);
                    $cdata['order_item_value'] = number_format($cdata['order_item_value'], $settings['coin_currency_number_delim'], $settings['coin_currency_decimal_delim'], $settings['coin_currency_thousand_delim']);
                    $cdata['order_item_quantity'] = intval($cdata['order_item_quantity']);
                    $cdata['order_total'] = number_format($cdata['order_total'], $settings['coin_currency_number_delim'], $settings['coin_currency_decimal_delim'], $settings['coin_currency_thousand_delim']);
                    $cdata['order_total_shipping'] = number_format($cdata['order_total_shipping'], $settings['coin_currency_number_delim'], $settings['coin_currency_decimal_delim'], $settings['coin_currency_thousand_delim']);
                    $cdata['order_total_tax'] = number_format($cdata['order_total_tax'], $settings['coin_currency_number_delim'], $settings['coin_currency_decimal_delim'], $settings['coin_currency_thousand_delim']);
                    $html->set_block('items', $cdata);
                }
            } else {
                if (iSUPERADMIN) {
                    add_notice('danger', 'There are no order items logged here.');
                }
            }
            $html->set_tag('date_time', showdate('longdate', $data['transaction_datestamp']));

            return $html->get_output();
        }

        return NULL;
    }


}
