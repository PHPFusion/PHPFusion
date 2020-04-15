<?php
/** Load maincore and dependencies */
require_once(__DIR__.'/../../maincore.php');
/** no cache headers */
//require_once(INCLUDES.'ajax_include.php');

define('FUSION_AJAX', TRUE);
header('Content-Type: text/html; charset=utf-8');
header('X-Robots-Tag: noindex');
// maybe i can get a cookie.
/** If token no error */
if (fusion_safe()) {

    /** Defines the core acceptable actions in tandem with the user access rights */
    $core_accepted_args = array(
        'SL' => array("add_links", "update_links", "remove_links", "update_menu")
    );
    $allowable_action = flatten_array($core_accepted_args);
    $hook = post('action_hook');
    /** @var $current_action - function name for hooks must work on underscore */
    $current_action = str_replace('-', '_', $hook);
    /** @var $acp_hook_file - The hook file must use hyphens as spacer and all lowercase */
    $acp_hook_file = str_replace('_', '-', $hook);

    if (in_array($current_action, $allowable_action)) {
        require_once(__DIR__.'/../actions/'.$acp_hook_file.'.php');
        /** load the action into the hook */
        fusion_add_hook('fusion_acp_action', $current_action);
        /** check for post rights and token **/
        $current_rights = get('rights');
        $user_token = get('token');
        if (!check_get('token')) {
            $user_token = cookie(COOKIE_PREFIX.'user');
        }
        /** Authenticate user with administrator token where the user token must be encrypted with site secret key. */
        /** @var $auth - the user auth data */
        try {
            if ($auth = fusion_authenticate_user($user_token)) {
                if (iADMIN && checkrights($current_rights)) {
                    if (isset($core_accepted_args[$current_rights])) {
                        $filter = fusion_filter_hook('fusion_acp_action', $_POST);
                        //print_P($filter);
                        //echo json_encode($_POST);
                    }
                } else {
                    //print_p('You need to login as admin to perform this action');
                    die('You need to login as admin to perform this action');
                }
            }
        } catch (Exception $e) {
            die('Could not authenticate user');
        }
    } else {
        die('Action is not allowable');
    }
}
/** Exit the code */
die();
