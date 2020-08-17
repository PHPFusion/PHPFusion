<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: meta-ui-tags.php
| Author: Frederick MC Chan (Deviance)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

define('FUSION_ALLOW_REMOTE', true);
require_once __DIR__.'/../../../maincore.php';
require_once INCLUDES.'ajax_include.php';

// how to find most common where wiki_tag = title,title2,title3
// to continue here after i have coded the rest.
//$result = dbquery("SELECT COUNT(wiki_tag_id)  t.* FROM ".DB_TAGS." t WHERE ");