<?php
// Build the rules here for the api to read and output it's data

$api['auth']['token'] = [
    'name' => 'Fetches a token that can be used in the current site',
    'method' => 'get',
    'params' => [
        'form_id' => 'string',
        'max_tokens' => 'int',
        'file' => 'string',
        'token_time' => 'int',
    ],
    'callback' => ['\Defender\Token', 'generate_token'] // bind this to ReflectionClass for output?
];
$api['auth']['login'] = [
    'name' => 'Login to the current system',
    'method' => 'post',
    'construct' => [
        'user_id' => 'string',
        'user_email' => 'string',
        'user_pass' => 'string',
    ],
    'callback' => ['\PHPFusion\Authenticate', 'getUserData'] // bind this to ReflectionClass for output?
];
$api['auth']['logout'] = [
    'name' => 'Logout from the current system',
    'method' => 'post',
    'callback' => ['\PHPFusion\Authenticate', 'logOut'] // bind this to ReflectionClass for output?
];