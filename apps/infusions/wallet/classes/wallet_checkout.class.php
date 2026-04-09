<?php
namespace PHPFusion\Infusions\Wallet\Classes;

class Wallet_Checkout {

    private $transaction = [];
    private $order = [];


    /**
     *  Get the transaction completed
     * This model requires the following $POST
     * @param string $transaction_id
     * @param string $transaction_ref
     *
     * @return array
     */
    public function getTransaction($transaction_id = "", $transaction_ref = "") {

        if (!empty($transaction_id) && isnum($transaction_id) && !empty($transaction_ref)) {

            $transaction_id = stripinput($transaction_id);

            $transaction_ref = stripinput($transaction_ref);

            // The Return is a Transaction Record
            // The transaction does not contain complete or paid -- it is stored in per orders
            $tresult = dbquery("SELECT * FROM ".DB_WALLET_TRANSACTIONS." WHERE transaction_id=:tid AND transaction_ref=:tref",
                [
                    ':tid'  => (int)$transaction_id,
                    ':tref' => $transaction_ref,
                ]
            );
            if (dbrows($tresult)) {

                $this->transaction = dbarray($tresult);

                if ($this->transaction['transaction_status'] >= 2) {
                    add_notice("warning", "Payment for the transaction is currently pending or being declined. Please try again.");
                    $this->transaction = [];
                    return [];
                }
                return (array)$this->transaction;
            }
        } else {
            add_notice('danger', 'Cannot find transaction during checkout.');
        }
        return [];
    }

    /**
     * Get the current orders paid
     *
     * @return array
     */
    /**
     * @return array
     */
    public function getOrders() {
        if (!empty($this->transaction['transaction_number'])) {

            $order_result = dbquery("SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_ref=:thash AND order_paid=1", [':thash' => $this->transaction['transaction_number']]);
            if ($rows = dbrows($order_result)) {
                while ($order_data = dbarray($order_result)) {
                    $this->order[$order_data['order_id']] = $order_data;
                }
                return (array)$this->order;
            }
        }
        fusion_stop();
        return [];
    }

    public function getCompletedOrders() {
        if (!empty($this->transaction['transaction_number'])) {
            $order_result = dbquery(
                "SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_ref=:thash AND order_paid=1 AND order_completed=1",
                [':thash' => $this->transaction['transaction_number']]
            );
            if ($rows = dbrows($order_result)) {
                while ($order_data = dbarray($order_result)) {
                    $this->order[$order_data['order_id']] = $order_data;
                }
            }
        }
        return (array)$this->order;
    }

    /**
     * Complete the order
     *
     * @param     $order_id
     * @param     $user_id
     * @param int $timestamp
     *
     */
    public function completeOrder($order_id, $user_id, $timestamp = TIME) {
        $arr = [
            'order_id'                  => $order_id,
            'order_completed'           => 1,
            'order_completed_user'      => $user_id,
            'order_completed_datestamp' => $timestamp
        ];
        dbquery_insert(DB_WALLET_ORDERS, $arr, 'update', ['keep_session' => TRUE]);

    }

    /**
     * @param     $transaction_id
     * @param int $transaction_next
     * @param int $transaction_interval
     */
    public function completeTransaction($transaction_id, $transaction_next = 0, $transaction_interval = 0) {
        $arr = array(
            "transaction_id"       => $transaction_id,
            "transaction_status"   => 1,
            "transaction_next"     => $transaction_next,
            "trnasaction_interval" => $transaction_interval,
        );
        dbquery_insert(DB_WALLET_TRANSACTIONS, $arr, "update");
    }

}
