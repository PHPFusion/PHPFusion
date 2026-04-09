<?php
namespace PHPFusion\Infusions\Wallet\Drivers\Twocheckout\Includes\Api;

class Twocheckout_Error extends \Exception
{
    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
