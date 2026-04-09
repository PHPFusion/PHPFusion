<?php
namespace PHPFusion\Infusions\Wallet\Classes;

use PHPFusion\Template;

class Wallet_Payment {
    /**
     * Invoice html output
     *
     * @var string
     */
    private $invoice = '';

    public function __construct() {
        // model
        if ($invoice_code = get('ref')) {
            if (iMEMBER) {
                require_once INFUSIONS.'wallet/wallet_include.php';
                $transaction_id = dbresult(dbquery("SELECT transaction_id FROM ".DB_WALLET_TRANSACTIONS." WHERE transaction_ref=:ref", [
                    //':uid' => fusion_get_userdata('user_id'),
                    ':ref' => $invoice_code
                ]), 0);
                $transaction = new Wallet_Transaction();
                if ($transaction->get($transaction_id)) {
                    $this->invoice = $transaction->showInvoice();
                    echo $this->viewPayment();
                }
            } else {
                $tpl = twig_init(INFUSIONS.'wallet/templates/', FALSE);
                echo $tpl->render('login.twig', [
                    'settings'   => fusion_get_settings(),
                    'locale'     => fusion_get_locale(),
                    'login_link' => BASEDIR.'login.php?rel='.INFUSIONS.'wallet/payment.php?ref='.$invoice_code
                ]);
            }
        } else {
            add_notice('error', 'The invoice reference number is invalid.', 'all');
            redirect(BASEDIR.'home.php');
        }
    }

    // Payment Home Template
    private function viewPayment() {
        $tpl = Template::getInstance('wallet-payment');
        $tpl->set_template(__DIR__.'/../templates/payment-home.html');
        $tpl->set_tag('invoice', $this->invoice);
        return $tpl->get_output();
    }

}
