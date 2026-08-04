<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: admin_header.php
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

use PHPFusion\Admins;

defined('IN_FUSION') || exit;

const ADMIN_PANEL = TRUE;

$settings = fusion_get_settings();
$userdata = fusion_get_userdata();
$locale = fusion_get_locale();

if ($settings['maintenance'] == "1" && ((iMEMBER && $settings['maintenance_level'] == USER_LEVEL_MEMBER && $userdata['user_id'] != "1") || ($settings['maintenance_level'] < $userdata['user_level']))) {
    redirect(BASEDIR."maintenance.php");
}

require_once INCLUDES."breadcrumbs.php";
if (file_exists(INCLUDES."header_includes.php")) {
    require_once INCLUDES."header_includes.php";
}

$globalAdminTheme = (string)$settings['admin_theme'];
$userAdminTheme = (string)($userdata['user_admin_theme'] ?? 'Default');
$adminTheme = $userAdminTheme !== 'Default' ? $userAdminTheme : $globalAdminTheme;
$adminThemeIsValid = static fn(string $theme): bool =>
    (bool)preg_match("/^([a-z0-9_-]){2,50}$/i", $theme)
    && file_exists(THEMES."admin_themes/".$theme."/acp_theme.php");

if (!$adminThemeIsValid($adminTheme) && $adminTheme !== $globalAdminTheme) {
    $adminTheme = $globalAdminTheme;
}

if ($adminThemeIsValid($adminTheme)) {
    if (!defined('ADMIN_THEME_NAME')) {
        define('ADMIN_THEME_NAME', $adminTheme);
    }
    require_once THEMES."admin_themes/".$adminTheme."/acp_theme.php";
} else {
    die('WARNING: Invalid Admin Panel Theme'); // TODO: improve this
}

// The administration page renders its buffered content before admin_layout.php
// is loaded. Boot the selected UI framework now so shared helpers and
// component adapters are available to controllers such as SettingsForm.
require_once INCLUDES.'frameworks/framework_engine.php';
fusion_framework_boot('admin');

fusion_load_script('https://fonts.googleapis.com', 'connection');
fusion_load_script('https://fonts.gstatic.com', 'connection');
fusion_load_script(
    'https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Geist+Mono:wght@100..900&display=swap',
    'css'
);

$adminDocumentLanguage = function_exists('get_inline_language') ? get_inline_language() : 'en-US';
$adminIsChineseLanguage = $adminDocumentLanguage === 'zh-CN' || $adminDocumentLanguage === 'zh-TW';
if ($adminIsChineseLanguage) {
    fusion_load_script(
        'https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@100..900&display=swap',
        'css'
    );
}
$adminCjkFallback = $adminIsChineseLanguage ? ", 'Noto Sans SC'" : '';
$adminSansFont = "'Geist'{$adminCjkFallback}, system-ui, sans-serif";
$adminMonoFont = "'Geist Mono', ui-monospace, SFMono-Regular, Consolas, 'Liberation Mono', monospace";

add_to_head("<style id='admin-geist-fonts'>
html:root {
    --admin-font-sans: {$adminSansFont};
    --admin-font-mono: {$adminMonoFont};
    --theme-body-font: var(--admin-font-sans) !important;
    --theme-display-font: var(--admin-font-sans) !important;
    --theme-mono-font: var(--admin-font-mono) !important;
    --font-sans: var(--admin-font-sans) !important;
    --font-mono: var(--admin-font-mono) !important;
    --font-monospace: var(--admin-font-mono) !important;
    --tblr-font-sans-serif: var(--admin-font-sans) !important;
    --tblr-font-monospace: var(--admin-font-mono) !important;
    --bs-font-sans-serif: var(--admin-font-sans) !important;
    --bs-font-monospace: var(--admin-font-mono) !important;
    --ds-font-sans-serif: var(--admin-font-sans) !important;
    --ds-font-monospace: var(--admin-font-mono) !important;
}
html body,
html body button,
html body input,
html body select,
html body textarea {
    font-family: var(--admin-font-sans) !important;
}
html body code,
html body kbd,
html body pre,
html body samp,
html body .font-monospace {
    font-family: var(--admin-font-mono) !important;
}
</style>");

require_once INCLUDES."theme_functions_include.php";

// for compatibility
if (!defined('THEME_BULLET')) {
    define('THEME_BULLET', '&middot;');
}

if (iMEMBER) {
    $result = dbquery("UPDATE ".DB_USERS." SET user_lastvisit=:time, user_ip=:ip, user_ip_type=:ip_type WHERE user_id=:user_id",
        [
            ':time'    => time(),
            ':ip'      => USER_IP,
            ':ip_type' => USER_IP_TYPE,
            ':user_id' => fusion_get_userdata('user_id')
        ]
    );
}

Admins::getInstance()->setAdminPages();
Admins::getInstance()->setAdminBreadcrumbs();

ob_start();

@list($title) = dbarraynum(dbquery("SELECT admin_title FROM ".DB_ADMIN." WHERE admin_link=:base_url", [':base_url' => FUSION_SELF]));

set_title($locale['global_123'].$locale['global_201'].(!empty($title) ? $title : ''));
// If the user is not logged in as admin then don't parse the administration page
// otherwise it could result in bypass of the admin password and one could do
// changes to the system settings without even being logged into Admin Panel.
// After relogin the user can simply click back in browser and their input will
// still be there so nothing is lost
if (!check_admin_pass('')) {
    // If not admin, also must check if user_id is exists due to session time out.
    $user_id = fusion_get_userdata('user_id');
    if (empty($user_id)) {
        redirect(BASEDIR."index.php");
    }
    require_once __DIR__.'/footer.php';
    exit;
}
