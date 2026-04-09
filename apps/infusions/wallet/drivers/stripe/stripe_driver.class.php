<?php

namespace PHPFusion\Infusions\Wallet\Drivers\Stripe;

use Defender;
use PHPFusion\Infusions\Wallet\Classes\Wallet;
use PHPFusion\Infusions\Wallet\Classes\Wallet_Model;
use PHPFusion\Infusions\Wallet\Classes\Wallet_Transaction;
use ThemeFactory\Core;


/**
 * Class PayPal_Driver
 * Documentation https://stripe.com/docs/payments
 *
 * @package PHPFusion\Infusions\Wallet\Drivers\Stripe
 */
class Stripe_Driver {

    private static $instance = NULL;

    private $stripe_token = 'stripe_return';

    private $defender = NULL;

    private $wallet = NULL;

    private $info = [];

    private $wallet_settings = [];


    private $errors = [
        80  => [
            "title"       => "Payment Error: Invalid payment amount (Error Code: 80)",
            "description" => "The bill and the payment made are different.",
            "code"        => 80,
        ],
        100 => [
            "title"       => "Payment Error: Payment was not completed (Error Code: 100)",
            "description" => "Your payment has not gone through or is currently pending.",
            "code"        => 100,
        ],
        200 => [
            "title"       => "Payment Error: Invalid merchant mail verification (Error Code: 200)",
            "description" => "The mail verification failed.",
            "code"        => 200,
        ],
        300 => [
            "title"       => "Payment Error: Invalid currency verification (Error Code: 300)",
            "description" => "The currency verification failed.",
            "code"        => 300,
        ],
        400 => [
            "title"       => "Payment Error: Paypal error (Error Code: 400)",
            "description" => "There was no transaction id being sent by Paypal.",
            "code"        => 400,
        ],
        500 => [
            'title'       => "Payment Error: Paypal IPN Error (Error Code: 500)",
            'description' => "Your last transaction has an invalid transaction token.",
            "code"        => 500,
        ],
        600 => [
            'title'       => "Payment Error: Invalid Payment Verification (Error Code: 600)",
            'description' => "Your last transaction has an invalid transaction token.",
            "code"        => 600,
        ],
        700 => [
            'title'       => "Transaction could not be verified (Error Code: 700)",
            'description' => "Transaction contains an invalid transaction number.",
            "code"        => 700,
        ],
        800 => [
            'title'       => "Delivery cannot be made (Error Code: 800)",
            'description' => "No transaction file defined.",
            "code"        => 800,
        ],
        900 => [
            'title'       => 'No transaction found (Error Code: 900)',
            'description' => 'No transaction can be found for this request.',
            "code"        => 900,
        ]
    ];

    /*
     * PHP-Fusion Wallet Module
     */

    public function __construct() {
        $this->defender = Defender::getInstance();
        $this->wallet = new Wallet();
        $this->wallet_settings = Wallet::walletSettings();
    }

    /**
     * @return static|null
     */
    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /*
     * Gateway Configurations
     */

    public static function __getOption() {
    }

    public static function refund() {
    }

    public static function record() {
    }

    public static function read() {
    }

    public function __clone() {
        die('Cloning of this class is prohibited');
    }

