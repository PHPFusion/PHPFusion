<?php
/*
<Files ~ "\.inc$">
Order allow,deny
Deny from all
</Files>
*/
namespace PHPFusion\Installer;

use PHPFusion\Locale;
const STEP_INTRO = 1;
const STEP_PERMISSIONS = 2;
const STEP_DB_SETTINGS_FORM = 3;
const STEP_DB_SETTINGS_SAVE = 4;
const STEP_PRIMARY_ADMIN_FORM = '5';   //must be between quotation marks because of implicit type conversion
const STEP_PRIMARY_ADMIN_SAVE = '5/2';
const STEP_INFUSIONS = 6;
const STEP_SETUP_COMPLETE = 7;
const STEP_EXIT = 8;

class Install_Core {

    protected static $locale = array();
    private static $setup_instance = NULL;

    public static function getInstance() {
        if (self::$setup_instance == NULL) {

            self::$setup_instance = new static();

            self::verify_requirements();

            ini_set('display_errors', 1);

            define("FUSION_QUERY", isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : "");

            define("FUSION_SELF", basename($_SERVER['PHP_SELF']));

            define("FUSION_REQUEST", isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != "" ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME']);

            define('INSTALLATION_STEP', filter_input(INPUT_POST, 'infuse') || filter_input(INPUT_POST,
                                                                                           'defuse') ? STEP_INFUSIONS : (filter_input(INPUT_POST,
                                                                                                                                      'step') ?: (isset($_SESSION['step']) ? $_SESSION['step'] : STEP_INTRO)));

            self::$locale = Locale::getLocale('', BASEDIR.'locale/English/setup.php');

            session_start();
        }

        return self::$setup_instance;
    }

    private static function verify_requirements() {
        if (version_compare(PHP_VERSION, '5.5.9') < 0) {
            print 'PHP-Fusion 9 requires at least PHP 5.5.9. See the <a href="https://www.php-fusion.co.uk/requirements">system requirements</a> page for more information.';
            exit;
        }
        if (function_exists('opcache_get_status') && opcache_get_status()['opcache_enabled'] && !ini_get('opcache.save_comments')) {
            print 'Systems with OPcache installed must have <a href="http://php.net/manual/en/opcache.configuration.php#ini.opcache.save-comments">opcache.save_comments</a> enabled.';
            exit();
        }
    }

    public function install_phpfusion() {
        echo strtr(Console_Core::getInstance()->getLayout(), array(
                                                               "{%content%}" => Console_Core::getInstance()->getView($this->get_install_content(INSTALLATION_STEP))
                                                           )
        );
    }

    // fetch the step content and executions
    private function get_install_content($step) {
        return "aaaaa";
    }

    /**
     * Render button with custom name and label
     *
     * @param string $name
     * @param string $label
     */
    private function renderButton($name, $label, $mode = 'next') {
        $icon = 'right-dir';
        $btnType = 'btn-primary';
        if ($mode === 'refresh') {
            $icon = 'cw';
        } elseif ($mode === 'tryagain') {
            $btnType = 'btn-warning';
            $icon = 'cw';
        }
        echo "<div class='text-right'>\n";
        echo "<button type='submit' name='".$name."' value='$label' class='btn $btnType m-t-20'><i class='entypo $icon'></i> $label</button>\n";
        echo "</div>\n";
    }

    // Generate a random string
    private function createRandomPrefix($length = 5) {
        $chars = array("abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ", "123456789");
        $count = array((strlen($chars[0]) - 1), (strlen($chars[1]) - 1));
        $prefix = "";
        for ($i = 0; $i < $length; $i++) {
            $type = mt_rand(0, 1);
            $prefix .= substr($chars[$type], mt_rand(0, $count[$type]), 1);
        }

        return $prefix;
    }

    // Get Current URL
    private function getCurrentURL() {
        $s = empty($_SERVER["HTTPS"]) ? "" : ($_SERVER["HTTPS"] == "on") ? "s" : "";
        $protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
        $port = ($_SERVER["SERVER_PORT"] == "80" || ($_SERVER['SERVER_PORT'] == "443" && $s == "s")) ? "" : (":".$_SERVER["SERVER_PORT"]);

        return $protocol."://".$_SERVER['SERVER_NAME'].$port.(str_replace(basename(cleanurl($_SERVER['PHP_SELF'])), "", $_SERVER['REQUEST_URI']));
    }

    private function strleft($s1, $s2) {
        return substr($s1, 0, strpos($s1, $s2));
    }

    // Generate a standard .htaccess file
    private function write_htaccess($site_path) {
        if (!file_exists(BASEDIR.'.htaccess')) {
            if (file_exists(BASEDIR."_htaccess") && function_exists("rename")) {
                @rename(BASEDIR."_htaccess", ".htaccess");
            } else {
                touch(BASEDIR.".htaccess");
            }
        }
        $htc = "# Force utf-8 charset".PHP_EOL;
        $htc .= "AddDefaultCharset utf-8".PHP_EOL.PHP_EOL;
        $htc .= "# Security".PHP_EOL;
        $htc .= "ServerSignature Off".PHP_EOL.PHP_EOL;
        $htc .= "# Secure htaccess file".PHP_EOL;
        $htc .= "<Files .htaccess>".PHP_EOL;
        $htc .= "order allow,deny".PHP_EOL;
        $htc .= "deny from all".PHP_EOL;
        $htc .= "</Files>".PHP_EOL.PHP_EOL;
        $htc .= "# Protect config.php".PHP_EOL;
        $htc .= "<Files config.php>".PHP_EOL;
        $htc .= "order allow,deny".PHP_EOL;
        $htc .= "deny from all".PHP_EOL;
        $htc .= "</Files>".PHP_EOL.PHP_EOL;
        $htc .= "# Block Nasty Bots".PHP_EOL;
        $htc .= "<IfModule mod_setenvifno.c>".PHP_EOL;
        $htc .= "	SetEnvIfNoCase ^User-Agent$ .*(craftbot|download|extract|stripper|sucker|ninja|clshttp|webspider|leacher|collector|grabber|webpictures) HTTP_SAFE_BADBOT".PHP_EOL;
        $htc .= "	SetEnvIfNoCase ^User-Agent$ .*(libwww-perl|aesop_com_spiderman) HTTP_SAFE_BADBOT".PHP_EOL;
        $htc .= "	Deny from env=HTTP_SAFE_BADBOT".PHP_EOL;
        $htc .= "</IfModule>".PHP_EOL.PHP_EOL;
        $htc .= "# Disable directory listing".PHP_EOL;
        $htc .= "Options -Indexes".PHP_EOL.PHP_EOL;
        $htc .= "ErrorDocument 400 ".$site_path."error.php?code=400".PHP_EOL;
        $htc .= "ErrorDocument 401 ".$site_path."error.php?code=401".PHP_EOL;
        $htc .= "ErrorDocument 403 ".$site_path."error.php?code=403".PHP_EOL;
        $htc .= "ErrorDocument 404 ".$site_path."error.php?code=404".PHP_EOL;
        $htc .= "ErrorDocument 500 ".$site_path."error.php?code=500".PHP_EOL;
        file_put_contents(BASEDIR.".htaccess", $htc);
    }

    /**
     * A wrapper function for file_put_contents with cache invalidation
     *
     * If opcache is enabled on the server, this function will write the file
     * as the original file_put_contents and invalidate the cache of the file.
     *
     * It is needed when you create a file dynamically and want to include it
     * before the cache is invalidated. Redirection does not matter.
     *
     * @todo Find a better place and/or name for this function
     *
     * @param string          $file file path
     * @param string|string[] $data
     * @param int             $flags
     * @return int Number of written bytes
     */
    private function fusion_file_put_contents($file, $data, $flags = NULL) {
        $bytes = NULL;
        if ($flags === NULL) {
            $bytes = \file_put_contents($file, $data);
        } else {
            $bytes = \file_put_contents($file, $data, $flags);
        }
        if (function_exists('opcache_invalidate')) {
            \opcache_invalidate($file, TRUE);
        }

        return $bytes;
    }

}