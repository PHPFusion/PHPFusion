<?php
namespace ThemeFactory;

class Core {

    private static $options = array(
        'header' => TRUE, // has header
        'breadcrumbs' => FALSE, // show breadcrumbs
        'right' => TRUE,
        'left' => TRUE,
        'upper' => TRUE,
        'lower' => TRUE,
        'footer' => TRUE, // has footer
        'copyright' => TRUE,




        'container' => TRUE, // whether is a container or full grid

        'headerBg' => TRUE, // use header_background
        'headerBg_class' => '', // use custom header background class
        'header_content' => '', // content in the header

        'subheader_content' => '', // page title

        'right_span' => 3,
        'main_span' => 12,

        'right_is_affix' => FALSE, // @todo: auto affix
        'right_pre_content' => '', // right side top content
        'right_post_content' => '', // right side bottom content
    );

    private static $instance = NULL;
    private static $module_instance = NULL;
    private static $module_list = array();

    private function __construct() {
        if (empty(self::$module_list)) {
            // Get Theme Factory Modules
            $ModuleType = makefilelist(THEME."ThemeFactory/Lib/Modules", ".|..|.htaccess|index.php|._DS_STORE|.tmp", "folder");
            if (!empty($ModuleType)) {
                foreach ($ModuleType as $ModuleFolder) {
                    $Modules = makefilelist(THEME."ThemeFactory/Lib/Modules/$ModuleFolder", ".|..|.htaccess|index.php|._DS_STORE|.tmp");
                    if (!empty($Modules)) {
                        foreach ($Modules as $ModuleFile) {
                            self::$module_list[] = "$ModuleFolder\\".str_replace('.php', '', $ModuleFile);
                        }
                    }
                }
            }

            // Calculate Span
            if (RIGHT || self::getParam('right_pre_content') || self::getParam('right_post_content')) {
                self::replaceParam('main_span', self::getParam('main_span') - self::getParam('right_span'));
            }

        }
    }

    protected static function getParam($prop = FALSE) {
        if (isset(self::$options[$prop])) {
            return self::$options[$prop];
        }

        return NULL;
    }

    public static function replaceParam($prop, $value) {
        self::$options[$prop] = $value;
    }

    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }
        return (object)self::$instance;
    }

    public static function setParam($prop, $value) {
        self::$options[$prop] = (is_bool($value)) ? $value : self::getParam($prop).$value;
    }

    public function get_themePack($themePack) {
        $path = THEME."ThemePack/".$themePack."/Theme.php";
        $cssPath = THEME."ThemePack/".$themePack."/Styles.css";
        add_to_head("<link rel='stylesheet' href='$cssPath' type='text/css'/>");
        require_once $path;
    }


    /**
     * @param string $modules
     * @return mixed
     */
    protected function get_Modules($modules = 'Footer\\News') {
        if (!isset(self::$module_instance[$modules]) or self::$module_instance[$modules] === NULL) {
            if (!empty(self::$module_list)) {
                $module_ = array_flip(self::$module_list);
                if (isset($module_[$modules])) {
                    $namespace_ = "ThemeFactory\\Lib\\Modules\\";
                    $module_ = new \ReflectionClass($namespace_.$modules);
                    self::$module_instance[$modules] = $module_->newInstance();
                }
            }
        }

        return self::$module_instance[$modules];
    }

    private function __clone() {
    }

    private function __wakeup() {
    }

}