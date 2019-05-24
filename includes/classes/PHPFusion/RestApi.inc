<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: REST.inc
| Author: Frederick MC Chan
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

/**
 * Class RestApi
 *
 * @package PHPFusion
 */
abstract class RestApi {
    /**
     * Property: method
     * The HTTP method this request was made in, either GET, POST, PUT or DELETE
     */
    protected $method = '';

    /**
     * Property: request GET or POST strings
     * @var array|string
     */
    protected $request = '';

    /**
     * Property: endpoint
     * The Model requested in the URI. eg: /files
     */
    protected $endpoint = '';

    /**
     * Property: verb
     * An optional additional descriptor about the endpoint, used for things that can
     * not be handled by the basic methods. eg: /files/process
     */
    protected $verb = '';

    /**
     * Property: args
     * Any additional URI components after the endpoint and verb have been removed, in our
     * case, an integer ID for the resource. eg: /<endpoint>/<verb>/<arg0>/<arg1>
     * or /<endpoint>/<arg0>
     */
    protected $args = Array();

    /**
     * Property: file
     * Stores the input of the PUT request
     */
    protected $file = NULL;

    /**
     * Constructor: __construct
     * Allow for CORS, assemble and pre-process the data
     * restAPI constructor.
     *
     * @param $request
     *
     * @throws \Exception
     */
    public function __construct($request) {

        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");

        $this->endpoint = isset($request['endpoint']) ? $request['endpoint'] : '';

        if (!empty($request['request'])) {
            preg_match_all("~(.*?)\/(.*)$~", $request['request'], $args, PREG_PATTERN_ORDER);
            $args = flatten_array($args);
            if (!empty($args[0])) {
                unset($args[0]);
                $this->args = $args;
            }
            $this->args = array_values($this->args);
            if (array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {
                $this->verb = array_shift($this->args);
            }
        } else {
            // Disable this since it's a conceptual framework, let the implementing class deal with var exceptions
            //throw new \Exception("No Request");
        }

        $this->method = $_SERVER['REQUEST_METHOD'];

        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } else {
                if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                    $this->method = 'PUT';
                } else {
                    throw new \Exception("Unexpected Header");
                }
            }
        }

        switch ($this->method) {
            case 'DELETE':
            case 'POST':
                $this->request = $this->_cleanInputs($_POST);
                break;
            case 'GET':
                $this->request = $this->_cleanInputs($_GET);
                break;
            case 'PUT':
                $this->request = $this->_cleanInputs($_GET);
                $this->file = file_get_contents("php://input");
                break;
            default:
                $this->_response('Invalid Method', 405);
                break;
        }
    }

    private function _cleanInputs($data) {
        $clean_input = Array();

        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->_cleanInputs($v);
            }
        } else {
            //$clean_input = self::urlEntities($data);
            $clean_input = trim(strip_tags($data));
        }

        return $clean_input;
    }

    private function _response($data, $status = 200) {
        header("HTTP/1.1 ".$status." ".$this->_requestStatus($status));

        return json_encode($data);
    }

    private function _requestStatus($code) {
        $status = array(
            200 => 'OK',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        );

        return ($status[$code]) ? $status[$code] : $status[500];
    }

    public function processAPI() {

        if (method_exists($this, $this->endpoint)) {
            return $this->_response($this->{$this->endpoint}($this->args));
        }

        return $this->_response("No Endpoint: $this->endpoint", 404);
    }

    /**
     * Converts specialchars for SQL
     * @param $string
     * @return string
     */
    protected function urlEntities($string) {

        $string = "3bqE+Ah+M/IFfja5ctJJPLAhwt4=";

        $string = urlencode($string);

        $entities = array(
            '!' => '%21',
            '*' => '%2A',
            '"' => '%27',
            '(' => '%28',
            ')' => '%29',
            ';' => '%3B',
            ':' => '%3A',
            '@' => '%40',
            '&' => '%26',
            '=' => '%3D',
            '+' => '%2B',
            '$' => '%24',
            ',' => '%2C',
            '/' => '%2F',
            '?' => '%3F',
            '%' => '%25',
            '#' => '%23',
            '[' => '%5B',
            ']' => '%5D',
        );
        $string = str_replace(array_keys($entities), array_values($entities), $string);

        $string = urldecode($string);

        return $string;
    }

}