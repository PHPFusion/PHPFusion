<?php

namespace PHPFusion\Infusions\Wallet\Drivers\FirstData;

/**
 * First Data
 * used to perform the actual api calls
 *
 * @since  1.0
 * @author Vincent Gabriel
 */

/*
 * The MIT License (MIT)
*
* Copyright (c) 2013 - Vincent Gabriel & Jason Gill
*
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*
*/
class FirstDataIPN {

    const LIVE_API_URL = 'https://www4.ipg-online.com/connect/gateway/processing';

    const TEST_API_URL = 'https://api.demo.globalgatewaye4.firstdata.com/transaction/';
    /**
     * @var string - the api username
     */
    protected $username = NULL;
    /**
     * @var string - the api password
     */
    protected $password = NULL;
    /**
     * @var int - api transaction type
     */
    protected $transactionType = '00';
    /**
     *  the error code if one exists
     *
     * @var integer
     */
    protected $errorCode = 0;
    /**
     * the error message if one exists
     *
     * @var string
     */
    protected $errorMessage = '';
    /**
     *  the response message
     *
     * @var string
     */
    protected $response = '';
    /**
     *  the headers returned from the call made
     *
     * @var array
     */
    protected $headers = '';
    /**
     * The response represented as an array
     *
     * @var array
     */
    protected $arrayResponse = array();
    /**
     * All the post fields we will add to the call
     *
     * @var array
     */
    protected $postFields = array();
    /**
     * The api type we are about to call
     *
     * @var string
     */
    protected $apiVersion = 'v11';
    /**
     * The api key id needed for hmac headers
     *
     * @var string
     */
    protected $apiId = '';
    /**
     * The api key needed for hmac headers
     *
     * @var string
     */
    protected $apiKey = '';
    /**
     * @var boolean - set whether we are in a test mode or not
     */
    public static $testMode = false;
    /**
     * Default options for curl.
     */
    public static $CURL_OPTS = array(
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_FRESH_CONNECT  => 1,
        CURLOPT_PORT           => 443,
        CURLOPT_USERAGENT      => 'curl-php',
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_HTTPHEADER     => array('Content-Type: application/json; charset=UTF-8;', 'Accept: application/json'),
    );

    /**
     * Transaction types
     */
    const TRAN_PURCHASE = '00';
    const TRAN_PREAUTH = '01';
    const TRAN_PREAUTHCOMPLETE = '02';
    const TRAN_FORCEDPOST = '03';
    const TRAN_REFUND = '04';
    const TRAN_PREAUTHONLY = '05';
    const TRAN_PAYPALORDER = '07';
    const TRAN_VOID = '13';
    const TRAN_TAGGEDPREAUTHCOMPLETE = '32';
    const TRAN_TAGGEDVOID = '33';
    const TRAN_TAGGEDREFUND = '34';
    const TRAN_CASHOUT = '83';
    const TRAN_ACTIVATION = '85';
    const TRAN_BALANCEINQUIRY = '86';
    const TRAN_RELOAD = '88';
    const TRAN_DEACTIVATION = '89';

    /**
     * Constructor
     *
     * @param string $username - username
     * @param string $password - password
     * @param bool   $debug    - debug mode
     */
    public function __construct($username, $password, $debug = false) {
        $this->username = $username;
        $this->password = $password;
        $this->setTestMode((bool)$debug);
    }

    /**
     * set the api username we are going to user
     *
     * @param string $username - the api username
     *
     * @return object
     */
    public function setUsername($username) {
        $this->username = $username;

        return $this;
    }

    /**
     * set the api password we are going to user
     *
     * @param string $username - the api password
     *
     * @return object
     */
    public function setPassword($password) {
        $this->password = $password;

        return $this;
    }

    /**
     * Return the post data fields as an array
     *
     * @return array
     */
    public function getPostData() {
        return $this->postFields;
    }

    /**
     * Set post fields
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return object
     */
    public function setPostData($key, $value = NULL) {
        if (is_array($key) && !$value) {
            foreach ($key as $k => $v) {
                $this->postFields[$k] = $v;
            }
        } else {
            $this->postFields[$key] = $value;
        }

        return $this;
    }

    /**
     * Set the api version we are going to use
     *
     * @param string $version the new api version
     *
     * @return object
     */
    public function setApiVersion($version) {
        $this->apiVersion = $version;

        return $this;
    }

    /**
     * Set the api id we are going to use for hmac hash
     *
     * @param string $id the new api id
     *
     * @return object
     */
    public function setApiId($id) {
        $this->apiId = $id;

        return $this;
    }

    /**
     * Set the api key we are going to use for hmac hash
     *
     * @param string $key the new api key
     *
     * @return object
     */
    public function setApiKey($key) {
        $this->apiKey = $key;

        return $this;
    }

    /**
     * Set whether we are in a test mode or not
     *
     * @param boolean $value
     *
     * @return void
     */
    public function setTestMode($value) {
        self::$testMode = (bool)$value;
    }

    /**
     * Set transaction type
     *
     * @param int $transactionType
     *
     * @return object
     */
    public function setTransactionType($transactionType) {
        $this->transactionType = $transactionType;

        return $this;
    }

    /**
     * Return transaction type
     *
     * @return int
     */
    public function getTransactionType() {
        return $this->transactionType;
    }

    /**
     * set credit card number
     *
     * @param int $number
     *
     * @return object
     */
    public function setCreditCardNumber($number) {
        $this->setPostData('cc_number', (string)$number);

        return $this;
    }

    /**
     * set credit card type
     *
     * @param int $number
     *
     * @return object
     */
    public function setCreditCardType($type) {
        $this->setPostData('credit_card_type', $type);

        return $this;
    }

    /**
     * Setting Track 1 and Track 2 data allows input from USB credit card swiper
     * For format of track data see: http://www.gae.ucm.es/~padilla/extrawork/magexam1.html
     */

    /**
     * set Track1 data
     *
     * @param string $track
     *
     * @return object
     */
    public function setTrack1($track) {
        $this->setPostData('track1', $track);

        return $this;
    }

    /**
     * set Track2 data
     *
     * @param string $track
     *
     * @return object
     */
    public function setTrack2($track) {
        $this->setPostData('track2', $track);

        return $this;
    }

    /**
     * set credit card holder name
     *
     * @param string $name
     *
     * @return object
     */
    public function setCreditCardName($name) {
        $this->setPostData('cardholder_name', $name);

        return $this;
    }

