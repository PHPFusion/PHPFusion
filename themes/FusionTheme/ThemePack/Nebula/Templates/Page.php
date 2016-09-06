<?php
namespace ThemePack\Nebula\Templates;

use PHPFusion\Panels;
use ThemeFactory\Core;

class Page extends Core {

    public static function display_page($info) {
        //echo render_breadcrumbs();
        self::setParam('container', FALSE);
        if (isset($_GET['viewpage']) && $_GET['viewpage'] == 1) {
            self::setParam('headerBg', FALSE);
        }
        Panels::getInstance(TRUE)->hide_panel('RIGHT');
		
        // cp_idx
        if (!empty($info['error'])) :
            echo "<div class='well text-center'>".$info['error']."</div>\n";
        else:
            echo $info['body'];
        endif;
        }
}