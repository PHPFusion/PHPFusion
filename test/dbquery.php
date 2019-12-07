<?php
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/header.php';

$data[] = [ 'news_id' => '1', 'news_name' => 'aaa', 'news_subject' => 'test' ];
$data[] = [ 'news_id' => '2', 'news_name' => 'aaa', 'news_subject' => 'test' ];
$data[] = [ 'news_id' => '2', 'news_name' => 'aaa', 'news_subject' => 'test', 'news_datestamp' => TIME ];

dbquery_insert( DB_NEWS, $data, 'multi_update', [ 'debug' => TRUE ] );

require_once THEMES.'templates/footer.php';