    /**
     * set credit card expiration date
     *
     * @param int $date
     *
     * @return object
     */
    public function setCreditCardExpiration($date) {
        $this->setPostData('cc_expiry', $date);

        return $this;
    }

    /**
     * set amount
     *
     * @param double $amount
     *
     * @return object
     */
    public function setAmount($amount) {
        $this->setPostData('amount', $amount);

        return $this;
    }

    /**
     * set trans armor token
     *
     * @param string $token
     *
     * @return object
     */
    public function setTransArmorToken($token) {
        $this->setPostData('transarmor_token', $token);

        return $this;
    }

    /**
     * set auth number
     *
     * @param string $number
     *
     * @return object
     */
    public function setAuthNumber($number) {
        $this->setPostData('authorization_num', $number);

        return $this;
    }

    /**
     * set credit card address
     * VerificationStr1 is comprised of the following constituent address values: Street, Zip/Postal Code, City, State/Provence, Country.
     * They are separted by the Pipe Character "|".
     *    Street Address|Zip/Postal|City|State/Prov|Country
     *
     * used for verification
     *
     * @param string $address
     *
     * @return object
     */
    public function setCreditCardAddress($address) {
        $this->setPostData('cc_verification_str1', $address);

        return $this;
    }

    /**
     * set credit card address
     * used for verification, replaces the old cc_verification_str1 with the new address type
     *
     * @param array $address
     *
     * @return object
     */
    public function setCreditCardAddressNew($address) {
        $this->setPostData('address', $address);
    }

    /**
     * set credit card cvv code
     * This is the 0, 3, or 4-digit code on the back of the credit card sometimes called the CVV2 or CVD value.
     *
     * used for verification
     *
     * @param int $cvv
     *
     * @return object
     */
    public function setCreditCardVerification($cvv) {
        $this->setPostData('cc_verification_str2', $cvv);
        $this->setPostData('cvd_presence_ind', 1);

        return $this;
    }

    /**
     * set credit card cavv code
     * This is the 0, 3, or 4-digit code on the back of the credit card sometimes called the CVV2 or CVD value.
     *
     * used for verification
     *
     * @param int $cvv
     *
     * @return object
     */
    public function setCreditCardCAVV($cavv) {
        $this->setPostData('cavv', $cavv);

        return $this;
    }

    /**
     * set credit card zip code
     *
     * used for verification
     *
     * @param int $zip
     *
     * @return object
     */
    public function setCreditCardZipCode($zip) {
        $this->setPostData('zip_code', $zip);

        return $this;
    }

    /**
     * set currency code
     *
     * @param string $code
     *
     * @return object
     */
    public function setCurrency($code) {
        $this->setPostData('currency_code', $code);

        return $this;
    }

    /**
     * set client ip
     *
     * @param string $ip
     *
     * @return object
     */
    public function setClientIp($ip) {
        $this->setPostData('client_ip', $ip);

        return $this;
    }

    /**
     * set client email
     *
     * @param string $email
     *
     * @return object
     */
    public function setClientEmail($email) {
        $this->setPostData('client_email', $email);

        return $this;
    }

    /**
     * set reference number
     *
     * @param int $number
     *
     * @return object
     */
    public function setReferenceNumber($number) {
        $this->setPostData('reference_no', $number);

        return $this;
    }

    /**
     * set transaction tag
     *
     * @param int $number
     *
     * @return object
     */
    public function setTransactionTag($number) {
        $this->setPostData('transaction_tag', $number);

        return $this;
    }

    /**
     * set customerNumber
     *
     * @param string $number
     *
     * @return object
     */
    public function setCustomerReferenceNumber($number) {
        $this->setPostData('customer_ref', $number);

        return $this;
    }

    /**
     * Perform the API call
     *
     * @return string
     */
    public function process() {
        return $this->doRequest();
    }

