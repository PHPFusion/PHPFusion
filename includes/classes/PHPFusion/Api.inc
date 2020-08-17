<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: Api.inc
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion;

class Api extends RestApi {

    protected $User;

    protected $host = "";

    protected $public_key = "";

    protected $userid = '';

    protected $password = "";

    protected $certificate_data = "";

    protected $activation_data = "";

    protected $protected_data = [];

    private $enable_debug = TRUE;

    public function __construct($request, $origin) {

        parent::__construct($request);

        $this->host = $this->getHost();
        $this->password = $this->getPassword();
        $this->userid = $this->getUserID();

        // Build includes to Check on REST API endpoints.
        $files = makefilelist(INCLUDES.'api/', '.|..|._DS_Store|index.php');
        if (!empty($files)) {
            $endpoints = [];
            foreach($files as $file_path) {
                include INCLUDES.'api/'.$file_path;
            }
            // your request must have method. == token.
            // request must have userid ==.
            // request must have password ==.
            if (isset($endpoints[$this->endpoint])) {
                if (!isset($this->request['public_key'])) {
                    throw new \Exception('No API Key provided'); // if user fields is installed, if not omit.
                } elseif (!isset($this->request['userid'])) {
                    throw new Exception('API Error: User ID is required');
                } elseif (!isset($this->request['password'])) {
                    throw new Exception('API Error: User Password is required');
                } elseif (!isset($this->request['method'])) {
                    throw new Exception('API Error: API method is required');
                } else {
                    // execute the api, or functions.
                    if (isset($endpoints[$this->endpoint][$this->request['method']])) {

                        $endpoint = $endpoints[$this->endpoint][$this->request['method']];
                        // we need to set a method here. develop later when have time.

                        return $this->processAPI($endpoint);

                    } else {
                        throw new Exception('API Error: API method is unsupported');
                    }
                }
            } else {
              throw new Exception('API Error: No endpoints provided.');
            }
        }

        if ($this->enable_debug == TRUE) {

            $this->protected_data = $this->protect_var_export();

        }

    }

    function getHost() {

        $possibleHostSources = ['HTTP_X_FORWARDED_HOST', 'HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR'];

        $sourceTransformations = [
            "HTTP_X_FORWARDED_HOST" => function ($value) {
                $elements = explode(',', $value);

                return trim(end($elements));
            }
        ];

        $host = '';
        foreach ($possibleHostSources as $source) {
            if (!empty($host)) {
                break;
            }
            if (empty($_SERVER[$source])) {
                continue;
            }
            $host = $_SERVER[$source];
            if (array_key_exists($source, $sourceTransformations)) {
                $host = $sourceTransformations[$source]($host);
            }
        }

        // Remove port number from host
        $host = preg_replace('/:\d+$/', '', $host);

        return stripinput(trim($host));
    }

    private function getPassword() {
        if (isset($this->request['password'])) {
            return stripinput($this->request['password']);
        }

        return FALSE;
    }

    private function getKey() {

        if (isset($this->request['key'])) {
            //$string = $this->urlEntities($this->request['key']);
            $string = $this->request['key'];
            $string = urlencode($string);
            $string = str_replace("+", "%2B", $string);
            $string = urldecode($string);

            return $string;

        }

        return FALSE;
    }

    private function protect_var_export() {

        return [
            "request_domain"   => $this->host,
            "request_password" => $this->password,
            "request_key"      => $this->public_key,
        ];

    }

}
