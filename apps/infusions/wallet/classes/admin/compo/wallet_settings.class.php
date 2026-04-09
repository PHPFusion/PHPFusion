<?php

namespace PHPFusion\Infusions\Wallet\Classes\Admin\Compo;

use PHPFusion\Geomap;
use PHPFusion\Infusions\Wallet\Classes\Gateways;
use PHPFusion\Infusions\Wallet\Classes\Wallet_Model;

/**
 * Class Wallet_Settings
 *
 * @package PHPFusion\Infusions\Wallet\Classes\Admin\Compo
 */
class Wallet_Settings extends Wallet_Model {

    /**
     * @return string
     */
    private function __coinSettings() {

        $locale = fusion_get_locale();

        $storeSettings = self::walletSettings();

        if (isset($_POST['save_changes']) || isset($_POST['form_id']) && $_POST['form_id'] === 'creditfrm') {

            $old_coin_unit_value = $storeSettings['coin_unit_value'];

            $storeSettings = [
                'coin_base_currency' => form_sanitizer($_POST['coin_base_currency'], 'USD', 'coin_base_currency'),
                'coin_unit_value'    => form_sanitizer($_POST['coin_unit_value'], '1', 'coin_unit_value'),
                'coin_tax_rate'      => form_sanitizer($_POST['coin_tax_rate'], '6', 'coin_tax_rate'),
                'coin_min_purchase'  => form_sanitizer($_POST['coin_min_purchase'], '50', 'coin_min_purchase'),
                'coin_max_float'     => form_sanitizer($_POST['coin_max_float'], '0', 'coin_max_float'),
                'coin_charging'      => isset($_POST['coin_charging']) ? 1 : 0,
                'coin_open'          => isset($_POST['coin_open']) ? 1 : 0,
                'coin_u2u_transfer'  => isset($_POST['coin_u2u_transfer']) ? 1 : 0,
                'coin_a2u_transfer'  => isset($_POST['coin_a2u_transfer']) ? 1 : 0,
            ];

            if (fusion_safe()) {
                foreach ($storeSettings as $key => $value) {
                    dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$value' WHERE `settings_name`=:key AND `settings_inf`=:inf", [
                        ":key" => $key,
                        ":inf" => 'wallet'
                    ]);
                }

                //$this->__recountPackages($storeSettings['coin_unit_value']);

                add_notice('success', 'Coin settings updated successfully');
                redirect(FUSION_REQUEST);
            }
        }

        $html = openform("creditfrm", "post", FUSION_REQUEST);

        $html .= "<h4 class='strong m-b-0'>Coin Value Settings</h4><span>Coin value and credit marketplace settings</span><hr/>";

        $html .= form_select("coin_base_currency", "Selling Currency", $storeSettings['coin_base_currency'], [
            "inline"  => TRUE,
            'width'   => '300px',
            "options" => Geomap::get_Currency()
        ]);

        add_to_jquery("$('#coin_base_currency').bind('change', function(e) { 
               var cval = $(this).val();
               $('#p-coin_unit_value-prepend').text(cval);     
               console.log(cval);                    
        });");

        $html .= form_text("coin_unit_value", "Coin Unit value", number_format($storeSettings['coin_unit_value'], 2),
            [
                "prepend"       => TRUE,
                "prepend_value" => $storeSettings['coin_base_currency'],
                "required"      => TRUE,
                "inner_width"   => "100px",
                "number_min"    => 0,
                "number_step"   => 0.05,
                "inline"        => TRUE,
                'type'          => 'number',
                "ext_tip"       => "This is the value of the currency per coin",
            ]);
        $html .= form_text("coin_min_purchase", "Min. Purchase Units", $storeSettings['coin_min_purchase'],
            ["required" => TRUE, 'width' => '100%', "inner_width" => "100px", "inline" => TRUE,
             'type'     => 'number'
            ]);
        $html .= form_text("coin_tax_rate", "Tax Rate", $storeSettings['coin_tax_rate'],
            [
                "width"  => "150px",
                "inline" => TRUE,
                'type'   => 'number', 'append' => TRUE, 'append_value' => '%'
            ]);

        $html .= "<h4 class='m-b-0 strong'>Coin Market Capital Settings</h4><span>The following configuration controls the coin market capital usage</span><hr/>";

        $html .= form_text("coin_max_float", "Credit Float Limit", $storeSettings['coin_max_float'],
            [
                "required"    => FALSE,
                'width'       => '100%',
                "inner_width" => "250px",
                "inline"      => TRUE,
                'type'        => 'number',
                'ext_tip'     => 'Credit float controls the maximum amount of token available in the system. 0 for unlimited (sell regardless)'
            ]);

        $html .= "<div class='clearfix'>";

        $html .= "<div class='col-xs-12 col-sm-3 p-l-0'>";

        $html .= "<label class='label-control'>Wallet Shop</label>";

        $html .= "</div><div class='col-xs-12 col-sm-9'>";

        $html .= form_checkbox("coin_charging", "Allow User to Purchase Credit", $storeSettings['coin_charging'], ["reverse_label" => TRUE]);

        $html .= form_checkbox("coin_open", "Allow User to Transact with Credit", $storeSettings['coin_open'], ["reverse_label" => TRUE]);

        $html .= form_checkbox("coin_u2u_transfer", "Allow User to User Credit Transfer", $storeSettings['coin_u2u_transfer'], ["reverse_label" => TRUE]);

        $html .= form_checkbox("coin_a2u_transfer", "Allow Administrator to User Credit Transfer", $storeSettings['coin_a2u_transfer'], ["reverse_label" => TRUE]);

        $html .= "</div></div>";

        $html .= form_button("save_changes", $locale['save_changes'], $locale['save_changes'], ["class" => "btn-primary"]);

        $html .= closeform();

        return $html;
    }

    public function __view() {

        if (check_post('cancel')) {
            redirect(clean_request('', ['configure'], FALSE));
        }

        $this->setPage("Store Settings", "str_settings");

        $this->setPage("Coin Market Settings", "srp_coins");

        $this->setPage("Wallet Settings", "srp_settings");

        $this->setPage("Gateway Settings", "srp_gateway");

        $page_active = tab_active(self::$page, "srp_settings", "page");

        echo opentab(self::$page, $page_active, "settings_tab", TRUE, "nav-tabs", "page", ["page", "configure"]);

        if (check_get("configure")) {

            $driverFunctions = new Gateways();

            if ($driver_class = $driverFunctions->loadDriver(get("configure"))) {

                if (method_exists($driver_class, "__Properties")) {

                    $info = $driver_class->__Properties();

                    if (isset($info["callback_settings_function"]) && method_exists($driver_class, $info["callback_settings_function"])) {

                        $str = $info["callback_settings_function"];

                        echo $driver_class->$str();
                    }
                }
            }
        } else {

            switch ($page_active) {
                case "srp_coins":
                    echo $this->__coinSettings();
                    break;
                case "srp_gateway":
                    echo $this->__driver_installer();
                    break;
                case "str_settings":
                    echo $this->__store_settings();
                    break;
                default:
                    echo $this->__wallet_settings();
            }
        }
        echo closetab();
    }

    /**
     * @return string
     */
    private function __store_settings() {
        $locale = fusion_get_locale();

        $wallet_settings = get_settings('wallet');

        $store_address = $wallet_settings["store_address"]
            ."|".$wallet_settings["store_address_2"]
            ."|".$wallet_settings["store_country"]
            ."|".$wallet_settings["store_region"]
            ."|".$wallet_settings["store_city"]
            ."|".$wallet_settings["store_postcode"];

        $store_phone = $wallet_settings["store_phone_cc"]."|".$wallet_settings["store_phone"];

        $store_fax = $wallet_settings["store_fax_cc"]."|".$wallet_settings["store_fax"];

        if (check_post("save_changes")) {

            $address = "";
            $address_2 = "";
            $city = "";
            $region = "";
            $country = "";
            $postcode = "";
            $phone_cc = "";
            $phone_number = "";
            $fax_cc = "";
            $fax_number = "";

            if ($store_address = sanitizer(["store_address"], "", "store_address")) {
                list($address, $address_2, $country, $region, $city, $postcode) = explode("|", $store_address);
            }

            if ($phone = sanitizer("store_phone", "", "store_phone")) {
                $phone_arr = explode("|", $phone);
                if (count($phone_arr) === 2) {
                    $phone_cc = $phone_arr[0];
                    $phone_number = $phone_arr[1];
                }
            }

            if ($phone = sanitizer("store_fax", "", "store_fax")) {
                $phone_arr = explode("|", $phone);
                if (count($phone_arr) === 2) {
                    $fax_cc = $phone_arr[0];
                    $fax_number = $phone_arr[1];
                }
            }

            $wallet_settings = [
                'store_name'            => form_sanitizer($_POST['store_name'], '', 'store_name'),
                'store_registration_no' => form_sanitizer($_POST['store_registration_no'], '', 'store_registration_no'),
                'store_email'           => form_sanitizer($_POST['store_email'], '', 'store_email'),
                'store_address'         => $address,
                'store_address_2'       => $address_2,
                'store_city'            => $city,
                'store_region'          => $region,
                'store_country'         => $country,
                'store_postcode'        => $postcode,
                'store_phone_cc'        => $phone_cc,
                'store_phone'           => $phone_number,
                'store_fax_cc'          => $fax_cc,
                'store_fax'             => $fax_number
            ];

            if (fusion_safe()) {

                foreach ($wallet_settings as $key => $value) {
                    dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$value' WHERE settings_name='$key' AND settings_inf='wallet'");
                }

                add_notice('success', "Store Settings Updated");

                redirect(FUSION_REQUEST);
            }
        }

        $html = openform("store_settingsfrm", "POST");

        $html .= "<h4 class='strong'>Wallet Settings</h4><hr/>";

        $html .= form_text('store_name', "Store Name", $wallet_settings['store_name'], ['required' => TRUE, 'inline' => TRUE]);

        $html .= form_text('store_registration_no', "Store Legal Registration", $wallet_settings['store_registration_no'], ['required' => TRUE, 'inline' => TRUE]);

        $html .= form_text('store_email', "Store Email", $wallet_settings['store_email'], ['required' => TRUE, 'inline' => TRUE, 'email' => TRUE]);

        $html .= form_geo("store_address", "Store Address", $store_address, ["required" => TRUE, "inline" => TRUE]);

        $html .= form_contact('store_phone', 'Phone Number', $store_phone, ['inline' => TRUE, 'required' => FALSE]);

        $html .= form_contact('store_fax', 'Fax Number', $store_fax, ['inline' => TRUE, 'required' => FALSE]);

        $html .= "<hr>";

        $html .= form_button("save_changes", $locale['save_changes'], $locale['save_changes'], ["class" => "btn-primary"]);

        $html .= closeform();

        return $html;
    }

    private function __wallet_settings() {

        $locale = fusion_get_locale();

        $walletSettings = get_settings('wallet');

        if (isset($_POST['save_changes'])) {

            $walletSettings = [
                'coin_sell_location'        => sanitizer("coin_sell_location", '', 'coin_sell_location'),
                'coin_location_restriction' => sanitizer("coin_location_restriction", '', 'coin_location_restriction'),
                'coin_customer_location'    => sanitizer("coin_customer_location", '', 'coin_customer_location'),
                'coin_notice_status'        => post('coin_notice_status') ? 1 : 0,
                'coin_notice_message'       => sanitizer("coin_notice_message", '', 'coin_notice_message'),
                'coin_repeat_order'         => post('coin_repeat_order') ? 1 : 0,
                'coin_clear_order'          => post('coin_clear_order') ? 1 : 0,
            ];

            if (fusion_safe()) {
                foreach ($walletSettings as $key => $value) {
                    dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='$value' WHERE settings_name='$key' AND settings_inf='Wallet'");
                }
                add_notice('success', 'Wallet Settings Updated');
                redirect(FUSION_REQUEST);
            }
        }

        // Your settings have been saved
        $html = openform("wallet_settingsfrm", "post");

        $html .= "<h4 class='strong m-b-0'>Checkout Settings</h4><span>Global checkout settings</span><hr/>";

        // selling opts
        $selling_opts = [
            "0" => "Sell to All Countries",
            "1" => "Sell to Specific Countries"
        ];

        $html .= form_select("coin_sell_location", "Selling Location", $walletSettings['coin_sell_location'], [
            "inline" => TRUE, "options" => $selling_opts, "width" => "400px"
        ]);

        add_to_jquery("
        $('#coin_location_restriction-field').hide();
        $('#coin_sell_location').bind('change', function() {
        if ($(this).val() == 1) {
            $('#coin_location_restriction-field').show();
        } else {
            $('#coin_location_restriction-field').hide();
        }
        });
        ");

        $html .= form_location("coin_location_restriction", "Specific Countries", $walletSettings['coin_location_restriction'], ["inline" => TRUE, "multiple" => TRUE, "width" => "400px"]);

        $calculate_opts = [
            0 => "Customer address",
            1 => "Store address",
            2 => "Geo Locate",
        ];

        $html .= form_select("coin_customer_location", "Customer Default Location", $walletSettings['coin_customer_location'], ["inline" => TRUE, "options" => $calculate_opts, "width" => "400px"]);

        $html .= "<div class='clearfix'>";
        $html .= "<div class='col-xs-12 col-sm-3 p-l-0'>";
        $html .= "<label class='label-control'>Store Notices</label>";
        $html .= "</div><div class='col-xs-12 col-sm-9'>";
        $html .= form_checkbox("coin_notice_status", "Enable Store-wide notice", $walletSettings['coin_notice_status'], ["reverse_label" => TRUE, "width" => "400px"]);
        $html .= "<div style='width:400px;'>";
        $html .= form_textarea("coin_notice_message", "", $walletSettings['coin_notice_message'],
            ["autosize" => TRUE, "placeholder" => "Store Notices Text Messages"]);
        $html .= "</div>";
        $html .= "</div></div>";

        $html .= "<div class='clearfix'>";
        $html .= "<div class='col-xs-12 col-sm-3 p-l-0'>";
        $html .= "<label class='label-control'>Billing Account Options</label>";
        $html .= "</div><div class='col-xs-12 col-sm-9'>";
        $html .= form_checkbox("coin_repeat_order", "Allow registered customer repurchase orders from their account page", $walletSettings['coin_repeat_order'], ["deactivate" => TRUE, "reverse_label" => TRUE]);
        $html .= form_checkbox("coin_clear_order", "Clear customer unpaid orders when they logout", $walletSettings['coin_clear_order'], ["deactivate" => TRUE, "reverse_label" => TRUE]);
        $html .= "</div></div>";

        add_to_jquery("
        ".(!$walletSettings['coin_notice_status'] ? "$('#coin_notice_message-field').hide();" : "$('#coin_notice_message-field').show();")."
        $('#coin_notice_status').bind('change', function() {
        var val = $(this).prop('checked');
        if (val == 1) {
            $('#coin_notice_message-field').show();
        } else {
            $('#coin_notice_message-field').hide();
        }
        });
        ");
        $html .= "<hr/>";
        $html .= form_button("save_changes", $locale['save_changes'], $locale['save_changes'], ["class" => "btn-primary"]);
        $html .= closeform();

        return $html;
    }

    private function __driver_installer() {
        $list_html = '';

        $driverFunctions = new Gateways();

        $driver_list = $driverFunctions->getDriverDir();

        $installed_drivers = $driverFunctions->getInstalledDrivers();

        if (!empty($driver_list)) {

            foreach ($driver_list as $folder_name => $folder_paths) {

                $driver_obj = $driverFunctions->loadDriver($folder_name);

                $info = $driver_obj->__Properties();

                $install = form_button('install', 'Install', $folder_name, ['class' => 'btn-primary']);

                $uninstall = form_button('uninstall', 'Uninstall', $folder_name, ['class' => 'btn-default']);

                $button = (isset($installed_drivers[$folder_name]) ? $uninstall : $install);

                // check driver installations
                $list_html .= "
                    <tr>
                    <td>$button</td>
                    <td><a title='Configure' href='".clean_request('configure='.$folder_name, ['configure'], FALSE)."'>".$info['title']."</a></td>
                    <td>".$info['description']."</td>
                    <td>".($info['link'] ? "<a href='".$info['link']."'>Link</a>" : '')."</td>
                    <td>".$info['author']."</td>
                    <td>".($info['author_email'] ? "<a href='mailto:".$info['author_email']."'>Email</a>" : '')."</td>
                    <td>".($info['author_web'] ? "<a href='".$info['author_web']."'>Link</a>" : '')."</td>
                    <td>".$info['version']."</td>
                    </tr>
                ";
            }
        }

        $html = openform('gateway_frm', 'post', FUSION_REQUEST);
        $html .= "
        <table class='table table-striped'>
            <thead>
                <tr>
                    <td colspan='4'><strong>Module Information</strong></td>
                    <td colspan='4'><strong>Developer Information</strong></td>
                </tr>
                <tr>
                    <td class='no-break'>(".format_word(count($driver_list), 'driver|drivers').")</td>
                    <td>Payment Module</td>
                    <td>Description</td>
                    <td>Documentation</td>
                    <td>Developer</td>
                    <td>Email</td>
                    <td>Website</td>
                    <td>Driver Version</td>
                </tr>
            </thead>
            <tbody>
                $list_html
            </tbody>
        </table>
        ";
        $html .= closeform();

        if (isset($_POST['install'])) {
            // If not installed
            $install = stripinput($_POST['install']);
            $count = dbcount("(driver_folder)", DB_WALLET_DRIVERS, "driver_folder=:driver", [':driver' => $install]);
            if (!$count) {
                if ($driver_class = $driverFunctions->loadDriver($install)) {
                    $info = $driver_class->__Properties();
                    $_driver = [
                        'driver_title'   => $info['title'],
                        'driver_folder'  => $install,
                        'driver_version' => $info['version'],
                    ];
                    dbquery_insert(DB_WALLET_DRIVERS, $_driver, 'save');
                    add_notice('success', $_driver['driver_title']." installed");
                }
                redirect(FUSION_REQUEST);
            }
        }

        if ($uninstall = post("uninstall")) {
            // If not installed
            if ($count = dbcount("(driver_folder)", DB_WALLET_DRIVERS, "driver_folder=:inst", [":inst" => $uninstall])) {
                if ($driver_class = $driverFunctions->loadDriver($uninstall)) {
                    $info = $driver_class->__Properties();
                    $driver_title = $info['title'];

                    dbquery("DELETE FROM ".DB_WALLET_DRIVERS." WHERE driver_folder=:inst", [":inst"=>$uninstall]);
                    add_notice("success", $driver_title." uninstalled");
                    redirect(FORM_REQUEST);
                }
            }
        }

        return $html;
    }

}
