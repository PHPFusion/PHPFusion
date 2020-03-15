<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: file_manager.php
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
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';

pageAccess('FM');

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/image_uploads.php');

add_to_title($locale['100']);

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'file_manager.php'.fusion_get_aidlink(), 'title' => $locale['100']]);
opentable($locale['100']);
add_to_head('<script src="'.INCLUDES.'jquery/jquery-ui.min.js"></script>');
add_to_head('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.min.css">');
add_to_head('<script src="'.INCLUDES.'eLfinder/js/elfinder.min.js"></script>');
add_to_head('<link rel="stylesheet" href="'.INCLUDES.'elFinder/css/elfinder.min.css">');
add_to_head('<link rel="stylesheet" href="'.INCLUDES.'elFinder/css/theme.css">');

$lang = '';
if (file_exists(INCLUDES.'elFinder/js/i18n/elfinder.'.$locale['filemanager'].'.js')) {
    $lang = ',lang: "'.$locale['filemanager'].'"';
}

add_to_jquery('
var elfinder_path = "//" + window.location.host + window.location.pathname.replace(/[\\\/][^\\\/]*$/, "") + "/";
$("#elfinder").elfinder({
    baseUrl: elfinder_path.replace("administration", "includes/elFinder"),
    url: elfinder_path.replace("administration", "includes/elFinder") + "php/connector.php"
    '.$lang.',
    themes: {
        "material-light": "themes/manifests/material-light.json",
        "material": "themes/manifests/material-default.json",
        "material-gray": "themes/manifests/material-gray.json"
    },
});
');

echo '<div id="elfinder"></div>';
closetable();

require_once THEMES.'templates/footer.php';
