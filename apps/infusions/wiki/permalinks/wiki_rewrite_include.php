<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: wiki_rewrite_include.php
| Author: RobiNN
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

$regex = [
    '%wiki_id%'   => '([0-9]+)',
    '%wiki_name%' => '([0-9a-zA-Z._\W]+)',
    '%stype%'     => '(w)'
];

$pattern = [
    'submit-%stype%/wiki'                         => 'submit.php?stype=%stype%',
    'submit-%stype%/wiki/submitted-and-thank-you' => 'submit.php?stype=%stype%&submitted=wiki',
    'wiki/%wiki_id%/%wiki_name%'                  => 'infusions/wiki/documentation.php?page_id=%wiki_id%',
    'wiki'                                        => 'infusions/wiki/documentation.php',
    'wiki/category/%wiki_cat_id%/%wiki_cat_name%' => 'infusions/wiki/index.php?cat_id=%wiki_cat_id%',
];

$pattern_tables['%wiki_id%'] = [
    'table'       => DB_WIKI,
    'primary_key' => 'wiki_id',
    'id'          => ['%wiki_id%' => 'wiki_id'],
    'columns'     => [
        '%wiki_name%' => 'wiki_name'
    ]
];

$pattern_tables['%wiki_cat_id%'] = [
    'table'       => DB_WIKI_CATS,
    'primary_key' => 'wiki_cat_id',
    'id'          => ['%wiki_cat_id%' => 'wiki_cat_id'],
    'columns'     => [
        '%wiki_cat_name%' => 'wiki_cat_name'
    ]
];
