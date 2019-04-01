<?php

/**
 * The API is as following:
 * 2 new strings:
 *  -   $user_fields_section         - required keys are 'title', 'id'
 *  -   $user_fields                 - the view applicable to the current $GET section.
 */

$user_fields_section = array(
    'title' => [
        0 => 'Section A',
        1 => 'Section B',
    ],
    'id' => [
        0 => 'a',
        1 => 'b'
    ]
);
switch($_GET['section']) {
    default:
    case 'a':
    $user_fields = 'Content View';
        break;
    case 'b':
    $user_fields = 'Content B View';
        break;
}
