<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: basetime.php
| Author: Frederick MC Chan
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once dirname(__FILE__).'/../maincore.php';
require_once THEMES.'templates/header.php';
?>
    <h1 class='text-center'>Base Time Optimization Test</h1>
<?php
$logs = \PHPFusion\Database\DatabaseFactory::getConnection('default')->getQueryLog();
print_p($logs);
require_once THEMES.'templates/footer.php';

/**
 * Developer's use only
 * This file is intended for optimization and test benchmark issues on your server.
 *
 * Test Log - date / render excerpts
 * 31/5/2017   Render time: 0.36602 seconds | Average: 0.31462 (0.0197) seconds | Queries: 23
 *
 */