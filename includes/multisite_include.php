<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: multisite_include.php
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
// Database table definitions
define("DB_ADMIN", DB_PREFIX."admin");
define("DB_ADMIN_RESETLOG", DB_PREFIX."admin_resetlog");
define("DB_BBCODES", DB_PREFIX."bbcodes");
define("DB_BLACKLIST", DB_PREFIX."blacklist");
define("DB_CAPTCHA", DB_PREFIX."captcha");
define("DB_COMMENTS", DB_PREFIX."comments");
define("DB_CUSTOM_PAGES", DB_PREFIX."custom_pages");
define("DB_CUSTOM_PAGES_GRID", DB_PREFIX."custom_pages_grid");
define("DB_CUSTOM_PAGES_CONTENT", DB_PREFIX."custom_pages_content");
define("DB_EMAIL_VERIFY", DB_PREFIX."email_verify");
define("DB_EMAIL_TEMPLATES", DB_PREFIX."email_templates");
define("DB_ERRORS", DB_PREFIX."errors");
define("DB_FLOOD_CONTROL", DB_PREFIX."flood_control");
define("DB_INFUSIONS", DB_PREFIX."infusions");
define("DB_LANGUAGE_TABLES", DB_PREFIX."mlt_tables");
define("DB_LANGUAGE_SESSIONS", DB_PREFIX."language_sessions");
define("DB_MESSAGES", DB_PREFIX."messages");
define("DB_NEW_USERS", DB_PREFIX."new_users");
define("DB_ONLINE", DB_PREFIX."online");
define("DB_PANELS", DB_PREFIX."panels");
define("DB_PERMALINK_REWRITE", DB_PREFIX."permalinks_rewrites");
define("DB_PERMALINK_METHOD", DB_PREFIX."permalinks_method");
define("DB_PERMALINK_ALIAS", DB_PREFIX."permalinks_alias");
define("DB_RATINGS", DB_PREFIX."ratings");
define("DB_SETTINGS", DB_PREFIX."settings");
define("DB_SETTINGS_INF", DB_PREFIX."settings_inf");
define("DB_SETTINGS_THEME", DB_PREFIX."settings_theme");
define("DB_SITE_LINKS", DB_PREFIX."site_links");
define("DB_SMILEYS", DB_PREFIX."smileys");
define("DB_SUBMISSIONS", DB_PREFIX."submissions");
define("DB_SUSPENDS", DB_PREFIX."suspends");
define("DB_USER_FIELD_CATS", DB_PREFIX."user_field_cats");
define("DB_USER_FIELDS", DB_PREFIX."user_fields");
define("DB_USER_GROUPS", DB_PREFIX."user_groups");
define("DB_USER_LOG", DB_PREFIX."user_log");
define("DB_USERS", DB_PREFIX."users");
define("DB_THEME", DB_PREFIX."theme");