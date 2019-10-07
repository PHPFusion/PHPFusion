<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: license-check.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/header.php';

class LicenseChecker {

    public function check_license($password, $public_key) {
        // this is the code from fusion_license(); on Babylon Repository
        $curl = curl_init('https://www.php-fusion.co.uk/infusions/license/api/v1/api.php');
        $curl_post_data = [
            "password" => $password,
            "key"      => $public_key,
            'endpoint' => 'certificates'
        ];
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
        $curl_response = curl_exec($curl);
        curl_close($curl);

        return (json_decode($curl_response));
    }

    /**
     * Never reveal the information to outsiders as it contains your private key
     * @param $values
     *
     * @return false|string
     */
    public function print_p($values) {
        if (iSUPERADMIN || iADMIN) {
            //debug_print_backtrace();
            ob_start();
            echo htmlspecialchars(print_r($values, TRUE), ENT_QUOTES, 'utf-8');
            $debug = ob_get_clean();

            echo "<pre style='white-space:pre-wrap !important;'>";
            echo $debug;
            echo "</pre>\n";

            return $debug;
        }
        return '';
    }

}

$user_password = 'YOUR-PASSWORD';
$license_public_key = 'YOUR-PUBLIC-KEY';

$license = new LicenseChecker();
$output = $license->check_license($user_password, $license_public_key);
$license->print_p($output);

require_once THEMES.'templates/footer.php';