    public function __Properties() {
        return [
            'title'                      => 'Stripe',
            'description'                => 'Pay with your credit card with Stripe.',
            'admin_description'          => 'Payment Gateway for Stripe',
            'link'                       => 'https://stripe.com/docs',
            'author'                     => 'PHP Fusion Inc',
            'author_web'                 => 'https://www.php-fusion.co.uk',
            'author_email'               => 'mt@php-fusion.co.uk',
            'version'                    => '1.00',
            'pay_method'                 => 'Stripe Checkout',
            'pay_image'                  => "            
            <img alt='Visa with Stripe' style='width:50px;max-width: none;' src='data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkJz48c3ZnIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDY0IDY0IiBoZWlnaHQ9IjY0cHgiIGlkPSJMYXllcl8xIiB2ZXJzaW9uPSIxLjEiIHZpZXdCb3g9IjAgMCA2NCA2NCIgd2lkdGg9IjY0cHgiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPjxnPjxnPjxnPjxnPjxwb2x5Z29uIGZpbGw9IiMzQzU4QkYiIHBvaW50cz0iMjMuNiw0MSAyNi44LDIzIDMxLjgsMjMgMjguNyw0MSAgICAgIi8+PC9nPjwvZz48Zz48Zz48cG9seWdvbiBmaWxsPSIjMjkzNjg4IiBwb2ludHM9IjIzLjYsNDEgMjcuNywyMyAzMS44LDIzIDI4LjcsNDEgICAgICIvPjwvZz48L2c+PGc+PGc+PHBhdGggZD0iTTQ2LjgsMjMuMmMtMS0wLjQtMi42LTAuOC00LjYtMC44Yy01LDAtOC42LDIuNS04LjYsNi4xYzAsMi43LDIuNSw0LjEsNC41LDVjMiwwLjksMi42LDEuNSwyLjYsMi4zICAgICAgYzAsMS4yLTEuNiwxLjgtMywxLjhjLTIsMC0zLjEtMC4zLTQuOC0xbC0wLjctMC4zbC0wLjcsNC4xYzEuMiwwLjUsMy40LDEsNS43LDFjNS4zLDAsOC44LTIuNSw4LjgtNi4zYzAtMi4xLTEuMy0zLjctNC4zLTUgICAgICBjLTEuOC0wLjktMi45LTEuNC0yLjktMi4zYzAtMC44LDAuOS0xLjYsMi45LTEuNmMxLjcsMCwyLjksMC4zLDMuOCwwLjdsMC41LDAuMkw0Ni44LDIzLjJMNDYuOCwyMy4yeiIgZmlsbD0iIzNDNThCRiIvPjwvZz48L2c+PGc+PGc+PHBhdGggZD0iTTQ2LjgsMjMuMmMtMS0wLjQtMi42LTAuOC00LjYtMC44Yy01LDAtNy43LDIuNS03LjcsNi4xYzAsMi43LDEuNiw0LjEsMy42LDVjMiwwLjksMi42LDEuNSwyLjYsMi4zICAgICAgYzAsMS4yLTEuNiwxLjgtMywxLjhjLTIsMC0zLjEtMC4zLTQuOC0xbC0wLjctMC4zbC0wLjcsNC4xYzEuMiwwLjUsMy40LDEsNS43LDFjNS4zLDAsOC44LTIuNSw4LjgtNi4zYzAtMi4xLTEuMy0zLjctNC4zLTUgICAgICBjLTEuOC0wLjktMi45LTEuNC0yLjktMi4zYzAtMC44LDAuOS0xLjYsMi45LTEuNmMxLjcsMCwyLjksMC4zLDMuOCwwLjdsMC41LDAuMkw0Ni44LDIzLjJMNDYuOCwyMy4yeiIgZmlsbD0iIzI5MzY4OCIvPjwvZz48L2c+PGc+PGc+PHBhdGggZD0iTTU1LjQsMjNjLTEuMiwwLTIuMSwwLjEtMi42LDEuM0w0NS4zLDQxaDUuNGwxLTNoNi40bDAuNiwzaDQuOGwtNC4yLTE4SDU1LjR6IE01My4xLDM1ICAgICAgYzAuMy0wLjksMi01LjMsMi01LjNjMCwwLDAuNC0xLjEsMC43LTEuOGwwLjMsMS43YzAsMCwxLDQuNSwxLjIsNS41SDUzLjFMNTMuMSwzNXoiIGZpbGw9IiMzQzU4QkYiLz48L2c+PC9nPjxnPjxnPjxwYXRoIGQ9Ik01Ni42LDIzYy0xLjIsMC0yLjEsMC4xLTIuNiwxLjNMNDUuMyw0MWg1LjRsMS0zaDYuNGwwLjYsM2g0LjhsLTQuMi0xOEg1Ni42eiBNNTMuMSwzNSAgICAgIGMwLjQtMSwyLTUuMywyLTUuM2MwLDAsMC40LTEuMSwwLjctMS44bDAuMywxLjdjMCwwLDEsNC41LDEuMiw1LjVINTMuMUw1My4xLDM1eiIgZmlsbD0iIzI5MzY4OCIvPjwvZz48L2c+PGc+PGc+PHBhdGggZD0iTTE0LjQsMzUuNkwxMy45LDMzYy0wLjktMy0zLjgtNi4zLTctNy45bDQuNSwxNmg1LjRsOC4xLTE4aC01LjRMMTQuNCwzNS42eiIgZmlsbD0iIzNDNThCRiIvPjwvZz48L2c+PGc+PGc+PHBhdGggZD0iTTE0LjQsMzUuNkwxMy45LDMzYy0wLjktMy0zLjgtNi4zLTctNy45bDQuNSwxNmg1LjRsOC4xLTE4aC00LjRMMTQuNCwzNS42eiIgZmlsbD0iIzI5MzY4OCIvPjwvZz48L2c+PGc+PGc+PHBhdGggZD0iTTAuNSwyM2wwLjksMC4yYzYuNCwxLjUsMTAuOCw1LjMsMTIuNSw5LjhsLTEuOC04LjVjLTAuMy0xLjItMS4yLTEuNS0yLjMtMS41SDAuNXoiIGZpbGw9IiNGRkJDMDAiLz48L2c+PC9nPjxnPjxnPjxwYXRoIGQ9Ik0wLjUsMjNMMC41LDIzYzYuNCwxLjUsMTEuNyw1LjQsMTMuNCw5LjlsLTEuNy03LjFjLTAuMy0xLjItMS4zLTEuOS0yLjQtMS45TDAuNSwyM3oiIGZpbGw9IiNGNzk4MUQiLz48L2c+PC9nPjxnPjxnPjxwYXRoIGQ9Ik0wLjUsMjNMMC41LDIzYzYuNCwxLjUsMTEuNyw1LjQsMTMuNCw5LjlMMTIuNywyOWMtMC4zLTEuMi0wLjctMi40LTIuMS0yLjlMMC41LDIzeiIgZmlsbD0iI0VEN0MwMCIvPjwvZz48L2c+PC9nPjxnPjxwYXRoIGQ9Ik0xOS40LDM1TDE2LDMxLjZsLTEuNiwzLjhsLTAuNC0yLjVjLTAuOS0zLTMuOC02LjMtNy03LjlsNC41LDE2aDUuNEwxOS40LDM1eiIgZmlsbD0iIzA1MTI0NCIvPjwvZz48Zz48cG9seWdvbiBmaWxsPSIjMDUxMjQ0IiBwb2ludHM9IjI4LjcsNDEgMjQuNCwzNi42IDIzLjYsNDEgMjguNyw0MSAgICIvPjwvZz48Zz48cGF0aCBkPSJNNDAuMiwzNC44TDQwLjIsMzQuOGMwLjQsMC40LDAuNiwwLjcsMC41LDEuMWMwLDEuMi0xLjYsMS44LTMsMS44Yy0yLDAtMy4xLTAuMy00LjgtMWwtMC43LTAuM2wtMC43LDQuMSAgICBjMS4yLDAuNSwzLjQsMSw1LjcsMWMzLjIsMCw1LjgtMC45LDcuMy0yLjVMNDAuMiwzNC44eiIgZmlsbD0iIzA1MTI0NCIvPjwvZz48Zz48cGF0aCBkPSJNNDYsNDFoNC43bDEtM2g2LjRsMC42LDNoNC44bC0xLjctNy4zbC02LTUuOGwwLjMsMS42YzAsMCwxLDQuNSwxLjIsNS41aC00LjJjMC40LTEsMi01LjMsMi01LjMgICAgYzAsMCwwLjQtMS4xLDAuNy0xLjgiIGZpbGw9IiMwNTEyNDQiLz48L2c+PC9nPjwvc3ZnPg=='>
            <img alt='Mastercard with Stripe' style='width:47px; max-width: none; margin-left: 10px;' src='data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkJz48c3ZnIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDY0IDY0IiBoZWlnaHQ9IjY0cHgiIGlkPSJMYXllcl8xIiB2ZXJzaW9uPSIxLjEiIHZpZXdCb3g9IjAgMCA2NCA2NCIgd2lkdGg9IjY0cHgiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPjxnPjxnPjxnPjxwYXRoIGQ9Ik02My41LDMyYzAsMTAuNC04LjQsMTguOS0xOC45LDE4LjljLTEwLjQsMC0xOC45LTguNS0xOC45LTE4Ljl2MGMwLTEwLjQsOC40LTE4LjksMTguOC0xOC45ICAgICBDNTUuMSwxMy4xLDYzLjUsMjEuNiw2My41LDMyQzYzLjUsMzIsNjMuNSwzMiw2My41LDMyeiIgZmlsbD0iI0ZGQjYwMCIvPjwvZz48L2c+PGc+PGc+PHBhdGggZD0iTTQ0LjYsMTMuMWMxMC40LDAsMTguOSw4LjUsMTguOSwxOC45YzAsMCwwLDAsMCwwYzAsMTAuNC04LjQsMTguOS0xOC45LDE4LjljLTEwLjQsMC0xOC45LTguNS0xOC45LTE4LjkgICAgICIgZmlsbD0iI0Y3OTgxRCIvPjwvZz48L2c+PGc+PGc+PHBhdGggZD0iTTQ0LjYsMTMuMWMxMC40LDAsMTguOSw4LjUsMTguOSwxOC45YzAsMCwwLDAsMCwwYzAsMTAuNC04LjQsMTguOS0xOC45LDE4LjkiIGZpbGw9IiNGRjg1MDAiLz48L2c+PC9nPjxnPjxnPjxwYXRoIGQ9Ik0xOS4yLDEzLjFDOC45LDEzLjIsMC41LDIxLjYsMC41LDMyYzAsMTAuNCw4LjQsMTguOSwxOC45LDE4LjljNC45LDAsOS4zLTEuOSwxMi43LTQuOWwwLDBoMCAgICAgYzAuNy0wLjYsMS4zLTEuMywxLjktMmgtMy45Yy0wLjUtMC42LTEtMS4zLTEuNC0xLjloNi43YzAuNC0wLjYsMC44LTEuMywxLjEtMmgtOC45Yy0wLjMtMC42LTAuNi0xLjMtMC44LTJoMTAuNCAgICAgYzAuNi0xLjksMS0zLjksMS02YzAtMS40LTAuMi0yLjctMC40LTRIMjYuMmMwLjEtMC43LDAuMy0xLjMsMC41LTJoMTAuNGMtMC4yLTAuNy0wLjUtMS40LTAuOC0yaC04LjhjMC4zLTAuNywwLjctMS4zLDEuMS0yaDYuNyAgICAgYy0wLjQtMC43LTAuOS0xLjQtMS41LTJoLTMuN2MwLjYtMC43LDEuMi0xLjMsMS45LTEuOWMtMy4zLTMuMS03LjgtNC45LTEyLjctNC45QzE5LjMsMTMuMSwxOS4zLDEzLjEsMTkuMiwxMy4xeiIgZmlsbD0iI0ZGNTA1MCIvPjwvZz48L2c+PGc+PGc+PHBhdGggZD0iTTAuNSwzMmMwLDEwLjQsOC40LDE4LjksMTguOSwxOC45YzQuOSwwLDkuMy0xLjksMTIuNy00LjlsMCwwaDBjMC43LTAuNiwxLjMtMS4zLDEuOS0yaC0zLjkgICAgIGMtMC41LTAuNi0xLTEuMy0xLjQtMS45aDYuN2MwLjQtMC42LDAuOC0xLjMsMS4xLTJoLTguOWMtMC4zLTAuNi0wLjYtMS4zLTAuOC0yaDEwLjRjMC42LTEuOSwxLTMuOSwxLTZjMC0xLjQtMC4yLTIuNy0wLjQtNCAgICAgSDI2LjJjMC4xLTAuNywwLjMtMS4zLDAuNS0yaDEwLjRjLTAuMi0wLjctMC41LTEuNC0wLjgtMmgtOC44YzAuMy0wLjcsMC43LTEuMywxLjEtMmg2LjdjLTAuNC0wLjctMC45LTEuNC0xLjUtMmgtMy43ICAgICBjMC42LTAuNywxLjItMS4zLDEuOS0xLjljLTMuMy0zLjEtNy44LTQuOS0xMi43LTQuOWMwLDAtMC4xLDAtMC4xLDAiIGZpbGw9IiNFNTI4MzYiLz48L2c+PC9nPjxnPjxnPjxwYXRoIGQ9Ik0xOS40LDUwLjljNC45LDAsOS4zLTEuOSwxMi43LTQuOWwwLDBoMGMwLjctMC42LDEuMy0xLjMsMS45LTJoLTMuOWMtMC41LTAuNi0xLTEuMy0xLjQtMS45aDYuNyAgICAgYzAuNC0wLjYsMC44LTEuMywxLjEtMmgtOC45Yy0wLjMtMC42LTAuNi0xLjMtMC44LTJoMTAuNGMwLjYtMS45LDEtMy45LDEtNmMwLTEuNC0wLjItMi43LTAuNC00SDI2LjJjMC4xLTAuNywwLjMtMS4zLDAuNS0yICAgICBoMTAuNGMtMC4yLTAuNy0wLjUtMS40LTAuOC0yaC04LjhjMC4zLTAuNywwLjctMS4zLDEuMS0yaDYuN2MtMC40LTAuNy0wLjktMS40LTEuNS0yaC0zLjdjMC42LTAuNywxLjItMS4zLDEuOS0xLjkgICAgIGMtMy4zLTMuMS03LjgtNC45LTEyLjctNC45YzAsMC0wLjEsMC0wLjEsMCIgZmlsbD0iI0NCMjAyNiIvPjwvZz48L2c+PGc+PGc+PGc+PHBhdGggZD0iTTI2LjEsMzYuOGwwLjMtMS43Yy0wLjEsMC0wLjMsMC4xLTAuNSwwLjFjLTAuNywwLTAuOC0wLjQtMC43LTAuNmwwLjYtMy41aDEuMWwwLjMtMS45aC0xbDAuMi0xLjJoLTIgICAgICBjMCwwLTEuMiw2LjYtMS4yLDcuNGMwLDEuMiwwLjcsMS43LDEuNiwxLjdDMjUuNCwzNy4xLDI1LjksMzYuOSwyNi4xLDM2Ljh6IiBmaWxsPSIjRkZGRkZGIi8+PC9nPjwvZz48Zz48Zz48cGF0aCBkPSJNMjYuOCwzMy42YzAsMi44LDEuOSwzLjUsMy41LDMuNWMxLjUsMCwyLjEtMC4zLDIuMS0wLjNsMC40LTEuOWMwLDAtMS4xLDAuNS0yLjEsMC41ICAgICAgYy0yLjIsMC0xLjgtMS42LTEuOC0xLjZoNC4xYzAsMCwwLjMtMS4zLDAuMy0xLjhjMC0xLjMtMC43LTIuOS0yLjktMi45QzI4LjMsMjguOSwyNi44LDMxLjEsMjYuOCwzMy42eiBNMzAuMywzMC43ICAgICAgYzEuMSwwLDAuOSwxLjMsMC45LDEuNEgyOUMyOSwzMiwyOS4yLDMwLjcsMzAuMywzMC43eiIgZmlsbD0iI0ZGRkZGRiIvPjwvZz48L2c+PGc+PGc+PHBhdGggZD0iTTQzLDM2LjhsMC40LTIuMmMwLDAtMSwwLjUtMS43LDAuNWMtMS40LDAtMi0xLjEtMi0yLjNjMC0yLjQsMS4yLTMuNywyLjYtMy43YzEsMCwxLjgsMC42LDEuOCwwLjYgICAgICBsMC4zLTIuMWMwLDAtMS4yLTAuNS0yLjMtMC41Yy0yLjMsMC00LjYsMi00LjYsNS44YzAsMi41LDEuMiw0LjIsMy42LDQuMkM0MS45LDM3LjEsNDMsMzYuOCw0MywzNi44eiIgZmlsbD0iI0ZGRkZGRiIvPjwvZz48L2c+PGc+PGc+PHBhdGggZD0iTTE1LjEsMjguOWMtMS40LDAtMi40LDAuNC0yLjQsMC40bC0wLjMsMS43YzAsMCwwLjktMC40LDIuMi0wLjRjMC43LDAsMS4zLDAuMSwxLjMsMC43ICAgICAgYzAsMC40LTAuMSwwLjUtMC4xLDAuNXMtMC42LDAtMC45LDBjLTEuNywwLTMuNiwwLjctMy42LDNjMCwxLjgsMS4yLDIuMiwxLjksMi4yYzEuNCwwLDItMC45LDIuMS0wLjlsLTAuMSwwLjhoMS44bDAuOC01LjUgICAgICBDMTcuOCwyOSwxNS44LDI4LjksMTUuMSwyOC45eiBNMTUuNSwzMy40YzAsMC4zLTAuMiwxLjktMS40LDEuOWMtMC42LDAtMC44LTAuNS0wLjgtMC44YzAtMC41LDAuMy0xLjIsMS44LTEuMiAgICAgIEMxNS40LDMzLjQsMTUuNSwzMy40LDE1LjUsMzMuNHoiIGZpbGw9IiNGRkZGRkYiLz48L2c+PC9nPjxnPjxnPjxwYXRoIGQ9Ik0xOS43LDM3YzAuNSwwLDMsMC4xLDMtMi42YzAtMi41LTIuNC0yLTIuNC0zYzAtMC41LDAuNC0wLjcsMS4xLTAuN2MwLjMsMCwxLjQsMC4xLDEuNCwwLjFsMC4zLTEuOCAgICAgIGMwLDAtMC43LTAuMi0xLjktMC4yYy0xLjUsMC0zLDAuNi0zLDIuNmMwLDIuMywyLjUsMi4xLDIuNSwzYzAsMC42LTAuNywwLjctMS4yLDAuN2MtMC45LDAtMS44LTAuMy0xLjgtMC4zbC0wLjMsMS44ICAgICAgQzE3LjUsMzYuOCwxOCwzNywxOS43LDM3eiIgZmlsbD0iI0ZGRkZGRiIvPjwvZz48L2c+PGc+PGc+PHBhdGggZD0iTTU5LjYsMjcuM0w1OS4yLDMwYzAsMC0wLjgtMS0xLjktMWMtMS44LDAtMy40LDIuMi0zLjQsNC44YzAsMS42LDAuOCwzLjMsMi41LDMuMyAgICAgIGMxLjIsMCwxLjktMC44LDEuOS0wLjhsLTAuMSwwLjdoMmwxLjUtOS42TDU5LjYsMjcuM3ogTTU4LjcsMzIuNmMwLDEuMS0wLjUsMi41LTEuNiwyLjVjLTAuNywwLTEuMS0wLjYtMS4xLTEuNiAgICAgIGMwLTEuNiwwLjctMi42LDEuNi0yLjZDNTguMywzMC45LDU4LjcsMzEuNCw1OC43LDMyLjZ6IiBmaWxsPSIjRkZGRkZGIi8+PC9nPjwvZz48Zz48Zz48cGF0aCBkPSJNNC4yLDM2LjlsMS4yLTcuMmwwLjIsNy4ySDdsMi42LTcuMmwtMS4xLDcuMmgyLjFsMS42LTkuNkg4LjlsLTIsNS45bC0wLjEtNS45SDMuOWwtMS42LDkuNkg0LjJ6IiBmaWxsPSIjRkZGRkZGIi8+PC9nPjwvZz48Zz48Zz48cGF0aCBkPSJNMzUuMiwzNi45YzAuNi0zLjMsMC43LTYsMi4xLTUuNWMwLjItMS4zLDAuNS0xLjgsMC43LTIuM2MwLDAtMC4xLDAtMC40LDBjLTAuOSwwLTEuNiwxLjItMS42LDEuMiAgICAgIGwwLjItMS4xaC0xLjlsLTEuMyw3LjhIMzUuMnoiIGZpbGw9IiNGRkZGRkYiLz48L2c+PC9nPjxnPjxnPjxwYXRoIGQ9Ik00Ny42LDI4LjljLTEuNCwwLTIuNCwwLjQtMi40LDAuNGwtMC4zLDEuN2MwLDAsMC45LTAuNCwyLjItMC40YzAuNywwLDEuMywwLjEsMS4zLDAuNyAgICAgIGMwLDAuNC0wLjEsMC41LTAuMSwwLjVzLTAuNiwwLTAuOSwwYy0xLjcsMC0zLjYsMC43LTMuNiwzYzAsMS44LDEuMiwyLjIsMS45LDIuMmMxLjQsMCwyLTAuOSwyLjEtMC45bC0wLjEsMC44aDEuOGwwLjgtNS41ICAgICAgQzUwLjQsMjksNDguMywyOC45LDQ3LjYsMjguOXogTTQ4LjEsMzMuNGMwLDAuMy0wLjIsMS45LTEuNCwxLjljLTAuNiwwLTAuOC0wLjUtMC44LTAuOGMwLTAuNSwwLjMtMS4yLDEuOC0xLjIgICAgICBDNDgsMzMuNCw0OCwzMy40LDQ4LjEsMzMuNHoiIGZpbGw9IiNGRkZGRkYiLz48L2c+PC9nPjxnPjxnPjxwYXRoIGQ9Ik01MiwzNi45YzAuNi0zLjMsMC43LTYsMi4xLTUuNWMwLjItMS4zLDAuNS0xLjgsMC43LTIuM2MwLDAtMC4xLDAtMC40LDBjLTAuOSwwLTEuNiwxLjItMS42LDEuMiAgICAgIGwwLjItMS4xaC0xLjlsLTEuMyw3LjhINTJ6IiBmaWxsPSIjRkZGRkZGIi8+PC9nPjwvZz48L2c+PGc+PGc+PGc+PHBhdGggZD0iTTIzLDM1LjRjMCwxLjIsMC43LDEuNywxLjYsMS43YzAuNywwLDEuMy0wLjIsMS41LTAuM2wwLjMtMS43Yy0wLjEsMC0wLjMsMC4xLTAuNSwwLjEgICAgICBjLTAuNywwLTAuOC0wLjQtMC43LTAuNmwwLjYtMy41aDEuMWwwLjMtMS45aC0xbDAuMi0xLjIiIGZpbGw9IiNEQ0U1RTUiLz48L2c+PC9nPjxnPjxnPjxwYXRoIGQ9Ik0yNy44LDMzLjZjMCwyLjgsMC45LDMuNSwyLjUsMy41YzEuNSwwLDIuMS0wLjMsMi4xLTAuM2wwLjQtMS45YzAsMC0xLjEsMC41LTIuMSwwLjUgICAgICBjLTIuMiwwLTEuOC0xLjYtMS44LTEuNmg0LjFjMCwwLDAuMy0xLjMsMC4zLTEuOGMwLTEuMy0wLjctMi45LTIuOS0yLjlDMjguMywyOC45LDI3LjgsMzEuMSwyNy44LDMzLjZ6IE0zMC4zLDMwLjcgICAgICBjMS4xLDAsMS4zLDEuMywxLjMsMS40SDI5QzI5LDMyLDI5LjIsMzAuNywzMC4zLDMwLjd6IiBmaWxsPSIjRENFNUU1Ii8+PC9nPjwvZz48Zz48Zz48cGF0aCBkPSJNNDMsMzYuOGwwLjQtMi4yYzAsMC0xLDAuNS0xLjcsMC41Yy0xLjQsMC0yLTEuMS0yLTIuM2MwLTIuNCwxLjItMy43LDIuNi0zLjdjMSwwLDEuOCwwLjYsMS44LDAuNiAgICAgIGwwLjMtMi4xYzAsMC0xLjItMC41LTIuMy0wLjVjLTIuMywwLTMuNiwyLTMuNiw1LjhjMCwyLjUsMC4yLDQuMiwyLjYsNC4yQzQxLjksMzcuMSw0MywzNi44LDQzLDM2Ljh6IiBmaWxsPSIjRENFNUU1Ii8+PC9nPjwvZz48Zz48Zz48cGF0aCBkPSJNMTIuNCwzMS4xYzAsMCwwLjktMC40LDIuMi0wLjRjMC43LDAsMS4zLDAuMSwxLjMsMC43YzAsMC40LTAuMSwwLjUtMC4xLDAuNXMtMC42LDAtMC45LDAgICAgICBjLTEuNywwLTMuNiwwLjctMy42LDNjMCwxLjgsMS4yLDIuMiwxLjksMi4yYzEuNCwwLDItMC45LDIuMS0wLjlsLTAuMSwwLjhoMS44bDAuOC01LjVjMC0yLjMtMi0yLjQtMi44LTIuNCBNMTYuNSwzMy40ICAgICAgYzAsMC4zLTEuMiwxLjktMi40LDEuOWMtMC42LDAtMC44LTAuNS0wLjgtMC44YzAtMC41LDAuMy0xLjIsMS44LTEuMkMxNS40LDMzLjQsMTYuNSwzMy40LDE2LjUsMzMuNHoiIGZpbGw9IiNEQ0U1RTUiLz48L2c+PC9nPjxnPjxnPjxwYXRoIGQ9Ik0xNy41LDM2LjhjMCwwLDAuNiwwLjIsMi4zLDAuMmMwLjUsMCwzLDAuMSwzLTIuNmMwLTIuNS0yLjQtMi0yLjQtM2MwLTAuNSwwLjQtMC43LDEuMS0wLjcgICAgICBjMC4zLDAsMS40LDAuMSwxLjQsMC4xbDAuMy0xLjhjMCwwLTAuNy0wLjItMS45LTAuMmMtMS41LDAtMiwwLjYtMiwyLjZjMCwyLjMsMS41LDIuMSwxLjUsM2MwLDAuNi0wLjcsMC43LTEuMiwwLjciIGZpbGw9IiNEQ0U1RTUiLz48L2c+PC9nPjxnPjxnPjxwYXRoIGQ9Ik01OS4yLDMwYzAsMC0wLjgtMS0xLjktMWMtMS44LDAtMi40LDIuMi0yLjQsNC44YzAsMS42LTAuMiwzLjMsMS41LDMuM2MxLjIsMCwxLjktMC44LDEuOS0wLjhsLTAuMSwwLjcgICAgICBoMmwxLjUtOS42IE01OS4xLDMyLjZjMCwxLjEtMC45LDIuNS0yLDIuNWMtMC43LDAtMS4xLTAuNi0xLjEtMS42YzAtMS42LDAuNy0yLjYsMS42LTIuNkM1OC4zLDMwLjksNTkuMSwzMS40LDU5LjEsMzIuNnoiIGZpbGw9IiNEQ0U1RTUiLz48L2c+PC9nPjxnPjxnPjxwYXRoIGQ9Ik00LjIsMzYuOWwxLjItNy4ybDAuMiw3LjJIN2wyLjYtNy4ybC0xLjEsNy4yaDIuMWwxLjYtOS42SDkuN2wtMi44LDUuOWwtMC4xLTUuOUg1LjdsLTMuNCw5LjZINC4yeiIgZmlsbD0iI0RDRTVFNSIvPjwvZz48L2c+PGc+PGc+PHBhdGggZD0iTTMzLjEsMzYuOWgyLjFjMC42LTMuMywwLjctNiwyLjEtNS41YzAuMi0xLjMsMC41LTEuOCwwLjctMi4zYzAsMC0wLjEsMC0wLjQsMGMtMC45LDAtMS42LDEuMi0xLjYsMS4yICAgICAgbDAuMi0xLjEiIGZpbGw9IiNEQ0U1RTUiLz48L2c+PC9nPjxnPjxnPjxwYXRoIGQ9Ik00NC45LDMxLjFjMCwwLDAuOS0wLjQsMi4yLTAuNGMwLjcsMCwxLjMsMC4xLDEuMywwLjdjMCwwLjQtMC4xLDAuNS0wLjEsMC41cy0wLjYsMC0wLjksMCAgICAgIGMtMS43LDAtMy42LDAuNy0zLjYsM2MwLDEuOCwxLjIsMi4yLDEuOSwyLjJjMS40LDAsMi0wLjksMi4xLTAuOWwtMC4xLDAuOGgxLjhsMC44LTUuNWMwLTIuMy0yLTIuNC0yLjgtMi40IE00OSwzMy40ICAgICAgYzAsMC4zLTEuMiwxLjktMi40LDEuOWMtMC42LDAtMC44LTAuNS0wLjgtMC44YzAtMC41LDAuMy0xLjIsMS44LTEuMkM0OCwzMy40LDQ5LDMzLjQsNDksMzMuNHoiIGZpbGw9IiNEQ0U1RTUiLz48L2c+PC9nPjxnPjxnPjxwYXRoIGQ9Ik00OS45LDM2LjlINTJjMC42LTMuMywwLjctNiwyLjEtNS41YzAuMi0xLjMsMC41LTEuOCwwLjctMi4zYzAsMC0wLjEsMC0wLjQsMGMtMC45LDAtMS42LDEuMi0xLjYsMS4yICAgICAgbDAuMi0xLjEiIGZpbGw9IiNEQ0U1RTUiLz48L2c+PC9nPjwvZz48L2c+PC9zdmc+'>                                                            
            ",
            // Driver Directory Specs
            'callback_settings_function' => 'settings_admin',
            'callback_charge_function'   => 'checkout',
            'callback_validate_function' => 'validate',
            'callback_refund_function'   => 'refund',
            'callback_record_function'   => 'record',
            'callback_read_function'     => 'read',
            'callback_form_function'     => 'form',
        ];
    }

