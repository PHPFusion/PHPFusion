<?php

use PHPFusion\QuantumFields;

defined('IN_FUSION') || exit;

$locale = fusion_get_locale('', LOCALE . LOCALESET . 'admin/fields.php');

$contents = [
    'post'     => 'pf_post',
    'view'     => 'pf_view',
    'button'   => 'pf_button',
    'js'       => 'pf_js',
    'settings' => TRUE,
    'link'     => ( $admin_link ?? '' ),
    'title'    => $locale['202'],
    //'description' => $locale['BN_001'],
    'actions'  => pf_actions(),
];

function pf_actions()
: array {
    return (new QuantumFields())->quantumActions();
}

function pf_button()
: string {

    return ( new QuantumFields() )->quantumButtons();
}

function pf_view() {

    $user_field = new PHPFusion\QuantumFields();
    $user_field->method = 'input'; //setMethod('input');
    $user_field->displayAdmin();

}
