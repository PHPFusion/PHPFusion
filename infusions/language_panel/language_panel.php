<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: language_panel.php
| Author: Frederick MC Chan (Chan)
| Author: Joakim Falk (Falk)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

$enabled_languages = fusion_get_enabled_languages();

if (count($enabled_languages) > 0) {
    $locale = fusion_get_locale();
    openside($locale['global_ML102']);
    echo "<h5><strong>".$locale['UM101']."</strong></h5>\n";

    echo openform('lang_menu_form', 'post', FUSION_SELF);
    echo form_select('lang_menu', '', fusion_get_settings('locale'), ["options" => fusion_get_enabled_languages(), "width" => "100%"]);
    echo closeform();
    add_to_jquery("
    function showflag(item){
        return '<div class=\"clearfix\" style=\"width:100%; padding-left:10px;\">
            <img style=\"height:20px; margin-top:3px !important;\" class=\"img-responsive pull-left\" src=\"".LOCALE."' + item.text + '/'+item.text + '-s.png\" alt=\"'+item.text + '\"/>
            <span class=\"p-l-10\">'+ item.text +'</span>
        </div>';
    }
    $('#lang_menu').select2({
    placeholder: '".$locale['global_ML103']."',
    formatSelection: showflag,
    escapeMarkup: function(m) { return m; },
    formatResult: showflag,
    }).bind('change', function(item) {
        window.location.href = '".FUSION_REQUEST."?lang='+$(this).val();
    });
");
    closeside();
}
