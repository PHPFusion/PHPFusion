<?php
/**
 * Response 400 - Invalid Request
 * Response 300 - Package Disabled
 * Response 200 - Success
 */
require_once __DIR__.'/../../../../maincore.php';
require_once INCLUDES.'ajax_include.php';

$data = [
    "query"                   => get('q', FILTER_VALIDATE_INT),
    "response"                => 400,
    "package_id"              => "",
    "package_status"          => "",
    "package_coin_quantity"   => "",
    "package_price"           => "",
    "package_datestamp"       => "",
    "package_promotion"       => "",
    "package_promotion_start" => "",
    "package_promotion_end"   => "",
    "package_promotion_bonus" => "",
];

if ( $data['query'] ) {

    $result = dbquery( "SELECT * FROM ".DB_COIN_PACKS." WHERE package_id=:id AND package_status=1 AND (package_promotion_start=0||package_promotion_start<=:time00)
        AND (package_promotion_end=0||package_promotion_end>=:time01)", [
        ':id'    => (int) $data['query'] ,
        'time00' => TIME,
        'time01' => TIME,
    ] );
    if ( dbrows( $result ) ) {
        $data = dbarray( $result );
        $data['response'] = 200;
        $bonus = $data['package_promotion'] && $data['package_promotion_bonus'] ? ' + '.number_format( $data['package_promotion_bonus'], 0 ).' Coin(s) bonus' : '';
        $data['package_coin_quantity'] = number_format( $data['package_coin_quantity'], 0 ).$bonus;
    } else {
        $data['response'] = 300;
    }
}

echo json_encode( $data );
