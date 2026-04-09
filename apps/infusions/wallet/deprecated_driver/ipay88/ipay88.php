<?php

namespace PHPFusion\Infusions\Wallet\Drivers\IPay88;

/**
 * Integrate Ipay88 (Malaysia) payment gateway system.
 *
 * @author Leow Kah Thong <http://kahthong.com>
 * @copyright Leow Kah Thong 2012
 * @version 2.0
 */
class Ipay88 {

    // Payment methods, please view technical spec for latest update.
    public static $paymentMethods = array(
        54 => array('Alipay', 'USD'),
        8 => array('Alliance Online Transfer', 'MYR'),
        10 => array('AmBank', 'MYR'),
        21 => array('China Union Pay', 'MYR'),
        20 => array('CIMB Clicks', 'MYR'),
        39 => array('Credit Card', 'AUD'),
        37 => array('Credit Card', 'CAD'),
        41 => array('Credit Card', 'EUR'),
        35 => array('Credit Card', 'GBP'),
        42 => array('Credit Card', 'HKD'),
        46 => array('Credit Card', 'IDR'),
        45 => array('Credit Card', 'INR'),
        2 => array('Credit Card', 'MYR'),
        40 => array('Credit Card', 'MYR'), // For multi-currency only
        47 => array('Credit Card', 'PHP'),
        38 => array('Credit Card', 'SGD'),
        36 => array('Credit Card', 'THB'),
        50 => array('Credit Card', 'TWD'),
        25 => array('Credit Card', 'USD'),
        16 => array('FPX', 'MYR'),
        15 => array('Hong Leong Bank Transfer', 'MYR'),
        6 => array('Maybank2U', 'MYR'),
        23 => array('Meps Cash', 'MYR'),
        17 => array('Mobile Money', 'MYR'),
        32 => array('Payeasy', 'PHP'),
        65 => array('PayPal', 'AUD'),
        63 => array('PayPal', 'CAD'),
        66 => array('PayPal', 'EUR'),
        61 => array('PayPal', 'GBP'),
        67 => array('PayPal', 'HKD'),
        48 => array('PayPal', 'MYR'),
        56 => array('PayPal', 'PHP'),
        64 => array('PayPal', 'SGD'),
        62 => array('PayPal', 'THB'),
        68 => array('PayPal', 'TWD'),
        33 => array('PayPal', 'USD'),
        53 => array('Paysbuy (Credit Card only)', 'THB'),
        52 => array('Paysbuy (E-wallet & Counter Services only)', 'THB'),
        14 => array('RHB', 'MYR'),
    );
    public static $paymentUrl               = 'https://www.mobile88.com/epayment/entry.asp';
    public static $requeryUrl               = 'https://www.mobile88.com/epayment/enquiry.asp';
    public static $refererHost              = 'www.mobile88.com';  // Without scheme (http/https).
    public static $recurringUrlSubscription = 'https://www.ipay88.com/recurringpayment/webservice/RecurringPayment.asmx/Subscription';
    public static $recurringUrlTermination  = 'https://www.ipay88.com/recurringpayment/webservice/RecurringPayment.asmx/Termination';
    private $merchantKey = '';
    const TRANSACTION_TYPE_PAYMENT                = 'payment';
    const TRANSACTION_TYPE_RECURRING_SUBSCRIPTION = 'recurring_subscription';
    const TRANSACTION_TYPE_RECURRING_TERMINATION  = 'recurring_termination';
    private $transactionType = '';
    // Details to be sent to IPay88 for payment request.
    private $paymentRequest = array(
        'MerchantCode' => '',       // Merchant code assigned by iPay88. (varchar 20)
        'PaymentId'    => '',       // (Optional) (int)
        'RefNo'        => '',       // Unique merchant transaction number / Order ID (Retry for same RefNo only valid for 30 mins). (varchar 20)
        'Amount'       => '',       // Payment amount with two decimals.
        'Currency'     => '',       // (varchar 5)
        'ProdDesc'     => '',       // Product description. (varchar 100)
        'UserName'     => '',       // Customer name. (varchar 100)
        'UserEmail'    => '',       // Customer email.  (varchar 100)
        'UserContact'  => '',       // Customer contact.  (varchar 20)
        'Remark'       => '',       // (Optional) Merchant remarks. (varchar 100)
        'Lang'         => 'UTF-8',  // (Optional) Encoding type:- ISO-8859-1 (English), UTF-8 (Unicode), GB2312 (Chinese Simplified), GD18030 (Chinese Simplified), BIG5 (Chinese Traditional)
        'Signature'    => '',       // SHA1 signature.
        'ResponseURL'  => '',       // (Optional) Payment response page.
    );
    /* Return response from iPay88 for normal payments:
     * - MerchantCode -
     * - PaymentId    - (Optional)
     * - RefNo        -
     * - Amount       -
     * - Currency     -
     * - Remark       - (Optional)
     * - TransId      - (Optional) IPay88 transaction Id.
     * - AuthCode     - (Optional) Bank's approval code.
     * - Status       - Payment status:- 1 - Success, 0 - Failed.
     * - ErrDesc      - (Optional) Payment status description.
     * - Signature    -
     */
    // Details to be sent to iPay88 for recurring subscription payment request.
    private $recurringSubscriptionRequest = array(
        'MerchantCode'     => '',  // Merchant code assigned by iPay88. (varchar 20)
        'RefNo'            => '',  // Unique merchant transaction number / Order ID. (varchar 20)
        'FirstPaymentDate' => '',  // (ddmmyyyy)
        'Currency'         => '',  // MYR only. (varchar 5)
        'Amount'           => '',  // Payment amount with two decimals.
        'NumberOfPayments' => '',  // (int)
        'Frequency'        => '',  // Frequency type; 1 - Monthly, 2 - Quarterly, 3 - Half-Yearly, 4 - Yearly. (int)
        'Desc'             => '',  // Product description. (varchar 100)
        'CC_Name'          => '',  // Name printed on credit card. (varchar 100)
        'CC_PAN'           => '',  // 16-digit credit card number (Visa/Mastercard). (varchar 16)
        'CC_CVC'           => '',  // 3-digit verification code behind credit card. (varchar 3)
        'CC_ExpiryDate'    => '',  // Credit card expiry date. (mmyyyy)
        'CC_Country'       => '',  // Credit card issuing country. (varchar 100)
        'CC_Bank'          => '',  // Credit card issuing bank. (varchar 100)
        'CC_Ic'            => '',  // Credit card holder IC / Passport number. (varchar 50)
        'CC_Email'         => '',  // Credit card holder email address. (varchar 255)
        'CC_Phone'         => '',  // Credit card phone number. (varchar 100)
        'CC_Remark'        => '',  // (Optional) Remarks. (varchar 100)
        'P_Name'           => '',  // Subscriber name as printed in IC / Passport. (varchar 100)
        'P_Email'          => '',  // Subscriber email address. (varchar 255)
        'P_Phone'          => '',  // Subscriber phone number. (varchar 100)
        'P_Addrl1'         => '',  // Subscriber address line 1. (varchar 100)
        'P_Addrl2'         => '',  // (Optional) Subscriber address line 2. (varchar 100)
        'P_City'           => '',  // Subscriber city. (varchar 100)
        'P_State'          => '',  // Subscriber state. (varchar 100)
        'P_Zip'            => '',  // Subscriber zip code. (varchar 100)
        'P_Country'        => '',  // Subscriber country. (varchar 100)
        'BackendURL'       => '',  // Payment backend response page. (varchar 255)
        'Signature'        => '',  // SHA1 signature. (varchar 100)
    );
    /* Return response from iPay88 for recurring subscripton payments:
     * - MerchantCode     -
     * - RefNo            -
     * - SubscriptionNo   - Unique iPay88 subscription number. 'SubscriptionNo' will be the 'RefNo' that will be returned back to merchant 'BackendURL' when its charged.
     * - FirstPaymentDate -
     * - Amount           -
     * - Currency         -
     * - NumberOfPayments -
     * - Frequency        -
     * - Desc             - (Optional)
     * - Status           - Subscription status:- 1 - Success, 0 - Failed.
     * - ErrDesc          - (Optional)
     */
    // Details to be sent to iPay88 for recurring termination request.
    private $recurringTerminationRequest = array(
        'MerchantCode' => '',  // Merchant code assigned by iPay88. (varchar 20)
        'RefNo'        => '',  // Unique merchant transaction number / Order ID. (varchar 20)
        'Signature'    => '',  // SHA1 signature. (varchar 20) ???
    );
    /* Return response from iPay88 for recurring termination request:
     * - MerchantCode -
     * - RefNo        -
     * - Status       - Subscription status:- 1 - Success, 0 - Failed.
     * - ErrDesc      - (Optional)
     */
    /* Response from iPay88 after recurring payment is charged.
     * - MerchantCode -
     * - PaymentId    - Default to 2 (credit card MYR).
     * - RefNo        - Unique transaction number returned from iPay88.
     *                  This is the 'SubscriptionNo' returned to merchant after subscription of recurring payment.
     *                  Eg:
     *                    S00001701-1 (First recurring payment)
     *                    S00001701-2 (Second recurring payment)
     *                  The returned 'RefNo' will have a hyphen followed by a number to indicate the installment.
     * - Amount       -
     * - Currency     - Default to MYR.
     * - Remark       - (Optional)
     * - TransId      - (Optional) iPay88 transaction ID.
     * - AuthCode     - (Optional) Bank's approval code.
     * - Status       - Payment status:- 1 - Success, 0 - Failed.
     * - ErrDesc      - (Optional)
     * - Signature    -
     */
    /**
     * @access public
     * @param string $merchantCode Merchant code supplied by Ipay88.
     * @param string $merchantKey Merchant key supplied by Ipay88.
     * @param string $transactionType (Optional) Transaction type. Available values are; TRANSACTION_TYPE_PAYMENT, TRANSACTION_TYPE_RECURRING_SUBSCRIPTION, or TRANSACTION_TYPE_RECURRING_TERMINATION.
     */
    public function __construct($merchantCode, $merchantKey, $transactionType = self::TRANSACTION_TYPE_PAYMENT) {
        $this->setField('MerchantCode', "Merchant Code", $merchantCode);
        $this->setMerchantKey($merchantKey);
        $this->setTransactionType($transactionType);
    }
    /**
     * Validate the data given by user according to the rules specified by IPay88 API.
     *
     * @access public
     * @param string $field The field to check.
     * @param string $data  Data supplied by user.
     * @return boolean true if passed validation and vice-versa.
     */
    public function validateField($field, $data) {
        switch ($field) {
            case 'MerchantCode':
            case 'RefNo':
            case 'UserContact':
                if (strlen($data) <= 20) {
                    return true;
                }
                break;
            case 'PaymentId':
            case 'NumberOfPayments':
                if (is_int($data)) {
                    return true;
                }
                break;
            case 'Amount':
                if (preg_match('^[0-9]+\.[0-9]{2}$^', $data)) {
                    return true;
                }
                break;
            case 'Currency':
                if (strlen($data) <= 5) {
                    return true;
                }
                break;
            case 'CC_Email':
            case 'P_Email':
            case 'BackendURL':
                if (strlen($data) <= 255) {
                    return true;
                }
                break;
            case 'ProdDesc':
            case 'UserName':
            case 'UserEmail':
            case 'Remark':
            case 'Desc':
            case 'CC_Name':
            case 'CC_Country':
            case 'CC_Bank':
            case 'CC_Phone':
            case 'CC_Remark':
            case 'P_Name':
            case 'P_Phone':
            case 'P_Addrl1':
            case 'P_Addrl2':
            case 'P_City':
            case 'P_State':
            case 'P_Zip':
            case 'P_Country':
                if (strlen($data) <= 100) {
                    return true;
                }
                break;
            case 'CC_Ic':
                if (strlen($data) <= 50) {
                    return true;
                }
                break;
            case 'Lang':
                if (in_array(strtoupper($data), array('ISO-8859-1', 'UTF-8', 'GB2312', 'GD18030', 'BIG5'))) {
                    return true;
                }
                break;
            case 'Signature':
                if (strlen($data) <= 40) {
                    return true;
                }
                break;
            case 'FirstPaymentDate':
                if (strlen($data) == 8) {
                    return true;
                }
                break;
            case 'CC_ExpiryDate':
                if (strlen($data) == 6) {
                    return true;
                }
                break;
            case 'Frequency':
                if (in_array((int) $data, array(1, 2, 3, 4))) {
                    return true;
                }
                break;
            case 'CC_PAN':
                if (ctype_digit($data) && strlen($data) == 16) {
                    return true;
                }
                break;
            case 'CC_CVC':
                if (ctype_digit($data) && strlen($data) == 3) {
                    return true;
                }
                break;
            case 'MerchantKey':
            case 'ResponseURL':
            case 'TransId':
            case 'AuthCode':
            case 'Status':
            case 'ErrDesc':
            case 'SubscriptionNo':
                return true;
        }
        return false;
    }
    /**
     * @access private
     * @return string Merchant key.
     */
    private function getMerchantKey() {
        return $this->merchantKey;
    }
    /**
     * Get info about payment method.
     *
     * @access public
     * @param int $paymentId Payment method ID.
     * @return array Name and currency of payment method.
     */
    public function getPaymentMethod($paymentId) {
        if (isset(self::$paymentMethods[$paymentId])) {
            list($name, $currency) = self::$paymentMethods[$paymentId];
            return array(
                'name' => $name,
                'currency' => $currency,
            );
        }
    }
    /**
     * Wrapper method to receive response and return status. If transaction was successful, a requery will be done to double-check.
     *
     * @access public
     * @param boolean $requery     (Optional) Whether to requery Ipay88 server for transaction confirmation.
     * @param boolean $return_data (Optional) Whether to return data back.
     * @return array Status of the transaction and processed response.
     */
    public function getResponse($requery = true, $return_data = true) {
        $return = array(
            'status' => '',
            'message' => '',
            'data' => array(),
        );
        $data = $_POST;
        $return['status'] = isset($data['Status']) ? $data['Status'] : false;
        $return['message'] = isset($data['ErrDesc']) ? $data['ErrDesc'] : '';
        if ($requery && $return['status']) {
            $data['_RequeryStatus'] = $this->requery($data);
            if ($data['_RequeryStatus'] != '00') {
                // Requery failed, return empty array.
                $return['status'] = false;
                return $return;
            }
        }
        if ($return_data) {
            $return['data'] = $data;
        }
        return $return;
    }
    /**
     * Return all the fields (normally after setField() method is called).
     * Can be used to populate forms.
     *
     * @access public
     * @return array Payment method fields.
     */
    public function getFields() {
        if ($this->getTransactionType() == self::TRANSACTION_TYPE_PAYMENT) {
            return $this->paymentRequest;
        } else if ($this->getTransactionType() == self::TRANSACTION_TYPE_RECURRING_SUBSCRIPTION) {
            return $this->recurringSubscriptionRequest;
        } else if ($this->getTransactionType() == self::TRANSACTION_TYPE_RECURRING_TERMINATION) {
            return $this->recurringTerminationRequest;
        }
    }
    /**
     * Get payment URL.
     *
     * @access public
     * @return array Payment method fields.
     */
    public function getTransactionUrl() {
        if ($this->getTransactionType() == self::TRANSACTION_TYPE_PAYMENT) {
            return self::$paymentUrl;
        } else if ($this->getTransactionType() == self::TRANSACTION_TYPE_RECURRING_SUBSCRIPTION) {
            return self::$recurringUrlSubscription;
        } else if ($this->getTransactionType() == self::TRANSACTION_TYPE_RECURRING_TERMINATION) {
            return self::$recurringUrlTermination;
        }
    }
    /**
     * Return individual field values.
     *
     * @access public
     * @param string $field Field name.
     * @return string Value of the field. If field name is invalid, returns FALSE.
     */
    public function getField($field) {
        if ($this->getTransactionType() == self::TRANSACTION_TYPE_PAYMENT) {
            return (isset($this->paymentRequest[$field]) ? $this->paymentRequest[$field] : false);
        } else if ($this->getTransactionType() == self::TRANSACTION_TYPE_RECURRING_SUBSCRIPTION) {
            return (isset($this->recurringSubscriptionRequest[$field]) ? $this->recurringSubscriptionRequest[$field] : false);
        } else if ($this->getTransactionType() == self::TRANSACTION_TYPE_RECURRING_TERMINATION) {
            return (isset($this->recurringTerminationRequest[$field]) ? $this->recurringTerminationRequest[$field] : false);
        }
    }

