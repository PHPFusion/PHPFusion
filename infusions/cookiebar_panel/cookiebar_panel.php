<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: cookiebar_panel/cookiebar_panel.php
| Author: PHP-Fusion Development Team
| Co-Author: Joakim Falk (Domi)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

if (!isset($_COOKIE[COOKIE_PREFIX.'cookieconsent'])) {
    $settings = fusion_get_settings();
    $locale = fusion_get_locale("", COOKIE_LOCALE);

    add_to_head("<link rel='stylesheet' type='text/css' href='".INFUSIONS."cookiebar_panel/cookiebar_panel.css' />");

    add_to_footer("<script type='text/javascript' src='".INCLUDES."jquery/colorbox/jquery.colorbox.js'></script>");

    add_to_jquery("
    $('#consentcookies').bind('click', function(evt) {
        console.log('clicked');
        $.ajax({
            type:'POST',
            url:'".INFUSIONS."cookiebar_panel/consentcookies.php',
            data: $('#consentcookieform').serialize(),
            dataType:'html',
            success:function(data) {
                $('#Cookiebar').slideUp();
            }
        });
        evt.preventDefault();
    });
    $('.cookieoverlay').colorbox({height:'100%',width:'100%',maxWidth:'800px',maxHeight:'700px',scrolling:true,overlayClose:false,transition:'elastic'});
    ");

    echo "<div id='Cookiebar'>\n";
    echo "<div class='container'>\n";

    echo openform('consentcookieform', 'post', FUSION_REQUEST, ['remote_url'=>fusion_get_settings('site_path').'infusions/cookiebar_panel/consentcookies.php', 'class'=>'pull-right m-l-15']);
    echo form_button('consentcookies', $locale['CBP100'], 'consentcookies', ['class'=>'btn-primary', 'icon' => 'fa fa-check-circle']);
    echo closeform();

    echo "<div class='overflow-hide'>\n";
    echo $locale['CBP101']."<br/>\n".$locale['CBP103'];
    echo "<a class='cookieoverlay' href='".INFUSIONS."cookiebar_panel/cookiesinfo.php'>".$locale['CBP102']."</a>\n";
    echo "</div>\n";

    echo "</div>\n";
    echo "</div>\n";

}
