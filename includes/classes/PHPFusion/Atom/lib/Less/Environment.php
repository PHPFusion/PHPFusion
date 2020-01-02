<?php


/**
 * Environment
 *
 * @package Less
 * @subpackage environment
 */
class Less_Environment {

    //public $paths = array();				// option - unmodified - paths to search for imports on
    //public static $files = array();		// list of files that have been imported, used for import-once
    //public $rootpath;						// option - rootpath to append to URL's
    //public static $strictImports = null;	// option -
    //public $insecure;						// option - whether to allow imports from insecure ssl hosts
    //public $processImports;				// option - whether to process imports. if false then imports will not be imported
    //public $javascriptEnabled;			// option - whether JavaScript is enabled. if undefined, defaults to true
    //public $useFileCache;					// browser only - whether to use the per file session cache
    public static $parensStack = 0;                // information about the current file - for error reporting and importing and making urls relative etc.
    public static $tabLevel = 0;        // whether we are currently importing multiple copies
    public static $lastRule = FALSE;
    public static $_outputMap;
    public static $mixin_stack = 0;
    public $currentFileInfo;
    public $importMultiple = FALSE;
    /**
     * @var array
     */
    public $frames = [];
    /**
     * @var array
     */
    public $mediaBlocks = [];
    /**
     * @var array
     */
    public $mediaPath = [];
    /**
     * @var array
     */
    public $functions = [];

    public static function isMathOn() {
        return !Less_Parser::$options['strictMath'] || Less_Environment::$parensStack;
    }

    public static function isPathRelative($path) {
        return !preg_match('/^(?:[a-z-]+:|\/)/', $path);
    }

    /**
     * Canonicalize a path by resolving references to '/./', '/../'
     * Does not remove leading "../"
     *
     * @param string path or url
     *
     * @return string Canonicalized path
     *
     */
    public static function normalizePath($path) {

        $segments = explode('/', $path);
        $segments = array_reverse($segments);

        $path = [];
        $path_len = 0;

        while ($segments) {
            $segment = array_pop($segments);
            switch ($segment) {

                case '.':
                    break;

                case '..':
                    if (!$path_len || ($path[$path_len - 1] === '..')) {
                        $path[] = $segment;
                        $path_len++;
                    } else {
                        array_pop($path);
                        $path_len--;
                    }
                    break;

                default:
                    $path[] = $segment;
                    $path_len++;
                    break;
            }
        }

        return implode('/', $path);
    }

    public function Init() {

        self::$parensStack = 0;
        self::$tabLevel = 0;
        self::$lastRule = FALSE;
        self::$mixin_stack = 0;

        if (Less_Parser::$options['compress']) {

            Less_Environment::$_outputMap = [
                ','  => ',',
                ': ' => ':',
                ''   => '',
                ' '  => ' ',
                ':'  => ' :',
                '+'  => '+',
                '~'  => '~',
                '>'  => '>',
                '|'  => '|',
                '^'  => '^',
                '^^' => '^^'
            ];

        } else {

            Less_Environment::$_outputMap = [
                ','  => ', ',
                ': ' => ': ',
                ''   => '',
                ' '  => ' ',
                ':'  => ' :',
                '+'  => ' + ',
                '~'  => ' ~ ',
                '>'  => ' > ',
                '|'  => '|',
                '^'  => ' ^ ',
                '^^' => ' ^^ '
            ];

        }
    }

    public function copyEvalEnv($frames = []) {
        $new_env = new Less_Environment();
        $new_env->frames = $frames;

        return $new_env;
    }

    public function unshiftFrame($frame) {
        array_unshift($this->frames, $frame);
    }

    public function shiftFrame() {
        return array_shift($this->frames);
    }

}