    /**
     * Return the field label for localization support
     *
     * @param $field
     *
     * @return mixed|null
     */
    public function getFieldLabel($field) {
        if (!empty($this->labels[$field])) {
            return $this->labels[$field];
        }
        return NULL;
    }


    /**
     * Get the current transaction type / mode.
     *
     * @access public
     * @return string Transaction type.
     */
    public function getTransactionType() {
        return $this->transactionType;
    }
    /**
     * Change transaction type.
     *
     * @access public
     * @param string $transactionType Transaction type.
     */
    public function setTransactionType($transactionType) {
        if ($transactionType == self::TRANSACTION_TYPE_PAYMENT || $transactionType == self::TRANSACTION_TYPE_RECURRING_SUBSCRIPTION || $transactionType == self::TRANSACTION_TYPE_RECURRING_TERMINATION) {
            $this->transactionType = $transactionType;
        }
    }
    /**
     * Set variable to field. Data supplied will be validated before it is set and any error found will be thrown to user.
     *
     * @access public
     * @param string $field The field name to set.
     * @param string $data  Data supplied by user.
     */
    private $labels = array();

    public function setField($field, $label, $data) {
        if ($this->validateField($field, $data)) {
            switch ($field) {
                case 'Currency':
                case 'Lang':
                    $data = strtoupper($data);
                    break;
            }
            if ($field == 'MerchantCode') {
                $this->paymentRequest[$field] = $data;
                $this->recurringSubscriptionRequest[$field] = $data;
                $this->recurringTerminationRequest[$field] = $data;
            } else {
                if ($this->getTransactionType() == self::TRANSACTION_TYPE_PAYMENT) {
                    $this->paymentRequest[$field] = $data;
                } else if ($this->getTransactionType() == self::TRANSACTION_TYPE_RECURRING_SUBSCRIPTION) {
                    $this->recurringSubscriptionRequest[$field] = $data;
                } else if ($this->getTransactionType() == self::TRANSACTION_TYPE_RECURRING_TERMINATION) {
                    $this->recurringTerminationRequest[$field] = $data;
                }
            }
            $this->labels[$field] = $label;
        } else {
            // Return error message
            $field = "<em>$field</em>";
            $errorMsg = "Failed validation for $field. ";
            switch (strip_tags($field)) {
                case 'MerchantCode':
                case 'RefNo':
                case 'UserContact':
                    $errorMsg .= "$field must not be more than 20 characters in length.";
                    break;
                case 'PaymentId':
                case 'NumberOfPayments':
                    $errorMsg .= "$field must be a number.";
                    break;
                case 'Amount':
                    $errorMsg .= "$field must be a number with 2 decimal points.";
                    break;
                case 'Currency':
                    $errorMsg .= "$field must not be more than 5 characters in length.";
                    break;
                case 'CC_Email':
                case 'P_Email':
                case 'BackendURL':
                    $errorMsg .= "$field must not be more than 255 characters in length.";
                    break;
                case 'ProdDesc':
                case 'UserName':
                case 'UserEmail':
                case 'Remark':
                case 'Desc':
                case 'CC_Name':
                case 'CC_Country':
                case 'CC_Bank':
                case 'CC_Phone':
                case 'CC_Remark':
                case 'P_Name':
                case 'P_Phone':
                case 'P_Addrl1':
                case 'P_Addrl2':
                case 'P_City':
                case 'P_State':
                case 'P_Zip':
                case 'P_Country':
                    $errorMsg .= "$field must not be more than 100 characters in length.";
                    break;
                case 'CC_Ic':
                    $errorMsg .= "$field must not be more than 50 characters in length.";
                    break;
                case 'Lang':
                    $langs = array('ISO-8859-1', 'UTF-8', 'GB2312', 'GD18030', 'BIG5');
                    $errorMsg .= "$field must be either " . implode(', ', $langs) . '.';
                    break;
                case 'Signature':
                    $errorMsg .= "$field must not be more than 40 characters in length.";
                    break;
                case 'FirstPaymentDate':
                    $errorMsg .= "$field must not be 8 characters in length.";
                    break;
                case 'CC_ExpiryDate':
                    $errorMsg .= "$field must not be 6 characters in length.";
                    break;
                case 'Frequency':
                    $errorMsg .= "$field must either 1, 2, 3, or 4 only.";
                    break;
                case 'CC_CVC':
                    $errorMsg .= "$field must digit with 3 characters only.";
                    break;
            }
            trigger_error($errorMsg);
        }
    }
    /**
     * Set merchant key.
     *
     * @access public
     * @param string $merchantKey Private key for merchant.
     */
    public function setMerchantKey($merchantKey) {
        $this->merchantKey = $merchantKey;
    }
    /**
     * Generate signature to be used for transaction.
     *
     * You may verify your signature with online tool provided by iPay88
     * http://www.mobile88.com/epayment/testing/TestSignature.asp
     *
     * @access public
     * @param array $signatureParams (Optional) Fields required to generate signature (MerchantKey is set via setMerchantKey() method). If not passed, will use values that were set earlier.
     * - MerchantCode
     * - RefNo
     * - Amount
     * - Currency
     */
    public function generateSignature($signatureParams = array()) {
        $signature = '';
        if ($signatureParams) {
            $_signatureParams = array();
            if ($this->getTransactionType() == self::TRANSACTION_TYPE_PAYMENT) {
                $_signatureParams = array('MerchantCode', 'RefNo', 'Amount', 'Currency');
            } else if ($this->getTransactionType() == self::TRANSACTION_TYPE_RECURRING_SUBSCRIPTION) {
                $_signatureParams = array('MerchantCode', 'RefNo', 'FirstPaymentDate', 'Currency', 'Amount', 'NumberOfPayments', 'Frequency', 'CC_PAN');
            } else if ($this->getTransactionType() == self::TRANSACTION_TYPE_RECURRING_TERMINATION) {
                $_signatureParams = array('MerchantCode', 'RefNo');
            }
            foreach ($_signatureParams as $val) {
                if (!isset($signatureParams[$val])) {
                    trigger_error('Missing required parameters for signature.');
                    return false;
                }
            }
            foreach ($signatureParams as $key => $val) {
                // Validate parameters for signature.
                if (!$this->validateField($key, $val)) {
                    trigger_error('Invalid parameters for signature.');
                    return false;
                }
                // Some formatting..
                switch ($key) {
                    case 'Amount':
                        // Remove ',' and '.' from amount
                        $signatureParams[$key] = str_replace(',', '', $val);
                        $signatureParams[$key] = str_replace('.', '', $val);
                        break;
                    case 'Currency':
                    case 'Lang':
                        $signatureParams[$key] = strtoupper($val);
                        break;
                }
            }
        } else {
            $signatureParams['MerchantCode'] = $this->getField('MerchantCode');
            $signatureParams['RefNo']        = $this->getField('RefNo');
            $signatureParams['Amount']       = str_replace('.', '', str_replace(',', '', $this->getField('Amount')));
            $signatureParams['Currency']     = $this->getField('Currency');
            if ($this->getTransactionType() == self::TRANSACTION_TYPE_RECURRING_SUBSCRIPTION) {
                $signatureParams['FirstPaymentDate'] = $this->getField('FirstPaymentDate');
                $signatureParams['NumberOfPayments'] = $this->getField('NumberOfPayments');
                $signatureParams['Frequency']        = $this->getField('Frequency');
                $signatureParams['CC_PAN']           = $this->getField('CC_PAN');
            }
        }
        if (!$this->getMerchantKey()) {
            trigger_error('Merchant key is required.');
            return false;
        }
        // Make sure the order is correct.
        if ($this->getTransactionType() == self::TRANSACTION_TYPE_PAYMENT) {
            $signature .= $this->getMerchantKey();
            $signature .= $signatureParams['MerchantCode'];
            $signature .= $signatureParams['RefNo'];
            $signature .= $signatureParams['Amount'];
            $signature .= $signatureParams['Currency'];
        } else if ($this->getTransactionType() == self::TRANSACTION_TYPE_RECURRING_SUBSCRIPTION) {
            $signature .= $signatureParams['MerchantCode'];
            $signature .= $this->getMerchantKey();
            $signature .= $signatureParams['RefNo'];
            $signature .= $signatureParams['FirstPaymentDate'];
            $signature .= $signatureParams['Currency'];
            $signature .= $signatureParams['Amount'];
            $signature .= $signatureParams['NumberOfPayments'];
            $signature .= $signatureParams['Frequency'];
            $signature .= $signatureParams['CC_PAN'];
        } else if ($this->getTransactionType() == self::TRANSACTION_TYPE_RECURRING_TERMINATION) {
            $signature .= $signatureParams['MerchantCode'];
            $signature .= $this->getMerchantKey();
            $signature .= $signatureParams['RefNo'];
        }
        // Hash the signature.
        $signature = base64_encode($this->_hex2bin(sha1($signature)));
        $this->setField('Signature', "Signature", $signature);
    }
    /**
     * Referred from iPay88 technical specification v1.5.2.
     *
     * @access private
     * @param string $source Source string to convert (hexadecimal value).
     * @return string Binary representation of the string.
     */
    private function _hex2bin($source) {
        $bin = '';
        for ($i = 0; $i < strlen($source); $i += 2) {
            $bin .= chr(hexdec(substr($source, $i, 2)));
        }
        return $bin;
    }
    /**
     * Receives response returned from iPay88 server after payment is processed.
     *
     * @access public
     * @param array $response Response returned from IPay88 server after transaction is processed.
     * @return boolean Only returns false for failed transaction. You should only check for false status.
     */
    public function validateResponse($response) {
        // Check referer, must be from www.mobile88.com only.
        // Only valid if payment went through IPay88.
        if (!isset($_SERVER['HTTP_REFERER'])) {
            trigger_error('Invalid request.');
            return false;
        }
        $referer = parse_url($_SERVER['HTTP_REFERER']);
        if ($referer['host'] != self::$refererHost) {
            trigger_error('Referer check failed, mismatch with settings.');
            return false;
        }
        // Re-query to check payment.
        if ($this->requery(array(
                'MerchantCode' => $response['MerchantCode'],
                'RefNo'        => $response['RefNo'],
                'Amount'       => $response['Amount'],
            )) != '00') {
            trigger_error('Requery with server failed to verify transaction.');
            return false;
        }
        // Compare signature.
        if ($this->generateSignature(array(
                'MerchantKey'  => $this->getMerchantKey(),
                'MerchantCode' => $response['MerchantCode'],
                'RefNo'        => $response['RefNo'],
                'Amount'       => $response['Amount'],
                'Currency'     => $response['Currency'],
            )) != trim($response['Signature'])) {
            trigger_error('Failed to verify signature.');
            return false;
        }
        return true;
    }
    /**
     * Check payment status (re-query).
     *
     * @access public
     * @param array $paymentDetails The following variables are required:
     * - MerchantCode (Optional)
     * - RefNo
     * - Amount
     * @return string Possible payment status from iPay88 server:
     * - 00                 - Successful payment
     * - Invalid parameters - Parameters passed is incorrect
     * - Record not found   - Could not find the record.
     * - Incorrect amount   - Amount differs.
     * - Payment fail       - Payment failed.
     * - M88Admin           - Payment status updated by Mobile88 Admin (Fail)
     */
    public function requery($paymentDetails) {
        if (!function_exists('curl_init')) {
            trigger_error('PHP cURL extension is required.');
            return false;
        }
        if (!isset($paymentDetails['MerchantCode'])) {
            $paymentDetails['MerchantCode'] = $this->getField('MerchantCode');
        }
        $curl = curl_init(self::$requeryUrl . '?' . http_build_query($paymentDetails));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = trim(curl_exec($curl));
        curl_close($curl);
        return $result;
    }
}