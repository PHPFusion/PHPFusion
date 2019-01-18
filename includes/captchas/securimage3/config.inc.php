<?php
require_once __DIR__.'../../../../maincore.php';
require_once __DIR__.'../../../../config.php';

if (session_status() == PHP_SESSION_NONE) {
    @session_start();
}

return [
    'session_name'           => COOKIE_PREFIX.'session',
    'wordlist_file_encoding' => 'UTF-8'
];