    public function __Enabled() {
        return !empty($this->wallet_settings["stripe_enabled"]);
    }

    public function settings_admin() {
        $defaults = [
            'stripe_api_key'            => '',
            'stripe_api_secret'         => '',
            'stripe_sandbox_api_key'    => '',
            'stripe_sandbox_api_secret' => '',
            'stripe_sandbox'            => 0,
            'stripe_enabled'            => 0,
        ];

        $settings = $this->wallet_settings ? $this->wallet_settings : [];

        $data = $settings + $defaults;

        if (post('save_stripe')) {
            //do manual
            $data = [
                'stripe_api_key'            => sanitizer('stripe_api_key', '', 'stripe_api_key'),
                'stripe_api_secret'         => sanitizer('stripe_api_secret', '', 'stripe_api_secret'),
                'stripe_sandbox_api_key'    => sanitizer('stripe_sandbox_api_key', '', 'stripe_sandbox_api_key'),
                'stripe_sandbox_api_secret' => sanitizer('stripe_sandbox_api_secret', '', 'stripe_sandbox_api_secret'),
                'stripe_sandbox'            => sanitizer('stripe_sandbox', '', 'stripe_sandbox'),
                "stripe_enabled"            => sanitizer("stripe_enabled", "", "stripe_enabled")
            ];

            if (fusion_safe()) {
                foreach ($data as $key => $input_value) {
                    $sql_param = [
                        ':val' => $input_value,
                        ':key' => $key,
                        ':inf' => 'wallet'
                    ];
                    if (isset($settings[$key])) {
                        dbquery(
                            "UPDATE `".DB_SETTINGS_INF."` SET `settings_value`=:val WHERE `settings_name`=:key AND `settings_inf`=:inf",
                            $sql_param
                        );
                    } else {
                        dbquery(
                            "INSERT INTO `".DB_SETTINGS_INF."` (`settings_name`, `settings_value`, `settings_inf`) VALUES (:key, :val, :inf)",
                            $sql_param
                        );
                    }
                }
                add_notice('success', 'Stripe Gateway Configuration Updated');
                if (post('save_stripe') == 'save_close') {
                    redirect(clean_request('', ['configure'], FALSE));
                }
                redirect(FUSION_REQUEST);
            }
        }

        $info = [
            'logo' => WALLET.'drivers/stripe/stripe.jpg',
            'form' => [
                'open'    => openform('stripeSettings', 'post', FORM_REQUEST, ['inline' => FALSE]),
                'close'   => closeform(),
                'field_1' =>
                    "<div class='row'><div class='col-xs-12 col-sm-3'><label>Stripe Status</label></div><div class='col-xs-12 col-sm-9'>".
                    form_checkbox("stripe_enabled", "Enable Stripe", $data["stripe_enabled"], ["reverse_label" => TRUE]).
                    "</div></div>".
                    form_text('stripe_api_key', 'Publishable API Key', $data['stripe_api_key'], ['required' => TRUE, 'inline' => TRUE,]),
                'field_2' => form_text('stripe_api_secret', 'Secret API Key', $data['stripe_api_secret'], ['required' => TRUE, 'inline' => TRUE]),
                'field_3' => form_text('stripe_sandbox_api_key', 'Secret Sandbox API Key', $data['stripe_sandbox_api_key'], ['required' => TRUE, 'inline' => TRUE,]),
                'field_4' => form_text('stripe_sandbox_api_secret', 'Secret Sandbox API Key', $data['stripe_sandbox_api_secret'], ['required' => TRUE, 'inline' => TRUE,]),
                'field_5' => "<div class='row'><div class='col-xs-12 col-sm-3'><label>Stripe Status</label></div><div class='col-xs-12 col-sm-9'>".
                    form_checkbox("stripe_sandbox", "Enable Sandbox Testing", $data["stripe_sandbox"], ["reverse_label" => TRUE]).
                    "</div></div>",
                'field_6' => form_button('cancel', 'Cancel', 'cancel_stripe', ['icon' => 'fad fa-times m-r-10']),
                'field_7' => form_button('save_stripe', 'Save Setings', 'save', ['icon' => 'fad fa-save']),
                'field_8' => form_button('save_stripe', 'Save and Close Settings', 'save_close', ['class' => 'btn-primary', 'icon' => 'fad fa-save']),
            ]
        ];

        return fusion_render(INFUSIONS."wallet/drivers/stripe/templates/", "admin.twig", $info, TRUE);

    }

