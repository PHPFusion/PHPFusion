<?php
require_once __DIR__."/../../../maincore.php";


/**
 * Get extended endpoint
 *
 * @return array
 */
function get_extended_endpoints() {
    if ($extended_endpoints = fusion_filter_hook("wallet_register_api_hook_paths")) {
        return flatten_array($extended_endpoints);
    }
    return [];
}


$endpoints = [
        "usertrans"        => "wallet-transaction.php",
        "user-list"        => "admin-user-list.php",
        "admin-user-order" => "admin-order-list.php",
        "checkout"         => "checkout-ajax.php",
        "topup"            => "wallet-topup.php",
    ] + get_extended_endpoints();

if ($api = get("api")) {
    if (isset($endpoints[$api])) {

        require $endpoints[$api];

        fusion_apply_hook("wallet_filters");

    } else {
        throw new Exception("End point is faulty");
    }
} else {
    throw new Exception("API is not specified");
}
