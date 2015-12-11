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
define("DB_ARTICLE_CATS", DB_PREFIX."article_cats");
define("DB_ARTICLES", DB_PREFIX."articles");
define("DB_BBCODES", DB_PREFIX."bbcodes");
define("DB_BLACKLIST", DB_PREFIX."blacklist");
define("DB_BLOG", DB_PREFIX."blog");
define("DB_BLOG_CATS", DB_PREFIX."blog_cats");
define("DB_CAPTCHA", DB_PREFIX."captcha");
define("DB_COMMENTS", DB_PREFIX."comments");
define("DB_CUSTOM_PAGES", DB_PREFIX."custom_pages");
define("DB_DOWNLOAD_CATS", DB_PREFIX."download_cats");
define("DB_DOWNLOADS", DB_PREFIX."downloads");
define("DB_EMAIL_VERIFY", DB_PREFIX."email_verify");
define("DB_EMAIL_TEMPLATES", DB_PREFIX."email_templates");
define("DB_ERRORS", DB_PREFIX."errors");
define("DB_ESHOP", DB_PREFIX."eshop");
define("DB_ESHOP_CATS", DB_PREFIX."eshop_cats");
define("DB_ESHOP_CART", DB_PREFIX."eshop_cart");
define("DB_ESHOP_PHOTOS", DB_PREFIX."eshop_photos");
define("DB_ESHOP_ALBUMS", DB_PREFIX."eshop_photo_albums");
define("DB_ESHOP_SHIPPINGCATS", DB_PREFIX."eshop_shippingcats");
define("DB_ESHOP_SHIPPINGITEMS", DB_PREFIX."eshop_shippingitems");
define("DB_ESHOP_PAYMENTS", DB_PREFIX."eshop_payments");
define("DB_ESHOP_CUSTOMERS", DB_PREFIX."eshop_customers");
define("DB_ESHOP_ORDERS", DB_PREFIX."eshop_orders");
define("DB_ESHOP_COUPONS", DB_PREFIX."eshop_coupons");
define("DB_ESHOP_FEATITEMS", DB_PREFIX."eshop_featitems");
define("DB_ESHOP_FEATBANNERS", DB_PREFIX."eshop_featbanners");
define("DB_FAQ_CATS", DB_PREFIX."faq_cats");
define("DB_FAQS", DB_PREFIX."faqs");
define("DB_FLOOD_CONTROL", DB_PREFIX."flood_control");
define("DB_FORUM_ATTACHMENTS", DB_PREFIX."forum_attachments");
define("DB_FORUM_POLL_OPTIONS", DB_PREFIX."forum_poll_options");
define("DB_FORUM_POLL_VOTERS", DB_PREFIX."forum_poll_voters");
define("DB_FORUM_POLLS", DB_PREFIX."forum_polls");
define("DB_FORUM_POSTS", DB_PREFIX."forum_posts");
define("DB_FORUM_RANKS", DB_PREFIX."forum_ranks");
define("DB_FORUM_THREAD_NOTIFY", DB_PREFIX."forum_thread_notify");
define("DB_FORUM_THREADS", DB_PREFIX."forum_threads");
define("DB_FORUM_VOTES", DB_PREFIX."forum_votes");
define("DB_FORUMS", DB_PREFIX."forums");
define("DB_INFUSIONS", DB_PREFIX."infusions");
define("DB_INFUSIONS_CAT", DB_PREFIX."infusions_cat");
define("DB_LANGUAGE_TABLES", DB_PREFIX."mlt_tables");
define("DB_LANGUAGE_SESSIONS", DB_PREFIX."language_sessions");
define("DB_MESSAGES", DB_PREFIX."messages");
define("DB_NEW_USERS", DB_PREFIX."new_users");
define("DB_NEWS", DB_PREFIX."news");
define("DB_NEWS_CATS", DB_PREFIX."news_cats");
define("DB_ONLINE", DB_PREFIX."online");
define("DB_PANELS", DB_PREFIX."panels");
define("DB_PERMALINK_REWRITE", DB_PREFIX."permalinks_rewrites");
define("DB_PERMALINK_METHOD", DB_PREFIX."permalinks_method");
define("DB_PERMALINK_ALIAS", DB_PREFIX."permalinks_alias");
define("DB_PHOTO_ALBUMS", DB_PREFIX."photo_albums");
define("DB_PHOTOS", DB_PREFIX."photos");
define("DB_POLL_VOTES", DB_PREFIX."poll_votes");
define("DB_POLLS", DB_PREFIX."polls");
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
define("DB_WEBLINK_CATS", DB_PREFIX."weblink_cats");
define("DB_WEBLINKS", DB_PREFIX."weblinks");
define("DB_THEME", DB_PREFIX."theme");
