<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: wiki.php
| Author: RobiNN
+--------------------------------------------------------*/
require_once __DIR__.'/../../maincore.php';

if (!defined('WIKI_EXIST')) {
    redirect(BASEDIR.'error.php?code=404');
} else {
    redirect(WIKI);
}
