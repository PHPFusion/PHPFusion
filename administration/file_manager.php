<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: file_manager.php
| Author: Core Development Team (coredevs@phpfusion.com)
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

pageAccess('FM');

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/image_uploads.php');

add_to_title($locale['100']);

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'file_manager.php'.fusion_get_aidlink(), 'title' => $locale['100']]);
opentable($locale['100']);
add_to_head('<script src="'.INCLUDES.'jquery/jquery-ui/jquery-ui.min.js"></script>');
add_to_head('<link rel="stylesheet" href="'.INCLUDES.'jquery/jquery-ui/jquery-ui.min.css">');
add_to_head('<script src="'.INCLUDES.'elFinder/js/elfinder.min.js"></script>');
add_to_head('<link rel="stylesheet" href="'.INCLUDES.'elFinder/css/elfinder.min.css">');
add_to_head('<link rel="stylesheet" href="'.INCLUDES.'elFinder/css/theme.css">');

$lang = '';
if (file_exists(INCLUDES.'elFinder/js/i18n/elFinder.'.$locale['filemanager'].'.js')) {
    $lang = ',lang: "'.$locale['filemanager'].'"';
}

add_to_jquery('
$("#elfinder").elfinder({
    baseUrl: "'.INCLUDES.'elFinder/",
    url: "'.INCLUDES.'elFinder/php/connector.php'.fusion_get_aidlink().'"
    '.$lang.',
    themes: {
        "material-light": "themes/manifests/material-light.json",
        "material": "themes/manifests/material-default.json",
        "material-gray": "themes/manifests/material-gray.json"
    },
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
