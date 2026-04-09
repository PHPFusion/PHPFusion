<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: wiki/index.php
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'/../../maincore.php';

if (!infusion_exists('wiki')) {
    redirect(BASEDIR.'error.php?code=404');
}

require_once THEMES.'templates/header.php';
require_once INCLUDES.'infusions_include.php';
require_once INFUSIONS.'wiki/templates/wiki.php';
require_once INFUSIONS.'wiki/OpenGraphWiki.php';

$locale = fusion_get_locale('', WIKI_LOCALE);
$userdata = fusion_get_userdata();
$wiki_settings = get_settings('wiki');

set_title($locale['wiki_title']);

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => INFUSIONS.'wiki/', 'title' => $locale['wiki_title']]);

$info = [
];

render_wiki_index($info);

require_once THEMES.'templates/footer.php';