    /**
     * Confirmation page will require this, we will need to confirm the payment in another page for this wallet model.
     *
     * Return and read records of the transaction status and provide a method to check whether it is verified or not.
     * if it is, then we will update the transaction and orders SQL
     * and then we will include the transaction file.
     *
     * @return array
     */
    public function validate() {
        $settings = fusion_get_settings();

        $nav_config = [
            [
                "profile-nav"        => [
                    "link_id"    => "profile-nav",
                    "link_name"  => "Go to Profile",
                    "link_cat"   => 0,
                    "link_class" => "btn btn-sm btn-primary",
                    "link_url"   => BASEDIR."edit_profile.php"
                ],
                "print-invoice-link" => [
                    "link_id"    => "print-invoice-link",
                    "link_name"  => "Print this Invoice",
                    "link_cat"   => 0,
                    "link_class" => "btn btn-sm btn-inverse print-invoice",
                    "link_url"   => "#"
                ],
            ]
        ];

        Core::replaceAdditionalNav($nav_config);

        $transaction_number = get("token");
        $transaction_ref = get("transaction_ref");

        if (!$transaction_number || !$transaction_ref) {
            add_notice('danger', 'An invalid checkout url was provided.', $settings["opening_page"]);
            redirect(BASEDIR.$settings["opening_page"]);
        }

        // Log Requests
        $transaction = new Wallet_Transaction();

        if ($transaction->getRef($transaction_ref)) {

            $transaction_data = $transaction->transactionData();

            if ($transaction_data["transaction_number"] === $transaction_number) {

                $timestamp = $transaction_data["transaction_datestamp"];

                $transaction_data["eta_date"] = date("j M Y, H:m:s", $transaction_data["transaction_datestamp"] + 86400);

                $transaction_data["date"] = date("j M Y, h:m:s", $transaction_data["transaction_datestamp"]);

                $transaction_data["interval_date"] = date("j M Y, H:m:s", $transaction_data["transaction_datestamp"] + 3600);

                $transaction_data["transaction_orders"] = $transaction->getOrders();

                $wallet_info = Wallet::getInstance()->getUserWallet($transaction_data["transaction_user"]);

                $this->info = [
                    'store_name'         => $this->get_config('merchant_name') ?: Wallet_Model::walletSettings('store_name'),
                    'invoice'            => get('transaction_ref'),
                    'datestamp'          => date('j M Y, H:M:s', $timestamp),
                    'payment_status'     => ((int)$transaction_data["transaction_status"] === TRANSACTION_PAID ? "Completed" : "Pending"),
                    'currency'           => post('mc_currency'),
                    'transaction_status' => TRANSACTION_FAILED,
                    'mc_gross'           => post('mc_gross'),
                    'business_email'     => post('business'),
                ];

                $this->info += [
                    "wallet"      => $wallet_info,
                    "transaction" => $transaction_data
                ];

                if ((int)$transaction_data["transaction_status"] === TRANSACTION_PAID) {

                    $transaction_file = str_replace(fusion_get_settings('siteurl'), BASEDIR, rawurldecode($transaction_data["transaction_file"]));

                    // we need to run
                    if (is_file($transaction_file)) {

                        require_once $transaction_file;

                        // To return order_id and order info array
                        flatten_array(fusion_filter_hook("wallet_checkout", $transaction));

                        if ($completed_orders = $transaction->getCompletedOrders()) {

                            $completed_orders = implode("','", $completed_orders);

                            $order_result = dbquery("SELECT * FROM ".DB_WALLET_ORDERS." WHERE order_id IN ('$completed_orders')");

                            if (dbrows($order_result)) {
                                while ($order_data = dbarray($order_result)) {
                                    $this->info["transaction"]["transaction_orders"][$order_data["order_id"]] = $order_data;
                                }
                            }
                        }
                    } else {
                        $this->setError(800);
                    }
                }

                return (array)$this->info;

            } else {
                // redirect to security page.
                $this->setError(800);
            }
        } else {
            $this->setError(900);
        }

        return NULL;
    }

