<?php
namespace PHPFusion\Infusions\Wallet\Classes;

use Defender;
use PHPFusion\Template;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class Wallet_Transaction
 *
 * @package PHPFusion\Infusions\Wallet\Classes
 */
class Wallet_Transaction {

    // Required parameter
    public $user = 0;

    public $datestamp = TIME;

    public $checkout_url = '';

    public $require_checkout = TRUE;

    public $custom_amount = 0;

    public $currency = "USD";
    /**
     * sets the order information
     *
     * @var array
     */
    public $order_data = [];
    /**
     * resulting array for orders including quantity, price, and total calculated
     *
     * @var array
     */
    public $transaction_cdata = [];

    public $order_cdata = [];

    private $order_info = [];

    private $user_ip = '';

    private $wallet = NULL;

    private $total = 0;

    private $transaction = [];

    private $order_filter = '';

    private $transaction_filter = '';

    private $order_ids = [];
    private $completed_orders = [];

    /**
     * Wallet_Transaction constructor.
     */
    public function __construct() {
        $this->wallet = Wallet::getInstance();
    }

    public function setOrderFilter($value) {
        $this->order_filter = $value;
    }

    public function setTransactionFilter($value) {
        $this->transaction_filter = $value;
    }

    /**
     * @param array $logs
     */
    public function logTransactionResponse($logs = []) {
        if ($this->transaction["transaction_id"]) {
            if (!$logs) {
                $logs = $_REQUEST;
            }
            $logs = fusion_encode($logs);
            $data = [
                "transaction_id"       => $this->transaction["transaction_id"],
                "transaction_response" => $logs,
            ];

            dbquery_insert(DB_WALLET_TRANSACTIONS, $data, "update", ["keep_session" => TRUE]);
        } else {
            set_error(E_CORE_WARNING, "Transaction log could not be updated because there are no transaction id", __FILE__, __LINE__);
        }
    }


