<?php

namespace PHPFusion\Installer;

class Console_Core extends Install_Core {

    private static $console_instance = NULL;

    public static function getInstance() {
        if (self::$console_instance == NULL) {
            self::$console_instance = new static();
        }

        return self::$console_instance;
    }

    public static function getView($content) {

        $html = $content;

        return $html;

    }

    public static function getLayout() {

        global $fusion_jquery_tags, $fusion_page_head_tags;

        $html = "<!DOCTYPE html>\n";
        $html .= "<head>\n";
        $html .= "<title>".self::$locale['setup_0000']."</title>\n";
        $html .= "<meta charset='".self::$locale['setup_0012']."' />";
        $html .= "<link rel='apple-touch-icon' sizes='57x57' href='".IMAGES."favicons/apple-touch-icon-57x57.png'/>\n";
        $html .= "<link rel='apple-touch-icon' sizes='114x114' href='".IMAGES."favicons/apple-touch-icon-114x114.png'/>\n";
        $html .= "<link rel='apple-touch-icon' sizes='72x72' href='".IMAGES."favicons/apple-touch-icon-72x72.png'/>\n";
        $html .= "<link rel='apple-touch-icon' sizes='144x144' href='".IMAGES."favicons/apple-touch-icon-144x144.png'/>\n";
        $html .= "<link rel='apple-touch-icon' sizes='60x60' href='".IMAGES."favicons/apple-touch-icon-60x60.png'/>\n";
        $html .= "<link rel='apple-touch-icon' sizes='120x120' href='".IMAGES."favicons/apple-touch-icon-120x120.png'/>\n";
        $html .= "<link rel='apple-touch-icon' sizes='76x76' href='".IMAGES."favicons/apple-touch-icon-76x76.png'/>\n";
        $html .= "<link rel='shortcut icon' href='".IMAGES."favicons/favicon.ico'/>\n";
        $html .= "<link rel='icon' type='image/png' href='".IMAGES."favicons/favicon-96x96.png' sizes='96x96'/>\n";
        $html .= "<link rel='icon' type='image/png' href='".IMAGES."favicons/favicon-16x16.png' sizes='16x16'/>\n";
        $html .= "<link rel='icon' type='image/png' href='".IMAGES."favicons/favicon-32x32.png' sizes='32x32'/>\n";
        $html .= "<meta name='msapplication-TileColor' content='#2d7793'/>\n";
        $html .= "<meta name='msapplication-TileImage' content='".IMAGES."favicons/mstile-144x144.png'/>\n";
        $html .= "<link rel='stylesheet' href='".THEMES."templates/setup_styles.css' type='text/css' />\n";
        $html .= "<meta http-equiv='X-UA-Compatible' content='IE=edge'>\n";
        $html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0' />\n";
        $html .= "<script type='text/javascript' src='".INCLUDES."jquery/jquery.js'></script>\n";
        $html .= "<script type='text/javascript' src='".INCLUDES."bootstrap/bootstrap.min.js'></script>\n";
        $html .= "<link href='".DYNAMICS."assets/select2/select2.css' rel='stylesheet' />\n";
        $html .= "<link href='".THEMES."templates/default.css' rel='stylesheet' />\n";
        $html .= "<link href='".INCLUDES."bootstrap/bootstrap.min.css' rel='stylesheet' />\n";
        $html .= "<link href='".INCLUDES."fonts/entypo/entypo.css' rel='stylesheet' />";
        $html .= $fusion_page_head_tags;
        $html .= "</head>\n<body>\n";
        $html .= "{%content%}";
        if (!empty($fusion_jquery_tags)) {
            $html .= "<script type=\"text/javascript\">\n$(function() {\n";
            $html .= "var diff_height = $('.block-container').height() - $('body').height();
            if (diff_height > 1) {
            $('.block-container').css({ 'margin-top' : diff_height+'px', 'margin-bottom' : diff_height/2+'px' });
            }
            ";
            $html .= $fusion_jquery_tags;
            $html .= "});\n</script>\n";
        }
        $html .= "</body>\n";
        $html .= "</html>\n";

        return $html;
    }

}