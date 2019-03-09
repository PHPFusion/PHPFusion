<?php

function fusion_load_bootstrap() {
    if (fusion_get_settings('bootstrap') or defined('BOOTSTRAP')) {
        $_themes = THEMES;
        if (!empty('CDN')) {
            $_themes = CDN.'themes/';
        }
        echo "<meta http-equiv='X-UA-Compatible' content='IE=edge' />\n";
        echo "<meta name='viewport' content='width=device-width, initial-scale=1.0' />\n";
        echo "<link href='".$_themes."templates/boilers/bootstrap3/bootstrap.min.css' rel='stylesheet' media='screen' />";
        if (fusion_get_locale('text-direction') == 'rtl') {
            echo "<link href='".$_themes."templates/boilers/bootstrap3/bootstrap-rtl.min.css' rel='stylesheet' media='screen' />";
        }
        add_to_footer("<script type='text/javascript' src='".$_themes."templates/boilers/bootstrap3/bootstrap.min.js'></script>");
    }

}

fusion_add_hook('start_boiler', 'fusion_load_bootstrap');