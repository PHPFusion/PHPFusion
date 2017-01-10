<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Console.core.inc
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Installer;

use PHPFusion\OutputHandler;

class Console_Core extends Install_Core {

    private static $console_instance = NULL;


    public static function getInstance() {
        if (self::$console_instance == NULL) {
            self::$console_instance = new static();
        }

        return self::$console_instance;
    }


    public static function getView($content) {

        $steps = array(
            '1' => self::$locale['setup_0101'],
            '2' => self::$locale['setup_0102'],
            '3' => self::$locale['setup_0103'],
            '4' => self::$locale['setup_0104'],
            '5' => self::$locale['setup_0106'],
            '6' => self::$locale['setup_0105'],
            '7' => self::$locale['setup_0107'],
        );

        $html = "<div class='block-container center-x'>\n";
        $html .= openform('setupform', 'post', FUSION_SELF."?localeset=".LANGUAGE);
        $html .= "<div class='block'>\n";
        $html .= "<div class='block-content'>\n";
        $html .= "<h6><strong>".self::$locale['setup_0000']."</strong></h6>\n";
        $html .= "<img class='pf-logo position-absolute' alt='PHP-Fusion' src='".IMAGES."php-fusion-icon.png'/>";

        $html .= "<p class='text-right'>".self::$locale['setup_0010'].self::BUILD_VERSION."</p>";
        $html .= renderNotices(getNotices());
        $html .= "<div class='row'>\n";
        $html .= "<div class='col-xs-12 col-sm-12 col-md-4 col-lg-3'>\n";
        $html .= "<h2 class='m-b-20 m-t-0 p-l-15'>".self::$locale['setup_0000']."</h2>\n";
        $html .= "<div class='list-group'>\n";
        foreach ($steps as $key => $value) {
            $active = intval(INSTALLATION_STEP) === $key;
            $html .= "<div class='list-group-item ".($active ? 'active xs sm' : '')."' style='border:0px;'>".(isset(self::$locale['setup_stepx']) ? sprintf(self::$locale['setup_stepx'],
                                                                                                                                                $key,
                                                                                                                                                $value) : $value)."</div>\n";
        }
        $html .= "</div>\n";
        $html .= "</div>\n";
        $html .= "<div class='col-xs-12 col-sm-12 col-md-8 col-lg-9'>\n";
        $html .= $content;
        if (self::$localeset) {
            $html .= form_hidden('localeset', self::$localeset);
        }

        if (self::$step) {
            foreach (self::$step as $button_prop) {
                $default_class['class'] = "btn-primary";
                $button_prop += $default_class;
                $html .= form_button($button_prop['name'], $button_prop['label'], $button_prop['value'],
                                     array(
                                         'class' => $button_prop['class'],
                                     ));
            }
        }
        $html .= "</div>\n</div>\n"; // end col-8 & row
        $html .= "</div>\n"; // end block-content
        $html .= "</div>\n"; // end block
        $html .= closeform();
        $html .= "</div>\n";

        return $html;
    }

    public static function getLayout() {
        $html = "<!DOCTYPE html>\n";
        $html .= "<html lang='".self::$locale['setup_0011']."'>\n";
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
        $html .= "<meta http-equiv='X-UA-Compatible' content='IE=edge'>\n";
        $html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0' />\n";
        $html .= "<script type='text/javascript' src='".INCLUDES."jquery/jquery.js'></script>\n";
        $html .= "<script type='text/javascript' src='".INCLUDES."bootstrap/bootstrap.min.js'></script>\n";
        $html .= "<link href='".DYNAMICS."assets/select2/select2.css' rel='stylesheet' />\n";
        $html .= "<link href='".THEMES."templates/default.css' rel='stylesheet' />\n";
        $html .= "<link href='".INCLUDES."bootstrap/bootstrap.min.css' rel='stylesheet' />\n";
        $html .= "<link href='".THEMES."templates/install.css' rel='stylesheet'/>\n";
        $html .= "<link href='".INCLUDES."fonts/entypo/entypo.css' rel='stylesheet' />";
        $html .= OutputHandler::$pageHeadTags;
        $html .= "</head>\n<body>\n";
        $html .= "{%content%}";
        $fusion_jquery_tags = OutputHandler::$jqueryTags;
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
        $html .= OutputHandler::$pageFooterTags;
        $html .= "</body>\n";
        $html .= "</html>\n";

        return $html;
    }

}
