<?php
namespace PHPFusion\Infusions\Wallet\Classes;

use ReflectionClass;

(defined("IN_FUSION") || exit);

/**
 * Class Gateways
 *
 * @package PHPFusion\Infusions\Wallet\Classes
 */
class Gateways extends Wallet_Model {

    // first lets load the driver
    private $driver_paths = [];

    private $driver_cache = [];

    private $drivers = [];

    private $driver_title = "";

    private $driver_load = "";

    private $driver_method = '';

    private $paymentMethods = [];

    //private $wallet = [];

    private $installed_drivers = [];

    // User wallet information added to gateway for driver
    private $wallet_info = [];

    /*
     * Read the drivers
     */
    public function getDriverDir() {

        if (empty($this->driver_paths)) {

            $default_driver_path = WALLET."drivers/";

            $folder = makefilelist($default_driver_path, ".|..|", TRUE, "folders");

            if (!empty($folder)) {

                foreach ($folder as $folder_name) {

                    $this->driver_paths[$folder_name] = $default_driver_path.$folder_name;

                }

            }
        }

        return $this->driver_paths;
    }

    /*
     * Get the driver to load with the payment method ID
     * Deprecate
     */
    public function getDriver($payment_method) {
        if (empty($this->driver_load)) {
            $result = dbquery("SELECT * FROM ".DB_WALLET_DRIVERS." WHERE driver_folder='".$payment_method."'");
            if (dbrows($result) > 0) {
                $this->driver_load = dbarray($result);
                $this->driver_title = $this->driver_load['driver_title'];
                $this->driver_method = $payment_method;
            } else if ($payment_method == 'credit') {
                $this->driver_load = [
                    'driver_folder'  => 'credit',
                    'driver_title'   => 'Wallet Credit',
                    'driver_version' => '1.00'
                ];
            }
        }
        return $this->driver_load;
    }

    // Upon executing getDriver
    public function getDriverName() {
        return $this->driver_title;
    }

    public function getDriverMethod() {
        return $this->driver_method;
    }

    /**
     * @param $wallet_data
     */
    public function setWalletInfo($wallet_data) {
        $this->wallet_info = $wallet_data;
    }

    /**
     * @param null $method
     *
     * @return array|mixed|null
     */
    public function getPaymentMethods($method = NULL) {

        if (empty($this->paymentMethods)) {

            $balance = 0;
            if (isset($this->wallet_info["gold_balance"])) {
                $balance = $this->wallet_info["gold_balance"];
            }

            $this->paymentMethods['credit'] = [
                'title'                      => 'Fusion Gold Coins',
                'description'                => 'Cheapest PHPFusion payments',
                'link'                       => '',
                'author'                     => 'PHP-Fusion Inc',
                'author_web'                 => 'https://www.php-fusion.co.uk',
                'author_email'               => 'mt@php-fusion.co.uk',
                'version'                    => '1.00',
                'pay_method'                 => 'Wallet Coin Credits',
                'pay_image'                  => '<h5 title="PHPFusion Coins" class="text-dark"><i class="far fa-coins fa-sm m-r-10"></i>'.number_format($balance, 2).'</h5>',
                'callback_settings_function' => '',
                'callback_charge_function'   => '',
                'callback_validate_function' => "validate",
                'callback_refund_function'   => 'refund',
                'callback_record_function'   => 'record',
                'callback_read_function'     => 'read',
                "callback_form_function"     => "form"
            ];

            if ($installedDrivers = $this->getInstalledDrivers()) {

                foreach ($installedDrivers as $driver_folder => $driver_name) {

                    $driver_folder = strtolower($driver_folder);

                    if ($driver = $this->loadDriver($driver_folder)) {

                        if ($driver->__Enabled() && !defined("IN_ADMIN")) {

                            $this->paymentMethods[$driver_folder] = $driver->__Properties();
                        }

                    }

                }
            }
        }


        return ($method !== NULL ? (isset($this->paymentMethods[$method]) ? $this->paymentMethods[$method] : NULL) : $this->paymentMethods);
    }

    /**
     * @return array
     */
    public function getInstalledDrivers() {
        if (empty($this->installed_drivers)) {
            $result = dbquery("SELECT driver_title, driver_folder, driver_version FROM ".DB_WALLET_DRIVERS);
            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $data['driver_path'] = WALLET."drivers/".$data['driver_folder']."/".$data['driver_folder']."-driver.class.php";
                    $this->installed_drivers[$data['driver_folder']] = $data['driver_title'];
                }
            }
        }
        return $this->installed_drivers;
    }

    /**
     * Load and require the driver file and set to class $drivers variable
     *
     * @param $driver_name - folder name
     *
     * @return object
     */
    public function loadDriver($driver_name) {

        $namespace = "\\PHPFusion\\Infusions\\Wallet\\Drivers\\";

        $driver_namespace = $driver_name."\\";

        $driver_class = ucfirst($driver_name)."_Driver";

        $class = $namespace.$driver_namespace.$driver_class;

        if (class_exists($class)) {
            try {
                $class = new ReflectionClass($class);

                $this->drivers[$driver_name] = $class->newInstance();

                if (isset($this->drivers[$driver_name])) {

                    return (object)$this->drivers[$driver_name];


                } else {
                    fusion_stop("$driver_name could not be loaded.");
                }
            } catch (\Exception $e) {
                set_error(E_CORE_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
            }
        } else {
            add_notice("danger", ucfirst($driver_name) . " error and could not be loaded.");
        }

        return NULL;
    }

    /**
     * @return array
     */
    public function cacheDrivers() {
        if (empty($this->driver_cache)) {
            $this->driver_cache['credit'] = "Credits";
            $result = dbquery("SELECT driver_title, driver_folder FROM ".DB_WALLET_DRIVERS);
            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $this->driver_cache[$data['driver_folder']] = $data['driver_title'];
                }
            }
        }
        return $this->driver_cache;
    }

}