    /**
     * Update transaction payment status
     *
     * @param        $data
     * @param array  $log_variable
     *
     * @return bool
     */
    public function updateTransactionPayment($data, $log_variable = []): bool {

        if ($this->transaction && isnum($data['transaction_status']) && iMEMBER) {

            // we will have the whole transaction array

            if (!$log_variable) {
                $log_variable = $_REQUEST;
            }

            $user_id = fusion_get_userdata("user_id");

            // Log all meta headers requests
            $data['transaction_response'] = fusion_encode(Defender::sanitize_array($log_variable));

            if (!$this->transaction["transaction_id"]) {
                die("No transaction ID found.");
            }

            $data["transaction_id"] = $this->transaction["transaction_id"];

            if (!$this->transaction["transaction_oid"]) {
                die("No transaction orders found.");
            }

            //$data['transaction_user'] = $user_id;
            // Update transaction with the intended status
            //$sql = "UPDATE ".DB_WALLET_TRANSACTIONS." SET transaction_title=:title,transaction_description=:desc,transaction_method=:method,transaction_datestamp=:time,transaction_status=:status,transaction_user=:uid,transaction_response=:response WHERE transaction_id=:id";
            //$param = [
            //    ':title'       => $data['transaction_title'],
            //    ':description' => $data['transaction_description'],
            //    ':method'      => $data['transaction_method'],
            //    ':time'        => time(),
            //    ':uid'         => $user_id,
            //    ':response'    => $response,
            //    ':status'      => $data['transaction_status'],
            //    ':id'          => (int)$this->transaction['transaction_id']
            //];
            //dbquery($sql, $param);
            //print_P($data);
            dbquery_insert(DB_WALLET_TRANSACTIONS, $data, 'update');

            // set the orders
            dbquery("UPDATE ".DB_WALLET_ORDERS." SET order_paid=:status, order_paid_datestamp=:time, order_paid_user=:user WHERE order_id IN (".str_replace(".", ",", $this->transaction['transaction_oid']).")", [
                ':status' => $data['transaction_status'],
                ':time'   => time(),
                ':user'   => $user_id
            ]);
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Get current user transaction
     *
     * @param $user_id
     *
     * @return bool
     */
    public function getUserTransactions($user_id): bool {
        $user_wallet = $this->wallet->getUserWallet((int)$user_id);
        if ($user_wallet['user_id']) {
            $result = dbquery("SELECT * FROM ".DB_WALLET_TRANSACTIONS." WHERE transaction_user=:uid ".($this->transaction_filter ? " AND ".$this->transaction_filter : "")." ORDER BY transaction_datestamp DESC", [':uid' => $user_wallet['user_id']]);
            if (dbrows($result)) {
                while ($data = dbarray($result)) {
                    $this->transaction = $data;
                    $this->transaction_cdata[$data['transaction_id']] = $data;
                    // get the orders
                    $cresult = dbquery("SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_id IN (".str_replace('.', ',', $this->transaction['transaction_oid']).") ".($this->order_filter ? " AND ".$this->order_filter : "")." ORDER BY order_id");
                    if (dbrows($cresult)) {
                        while ($cdata = dbarray($cresult)) {
                            $this->order_cdata[$cdata['order_id']] = $cdata;
                        }
                    }
                }
                return TRUE;
            }
            return FALSE;
        }
        return FALSE;
    }

    /**
     * @return array
     */
    public function transactionData(): array {
        return $this->transaction ?: [];
    }

    /**
     * Creates a new transaction and send email
     *
     * @param bool   $send_email
     * @param string $email_title
     * @param string $email_message
     *
     * @return bool|mixed
     */
    public function save($send_email = TRUE, $email_title = '', $email_message = '') {
        try {

            if ($this->checkTransactionParam()) {

                if ($this->add()) {

                    //add_notice('success', 'Transaction ID has been added #'.$this->transaction['transaction_id']);

                    // order data
                    foreach ($this->order_data as $this->order_info) {
                        if ($this->checkOrderDetails()) {
                            $this->addOrderDetails();
                        }
                    }

                    // Updates order_ids into transaction table
                    $this->set();

                    if ($send_email) {
                        if ($this->send($send_email, $email_title, $email_message)) {
                            return $this->transaction['transaction_id'];
                        }
                    }

                    return $this->transaction['transaction_id'];
                }
            }

            return FALSE;

        } catch (Exception $e) {
            setError(E_CORE_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
        }
        return FALSE;
    }

    /**
     * @return bool
     */
    private function checkTransactionParam() {

        if (!$this->user) {
            fusion_stop("API Error: User ID is required");
            die();
        }

        if ($this->user_ip = fusion_get_user($this->user, 'user_ip')) {
            if (!$this->user_ip) {
                fusion_stop("API Error: User IP could not be found");
                die();
            }
        }

        if (!$this->datestamp) {
            fusion_stop("API Error: Transaction datestamp is required.");
            die();
        }

        if (!$this->checkout_url && $this->require_checkout) {
            fusion_stop("API Error: Transaction url is required.");
            die();
        }

        if (empty($this->order_data)) {
            fusion_stop("API Error: Transaction orders items are empty.");
            die();
        }

        if (empty($this->currency)) {
            fusion_stop("API Error: Transaction currency are not set.");
            die();
        }

        return TRUE;
    }

    /**
     * Add new transaction record
     *
     * @return FALSE|int
     */
    private function add() {

        $this->transaction = [
            'transaction_id'        => 0,
            'transaction_ref'       => $this->wallet->getTransactionReference(),
            'transaction_number'    => $this->wallet->get_RandomString(),
            'transaction_user'      => (int)$this->user,
            'transaction_currency'  => $this->currency,
            'transaction_ip'        => $this->user_ip,
            'transaction_datestamp' => $this->datestamp,
            'transaction_file'      => $this->checkout_url,
        ];

        $id = dbquery_insert(DB_WALLET_TRANSACTIONS, $this->transaction, 'save', ["keep_session" => TRUE]);
        $this->transaction['transaction_id'] = $id;

        return $id;
    }

    /**
     * @return bool
     */
    private function checkOrderDetails() {

        if (!isset($this->order_info['order_title'])) {
            fusion_stop("API Error: Order title cannot be empty.");
            die();
        }
        if (!isset($this->order_info['order_description'])) {
            fusion_stop("API Error: Order description cannot be empty.");
            die();
        }
        if (!isset($this->order_info['order_item_id'])) {
            fusion_stop("API Error: Order item id could not be found.");
            die();
        }
        if (!$this->order_info['order_item_type']) {
            fusion_stop("API Error: Order item type could not be found");
            die();
        }
        if (!$this->order_info['order_item_quantity']) {
            fusion_stop("API Error: Order item quantity could not be found");
            die();
        }
        return TRUE;
    }

    /**
     * Requires order data
     */
    private function addOrderDetails() {

        $order_data = [
            "order_id"                  => 0,
            "order_ref"                 => $this->transaction["transaction_number"],
            "order_tid"                 => (int)$this->transaction["transaction_id"],
            "order_user"                => (int)$this->transaction["transaction_user"],
            "order_title"               => $this->order_info["order_title"],
            "order_description"         => $this->order_info["order_description"],
            "order_datestamp"           => $this->datestamp,
            "order_paid"                => ($this->order_info["order_paid"] ? 1 : 0),
            "order_paid_datestamp"      => ($this->order_info["order_paid"] ? $this->datestamp : 0),
            "order_paid_user"           => ($this->order_info["order_paid_user"] ? $this->order_info["order_paid_user"] : 0),
            "order_completed"           => ($this->order_info["order_completed"] ? 1 : 0),
            "order_completed_datestamp" => ($this->order_info["order_completed"] ? $this->datestamp : 0),
            "order_completed_user"      => ($this->order_info["order_completed_user"] ? $this->order_info["order_completed_user"] : 0),
            "order_item_id"             => (int)$this->order_info["order_item_id"],
            "order_item_type"           => $this->order_info["order_item_type"],
            "order_item_value"          => (float)number_format($this->order_info["order_item_value"], 4),
            "order_item_quantity"       => (float)number_format($this->order_info["order_item_quantity"], 4),
            "order_total"               => (float)number_format($this->order_info["order_item_value"] * $this->order_info["order_item_quantity"], 4),
            "order_currency"            => $this->order_info["order_currency"],
            "order_item_cycle"          => $this->order_info["order_item_cycle"],
            "order_item_interval"       => $this->order_info["order_item_interval"],
            "order_options"             => $this->order_info["order_options"],
        ];

        $order_id = dbquery_insert(DB_WALLET_ORDERS, $order_data, "save", ["keep_session" => TRUE]);

        $order_data["order_id"] = $order_id;

        $this->order_cdata[$order_id] = $order_data;

        $this->order_ids[$order_id] = $order_id;

        $this->total = $order_data['order_total'] + $this->total;

    }

    private function set() {
        $this->transaction["transaction_oid"] = implode(',', $this->order_ids);
        $sql = "UPDATE ".DB_WALLET_TRANSACTIONS." SET transaction_oid='".$this->transaction["transaction_oid"]."', transaction_amount='$this->total', transaction_item_total='$this->total' WHERE transaction_id='".(int)$this->transaction['transaction_id']."'";
        dbquery($sql);
    }

    /**
     * Send email of the bill to the user
     *
     * @param bool   $send_mail
     * @param string $subject
     * @param string $description
     *
     * @return bool
     * @throws Exception
     */
    public function send($send_mail = TRUE, $subject = '', $description = '') {
        $settings = fusion_get_settings();
        $locale = fusion_get_locale();

        $user = $this->wallet->getUserWallet($this->user);
        $name = $user['first_name'].' '.$user['last_name'];
        $merchant_email = fusion_get_settings('siteemail');

        if (!empty($this->order_cdata)) {

            $html = Template::getInstance('wallet-receipt');
            $html->set_template(WALLET.'templates/bills/invoice-email.html');
            $html->set_tag('name', $name);
            $html->set_tag('logo', $settings['siteurl'].'images/icon.png');
            $html->set_tag('order_total', '$'.number_format($this->total, 2).' USD');
            $html->set_tag('order_datestamp', showdate('shortdate', $this->datestamp));
            $html->set_tag('merchant_email', '<a href="mailto:'.$merchant_email.'">'.hide_email($merchant_email).'</a>');
            $html->set_tag('discord_link', '<a href="https://discord.gg/DhPANe9">Discord Server</a>');
            $html->set_tag('browser_link', '<a href="">View it in your browser.</a>');
            $html->set_tag('sitename', '<a href="https://www.php-fusion.co.uk">PHPFusion, Inc.</a>');
            $html->set_tag('address', '2nd Floor, Block 1, Unit 7 Metro Town, Jalan Bunga Ulam Raja, Off Tuaran Road, 88300, Kota Kinabalu, Sabah, Malaysia');
            $html->set_tag('source_program', 'PHP Fusion');
            $html->set_tag('bill_no', $this->transaction['transaction_number']);
            $html->set_tag('transaction_ref', '#'.$this->transaction['transaction_ref']);
            $html->set_block('wallet_link', ['link' => fusion_get_settings('siteurl').'infusions/wallet/payment.php?ref='.$this->transaction['transaction_ref']]);

            if (empty($description)) {
                $description = 'Thank you for your recent order!';
            }

            $html->set_block('message', [
                'name'  => $name,
                'title' => $description,
            ]);

            foreach ($this->order_cdata as $order_id => $order_data) {
                $html->set_block('wallet_order', [
                    'order_item'   => $order_data['order_title'].'<br/><small>'.$order_data['order_description'].'</small>',
                    'order_amount' => '$'.number_format($order_data['order_total'], 2).' USD'
                ]);
            }

            $toemail = $user['email'];
            $fromname = fusion_get_settings('sitename');
            $fromemail = fusion_get_settings('siteemail');
            if (empty($subject)) {
                $subject = 'Invoice from PHPFusion (#'.$this->transaction['transaction_number'].')';
            }
            $message = $html->get_output();
            if (!$toemail) {
                setError(E_USER_NOTICE, 'Sendmail error: Wallet email not present for '.$user['first_name'], 'wallet-transaction.php', '387');
            }
            if (!$fromname) {
                set_error(E_USER_NOTICE, 'Sendmail error: Sitename not present', 'wallet-transaction.php', '387');
            }
            if (!$fromemail) {
                set_error(E_USER_NOTICE, 'Sendmail error: Site email not present', 'wallet-transaction.php', '387');
            }
            if (!$message) {
                set_error(E_USER_NOTICE, 'Sendmail error: Email message error or not present', 'wallet-transaction.php', '387');
            }

            if ($send_mail) {
                require_once CLASSES.'PHPMailer/PHPMailer.php';
                require_once CLASSES.'PHPMailer/Exception.php';
                require_once CLASSES.'PHPMailer/SMTP.php';
                $mail = new PHPMailer();
                if (is_file(CLASSES."PHPMailer/language/phpmailer.lang-".$locale['phpmailer'].".php")) {
                    $mail->setLanguage($locale['phpmailer'], CLASSES."PHPMailer/language/");
                } else {
                    $mail->setLanguage("en", CLASSES."PHPMailer/language/");
                }
                if (!$settings['smtp_host']) {
                    $mail->isMAIL();
                } else {
                    $mail->isSMTP();
                    $mail->Host = $settings['smtp_host'];
                    $mail->Port = $settings['smtp_port'];
                    $mail->SMTPAuth = $settings['smtp_auth'] ? TRUE : FALSE;
                    $mail->Username = $settings['smtp_username'];
                    $mail->Password = $settings['smtp_password'];
                }
                $mail->CharSet = $locale['charset'];
                $mail->From = $fromemail;
                $mail->FromName = $fromname;
                $mail->addAddress($toemail, $name);
                $mail->addCC('sales.phpfusion@gmail.com', 'Transaction Notification from Main Site');
                //$mail->addCC('management@php-fusion.co.uk', 'Transaction Notification from Main Site');
                $mail->addReplyTo($fromemail, $fromname);
                $mail->isHTML(TRUE);
                $mail->Subject = $subject;
                $mail->Body = $message;
                //$mail->AddEmbeddedImage(IMAGES.'icon.png', 'logo');
                if (!$mail->send()) {
                    $mail->ErrorInfo;
                    $mail->clearAllRecipients();
                    $mail->clearReplyTos();
                    set_error(E_USER_NOTICE, 'Sendmail error to '.$toemail, 'wallet-transaction.php', '422');
                    return FALSE;
                } else {
                    $mail->clearAllRecipients();
                    $mail->clearReplyTos();

                    return TRUE;
                }
            }
            //} else {
            //    $modal = openmodal('aprev', 'Preview Cron Invoice', ['class' => 'modal-lg']);
            //    $modal .= $html->get_output();
            //    $modal .= closemodal();
            //    add_to_footer($modal);
            //}
        } else {
            set_error(E_USER_NOTICE, 'Sendmail error due to empty orders', 'wallet-transaction.php', '438');
        }
        return FALSE;
    }

    /**
     * Get the transaction id
     *
     * @param int $transaction_id
     *
     * @return bool
     */
    public function get($transaction_id = 0) {
        if (isnum($transaction_id) && $transaction_id > 0) {
            $result = dbquery("SELECT * FROM ".DB_WALLET_TRANSACTIONS." WHERE transaction_id=:tid", [':tid' => $transaction_id]);
            if (dbrows($result)) {
                $this->transaction = dbarray($result);
                $this->transaction_cdata[$this->transaction['transaction_id']] = $this->transaction;
                $this->user = $this->transaction['transaction_user'];
                $cresult = dbquery("SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_id IN (".str_replace('.', ',', $this->transaction['transaction_oid']).") ORDER BY order_id");
                if (dbrows($cresult)) {
                    while ($cdata = dbarray($cresult)) {
                        $this->order_cdata[$cdata['order_id']] = $cdata;
                    }
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    /**
     * @param int $payment_status
     * @param int $payment_datestamp
     * @param int $transaction_user
     *
     * @return bool
     */
    public function setPayment($payment_status = TRANSACTION_PAID, $payment_datestamp = TIME, $transaction_user = 0) {

        if (in_array($payment_status, [TRANSACTION_PAID, TRANSACTION_FAILED]) && isnum($payment_datestamp) && isnum($transaction_user) && !empty($transaction_user) && $this->transaction["transaction_id"]) {
            // payment can only be logged once.
            if (dbcount("(transaction_status)", DB_WALLET_TRANSACTIONS, "transaction_paid_user=0 AND transaction_id=:id", [":id" => (int)$this->transaction["transaction_id"]])) {
                dbquery("UPDATE ".DB_WALLET_TRANSACTIONS." SET transaction_status=:status, transaction_paid_datestamp=:datestamp, transaction_paid_user=:user WHERE transaction_id=:tid", [
                    ":tid"       => (int)$this->transaction["transaction_id"],
                    ":status"    => $payment_status,
                    ":datestamp" => $payment_datestamp,
                    ":user"      => $transaction_user
                ]);
                dbquery("UPDATE ".DB_WALLET_ORDERS." SET order_paid=:status, order_paid_datestamp=:datestamp, order_paid_user=:user WHERE order_id IN ('".implode("','", array_keys($this->order_cdata))."')", [
                    ":status"    => $payment_status,
                    ":datestamp" => $payment_datestamp,
                    ":user"      => $transaction_user
                ]);
                return TRUE;
            }
            return FALSE;
        } else {
            set_error(E_USER_ERROR, "API Error: Payment status could not be updated due to errors", "wallet_transaction.class.php", "543");
        }
        return FALSE;
    }

    /**
     * Get a wallet transaction with transaction num
     *
     * @param $transaction_num
     *
     * @return bool
     * @throws \Exception
     */
    public function getNumber($transaction_num) {
        if (!empty($transaction_num)) {
            $transaction_num = stripinput($transaction_num);
            $result = dbquery("SELECT * FROM ".DB_WALLET_TRANSACTIONS." WHERE transaction_number=:refcode", [':refcode' => $transaction_num]);
            if (!dbrows($result)) {
                throw new \Exception('Transaction is not found');
            }
            $this->transaction = dbarray($result);
            $this->transaction_cdata[$this->transaction['transaction_id']] = $this->transaction;
            $this->user = $this->transaction['transaction_user'];
            $cresult = dbquery("SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_id IN (".str_replace('.', ',', $this->transaction['transaction_oid']).") ORDER BY order_id");
            if (dbrows($cresult)) {
                while ($cdata = dbarray($cresult)) {
                    $this->order_cdata[$cdata['order_id']] = $cdata;
                }
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Get a wallet transaction with a reference
     *
     * @param $transaction_ref
     *
     * @return bool
     */
    public function getRef($transaction_ref) {
        if (!empty($transaction_ref)) {

            $transaction_ref = stripinput($transaction_ref);


            $result = dbquery("SELECT * FROM ".DB_WALLET_TRANSACTIONS." WHERE transaction_ref=:refcode", [':refcode' => $transaction_ref]);
            if (!dbrows($result)) {
                set_error(E_USER_ERROR, "Transaction is not found", "wallet_transaction.class.php", "590");
            }

            $this->transaction = dbarray($result);

            $this->transaction_cdata[$this->transaction['transaction_id']] = $this->transaction;

            $this->user = $this->transaction['transaction_user'];

            $cresult = dbquery("SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_id IN (".str_replace('.', ',', $this->transaction['transaction_oid']).") ORDER BY order_id");
            if (dbrows($cresult)) {
                while ($cdata = dbarray($cresult)) {
                    $this->order_cdata[$cdata['order_id']] = $cdata;
                }
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Get order compiled data
     *
     * @return array
     */
    public function orderData() {
        return $this->order_cdata;
    }

    public function showInvoice() {
        $user = Wallet::getInstance()->getUserWallet($this->transaction['transaction_user']);

        //if ($user['user_id'] == fusion_get_userdata('user_id')) {
        // we will need a checkout design
        $html = Template::getInstance('wallet-invoice');
        $html->set_css(WALLET.'templates/css/bill.css');
        $order_item = [];
        $merchant_email = fusion_get_settings('siteemail');
        $name = $user['first_name'].' '.$user['last_name'];
        $html->set_tag('logo', IMAGES.'icon.svg');
        $html->set_tag('name', $name);
        $html->set_tag('order_total', '$'.number_format($this->transaction['transaction_amount'], 2).' USD');
        $html->set_tag('order_datestamp', showdate('shortdate', $this->transaction['transaction_datestamp']));
        $html->set_tag('merchant_email', '<a href="mailto:'.$merchant_email.'">'.hide_email($merchant_email).'</a>');
        //$html->set_tag('discord_link', '<a href="https://discord.gg/DhPANe9">Discord Server</a>');
        $html->set_tag('order_method', $this->transaction['transaction_method']);
        $html->set_tag('browser_link', '<a href="'.INFUSIONS.'wallet/payment.php?ref='.$this->transaction['transaction_ref'].'">View it in your browser.</a>');
        $html->set_tag('sitename', '<a href="https://www.php-fusion.co.uk">PHPFusion, Inc.</a>');
        $html->set_tag('address', '2nd Floor, Block 1, Unit 7 Metro Town, Jalan Bunga Ulam Raja, Off Tuaran Road, 88300, Kota Kinabalu, Sabah, Malaysia');
        $html->set_tag('bill_no', $this->transaction['transaction_number']);
        $html->set_tag('transaction_ref', $this->transaction['transaction_ref']);
        //$html->set_tag('order_method', $this->getTransactionMethodImage($this->transaction['transaction_method']));
        if (!empty($this->order_cdata)) {
            foreach ($this->order_cdata as $order) {
                $html->set_block('wallet_order', [
                    'order_item'   => "<strong>".$order['order_title'].'</strong><br/><small>'.$order['order_description'].'</small>',
                    'order_amount' => '$'.number_format($order['order_total'], 2)
                ]);
            }

        }
        if ($this->transaction['transaction_status']) {
            switch ($this->transaction['transaction_status']) {
                default:
                case 2:
                    $html->set_block('payment_error');
                    break;
                case 1:
                    $html->set_block('payment_success', [
                        'order_total' => '$'.number_format($this->transaction['transaction_amount'], 2).' USD'
                    ]);
                    break;
            }

            $html->set_block('footer', [
                'text' => 'Thank you for your order. If your order is not activated (if applies) please contact the administrator.',
            ]);

            $html->set_template(WALLET.'templates/bills/receipt.html');
        }

        if (!$this->transaction['transaction_status']) {
            $html->set_block('footer', [
                'text' => 'We will deliver your order as soon as possible after your payment.',
            ]);
            $html->set_tag('source_program', 'PHPFusion Wallet');
            $html->set_block('wallet', [
                'display' => display_wallet([
                    'transaction_ref' => $this->transaction['transaction_ref'],
                    'order_currency'  => 'USD',
                    'delimiter'       => 2,
                    'label'           => FALSE,
                    'reverse_display' => FALSE,
                    'no_credits'      => FALSE, // options for disabling credits driver from the payment form
                    'credit_only'     => FALSE, // set to true if only coin payment options applicable.
                    'items'           => $order_item
                ])
            ]);
            $html->set_template(WALLET.'templates/bills/invoice.html');
        }

        return $html->get_output();
    }

    /**
     * Disable the transaction
     *
     * @param $transaction_id
     *
     * @return bool
     */
    public function disable($transaction_id = 0) {
        if (isnum($transaction_id) && dbcount("(transaction_id)", DB_WALLET_TRANSACTIONS, "transaction_id=:id", [':id' => (int)$transaction_id])) {
            dbquery("UPDATE ".DB_WALLET_TRANSACTIONS." SET transaction_status=3 WHERE transaction_id=:id", [
                ':id' => (int)$transaction_id
            ]);
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Mark the order as completed delivery
     * Used by delivery files
     *
     * @param     $order_id
     * @param     $user_id
     * @param int $timestamp
     *
     */
    public function markOrderCompleted($order_id, $user_id, $timestamp = TIME) {
        $arr = [
            'order_id'                  => $order_id,
            'order_completed'           => 1,
            'order_completed_user'      => $user_id,
            'order_completed_datestamp' => $timestamp
        ];

        $this->completed_orders[] = $order_id;

        dbquery_insert(DB_WALLET_ORDERS, $arr, 'update', ['keep_session' => TRUE]);

    }

    /**
     * Add to cache all completed orders
     * Used by delivery files
     *
     * @param $order_id
     */
    public function addCompletedOrder($order_id) {
        $this->completed_orders[] = $order_id;
    }

    /**
     * Get all completed orders
     * Used by Payment Drivers
     *
     * @return array
     */
    public function getCompletedOrders() {
        return $this->completed_orders;
    }

    /**
     * Market transaction as completed delivery
     *
     * @param int $transaction_next
     * @param int $transaction_interval
     */
    public function completeTransaction($transaction_next = 0, $transaction_interval = 0) {

        if ($orders = $this->getOrders()) {

            $orders_ids = array_keys($orders);

            $diff = array_diff_assoc($orders_ids, $this->completed_orders);

            // Ensure all orders are completed before marking this
            if (empty($diff)) {
                // wrong
                $transaction_data = $this->getTransaction();
                $transaction_data = flatten_array($transaction_data);

                $arr = [
                    "transaction_id"       => $transaction_data["transaction_id"],
                    "transaction_status"   => 1,
                    "transaction_next"     => $transaction_next,
                    "transaction_interval" => $transaction_interval,
                ];

                dbquery_insert(DB_WALLET_TRANSACTIONS, $arr, "update");

            }
            //else {
            //    fusion_stop("Transaction delivery could not be completed.");
            //}
        }
    }

    /**
     * @return array
     */
    public function getOrders() {
        return $this->order_cdata ?: [];
    }

    /**
     * @return array
     */
    public function getTransaction() {
        return $this->transaction_cdata ?: [];
    }

    private function getTransactionMethodImage($method) {
        if ($method != 'credit') {
            $gateway = new Gateways();
            $method = $gateway->getPaymentMethods($method);
            return $method['pay_image'];
        }
        return '<img class="icon-sm" src="'.WALLET.'images/wallet.png" alt="Fusion Wallet"></i> <strong>Fusion</strong>Wallet';
    }

}
