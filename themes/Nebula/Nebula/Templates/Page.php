<?php
namespace Nebula\Templates;

use Nebula\Layouts\MainFrame;
use PHPFusion\Panels;

class Page extends MainFrame {

    public static function display_page($info) {
        //echo render_breadcrumbs();
        Panels::getInstance(TRUE)->hide_panel('RIGHT');

        opentable($info['title']);
        echo "<!--cp_idx-->\n";
        if (!empty($info['error'])) {
            echo "<div class='well text-center'>\n";
            echo $info['error'];
            echo "</div>\n";
        } else {
            echo $info['body'];
        }
        closetable();
    }

}