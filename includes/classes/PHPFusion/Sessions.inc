<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Sessions.inc
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

namespace PHPFusion;

use PHPFusion\Database\DatabaseFactory;

/**
 * Class Sessions
 *
 * You must call session_set_save_handler() prior to calling session_start(), but you can define the functions themselves anywhere.
 * The real beauty of this approach is that you don't have to modify your code or the way you use sessions in any way.
 * $_SESSION still exists and behaves the same way, PHP still takes care of generating and propagating the session identifier,
 * and changes made to session configuration directives still apply. All you have to do is call this one function.
 *
 * @package PHPFusion
 */
class Sessions {

    private static $_sess = NULL;
    private static $db_info = [];
    private static $_sess_key = 'sessions';
    private $_sess_db = NULL;

    private function __construct() {
    }

    /**
     *  Get an instance by key
     *
     * @param string $handler
     *
     * @return static
     */
    public static function getInstance($handler) {
        $handlers = (string)$handler;
        // this one can use a token.
        if (empty(self::$_sess[$handlers])) {
            self::$_sess[$handlers] = new static();
        }

        return self::$_sess[$handlers];
    }

    /**
     * Methods to fetch the ID from the session.
     */
    public function getId() {
        return session_id();
    }

    /**
     * Set db configuration values
     *
     * @param $db_host
     * @param $db_user
     * @param $db_pass
     * @param $db_name
     * @param $db_port
     *
     * @return $this
     */
    public function setConfig($db_host, $db_user, $db_pass, $db_name, $db_port = 3306) {
        self::$db_info = [
            'db_host' => $db_host,
            'db_user' => $db_user,
            'db_pass' => $db_pass,
            'db_name' => $db_name,
            'db_port' => $db_port,
        ];

        return $this;
    }

