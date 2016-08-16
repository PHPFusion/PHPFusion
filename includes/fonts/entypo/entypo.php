<?php
require_once dirname(__FILE__)."../../../../maincore.php";
require_once THEMES."templates/header.php";

class icon {
    static function get_css($path) {
        $file = isset($path) && ($path) ? file($path) : print_p('no file');
        $init = 0;
        $css_token = array();
        foreach ($file as $arr => $value) {
            $value = stripinput($value);
            if (preg_match('@Marker@si', $value)) {
                $init = 1;
            }
            if ($init > 0) {
                if ($init > 1) {
                    $css_token = strtok($value, "{}");
                    $css[] = str_replace(':before', '', $css_token);
                }
                $init++;
            }
        }

        return $css;
    }

    static function show_icons($css) {
        $html = "<div class='panel panel-default'/>\n";
        $html .= "<div class='panel-body'>\n";
        $html .= "<div class='row'/>\n";
        $i = 0;
        foreach ($css as $arr => $class) {
            if ($i == 6) {
                $i = 0;
                $html .= "</div><div class='row'>\n";
            }
            $un_class = str_replace('.', '', $class);
            $html .= "<div class='col-xs-2 col-sm-2 col-md-2 col-lg-2 text-center' style='padding:10px; 0px;'>\n";
            $html .= "<i style='font-size:230%;' class='entypo $un_class'></i><br/>.entypo $un_class";
            $html .= "</div>\n";
            $i++;
        }
        $html .= "</div>\n";
        $html .= "</div></div>\n";

        return $html;
    }
}

$css_file = icon::get_css('entypo.css');
echo icon::show_icons($css_file);
require_once THEMES."templates/footer.php";

