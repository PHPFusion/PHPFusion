<?php

namespace PHPFusion\Infusions\Directory\Classes\View;

use PHPFusion\Infusions\Directory\Classes\View\Contents\Main;
use PHPFusion\Infusions\Directory\Classes\View\Contents\School;

/**
 * home.php controller
 */
class Home {
    
    public function __construct() {
        $locale = fusion_get_locale('', LOCALE.LOCALESET.'homepage.php');
        add_to_title($locale['home']);
        add_breadcrumb(['title' => $locale['home'], 'link' => BASEDIR.'home.php']);
    }
    
    public function view() {
        switch ($type = get('type')) {
            case 'school':
                $home_school = new School();
                echo $home_school->display();
                break;
            case 'main':
            default:
                $home_main = new Main();
                echo $home_main->display();
        }
    }
    
}
