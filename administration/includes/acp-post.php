<?php
/** Load maincore and dependencies */
require_once(__DIR__.'/../../maincore.php');
/** no cache headers */
require_once(INCLUDES.'ajax_include.php');

define('FUSION_AJAX', TRUE);
header('Content-Type: text/html; charset=utf-8');
header('X-Robots-Tag: noindex');

/** Defines the core acceptable actions in tandem with the user access rights */
$core_accepted_args = [
    'SL' => [
        'add_menu_items'
    ]
];

$current_action = str_replace('-', '_', get('action'));

if (in_array($current_action, flatten_array($core_accepted_args))) {
    require_once(__DIR__.'/action/'.$current_rights.'.php');
    /** load the action into the hook */
    fusion_add_hook('fusion_'.$current_action);
}

$current_rights = post('rights');
$user_token = post('token');

/** Authenticate user with administrator token where the user token must be encrypted with site secret key. */
/** @var $auth */
if ($auth = fusion_authenticate_user($user_token)) {
    if (iADMIN && checkrights($current_rights)) {
        if (isset($core_accepted_args[$current_rights])) {
            /** Generates the output in json format */
            //echo fusion_filter_hook('fusion_'.$current_action, post(array_keys($_POST)));
            echo json_encode($_POST);
        }
    } else {
        die('You need to login as admin to perform this action');
    }
}
//die();
