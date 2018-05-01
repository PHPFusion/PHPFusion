<?php
require_once dirname(__FILE__).'/../../maincore.php';
require_once INFUSIONS.'search/class/search.php';
require_once INCLUDES.'theme_functions_include.php';
// there must be keyword
define('THEME_BULLET', '');
if (isset($_POST['q']) && $_POST['q']) {
    \PHPFusion\Search\Search_Engine::getInstance();
    $config = strtr(fusion_get_config(), array('config.php'=>''));
    $path = pathinfo($_SERVER['HTTP_REFERER'])['dirname'];
    $path_2 = pathinfo($_SERVER['PHP_SELF'])['dirname'];
    $count =  substr_count($path, '/')-substr_count($path_2, '/');
    $prefix_ = str_repeat('../', $count);
    ob_start();
    Search::get_search_results($_POST['q']);
    $html = strtr(ob_get_clean(), array($config => $prefix_));
} else {
    $html = Search::get_default_view();
}
echo $html;