    /**
     * @param null $key
     *
     * @return mixed|null
     */
    public function get_config($key = NULL) {
        // get wallet settings
        /*
         *
                    $data = array(
                        'merchant_email' => $config['merchant_email'],
                        'paypal_sandbox' => $config['sandbox'],
                        'thanks_page'    => fusion_get_settings('siteurl').'infusions/wallet/wallet.php', // to return url
                        'notify_url'     => fusion_get_settings('siteurl').'infusions/wallet/paypal_ipn_verify.php', // ? to return.
                        'cancel_url'     => fusion_get_settings('siteurl').'infusions/wallet/wallet.php', // to origin url
                    );
         */
        $wallet_settings = $this->wallet_settings;
        $config['base_url'] = (server('HTTPS') ? "https" : "http")."://".server('SERVER_NAME').(server('SERVER_PORT') != 80 ? ":".server('SERVER_PORT') : '');
        $config['sandbox'] = $wallet_settings['stripe_sandbox'] ? TRUE : FALSE;
        $config['stripe_api_key'] = $wallet_settings['stripe_api_key'];
        $config['stripe_api_secret'] = $wallet_settings['stripe_api_secret'];
        if ($config['sandbox']) {
            $config['stripe_api_key'] = $wallet_settings['stripe_sandbox_api_key'];
            $config['stripe_api_secret'] = $wallet_settings['stripe_sandbox_api_secret'];
        }

        $config['DefaultCancelURL'] = isset($_REQUEST['origin_url']) ? $_REQUEST['origin_url'] : '';
        // The checkout url for stripe driver
        $config['DefaultCheckoutURL'] = fusion_get_settings('siteurl').'infusions/wallet/checkout.php?payment_method=stripe'.(isset($_REQUEST['payment_id']) ? "&payment_id=".$_REQUEST['payment_id'] : '');
        // Return checkout complete - the return url after completing purchase
        $config['DefaultCallbackURL'] = fusion_get_settings('siteurl').'infusions/wallet/confirmation.php?payment_method=stripe';
        // The notification processing url
        $config['DefaultNotificationURL'] = fusion_get_settings('siteurl').'infusions/wallet/ipn.php?payment_method=stripe'; // this is the IPN file
        // After done payment
        $config['DefaultReturnURL'] = fusion_get_settings('siteurl').'infusions/wallet/confirmation.php?payment_method=stripe'; // this is the thank you page.

        $config['UserID'] = fusion_get_userdata('user_id') ?: USER_IP;
        $config['PageTimeout'] = 15 * 60;
        $config['CustIP'] = FUSION_IP;

        // do notices for settings validation
        return ($key === NULL ? $config : (isset($config[$key]) ? $config[$key] : NULL));
    }

