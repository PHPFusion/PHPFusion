<?php
namespace Translate;

use PHPFusion\BreadCrumbs;
use PHPFusion\OutputHandler;
use PHPFusion\QuantumFields;
use Translate\Fo\File_Administration;
use Translate\Pc\Package_Administration;

/**
 * Class Administration
 *
 * @package Translate
 *
 * This system requires a core version set as core package.
 * When you update a core package, the system must be able to pick up the changes so every other package will be able to pick up the missing keys/values/array pairs.
 *
 */
class Administration {

    private static $instance = NULL;

    /*
     * Translate URI class variables
     */
    protected static $action_key = 'action';
    protected static $package_key = 'pack_id';
    protected static $file_key = 'files';
    protected static $item_key = 'id';
    protected static $locale = [];

    public static function __getInstance() {
        if (empty(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    private static $exit_link = '';
    protected static $header_link = [];

    public static function get_exit_link() {
        return self::$exit_link;
    }

    /**
     * What we will do is to find existing and keep asking questions.
     */
    public function display_admin() {
        self::$locale = fusion_get_locale('', TRANSLATE_LOCALE);
        self::$exit_link = clean_request('', [self::$action_key, self::$package_key], FALSE);
        add_breadcrumb([
            'link'  => clean_request(),
            'title' => self::$locale['translate_0000']
        ]);
        if (isset($_POST['cancel'])) {
            redirect(self::$exit_link);
        }

        if (isset($_POST['translate'])) {
            if (isset($_POST['translate_lang']) && isset($_POST['translate_to'])) {
                $translate_lang = stripinput($_POST['translate_lang']);
                $translate_to = stripinput($_POST['translate_to']);
                redirect(clean_request('translate_lang='.$translate_lang.'&translate_to='.$translate_to, ['translate_lang', 'translate_to'], FALSE));
            }
        }
        $_GET['translate_lang'] = isset($_GET['translate_lang']) && valid_language($_GET['translate_lang']) ? $_GET['translate_lang'] : LANGUAGE;
        $_GET['translate_to'] = isset($_GET['translate_to']) && valid_language($_GET['translate_to']) ? $_GET['translate_to'] : $_GET['translate_lang'];

        /**
         * Administration Forms
         */
        if (isset($_POST['search_server'])) {
            $class = new Search();
            $keywords = form_sanitizer($_POST['package_search_txt'], '', 'package_search_txt');
            if (strlen($keywords)>=2) {
                $class->set_search_keywords($keywords);
                $class->display_search_result();
            } else {
                echo "<div class='text-center'>The search text is too short. Please use a longer search keywords</div>";
            }

        } elseif (isset($_GET[self::$action_key])) {
            // There are files
            if (isset($_GET[self::$file_key]) && isnum($_GET[self::$file_key])) {
                switch ($_GET[self::$action_key]) {
                    case 'import_file':
                        add_breadcrumb(['link' => Translate_URI::get_link('import_file', $_GET[self::$file_key]), 'title' => 'Upload Locale File']);
                        File_Imports::display_import_form();
                        break;
                    case 'import_locale':
                        add_breadcrumb(['link' => Translate_URI::get_link('import_locale', $_GET[self::$package_key], $_GET[self::$file_key]), 'title' => 'Upload Translations']);
                        File_Imports::display_locale_import_form();
                        break;
                    case 'del_file':
                        add_breadcrumb(['link' => Translate_URI::get_link('delete_file', $_GET[self::$file_key]), 'title' => 'Delete Locale File']);
                        File_Administration::delete_file();
                        break;
                    default:
                        add_breadcrumb(['link' => Translate_URI::get_link('new_file', $_GET[self::$file_key]), 'title' => 'Add New File']);
                        File_Administration::display_form();
                }
            } else {
                // Package actions
                switch ($_GET[self::$action_key]) {
                    case 'delete_package':
                        add_breadcrumb(['link' => Translate_URI::get_link('delete_package', $_GET[self::$package_key]), 'title' => 'Delete Package']);
                        Package_Administration::delete_package();
                        break;
                    case 'edit_package':
                        add_breadcrumb(['link' => Translate_URI::get_link('edit_package', $_GET[self::$package_key]), 'title' => 'Delete Package']);
                        Package_Administration::display_form();
                        break;
                    case
                        'new_package':
                        add_breadcrumb(['link' => Translate_URI::get_link('new_package'), 'title' => 'Add New Package']);
                        Package_Administration::display_form();
                        break;
                }
            }
        } else {

            if (isset($_GET[self::$package_key]) && isnum($_GET[self::$package_key])) {
                $package_name = dbresult(dbquery("SELECT package_name FROM ".DB_TRANSLATE_PACKAGE." WHERE package_id=:id", [':id' => $_GET[self::$package_key]]), 0);
                $package_link = Translate_URI::get_link('view_package', $_GET[self::$package_key]);
                add_breadcrumb(['link' => $package_link, 'title' => $package_name]);
                self::$header_link[] = ['link' => $package_link, 'title' => $package_name];
            }

            // We need breadcrumbs here
            if ((isset($_GET[self::$file_key]) && isnum($_GET[self::$file_key])) && isset($_GET[self::$package_key]) && isnum($_GET[self::$package_key])) {
                $dir_index = dbquery_tree(DB_TRANSLATE_FILES, 'file_id', 'file_parent', "WHERE file_package='".$_GET[self::$package_key]."'");
                $dir_packData = dbquery_tree_full(DB_TRANSLATE_FILES, 'file_id', 'file_parent', "WHERE file_package='".$_GET[self::$package_key]."'");
                // then we make a infinity recursive function to loop/break it out.
                $crumb = self::get_readdir($dir_index, $dir_packData, 'file_id', 'file_name', self::$file_key, $_GET[self::$file_key]);
                // then we sort in reverse.
                if (count($crumb['title']) > 1) {
                    krsort($crumb['title']);
                    krsort($crumb['link']);
                }
                if (count($crumb['title']) > 1) {
                    foreach ($crumb['title'] as $i => $value) {
                        self::$header_link[] = ['link' => $crumb['link'][$i], 'title' => $value];
                        BreadCrumbs::getInstance()->addBreadCrumb(['link' => $crumb['link'][$i], 'title' => $value]);
                        if ($i == count($crumb['title']) - 1) {
                            OutputHandler::addToTitle($GLOBALS['locale']['global_200'].$value);
                            OutputHandler::addToMeta($value);
                        }
                    }
                } elseif (isset($crumb['title'])) {
                    OutputHandler::addToTitle($GLOBALS['locale']['global_200'].$crumb['title']);
                    OutputHandler::addToMeta($crumb['title']);
                    self::$header_link[] = ['link' => $crumb['link'], 'title' => $crumb['title']];
                    BreadCrumbs::getInstance()->addBreadCrumb(['link' => $crumb['link'], 'title' => $crumb['title']]);
                }
            }

            opentable(self::$locale['translate_0100']);
            $tab['title'][] = 'Translation Packages';
            $tab['id'][] = 'tps';
            $tab['title'][] = 'Translations Settings';
            $tab['id'][] = 'tls';
            $_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $tab['id']) ? $_GET['section'] : $tab['id'][0];
            echo opentab($tab, $_GET['section'], 'tpssd', TRUE, FALSE, 'section', Translate_URI::get_link_exclusions());
            // this is translation packages
            switch ($_GET['section']) {
                case 'tps':
                    if (isset($_GET[self::$package_key]) && isnum($_GET[self::$package_key])) {
                        File_Administration::display();
                    } else {
                        Package_Administration::display();
                    }
                    break;
                case 'tls':
                    echo "Section in development";
            }
            echo closetab();
            closetable();



        }
    }

    public static $package_data = [
        'package_id'          => 0,
        'package_name'        => '',
        'package_meta'        => '',
        'package_description' => '',
        'package_status'      => 0,
        'package_datestamp'   => TIME,
    ];

    public static function get_package_language($lang_key = NULL) {
        $array = fusion_get_enabled_languages();

        return ($lang_key !== NULL ? (isset($array[$lang_key]) ? $array[$lang_key] : NULL) : $array);
    }

    private static function get_readdir($tree_index, $tree_full, $id_col, $title_col, $getname, $id) {
        $crumb = &$crumb;
        if (isset($tree_index[get_parent($tree_index, $id)])) {
            $_name = get_parent_array($tree_full, $id);
            $crumb = array(
                'link'  => isset($_name[$id_col]) ? clean_request($getname."=".$_name[$id_col], ['aid', 'pack_id'], TRUE) : "",
                'title' => isset($_name[$title_col]) ? QuantumFields::parse_label($_name[$title_col]) : "",
            );
            if (get_parent($tree_index, $id) == 0) {
                return $crumb;
            }
            $crumb_1 = self::get_readdir($tree_index, $tree_full, $id_col, $title_col, $getname, get_parent($tree_index, $id));

            if (!empty($crumb_1)) {
                $crumb = array_merge_recursive($crumb, $crumb_1);
            }

        }

        return $crumb;
    }
}

require_once dirname(__FILE__).'/uri_request.php';
require_once dirname(__FILE__).'/form/packages.php';
require_once dirname(__FILE__).'/form/files.php';
require_once dirname(__FILE__).'/file_import.php';
require_once dirname(__FILE__).'/search.php';