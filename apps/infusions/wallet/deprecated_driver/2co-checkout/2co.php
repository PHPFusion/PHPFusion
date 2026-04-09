<?php
define('FUSION_ALLOW_REMOTE', true);
require_once dirname(__FILE__).'/../../../maincore.php';
require_once THEMES."templates/header.php";
$gateway = new \Wallet\Gateways();
$installed_drivers = $gateway->getInstalledDrivers();
if (isset($installed_drivers[$_GET['payment']])) {
    $payment_gateway = stripinput($_GET['payment']);
    $driver = $gateway->loadDriver($payment_gateway);
    $driverData = $driver->__Properties();
    if (method_exists($driver, $driverData['callback_validate_function'])) {
        $driver->$driverData['callback_validate_function']();
    } else {
        throw new \Exception('There are no Gateway');
    }
}
require_once THEMES."templates/footer.php";