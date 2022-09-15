<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: file_manager.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
pageaccess('FM');

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/image_uploads.php');

add_to_title($locale['IMGUP_100']);

add_breadcrumb(['link' => ADMIN.'file_manager.php'.fusion_get_aidlink(), 'title' => $locale['IMGUP_100']]);

opentable($locale['IMGUP_100']);
add_to_head('<script src="'.INCLUDES.'jquery/jquery-ui/jquery-ui.min.js"></script>');
add_to_head('<link rel="stylesheet" href="'.INCLUDES.'jquery/jquery-ui/jquery-ui.min.css">');
add_to_head('<script src="'.INCLUDES.'elFinder/js/elfinder.min.js"></script>');
add_to_head('<link rel="stylesheet" href="'.INCLUDES.'elFinder/css/elfinder.min.css">');
add_to_head('<link rel="stylesheet" href="'.INCLUDES.'elFinder/css/theme.css">');
$lang = '';
if (file_exists(LOCALE.LOCALESET.'includes/elFinder/js/i18n/elFinder.'.$locale['filemanager'].'.js')) {
    add_to_head('<script src="'.LOCALE.LOCALESET.'includes/elFinder/js/i18n/elFinder.'.$locale['filemanager'].'.js"></script>');
    $lang = ',lang: "'.$locale['filemanager'].'"';
}

add_to_jquery('
$("#elfinder").elfinder({
    baseUrl: "'.INCLUDES.'elFinder/",
    url: "'.INCLUDES.'elFinder/php/connector.php'.fusion_get_aidlink().'"
    '.$lang.',
    cssAutoLoad: ["themes/Material/css/theme-light.min.css"],
    ui: ["toolbar", "tree", "path", "stat"],
    uiOptions: {
        toolbar: [
            ["home", "back", "forward", "up", "reload"],
            ["mkdir", "mkfile", "upload"],
            ["open"],
            ["copy", "cut", "paste", "rm", "empty"],
            ["duplicate", "rename", "edit", "resize", "chmod"],
            ["quicklook", "info"],
            ["extract", "archive"],
            ["search"],
            ["view", "sort"],
            ["preference", "help"]
        ]
    }
});
');

echo '<div id="elfinder"></div>';
closetable();

require_once THEMES.'templates/footer.php';
