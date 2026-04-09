<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: infusion_db.php
| Author: RobiNN
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

if (!defined('WIKI_LOCALE')) {
    if (file_exists(INFUSIONS.'wiki/locale/'.LOCALESET.'wiki.php')) {
        define('WIKI_LOCALE', INFUSIONS.'wiki/locale/'.LOCALESET.'wiki.php');
    } else {
        define('WIKI_LOCALE', INFUSIONS.'wiki/locale/English/wiki.php');
    }
}

if (!defined('WIKI')) {
    define('WIKI', INFUSIONS.'wiki/');
}

if (!defined('IMAGES_WIKI')) {
    define('IMAGES_WIKI', INFUSIONS.'wiki/images/docs/');
}

if (!defined('DB_WIKI')) {
    define('DB_WIKI', DB_PREFIX.'wiki');
}

if (!defined('DB_WIKI_CATS')) {
    define('DB_WIKI_CATS', DB_PREFIX.'wiki_cats');
}

if (!defined('DB_WIKI_VERSIONS')) {
    define('DB_WIKI_VERSIONS', DB_PREFIX.'wiki_versions');
}

\PHPFusion\Admins::getInstance()->setAdminPageIcons('WIKI', '<i class="admin-ico fa fa-fw fa-wikipedia-w"></i>');

$inf_settings = get_settings('wiki');
if (!empty($inf_settings['wiki_allow_submission']) && $inf_settings['wiki_allow_submission']) {
    \PHPFusion\Admins::getInstance()->setSubmitData('w', [
        'infusion_name' => 'wiki',
        'link'          => INFUSIONS.'wiki/wiki_submit.php',
        'submit_link'   => 'submit.php?stype=w',
        'submit_locale' => fusion_get_locale('wiki_title', WIKI_LOCALE),
        'title'         => fusion_get_locale('docs_submit', WIKI_LOCALE),
        'admin_link'    => INFUSIONS.'wiki/admin.php'.fusion_get_aidlink().'&section=submissions&submit_id=%s'
    ]);
}

if (method_exists(\PHPFusion\Admins::getInstance(), 'setFolderPermissions')) {
    \PHPFusion\Admins::getInstance()->setFolderPermissions('wiki', [
        'infusions/wiki/images/docs/' => TRUE
    ]);
}

if (method_exists(\PHPFusion\Admins::getInstance(), 'setCustomFolder')) {
    \PHPFusion\Admins::getInstance()->setCustomFolder('WIKI', [
        [
            'path'  => IMAGES_WIKI,
            'URL'   => fusion_get_settings('siteurl').'infusions/wiki/images/docs/',
            'alias' => 'wiki'
        ]
    ]);
}
