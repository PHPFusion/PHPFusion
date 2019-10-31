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

    /**
     * @return null|static
     */
    public static function getConsoleInstance() {
        if (self::$console_instance == NULL) {
            self::$console_instance = new static();
        }

        return self::$console_instance;
    }

    public function getView($content) {
        $steps = [
            '1' => self::$locale['setup_0101'],
            '2' => self::$locale['setup_0102'],
            '3' => self::$locale['setup_0103'],
            '4' => self::$locale['setup_0104'],
            '5' => self::$locale['setup_0106'],
            '6' => self::$locale['setup_0105']
        ];

        $html = '<div class="block-container center-x">';
        $html .= openform('setupform', 'post', FUSION_SELF.'?localeset='.LANGUAGE);
        $html .= '<div class="block">';
        $html .= '<div class="row equal-height m-0">';
        $html .= '<div class="col-xs-12 col-sm-3 left-side p-0"><div>';
        $html .= '<img class="logo img-responsive" src="'.IMAGES.'php-fusion-icon.png" alt="PHP-Fusion"/>';
        $html .= '<h3 class="text-center m-t-0">PHP-Fusion CMS</h3>';

        $html .= '<ul class="menu list-style-none m-t-15">';
        foreach ($steps as $key => $value) {
            if ($key !== 4) {
                $active = intval(INSTALLATION_STEP) === $key;
                $html .= '<li'.($active ? ' class="active"' : '').'>'.$value.'</li>';
            }
        }
        $html .= '</ul>';
        $html .= '<div class="text-center build-version">'.self::$locale['setup_0010'].self::BUILD_VERSION.'</div>';
        $html .= '</div></div>'; // .left-side

        $html .= '<div class="col-xs-12 col-sm-9 content p-0"><div>';
        $html .= '<div class="block-content clearfix">';
        $html .= $content;

        if (self::$localeset) {
            $html .= form_hidden('localeset', self::$localeset);
        }

        $html .= '</div>';

        if (self::$step) {
            $html .= '<div class="buttons m-t-20">';
            foreach (self::$step as $button_prop) {
                $default_class['class'] = 'btn-primary';
                $button_prop += $default_class;
                $html .= form_button($button_prop['name'], $button_prop['label'], $button_prop['value'], ['class' => $button_prop['class']]);
            }
            $html .= '</div>';
        }

        $html .= '</div></div>'; // .content
        $html .= '</div>'; // .row
        $html .= '</div>'; // .block
        $html .= closeform();
        $html .= '</div>'; // .block-container

        return $html;
    }

    /**
     * Need to replace more things.
     *
     * @return string
     */
    public function getLayout() {
        $html = "<!DOCTYPE html>\n";
        $html .= "<html lang='".self::$locale['setup_0011']."' dir='".self::$locale['setup_0012a']."'>\n";
        $html .= "<head>\n";
        $html .= "<title>".(isset($_GET['upgrade']) ? self::$locale['setup_0020'] : self::$locale['setup_0000'])."</title>\n";
        $html .= "<meta charset='".self::$locale['setup_0012']."'>";
        $html .= '<link rel="shortcut icon" href="'.IMAGES.'favicons/favicon.ico">';
        $html .= "<meta http-equiv='X-UA-Compatible' content='IE=edge'>\n";
        $html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0' />\n";
        $html .= "<script src='".INCLUDES."jquery/jquery.min.js'></script>\n";
        $html .= "<script src='".INCLUDES."bootstrap/js/bootstrap.min.js'></script>\n";
        $html .= "<link rel='stylesheet' href='".THEMES."templates/default.min.css?v=".filemtime(THEMES.'templates/default.min.css')."'>\n";
        $html .= "<link rel='stylesheet' href='".INCLUDES."bootstrap/css/bootstrap.min.css'>\n";
        if (self::$locale['setup_0012a'] == 'rtl') {
            $html .= "<link rel='stylesheet' href='".INCLUDES."bootstrap/css/bootstrap-rtl.min.css'>";
        }
        $html .= "<link rel='stylesheet' href='".THEMES."templates/install.min.css?v=".filemtime(THEMES.'templates/install.min.css')."'>\n";
        $html .= "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome-5/css/all.min.css'>\n";
        $html .= OutputHandler::$pageHeadTags;
        $html .= "</head>\n<body".(isset($_GET['upgrade']) ? " class='upgrade'" : '').">\n";
        $html .= "{%content%}";
        $fusion_jquery_tags = OutputHandler::$jqueryTags;
        if (!empty($fusion_jquery_tags)) {
            $html .= "<script>$(function() {
            let container = $('.block-container');
            let diff_height = container.height() - $('body').height();
            if (diff_height > 1) {
            container.css({ 'margin-top' : diff_height+'px', 'margin-bottom' : diff_height/2+'px' });
            }
            ".$fusion_jquery_tags."
            });\n</script>\n";
        }
        $html .= OutputHandler::$pageFooterTags;
        $html .= "</body>\n";
        $html .= "</html>\n";

        return $html;
    }

}