    /**
     * The _open() and _close() functions are closely related. These are used to open the session data store and close it, respectively.
     * If you are storing sessions in the filesystem, these functions open and close files
     * (and you likely need to use a global variable for the file handler, so that the other session functions can use it).
     *
     * @return bool|\PHPFusion\Database\AbstractDatabaseDriver
     */
    public function _open() {
        $this->_sess_db = $this->connection();
        if ($this->_sess_db['dbselection_success'] && $this->_sess_db['connection_success']) {
            DatabaseFactory::getConnection(self::$_sess_key);
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @param bool $halt_on_error
     *
     * @return array
     */
    private function connection($halt_on_error = FALSE) {
        $connection_success = TRUE;
        $dbselection_success = TRUE;
        try {
            DatabaseFactory::connect(self::$db_info['db_host'], self::$db_info['db_user'], self::$db_info['db_pass'], self::$db_info['db_name'], [
                'debug'        => DatabaseFactory::isDebug(self::$_sess_key),
                'connectionid' => self::$_sess_key,
                'port'         => self::$db_info['db_port']
            ]);
        } catch (\Exception $e) {
            $connection_success = $e instanceof Database\Exception\SelectionException;
            $dbselection_success = FALSE;
            if ($halt_on_error and !$connection_success) {
                die("<strong>Unable to establish connection to MySQL</strong><br />".$e->getCode()." : ".$e->getMessage());
            } else if ($halt_on_error) {
                die("<strong>Unable to select MySQL database</strong><br />".$e->getCode()." : ".$e->getMessage());
            }
        }

        return [
            'connection_success'  => $connection_success,
            'dbselection_success' => $dbselection_success
        ];
    }

    public function _close() {
        DatabaseFactory::getConnection(self::$_sess_key)->close();

        return TRUE;
    }

    /**
     * Read and fetches the data from a session
     * The _read() function is called whenever PHP needs to read the session data.
     * This takes place immediately after _open(), and both are a direct result of your use of session_start().
     *
     * PHP expects the session data in return, and you don't have to worry about the format, because
     * PHP provides the data to the _write() function (covered in the next section) in the same format that it expects it.
     * Thus, this function returns exactly what is in the data column for the matching record.
     *
     * Note: The handler PHP uses to handle data serialization is defined by the session.serialize_handler configuration directive. It is set to php by default.
     *
     * @param string $name
     *
     * @return string
     */
    public function _read($name) {
        $names = (string)$name;
        $query = "SELECT session_data FROM ".DB_SESSIONS." WHERE session_id = :name";
        $parameters = [':name' => $names];
        $result = DatabaseFactory::getConnection(self::$_sess_key)->query($query, $parameters);
        if (DatabaseFactory::getConnection(self::$_sess_key)->countRows($result)) {
            $rows = DatabaseFactory::getConnection(self::$_sess_key)->fetchAssoc($result);
            return $rows['session_data'];
        }

        return '';
    }

    /**
     * Write a session
     *
     * The _write() function is called whenever PHP needs to write the session data. This takes place at the very end of the script.
     * PHP passes this function the session identifier and the session data.
     *
     * You don't need to worry with the format of the data - PHP serializes it, so that you can treat it like a string.
     * However, PHP does not modify it beyond this, so you want to properly escape it before using it in a query.
     *
     * @param $name
     * @param $data
     *
     * @return mixed
     */
    public function _write($name, $data) {
        $parameters = [
            ':name'   => $name,
            ':access' => TIME,
            ':data'   => $data,
        ];
        $query = "REPLACE INTO ".DB_SESSIONS." VALUES (:name, :access, :data)";
        if (DatabaseFactory::getConnection(self::$_sess_key)->query($query, $parameters)) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Destroys a session
     *
     * The _destroy() function is called whenever PHP needs to destroy all session data associated with a specific session identifier.
     * An obvious example is when you call session__destroy().
     *
     * @param $name
     *
     * @return mixed
     */
    public function _destroy($name) {
        $parameters = [
            ':name' => $name,
        ];
        $query = "DELETE FROM ".DB_SESSIONS." WHERE session_id=:name";
        DatabaseFactory::getConnection(self::$_sess_key)->query($query, $parameters);

        return TRUE;
    }

    public function _purge() {
        $query = "DELETE FROM ".DB_SESSIONS;
        DatabaseFactory::getConnection(self::$_sess_key)->query($query);

        return TRUE;
    }

    /**
     * Clear the session gc
     *
     * The _clean() function is called every once in a while in order to clean out (delete) old records in the session data store.
     * More specifically, the frequency in which this function is called is determined by two configuration directives,
     * session.gc_probability and session.gc_divisor.
     *
     * The default values for these are 1 and 1000, respectively, which means there is a 1 in 1000 (0.1%) chance for this function to be called per session initialization.
     * Because the _write() function keeps the timestamp of the last access in the access column for each record,
     * this can be used to determine which records to delete. PHP passes the maximum number of seconds allowed before a session is to be considered expired.
     *
     * @param $max
     * The value that PHP passes to this function comes directly from the session.gc_maxlifetime configuration directive.
     * You can actually ignore this and determine your own maximum lifetime allowed, but it is much better to adhere to the value PHP passes.
     * Doing so better adheres to the idea of transparently changing the storage mechanism.
     * From a developer's perspective, the behavior of sessions should not change.
     *
     * @return mixed
     */
    public function _clean($max) {
        $old = TIME - $max;
        $parameters = [
            ':old' => $old,
        ];
        $query = "DELETE FROM ".DB_SESSIONS." WHERE session_start < :old";

        DatabaseFactory::getConnection(self::$_sess_key)->query($query, $parameters);

        return TRUE;
    }

    public function remote_cache($path) {
        echo "<script>
        function timedRefresh(timeoutPeriod) {
            setTimeout(UpdateCache,timeoutPeriod);
        }
        function UpdateCache() {
            // get the latest state of the 3 fields
            var ttl = 36000;
            $.ajax({
            url: '".fusion_get_settings('site_path')."$path',
            type: 'post',
            success: function () {
                timedRefresh(ttl);
            }
            });
        }
        UpdateCache();
        </script>";
    }

    private function __clone() {
    }

}
