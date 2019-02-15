<?php
require_once __DIR__.'/../../maincore.php';

/**
 * Multipurpose boilerplate API
 */
$left_col = $fusion_steam->load('blocks')->box([
    'title'       => 'hi',
    'description' => 'This is the box content'
]);

$right_col = $fusion_steam->load('blocks')->box([
    'title'       => 'another box',
    'description' => 'This is another box content'
]);

echo $fusion_steam->load('layout')->grid(array(
    [
        'content' => $left_col,
        'xs' => 12,
        'sm' => 12,
        'md' => 9,
        'lg' => 9
    ],
    [
        'content' => $right_col,
        'xs' => 12,
        'sm' => 12,
        'md' => 3,
        'lg' => 3
    ]
));