    /**
     * Makes an HTTP request. This method can be overriden by subclasses if
     * developers want to do fancier things or use something other than curl to
     * make the request.
     *
     * @param CurlHandler optional initialized curl handle
     *
     * @return String the response text
     */
    protected function doRequest($ch = NULL) {
        if (!$ch) {
            $ch = curl_init();
        }
        $opts = self::$CURL_OPTS;
        $content = json_encode(array_merge($this->getPostData(), array('gateway_id' => $this->username, 'password' => $this->password, 'transaction_type' => $this->transactionType)));
        $opts[CURLOPT_POSTFIELDS] = $content;
        $opts[CURLOPT_URL] = self::$testMode ? self::TEST_API_URL.$this->apiVersion : self::LIVE_API_URL.$this->apiVersion;
        if ($this->apiVersion >= 'v12') {
            $gge4_date = gmdate('Y-m-d\TH:i:s').'Z'; // ISO8601
            $content_digest = sha1($content);
            $hmac = base64_encode(hash_hmac('sha1',
                "POST\n".
                "application/json; charset=UTF-8;\n".
                $content_digest."\n".
                $gge4_date."\n".
                "/transaction/".$this->apiVersion,
                $this->apiKey, true));
            $headers = array(
                'X-GGe4-Content-SHA1: '.$content_digest,
                'X-GGe4-Date: '.$gge4_date,
                'Authorization: GGE4_API '.$this->apiId.':'.$hmac,
                'Content-Length: '.strlen($content)
            );
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // set options
        curl_setopt_array($ch, $opts);

        // execute
        $this->setResponse(curl_exec($ch));
        $this->setHeaders(curl_getinfo($ch));

        // fetch errors
        $this->setErrorCode(curl_errno($ch));
        $this->setErrorMessage(curl_error($ch));

        // Convert response to array
        $this->convertResponseToArray();

        // We need to make sure we do not have any errors
        if (!$this->getArrayResponse()) {
            // We have an error
            $returnedMessage = $this->getResponse();

            // Pull out the error code from the message
            preg_match('/\(([0-9]+)\)/', $returnedMessage, $matches);

            $errorCodes = $this->getResponseCodes();

            if (isset($matches[1])) {
                // If it's not 00 then there was an error
                $this->setErrorCode(isset($errorCodes[$matches[1]]) ? $matches[1] : 42);
                $this->setErrorMessage(isset($errorCodes[$matches[1]]) ? $errorCodes[$matches[1]] : $errorCodes[42]);
            } else {
                $headers = $this->getHeaders();
                $this->setErrorCode($headers['http_code']);
                $this->setErrorMessage($returnedMessage);
            }
        } else {
            // for cases when exact_resp_code is 00, but bank response code is not successful
            if ($this->isError()) {
                $code = $this->getBankResponseCode();
                $codes = $this->getBankResponseCodes();
                $error = isset($codes[$code]) ? $codes[$code]['name'] : NULL;

                $this->setErrorMessage($error);
                $this->setErrorCode(42);
            } else {
                // We have a json string, empty error message
                $this->setErrorMessage('');
                $this->setErrorCode(0);
            }
        }

        // close
        curl_close($ch);

        // Reset
        $this->postFields = array();

        return $this->getResponse();
    }

    /**
     * Did we encounter an error?
     *
     * @return boolean
     */
    public function isError() {
        $headers = $this->getHeaders();
        $response = $this->getArrayResponse();
        // First make sure we got a valid response
        if (!in_array($headers['http_code'], array(200, 201, 202))) {
            return true;
        }

        // Make sure the response does not have error in it
        if (!$response || !count($response)) {
            return true;
        }

        // Do we have an error code
        if ($this->getErrorCode() > 0) {
            return true;
        }

        // bank response type
        if ($this->getBankResponseType() && $this->getBankResponseType() != 'S') {
            return true;
        }

        // exact response type
        if ($this->getExactResponseCode() > 0) {
            return true;
        }

        // No error
        return false;
    }

    /**
     * Was the last call successful
     *
     * @return boolean
     */
    public function isSuccess() {
        return !$this->isError() ? true : false;
    }

    /**
     * Check if transaction was approved
     *
     * @return int
     */
    public function isApproved() {
        return $this->getValueByKey($this->getArrayResponse(), 'transaction_approved');
    }

    /**
     * Get Transaction Tag
     *
     * @return int
     */
    public function getTransactionTag() {
        return $this->getValueByKey($this->getArrayResponse(), 'transaction_tag');
    }

    /**
     * Get transaction record/receipt
     *
     * @return string
     */
    public function getTransactionRecord() {
        return $this->getValueByKey($this->getArrayResponse(), 'ctr');
    }

    /**
     * Get transaction auth number
     *
     * @return string
     */
    public function getAuthNumber() {
        return $this->getValueByKey($this->getArrayResponse(), 'authorization_num');
    }

    /**
     * Get transaction transarmor token
     *
     * @return string
     */
    public function getTransArmorToken() {
        return $this->getValueByKey($this->getArrayResponse(), 'transarmor_token');
    }

    /**
     * Get transaction bank response code
     *
     * @return int
     */
    public function getBankResponseCode() {
        return $this->getValueByKey($this->getArrayResponse(), 'bank_resp_code');
    }

    /**
     * Get transaction bank response message
     *
     * @return string
     */
    public function getBankResponseMessage() {
        return $this->getValueByKey($this->getArrayResponse(), 'bank_message');
    }

    /**
     * Get transaction Exact response code
     *
     * @return int
     */
    public function getExactResponseCode() {
        return $this->getValueByKey($this->getArrayResponse(), 'exact_resp_code');
    }

    /**
     * Get transaction Exact response message
     *
     * @return string
     */
    public function getExactResponseMessage() {
        return $this->getValueByKey($this->getArrayResponse(), 'exact_message');
    }

    /**
     * Get the Address Verification System Response.
     *
     * @return string
     */
    public function getAvs() {
        return $this->getValueByKey($this->getArrayResponse(), 'avs');
    }

    /**
     * Get transaction bank response comment
     *
     * @return string
     */
    public function getBankResponseComments() {
        $code = $this->getBankResponseCode();
        $codes = $this->getBankResponseCodes();

        return isset($codes[$code]) ? $codes[$code]['comments'] : NULL;
    }

    /**
     * Get transaction bank response type
     *  S = Successful Response Codes
     *    R = Reject Response Codes
     *    D = Decline Response Codes
     *
     * @return string
     */
    public function getBankResponseType() {
        $code = $this->getBankResponseCode();
        $codes = $this->getBankResponseCodes();

        return isset($codes[$code]) ? $codes[$code]['response'] : NULL;
    }

    /**
     * Set the array response value
     *
     * @param array $value
     *
     * @return void
     */
    public function setArrayResponse($value) {
        $this->arrayResponse = $value;
    }

    /**
     * Return the array representation of the last response
     *
     * @return array
     */
    public function getArrayResponse() {
        return $this->arrayResponse;
    }

    /**
     * Return the response represented as string
     *
     * @return array
     */
    protected function convertResponseToArray() {
        if ($this->getResponse()) {
            $this->setArrayResponse(json_decode($this->getResponse()));
        }

        return $this->getArrayResponse();
    }

    /**
     * Set the response
     *
     * @param mixed the response returned from the call
     *
     * @return facebookLib object
     */
    protected function setResponse($response = '') {
        $this->response = $response;

        return $this;
    }

    /**
     * Get the response data
     *
     * @return mixed the response data
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * Set the headers
     *
     * @param array the headers array
     *
     * @return facebookLib object
     */
    protected function setHeaders($headers = '') {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Get the headers
     *
     * @return array the headers returned from the call
     */
    protected function getHeaders() {
        return $this->headers;
    }

    /**
     * Set the error code number
     *
     * @param integer the error code number
     *
     * @return facebookLib object
     */
    public function setErrorCode($code = 0) {
        $this->errorCode = $code;

        return $this;
    }

    /**
     * Get the error code number
     *
     * @return integer error code number
     */
    public function getErrorCode() {
        return $this->errorCode;
    }

    /**
     * Set the error message
     *
     * @param string the error message
     *
     * @return facebookLib object
     */
    public function setErrorMessage($message = '') {
        $this->errorMessage = $message;

        return $this;
    }

    /**
     * Get the error code message
     *
     * @return string error code message
     */
    public function getErrorMessage() {
        return $this->errorMessage;
    }

    /**
     * Find a key inside a multi dim. array
     *
     * @param        array /object $data
     * @param string $key
     *
     * @return mixed
     */
    protected function getValueByKey($data, $key) {
        if (count($data)) {
            foreach ($data as $k => $each) {
                if ($k == $key) {
                    return $each;
                }

                if (is_array($each)) {
                    if ($return = $this->getValueByKey($each, $key)) {
                        return $return;
                    }
                }
            }
        }

        // Nothing matched
        return NULL;
    }

    /**
     * Return api response codes
     *
     * @return array
     */
    protected function getResponseCodes() {
        return array(
            '00' => 'Transaction Normal',
            '08' => 'CVV2/CID/CVC2 Data not verified',
            '22' => 'Invalid Credit Card Number',
            '25' => 'Invalid Expiry Date',
            '26' => 'Invalid Amount',
            '27' => 'Invalid Card Holder',
            '28' => 'Invalid Authorization No',
            '31' => 'Invalid Verification String',
            '32' => 'Invalid Transaction Code',
            '57' => 'Invalid Reference No',
            '58' => 'Invalid AVS String, The length of the AVS String has exceeded the max. 40 characters',
            '60' => 'Invalid Customer Reference Number',
            '63' => 'Invalid Duplicate',
            '64' => 'Invalid Refund',
            '68' => 'Restricted Card Number',
            '72' => 'Data within the transaction is incorrect',
            '93' => 'Invalid authorization number entered on a pre-auth completion',
            '11' => 'Invalid Sequence No',
            '12' => 'Message Timed-out at Host',
            '21' => 'BCE Function Error',
            '23' => 'Invalid Response from First Data',
            '30' => 'Invalid Date From Host',
            '10' => 'Invalid Transaction Description',
            '14' => 'Invalid Gateway ID',
            '15' => 'Invalid Transaction Number',
            '16' => 'Connection Inactive',
            '17' => 'Unmatched Transaction',
            '18' => 'Invalid Reversal Response',
            '19' => 'Unable to Send Socket Transaction',
            '20' => 'Unable to Write Transaction to File',
            '24' => 'Unable to Void Transaction',
            '40' => 'Unable to Connect',
            '41' => 'Unable to Send Logon',
            '42' => 'Unable to Send Trans',
            '43' => 'Invalid Logon',
            '52' => 'Terminal not Activated',
            '53' => 'Terminal/Gateway Mismatch',
            '54' => 'Invalid Processing Center',
            '55' => 'No Processors Available',
            '56' => 'Database Unavailable',
            '61' => 'Socket Error',
            '62' => 'Host not Ready',
            '44' => 'Address not Verified',
            '70' => 'Transaction Placed in Queue',
            '73' => 'Transaction Received from Bank',
            '76' => 'Reversal Pending',
            '77' => 'Reversal Complete',
            '79' => 'Reversal Sent to Bank',
        );
    }

    /**
     * API Bank Response Code, type, name, comments and action required
     *
     * @return array
     */
    protected function getBankResponseCodes() {
        return array(
            100 => array(
                'response' => 'S',
                'code'     => '100',
                'name'     => 'Approved',
                'action'   => 'N/A',
                'comments' => 'Successfully approved',
            ),
            101 => array(
                'response' => 'S',
                'code'     => '101',
                'name'     => 'Validated',
                'action'   => 'N/A',
                'comments' => 'Account Passed edit checks',
            ),
            102 => array(
                'response' => 'S',
                'code'     => '102',
                'name'     => 'Verified',
                'action'   => 'N/A',
                'comments' => 'Account Passed external negative file',
            ),
            103 => array(
                'response' => 'S',
                'code'     => '103',
                'name'     => 'Pre-Noted',
                'action'   => 'N/A',
                'comments' => 'Passed Pre-Note',
            ),
            104 => array(
                'response' => 'S',
                'code'     => '104',
                'name'     => 'No Reason to Decline',
                'action'   => 'N/A',
                'comments' => 'Successfully approved',
            ),
            105 => array(
                'response' => 'S',
                'code'     => '105',
                'name'     => 'Received and Stored',
                'action'   => 'N/A',
                'comments' => 'Successfully approved',
            ),
            106 => array(
                'response' => 'S',
                'code'     => '106',
                'name'     => 'Provided Auth',
                'action'   => 'N/A',
                'comments' => 'Successfully approved Note: Indicates customized code was used in processing',
            ),
            107 => array(
                'response' => 'S',
                'code'     => '107',
                'name'     => 'Request Received',
                'action'   => 'N/A',
                'comments' => 'Successfully approved Note: Indicates customized code was used in processing',
            ),
            108 => array(
                'response' => 'S',
                'code'     => '108',
                'name'     => 'Approved for Activation',
                'action'   => 'N/A',
                'comments' => 'Successfully Activated',
            ),
            109 => array(
                'response' => 'S',
                'code'     => '109',
                'name'     => 'Previously&nbsp;Processed Transaction',
                'action'   => 'N/A',
                'comments' => 'Transaction was not re-authorized with the Debit Network because it was previously processed',
            ),
            110 => array(
                'response' => 'S',
                'code'     => '110',
                'name'     => 'BIN Alert',
                'action'   => 'N/A',
                'comments' => 'Successfully approved Note: Indicates customized code was used in processing',
            ),
            111 => array(
                'response' => 'S',
                'code'     => '111',
                'name'     => 'Approved for Partial',
                'action'   => 'N/A',
                'comments' => 'Successfully approved Note: Indicates customized code was used in processing',
            ),
            164 => array(
                'response' => 'S',
                'code'     => '164',
                'name'     => 'Conditional Approval',
                'action'   => 'Wait',
                'comments' => 'Conditional Approval - Hold shipping for 24 hours',
            ),
            704 => array(
                'response' => 'S',
                'code'     => '704',
                'name'     => 'FPO Accepted',
                'action'   => 'N/A',
                'comments' => 'Stored in FPO database',
            ),
            741 => array(
                'response' => 'R/D',
                'code'     => '741',
                'name'     => 'Validation Failed',
                'action'   => 'Fix',
                'comments' => 'Unable to validate the Debit Authorization Record - based on amount, action code, and MOP (Batch response reason code for Debit Only)',
            ),
            750 => array(
                'response' => 'R/D',
                'code'     => '750',
                'name'     => 'Invalid Transit Routing Number',
                'action'   => 'Fix',
                'comments' => 'EC - ABA transit routing number is invalid, failed check digit',
            ),
            751 => array(
                'response' => 'R/D',
                'code'     => '751',
                'name'     => 'Transit Routing Number Unknown',
                'action'   => 'Fix',
                'comments' => 'Transit routing number not on list of current acceptable numbers.',
            ),
            754 => array(
                'response' => 'R/D',
                'code'     => '754',
                'name'     => 'Account Closed',
                'action'   => 'Cust',
                'comments' => 'Bank account has been closed For PayPal and GoogleCheckout – the customer’s account was closed / restricted',
            ),
            201 => array(
                'response' => 'R',
                'code'     => '201',
                'name'     => 'Invalid CC Number',
                'action'   => 'Cust',
                'comments' => 'Bad check digit, length, or other credit card problem',
            ),
            202 => array(
                'response' => 'R',
                'code'     => '202',
                'name'     => 'Bad Amount Nonnumeric Amount',
                'action'   => 'If',
                'comments' => 'Amount sent was zero, unreadable, over ceiling limit, or exceeds maximum allowable amount.',
            ),
            203 => array(
                'response' => 'R',
                'code'     => '203',
                'name'     => 'Zero Amount',
                'action'   => 'Fix',
                'comments' => 'Amount sent was zero',
            ),
            204 => array(
                'response' => 'R',
                'code'     => '204',
                'name'     => 'Other Error',
                'action'   => 'Fix',
                'comments' => 'Unidentifiable error',
            ),
            205 => array(
                'response' => 'R',
                'code'     => '205',
                'name'     => 'Bad Total Auth Amount',
                'action'   => 'Fix',
                'comments' => 'The sum of the authorization amount from extended data information does not equal detail record authorization Amount. Amount sent was zero, unreadable, over ceiling limit, or exceeds Maximum allowable amount.',
            ),
            218 => array(
                'response' => 'R',
                'code'     => '218',
                'name'     => 'Invalid SKU Number',
                'action'   => 'Fix',
                'comments' => 'Non‐numeric value was sent',
            ),
            219 => array(
                'response' => 'R',
                'code'     => '219',
                'name'     => 'Invalid Credit Plan',
                'action'   => 'Fix',
                'comments' => 'Non‐numeric value was sent',
            ),
            220 => array(
                'response' => 'R',
                'code'     => '220',
                'name'     => 'Invalid Store Number',
                'action'   => 'Fix',
                'comments' => 'Non‐numeric value was sent',
            ),
            225 => array(
                'response' => 'R',
                'code'     => '225',
                'name'     => 'Invalid Field Data',
                'action'   => 'Fix',
                'comments' => 'Data within transaction is incorrect',
            ),
            227 => array(
                'response' => 'R',
                'code'     => '227',
                'name'     => 'Missing Companion Data',
                'action'   => 'Fix',
                'comments' => 'Specific and relevant data within transaction is absent',
            ),
            229 => array(
                'response' => 'R',
                'code'     => '229',
                'name'     => 'Percents do not total 100',
                'action'   => 'Fix',
                'comments' => 'FPO monthly payments do not total 100 Note: FPO only',
            ),
            230 => array(
                'response' => 'R',
                'code'     => '230',
                'name'     => 'Payments do not total 100',
                'action'   => 'Fix',
                'comments' => 'FPO monthly payments do not total 100 Note: FPO only',
            ),
            231 => array(
                'response' => 'R',
                'code'     => '231',
                'name'     => 'Invalid Division Number',
                'action'   => 'Fix',
                'comments' => 'Division number incorrect',
            ),
            233 => array(
                'response' => 'R',
                'code'     => '233',
                'name'     => 'Does not match MOP',
                'action'   => 'Fix',
                'comments' => 'Credit card number does not match method of payment type or invalid BIN',
            ),
            234 => array(
                'response' => 'R',
                'code'     => '234',
                'name'     => 'Duplicate Order Number',
                'action'   => 'Fix',
                'comments' => 'Unique to authorization recycle transactions. Order number already exists in system Note: Auth Recycle only',
            ),
            235 => array(
                'response' => 'R',
                'code'     => '235',
                'name'     => 'FPO Locked',
                'action'   => 'Resend',
                'comments' => 'FPO change not allowed Note: FPO only',
            ),
            236 => array(
                'response' => 'R',
                'code'     => '236',
                'name'     => 'Auth Recycle Host System Down',
                'action'   => 'Resend',
                'comments' => 'Authorization recycle host system temporarily unavailable Note: Auth Recycle only',
            ),
            237 => array(
                'response' => 'R',
                'code'     => '237',
                'name'     => 'FPO Not Approved',
                'action'   => 'Call',
                'comments' => 'Division does not participate in FPO. Contact your First Data Representative for information on getting set up for FPO Note: FPO only',
            ),
            238 => array(
                'response' => 'R',
                'code'     => '238',
                'name'     => 'Invalid Currency',
                'action'   => 'Fix',
                'comments' => 'Currency does not match First Data merchant setup for division',
            ),
            239 => array(
                'response' => 'R',
                'code'     => '239',
                'name'     => 'Invalid MOP for Division',
                'action'   => 'Fix',
                'comments' => 'Method of payment is invalid for the division',
            ),
            240 => array(
                'response' => 'R',
                'code'     => '240',
                'name'     => 'Auth Amount for Division',
                'action'   => 'Fix',
                'comments' => 'Used by FPO',
            ),
            241 => array(
                'response' => 'R',
                'code'     => '241',
                'name'     => 'Illegal Action',
                'action'   => 'Fix',
                'comments' => 'Invalid action attempted',
            ),
            243 => array(
                'response' => 'R',
                'code'     => '243',
                'name'     => 'Invalid Purchase Level 3',
                'action'   => 'Fix',
                'comments' => 'Data is inaccurate or missing, or the BIN is ineligible for P‐card',
            ),
            244 => array(
                'response' => 'R',
                'code'     => '244',
                'name'     => 'Invalid Encryption Format',
                'action'   => 'Fix',
                'comments' => 'Invalid encryption flag. Data is Inaccurate.',
            ),
            245 => array(
                'response' => 'R',
                'code'     => '245',
                'name'     => 'Missing or Invalid Secure Payment Data',
                'action'   => 'Fix',
                'comments' => 'Visa or MasterCard authentication data not in appropriate Base 64 encoding format or data provided on A non‐e‐Commerce transaction.',
            ),
            246 => array(
                'response' => 'R',
                'code'     => '246',
                'name'     => 'Merchant not MasterCard Secure code Enabled',
                'action'   => 'Call',
                'comments' => 'Division does not participate in MasterCard Secure Code. Contact your First Data Representative for information on getting setup for MasterCard SecureCode.',
            ),
            247 => array(
                'response' => 'R',
                'code'     => '247',
                'name'     => 'Check conversion Data Error',
                'action'   => 'Fix',
                'comments' => 'Proper data elements were not sent',
            ),
            248 => array(
                'response' => 'R',
                'code'     => '248',
                'name'     => 'Blanks not passed in reserved field',
                'action'   => 'Fix',
                'comments' => 'Blanks not passed in Reserved Field',
            ),
            249 => array(
                'response' => 'R',
                'code'     => '249',
                'name'     => 'Invalid (MCC)',
                'action'   => 'Fix',
                'comments' => 'Invalid Merchant Category (MCC) sent',
            ),
            251 => array(
                'response' => 'R',
                'code'     => '251',
                'name'     => 'Invalid Start Date',
                'action'   => 'Fix',
                'comments' => 'Incorrect start date or card may require an issue number, but a start date was submitted.',
            ),
            252 => array(
                'response' => 'R',
                'code'     => '252',
                'name'     => 'Invalid Issue Number',
                'action'   => 'Fix',
                'comments' => 'Issue number invalid for this BIN.',
            ),
            253 => array(
                'response' => 'R',
                'code'     => '253',
                'name'     => 'Invalid Tran. Type',
                'action'   => 'Fix',
                'comments' => 'If an “R” (Retail Indicator) is sent for a transaction with a MOTO Merchant Category Code (MCC)',
            ),
            257 => array(
                'response' => 'R',
                'code'     => '257',
                'name'     => 'Missing Cust Service Phone',
                'action'   => 'Fix',
                'comments' => 'Card was authorized, but AVS did not match. The 100 was overwritten with a 260 per the merchant’s request Note: Conditional deposits only',
            ),
            258 => array(
                'response' => 'R',
                'code'     => '258',
                'name'     => 'Not Authorized to Send Record',
                'action'   => 'Call',
                'comments' => 'Division does not participate in Soft Merchant Descriptor. Contact your First Data Representative for information on getting set up for Soft Merchant Descriptor.',
            ),
            261 => array(
                'response' => 'R',
                'code'     => '261',
                'name'     => 'Account Not Eligible For Division’s Setup',
                'action'   => 'N/A',
                'comments' => 'Account number not eligible for division’s Account Updater program setup',
            ),
            262 => array(
                'response' => 'R',
                'code'     => '262',
                'name'     => 'Authorization Code Response Date Invalid',
                'action'   => 'Fix',
                'comments' => 'Authorization code and/or response date are invalid. Note: MOP = MC, MD, VI only',
            ),
            263 => array(
                'response' => 'R',
                'code'     => '263',
                'name'     => 'Partial Authorization Not Allowed or Partial Authorization Request Note Valid',
                'action'   => 'Fix',
                'comments' => 'Action code or division does not allow partial authorizations or partial authorization request is not valid.',
            ),
            264 => array(
                'response' => 'R',
                'code'     => '264',
                'name'     => 'Duplicate Deposit Transaction',
                'action'   => 'N/A',
                'comments' => 'Transaction is a duplicate of a previously deposited transaction. Transaction will not be processed.',
            ),
            265 => array(
                'response' => 'R',
                'code'     => '265',
                'name'     => 'Missing QHP Amount',
                'action'   => 'Fix',
                'comments' => 'Missing QHP Amount',
            ),
            266 => array(
                'response' => 'R',
                'code'     => '266',
                'name'     => 'Invalid QHP Amount',
                'action'   => 'Fix',
                'comments' => 'QHP amount greater than transaction amount',
            ),
            274 => array(
                'response' => 'R',
                'code'     => '274',
                'name'     => 'Transaction Not Supported',
                'action'   => 'N/A',
                'comments' => 'The requested transaction type is blocked from being used with this card. Note:&nbsp; This may be the result of either an association rule, or a merchant boarding option.',
            ),
            351 => array(
                'response' => 'R',
                'code'     => '351',
                'name'     => 'TransArmor Service Unavailable',
                'action'   => 'Resend',
                'comments' => 'TransArmor Service temporarily unavailable.',
            ),
            353 => array(
                'response' => 'R',
                'code'     => '353',
                'name'     => 'TransArmor Invalid Token or PAN',
                'action'   => 'Fix',
                'comments' => 'TransArmor Service encountered a problem converting the given Token or PAN with the given Token Type.',
            ),
            354 => array(
                'response' => 'R',
                'code'     => '354',
                'name'     => 'TransArmor Invalid Result',
                'action'   => 'Cust',
                'comments' => 'TransArmor Service encountered a problem with the resulting Token/PAN.',
            ),
            740 => array(
                'response' => 'R',
                'code'     => '740',
                'name'     => 'Match Failed',
                'action'   => 'Fix',
                'comments' => 'Unable to validate the debit. Authorization Record - based on amount, action code, and MOP (Batch response reason code for Debit Only)',
            ),
            752 => array(
                'response' => 'R',
                'code'     => '752',
                'name'     => 'Missing Name',
                'action'   => 'Fix',
                'comments' => 'Pertains to deposit transactions only',
            ),
            753 => array(
                'response' => 'R',
                'code'     => '753',
                'name'     => 'Invalid Account Type',
                'action'   => 'Fix',
                'comments' => 'Pertains to deposit transactions only',
            ),
            834 => array(
                'response' => 'R',
                'code'     => '834',
                'name'     => 'Unauthorized User',
                'action'   => 'Fix',
                'comments' => 'Method of payment is invalid for the division',
            ),
            570 => array(
                'response' => 'D ',
                'code'     => '570 ',
                'name'     => 'Stop payment order one time recurring/ installment',
                'action'   => 'Fix',
                'comments' => 'Cardholder has requested this one recurring/installment payment be stopped.',
            ),
            000 => array(
                'response' => 'D',
                'code'     => '000',
                'name'     => 'No Answer',
                'action'   => 'Resend',
                'comments' => 'First Data received no answer from auth network',
            ),
            260 => array(
                'response' => 'D',
                'code'     => '260',
                'name'     => 'Soft AVS',
                'action'   => 'Cust.',
                'comments' => 'Authorization network could not reach the bank which issued the card',
            ),
            301 => array(
                'response' => 'D',
                'code'     => '301',
                'name'     => 'Issuer unavailable',
                'action'   => 'Resend',
                'comments' => 'Authorization network could not reach the bank which issued the card',
            ),
            302 => array(
                'response' => 'D',
                'code'     => '302',
                'name'     => 'Credit Floor',
                'action'   => 'Wait',
                'comments' => 'Insufficient funds',
            ),
            303 => array(
                'response' => 'D',
                'code'     => '303',
                'name'     => 'Processor Decline',
                'action'   => 'Cust.',
                'comments' => 'Generic decline – No other information is being provided by the Issuer',
            ),
            304 => array(
                'response' => 'D',
                'code'     => '304',
                'name'     => 'Not On File',
                'action'   => 'Cust.',
                'comments' => 'No card record, or invalid/nonexistent to account specified',
            ),
            305 => array(
                'response' => 'D',
                'code'     => '305',
                'name'     => 'Already Reversed',
                'action'   => 'N/A',
                'comments' => 'Transaction previously reversed. Note: MOP = any Debit MOP, SV, MC, MD, VI only',
            ),
            306 => array(
                'response' => 'D',
                'code'     => '306',
                'name'     => 'Amount Mismatch',
                'action'   => 'Fix',
                'comments' => 'Requested reversal amount does not match original approved authorization amount. Note: MOP = MC, MD, VI only',
            ),
            307 => array(
                'response' => 'D',
                'code'     => '307',
                'name'     => 'Authorization Not Found',
                'action'   => 'Fix',
                'comments' => 'Transaction cannot be matched to an authorization that was stored in the database. Note: MOP = MC, MD, VI only',
            ),
            352 => array(
                'response' => 'D',
                'code'     => '352',
                'name'     => 'Expired Lock',
                'action'   => 'Cust.',
                'comments' => 'ValueLink - Lock on funds has expired.',
            ),
            401 => array(
                'response' => 'D',
                'code'     => '401',
                'name'     => 'Call',
                'action'   => 'Voice',
                'comments' => 'Issuer wants voice contact with cardholder',
            ),
            402 => array(
                'response' => 'D',
                'code'     => '402',
                'name'     => 'Default Call',
                'action'   => 'Voice',
                'comments' => 'Decline',
            ),
            501 => array(
                'response' => 'D',
                'code'     => '501',
                'name'     => 'Pickup',
                'action'   => 'Cust',
                'comments' => 'Card Issuer wants card returned',
            ),
            502 => array(
                'response' => 'D',
                'code'     => '502',
                'name'     => 'Lost/Stolen',
                'action'   => 'Cust',
                'comments' => 'Card reported as lost/stolen Note: Does not apply to American Express',
            ),
            503 => array(
                'response' => 'D',
                'code'     => '503',
                'name'     => 'Fraud/ Security Violation',
                'action'   => 'Cust',
                'comments' => 'CID did not match Note: Discover only',
            ),
            505 => array(
                'response' => 'D',
                'code'     => '505',
                'name'     => 'Negative File',
                'action'   => 'Cust',
                'comments' => 'On negative file',
            ),
            508 => array(
                'response' => 'D',
                'code'     => '508',
                'name'     => 'Excessive PIN try',
                'action'   => 'Cust',
                'comments' => 'Allowable number of PIN tries exceeded',
            ),
            509 => array(
                'response' => 'D',
                'code'     => '509',
                'name'     => 'Over the limit',
                'action'   => 'Cust',
                'comments' => 'Exceeds withdrawal or activity amount limit',
            ),
            510 => array(
                'response' => 'D',
                'code'     => '510',
                'name'     => 'Over Limit Frequency',
                'action'   => 'Cust',
                'comments' => 'Exceeds withdrawal or activity count limit',
            ),
            519 => array(
                'response' => 'D',
                'code'     => '519',
                'name'     => 'On negative file',
                'action'   => 'Cust',
                'comments' => 'Account number appears on negative file',
            ),
            521 => array(
                'response' => 'D',
                'code'     => '521',
                'name'     => 'Insufficient funds',
                'action'   => 'Cust',
                'comments' => 'Insufficient funds/over credit limit',
            ),
            522 => array(
                'response' => 'D',
                'code'     => '522',
                'name'     => 'Card is expired',
                'action'   => 'Cust',
                'comments' => 'Card has expired',
            ),
            524 => array(
                'response' => 'D',
                'code'     => '524',
                'name'     => 'Altered Data',
                'action'   => 'Fix',
                'comments' => 'Altered Data\Magnetic stripe incorrect',
            ),
            530 => array(
                'response' => 'D',
                'code'     => '530',
                'name'     => 'Do Not Honor',
                'action'   => 'Cust',
                'comments' => 'Generic Decline – No other information is being provided by the issuer. Note: This is a hard decline for BML (will never pass with recycle attempts)',
            ),
            531 => array(
                'response' => 'D',
                'code'     => '531',
                'name'     => 'CVV2/VAK Failure',
                'action'   => 'Cust',
                'comments' => 'Issuer has declined auth request because CVV2 or VAK failed',
            ),
            534 => array(
                'response' => 'D',
                'code'     => '534',
                'name'     => 'Do Not Honor - High Fraud',
                'action'   => 'Cust',
                'comments' => 'The transaction has failed PayPal or Google Checkout risk models',
            ),
            571 => array(
                'response' => 'D',
                'code'     => '571',
                'name'     => 'Revocation of Authorization for All Recurring / Installments',
                'action'   => 'Cust',
                'comments' => 'Cardholder has requested all recurring/installment payments be stopped',
            ),
            572 => array(
                'response' => 'D',
                'code'     => '572',
                'name'     => 'Revocation of All Authorizations – Closed Account',
                'action'   => 'Cust',
                'comments' => 'Cardholder has requested that all authorizations be stopped for this account due to closed account. Note: Visa only',
            ),
            580 => array(
                'response' => 'D',
                'code'     => '580',
                'name'     => 'Account previously activated',
                'action'   => 'Cust',
                'comments' => 'Account previously activated',
            ),
            581 => array(
                'response' => 'D',
                'code'     => '581',
                'name'     => 'Unable to void',
                'action'   => 'Fix',
                'comments' => 'Unable to void',
            ),
            582 => array(
                'response' => 'D',
                'code'     => '582',
                'name'     => 'Block activation failed',
                'action'   => 'Fix',
                'comments' => 'Reserved for Future Use',
            ),
            583 => array(
                'response' => 'D',
                'code'     => '583',
                'name'     => 'Block Activation Failed',
                'action'   => 'Fix',
                'comments' => 'Reserved for Future Use',
            ),
            584 => array(
                'response' => 'D',
                'code'     => '584',
                'name'     => 'Issuance Does Not Meet Minimum Amount',
                'action'   => 'Fix',
                'comments' => 'Issuance does not meet minimum amount',
            ),
            585 => array(
                'response' => 'D',
                'code'     => '585',
                'name'     => 'No Original Authorization Found',
                'action'   => 'N/A',
                'comments' => 'No original authorization found',
            ),
            586 => array(
                'response' => 'D',
                'code'     => '586',
                'name'     => 'Outstanding Authorization, Funds on Hold',
                'action'   => 'N/A',
                'comments' => 'Outstanding Authorization, funds on hold',
            ),
            587 => array(
                'response' => 'D',
                'code'     => '587',
                'name'     => 'Activation Amount Incorrect',
                'action'   => 'Fix',
                'comments' => 'Activation amount incorrect',
            ),
            588 => array(
                'response' => 'D',
                'code'     => '588',
                'name'     => 'Block Activation Failed',
                'action'   => 'Fix',
                'comments' => 'Reserved for Future Use',
            ),
            589 => array(
                'response' => 'D',
                'code'     => '589',
                'name'     => 'CVD Value Failure',
                'action'   => 'Cust',
                'comments' => 'Magnetic stripe CVD value failure',
            ),
            590 => array(
                'response' => 'D',
                'code'     => '590',
                'name'     => 'Maximum Redemption Limit Met',
                'action'   => 'Cust',
                'comments' => 'Maximum redemption limit met',
            ),
            591 => array(
                'response' => 'D',
                'code'     => '591',
                'name'     => 'Invalid CC Number',
                'action'   => 'Cust',
                'comments' => 'Bad check digit, length or other credit card problem. Issuer generated',
            ),
            592 => array(
                'response' => 'D',
                'code'     => '592',
                'name'     => 'Bad Amount',
                'action'   => 'Fix',
                'comments' => 'Amount sent was zero or unreadable. Issuer generated',
            ),
            594 => array(
                'response' => 'D',
                'code'     => '594',
                'name'     => 'Other Error',
                'action'   => 'Fix',
                'comments' => 'Unidentifiable error. Issuer generated',
            ),
            595 => array(
                'response' => 'D',
                'code'     => '595',
                'name'     => 'New Card Issued',
                'action'   => 'Cust',
                'comments' => 'New Card Issued',
            ),
            596 => array(
                'response' => 'D',
                'code'     => '596',
                'name'     => 'Suspected Fraud',
                'action'   => 'Cust',
                'comments' => 'Issuer has flagged account as suspected fraud',
            ),
            599 => array(
                'response' => 'D',
                'code'     => '599',
                'name'     => 'Refund Not Allowed',
                'action'   => 'N/A',
                'comments' => 'Refund Not Allowed',
            ),
            602 => array(
                'response' => 'D',
                'code'     => '602',
                'name'     => 'Invalid Institution Code',
                'action'   => 'Fix',
                'comments' => 'Card is bad, but passes MOD 10 check digit routine, wrong BIN',
            ),
            603 => array(
                'response' => 'D',
                'code'     => '603',
                'name'     => 'Invalid Institution',
                'action'   => 'Cust',
                'comments' => 'Institution not valid (i.e. possible merger)',
            ),
            605 => array(
                'response' => 'D',
                'code'     => '605',
                'name'     => 'Invalid Expiration Date',
                'action'   => 'Cust',
                'comments' => 'Card has expired or bad date sent. Confirm proper date',
            ),
            606 => array(
                'response' => 'D',
                'code'     => '606',
                'name'     => 'Invalid Transaction Type',
                'action'   => 'Cust',
                'comments' => 'Issuer does not allow this type of transaction',
            ),
            607 => array(
                'response' => 'D',
                'code'     => '607',
                'name'     => 'Invalid Amount',
                'action'   => 'Fix',
                'comments' => 'Amount not accepted by network',
            ),
            610 => array(
                'response' => 'D',
                'code'     => '610',
                'name'     => 'BIN Block',
                'action'   => 'Cust',
                'comments' => 'Merchant has requested First Data not process credit cards with this BIN',
            ),
            802 => array(
                'response' => 'D',
                'code'     => '802',
                'name'     => 'Positive ID',
                'action'   => 'Voice',
                'comments' => 'Issuer requires further information',
            ),
            806 => array(
                'response' => 'D',
                'code'     => '806',
                'name'     => 'Restraint',
                'action'   => 'Cust',
                'comments' => 'Card has been restricted',
            ),
            811 => array(
                'response' => 'D',
                'code'     => '811',
                'name'     => 'Invalid Security Code',
                'action'   => 'Fix',
                'comments' => 'American Express CID is incorrect',
            ),
            813 => array(
                'response' => 'D',
                'code'     => '813',
                'name'     => 'Invalid PIN',
                'action'   => 'Cust',
                'comments' => 'PIN for online debit transactions is incorrect',
            ),
            825 => array(
                'response' => 'D',
                'code'     => '825',
                'name'     => 'No Account',
                'action'   => 'Cust',
                'comments' => 'Account does not exist',
            ),
            833 => array(
                'response' => 'D',
                'code'     => '833',
                'name'     => 'Invalid Merchant',
                'action'   => 'Fix',
                'comments' => 'Service Established (SE) number is incorrect, closed or Issuer does not allow this type of transaction',
            ),
            902 => array(
                'response' => 'D',
                'code'     => '902',
                'name'     => 'Process Unavailable',
                'action'   => 'Resend/ Call/ Cust.',
                'comments' => 'System error/malfunction with Issuer For Debit – The link is down or setup issue; contact your First Data Representative.',
            ),
            903 => array(
                'response' => 'D',
                'code'     => '903',
                'name'     => 'Invalid Expiration',
                'action'   => 'Cust',
                'comments' => 'Invalid or expired expiration date',
            ),
            904 => array(
                'response' => 'D',
                'code'     => '904',
                'name'     => 'Invalid Effective',
                'action'   => 'Cust./ Resend',
                'comments' => 'Card not active',
            ),
        );
    }
}