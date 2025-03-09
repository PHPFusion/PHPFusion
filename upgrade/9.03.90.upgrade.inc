<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: 9.03.90.upgrade.inc
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
$settings = fusion_get_settings();

$update_settings_tbl = [
    'version' => '9.03.90'
];

foreach ($update_settings_tbl as $key => $value) {
    if (isset($settings[$key])) {
        $inf_updatedbrow[] = DB_SETTINGS." SET settings_value='$value' WHERE settings_name='$key'";
    }
}

$insert_settings_tbl = [
    'number_delimiter'      => '.',
    'thousands_separator'   => ',',
    'error_logging_enabled' => 1,
    'error_logging_method'  => 'database'
];

foreach ($insert_settings_tbl as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS." (settings_name, settings_value) VALUES ('$key', '$value')";
    }
}

// PM fix
if (db_exists(DB_PREFIX.'messages')) {
    if (!column_exists(DB_MESSAGES, 'message_user')) {
        $inf_altertable[] = DB_MESSAGES." ADD message_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' AFTER message_from";
    }

    $inf_updatedbrow[] = DB_MESSAGES." SET message_user=message_to";
}
/**
 * Delete all unused files
 */
$deleted_files = [
    CLASSES.'PHPFusion/Atom/Admin.inc',
    CLASSES.'PHPFusion/Atom/Atom.inc',
    CLASSES.'PHPFusion/Database/Driver/MySQLi.inc',
    CLASSES.'PHPFusion/Database/Driver/PDOMySQL.inc',
    CLASSES.'PHPFusion/Database/Exception/ConnectionException.inc',
    CLASSES.'PHPFusion/Database/Exception/SelectionException.inc',
    CLASSES.'PHPFusion/Database/Exception/UndefinedConfigurationException.inc',
    CLASSES.'PHPFusion/Database/AbstractDatabaseDriver.inc',
    CLASSES.'PHPFusion/Database/Configuration.inc',
    CLASSES.'PHPFusion/Database/DatabaseFactory.inc',
    CLASSES.'PHPFusion/Feedback/Comments.inc',
    CLASSES.'PHPFusion/Installer/Lib/Core.settings.inc',
    CLASSES.'PHPFusion/Installer/Lib/Core.tables.inc',
    CLASSES.'PHPFusion/Installer/Steps/AdminSetup.inc',
    CLASSES.'PHPFusion/Installer/Steps/Complete.inc',
    CLASSES.'PHPFusion/Installer/Steps/DatabaseSetup.inc',
    CLASSES.'PHPFusion/Installer/Steps/InfusionsSetup.inc',
    CLASSES.'PHPFusion/Installer/Steps/Introduction.inc',
    CLASSES.'PHPFusion/Installer/Steps/Permissions.inc',
    CLASSES.'PHPFusion/Installer/Batch.core.inc',
    CLASSES.'PHPFusion/Installer/Console.core.inc',
    CLASSES.'PHPFusion/Installer/Infusion.core.inc',
    CLASSES.'PHPFusion/Installer/Install.core.inc',
    CLASSES.'PHPFusion/Installer/Requirements.core.inc',
    CLASSES.'PHPFusion/Minify/Cache/CacheException.inc',
    CLASSES.'PHPFusion/Minify/Cache/CacheItemInterface.inc',
    CLASSES.'PHPFusion/Minify/Cache/CacheItemPoolInterface.inc',
    CLASSES.'PHPFusion/Minify/Cache/InvalidArgumentException.inc',
    CLASSES.'PHPFusion/Minify/Exceptions/BasicException.inc',
    CLASSES.'PHPFusion/Minify/Exceptions/FileImportException.inc',
    CLASSES.'PHPFusion/Minify/Exceptions/IOException.inc',
    CLASSES.'PHPFusion/Minify/PathConverter/Converter.inc',
    CLASSES.'PHPFusion/Minify/PathConverter/ConverterInterface.inc',
    CLASSES.'PHPFusion/Minify/PathConverter/NoConverter.inc',
    CLASSES.'PHPFusion/Minify/CSS.inc',
    CLASSES.'PHPFusion/Minify/Exception.inc',
    CLASSES.'PHPFusion/Minify/JS.inc',
    CLASSES.'PHPFusion/Minify/Minify.inc',
    CLASSES.'PHPFusion/Page/Composer/Node/ComposeContent.inc',
    CLASSES.'PHPFusion/Page/Composer/Node/ComposeEngine.inc',
    CLASSES.'PHPFusion/Page/Composer/Node/ComposeSettings.inc',
    CLASSES.'PHPFusion/Page/Composer/PageComposer.inc',
    CLASSES.'PHPFusion/Page/Composer/PageList.inc',
    CLASSES.'PHPFusion/Page/PageAdmin.inc',
    CLASSES.'PHPFusion/Page/PageController.inc',
    CLASSES.'PHPFusion/Page/PageModel.inc',
    CLASSES.'PHPFusion/Page/PageView.inc',
    CLASSES.'PHPFusion/Page/WidgetAdminInterface.inc',
    CLASSES.'PHPFusion/Page/WidgetInterface.inc',
    CLASSES.'PHPFusion/Rewrite/Permalinks.inc',
    CLASSES.'PHPFusion/Rewrite/RewriteDriver.inc',
    CLASSES.'PHPFusion/Rewrite/Router.inc',
    CLASSES.'PHPFusion/Search/Search_Engine.inc',
    CLASSES.'PHPFusion/Search/Search_Model.inc',
    CLASSES.'PHPFusion/Admins.inc',
    CLASSES.'PHPFusion/AdminSearch.inc',
    CLASSES.'PHPFusion/Authenticate.inc',
    CLASSES.'PHPFusion/BreadCrumbs.inc',
    CLASSES.'PHPFusion/CustomPage.inc',
    CLASSES.'PHPFusion/Errors.inc',
    CLASSES.'PHPFusion/Event.inc',
    CLASSES.'PHPFusion/Geomap.inc',
    CLASSES.'PHPFusion/ImageRepo.inc',
    CLASSES.'PHPFusion/Locale.inc',
    CLASSES.'PHPFusion/LostPassword.inc',
    CLASSES.'PHPFusion/Members.inc',
    CLASSES.'PHPFusion/OpenGraph.inc',
    CLASSES.'PHPFusion/OutputHandler.inc',
    CLASSES.'PHPFusion/Panels.inc',
    CLASSES.'PHPFusion/PasswordAuth.inc',
    CLASSES.'PHPFusion/PrivateMessages.inc',
    CLASSES.'PHPFusion/QuantumFields.inc',
    CLASSES.'PHPFusion/RemoteFileReader.inc',
    CLASSES.'PHPFusion/Search.inc',
    CLASSES.'PHPFusion/Sessions.inc',
    CLASSES.'PHPFusion/SiteLinks.inc',
    CLASSES.'PHPFusion/Template.inc',
    CLASSES.'PHPFusion/UserFields.inc',
    CLASSES.'PHPFusion/UserFieldsInput.inc',
    CLASSES.'PHPFusion/UserGroups.inc',
    CLASSES.'Authenticate.class.inc',
    CLASSES.'LostPassword.class.inc',
    CLASSES.'PasswordAuth.class.inc',
    CLASSES.'UserFields.class.inc',
    CLASSES.'UserFieldsInput.class.inc',
    INCLUDES.'defender.inc',
    INCLUDES.'dynamics.inc',
    INCLUDES.'notify.inc'
];

foreach ($deleted_files as $file) {
    if (is_file($file)) {
        @unlink($file);
    } else {
        if (is_dir($file)) {
            rrmdir($file);
        }
    }
}