    /**
     * @param $error_code
     */
    private function setError($error_code) {
        fusion_stop();

        $errors = $this->errors[$error_code];

        add_notice('danger', "<strong>".$errors['title']."</strong> ".$errors["description"]);

        $log = [
            'log_errors'    => '(Stripe) '.$errors['title'],
            'log_id'        => 0,
            'log_user'      => fusion_get_userdata('user_id'),
            'log_data'      => Defender::sanitize_array($_REQUEST),
            'log_datestamp' => TIME,
        ];
        dbquery_insert(DB_WALLET_LOGS, $log, 'save');

        $this->info['errors'] = $errors;

    }

    /**
     * @param $options
     *
     * @return string
     */
    public function form($options) {

        $wallet = $this->wallet;

        $user_wallet = fusion_get_user_wallet(fusion_get_userdata('user_id'));

        $config = $this->get_config();

        if ($config['sandbox'] && iADMIN || !$config['sandbox']) {

            if ($config['sandbox']) {
                add_notice('danger', '<strong>Stripe Payment Notice:</strong> Development Sandbox Mode Enabled. No actual transactions will be made.');
            }

            $options += Wallet::get_driver_default_options();

            // Create a Payment Intent
            //$api_key = $config['stripe_api_secret'];
            $site_path = fusion_get_settings('site_path');
            $options['payment_id'] = $wallet->get_PaymentID($options);
            $options['transaction_shipping'] = $options['order_shipping'];
            $options['transaction_currency'] = $options['currency'];

            $options_json = json_encode($options);

            //$min_file = min_file(WALLET.'drivers/stripe/css/normalize.css');
            //add_to_head("<link rel='stylesheet' href='$min_file'/>");

            // Collecting Payment Information
            // Start Stripe Elements
            // See your keys here: https://dashboard.stripe.com/account/apikeys

            // Stripe Driver Js File
            $fusion_token = fusion_get_token($this->stripe_token, 1);
            //print_P($options);
            add_to_footer('<script src="https://js.stripe.com/v3/"></script>');
            fusion_load_script(INFUSIONS."wallet/drivers/stripe/js/sscc.js");
            $css_file_path = WALLET.'drivers/stripe/css/global.css';
            fusion_load_script($css_file_path, "css");
            add_to_jquery(/** @lang JavaScript */ "wwcc.ccfunction('".$options['payment_id']."', '".$config['stripe_api_key']."', $options_json, '".$options["display_amount_field"]."', '".$options['order_amount']."',  '$this->stripe_token', '$fusion_token');");

            // the action url stagnant and will be overidden by js
            $action_url = $site_path.'infusions/wallet/checkout.php?payment_method=stripe&payment_id='.$wallet->get_PaymentID($options);
            $openform = openform('stripePaymentForm', 'post', $action_url, ['remote_url' => $site_path.'infusions/wallet/api/?api=checkout', 'class' => 'sr-payment-form']);
            $closeform = closeform();
            $payment_type = form_hidden('order_payment_type', '', 'card', ['input_id' => 'payment_type_stripe']);
            $payment_method = form_hidden('order_payment_method', '', 'stripe', ['input_id' => 'payment_method_stripe']);
            $payment_currency = form_hidden('order_payment_currency', '', 'USD', ['input_id' => 'payment_currency_stripe']);
            $return_url = form_hidden("return_url", "", $options["return_url"], ["input_id" => "return_url_stripe"]);

            $order_items = '';
            if (!empty($options['items'])) {
                foreach ($options['items'] as $item_id => $item) {
                    $default = [
                        'id'          => '',
                        'type'        => '',
                        'title'       => '',
                        'description' => '',
                        'tax'         => '',
                        'shipping'    => '',
                        'quantity'    => '',
                        'price'       => '',
                        'currency'    => '',
                        'options'     => '',
                        'info'        => '',
                        'interval'    => '',
                        'cycle'       => '',
                    ];
                    $item += $default;

                    $order_items .= form_hidden('order_item_id[]', '', $item['id'], ['input_id' => 'str_oid_'.$item_id]);
                    $order_items .= form_hidden('order_item_type[]', '', $item['type'], ['input_id' => 'str_type'.$item_id]);
                    $order_items .= form_hidden('order_title[]', '', strip_tags($item['title']), ['input_id' => 'str_title_'.$item_id]);
                    $order_items .= form_hidden('order_description[]', '', strip_tags($item['description']), ['input_id' => 'str_desc_'.$item_id]);
                    $order_items .= form_hidden('order_tax[]', '', $item['tax'], ['input_id' => 'str_tax_'.$item_id]);
                    $order_items .= form_hidden('order_shipping[]', '', $item['shipping'], ['input_id' => 'str_shipping_'.$item_id]);
                    $order_items .= form_hidden('order_quantity[]', '', $item['quantity'], ['input_id' => 'str_qty_'.$item_id]);
                    $order_items .= form_hidden('order_amount[]', '', $item['price'], ['input_id' => 'str_amt_'.$item_id]);
                    $order_items .= form_hidden('order_currency[]', '', $item['currency'], ['input_id' => 'str_currency_'.$item_id]);
                    $order_items .= form_hidden('order_options[]', '', $item['options'], ['input_id' => 'str_opts_'.$item_id]);
                    $order_items .= form_hidden('order_info[]', '', $item['info'], ['input_id' => 'str_info_'.$item_id]);
                    $order_items .= form_hidden('order_interval[]', '', $item['interval'], ['input_id' => 'str_interval_'.$item_id]);
                    $order_items .= form_hidden('order_cycle[]', '', $item['cycle'], ['input_id' => 'str_cycle_'.$item_id]);
                }
            }

            $info = [
                'form' => [
                    'openform'         => $openform,
                    'closeform'        => $closeform,
                    'payment_type'     => $payment_type,
                    'payment_method'   => $payment_method,
                    'payment_currency' => $payment_currency,
                    'return_url'       => $return_url,
                    'order_items'      => $order_items,
                ]
            ];

            return fusion_render(WALLET.'drivers/stripe/templates/', 'form.twig', $info, TRUE);
        }

        return 'Wallet cannot be viewed due to insufficient rights';
    }

}
