<?php
namespace PHPFusion\Infusions\Wallet\Drivers\Twocheckout;

abstract class Twocheckout
{
    public static $sid;
    public static $privateKey;
    public static $username;
    public static $password;
    public static $sandbox;
    public static $verifySSL = true;
    public static $baseUrl = 'https://www.2checkout.com';
    public static $error;
    public static $format = 'array';

    const VERSION = '0.3.1';


    public static function sellerId($value = null) {
        self::$sid = $value;
    }

    public static function privateKey($value = null) {
        self::$privateKey = $value;
    }

    public static function username($value = null) {
        self::$username = $value;
    }

    public static function password($value = null) {
        self::$password = $value;
    }

    public static function sandbox($value = null) {
        if ($value == 1 || $value == true) {
            self::$sandbox = true;
            self::$baseUrl = 'https://sandbox.2checkout.com';
        } else {
            self::$sandbox = false;
            self::$baseUrl = 'https://www.2checkout.com';
        }
    }

    public static function verifySSL($value = null) {
        if ($value == 0 || $value == false) {
            self::$verifySSL = false;
        } else {
            self::$verifySSL = true;
        }
    }

    public static function format($value = null) {
        self::$format = $value;
    }

}

require(dirname(__FILE__).'/Includes/Api/TwocheckoutAccount.php');
require(dirname(__FILE__).'/Includes/Api/TwocheckoutPayment.php');
require(dirname(__FILE__).'/Includes/Api/TwocheckoutApi.php');
require(dirname(__FILE__).'/Includes/Api/TwocheckoutSale.php');
require(dirname(__FILE__).'/Includes/Api/TwocheckoutProduct.php');
require(dirname(__FILE__).'/Includes/Api/TwocheckoutCoupon.php');
require(dirname(__FILE__).'/Includes/Api/TwocheckoutOption.php');
require(dirname(__FILE__).'/Includes/Api/TwocheckoutUtil.php');
require(dirname(__FILE__).'/Includes/Api/TwocheckoutError.php');
require(dirname(__FILE__).'/Includes/TwocheckoutReturn.php');
require(dirname(__FILE__).'/Includes/TwocheckoutNotification.php');
require(dirname(__FILE__).'/Includes/TwocheckoutCharge.php');
require(dirname(__FILE__).'/Includes/TwocheckoutMessage.php');
