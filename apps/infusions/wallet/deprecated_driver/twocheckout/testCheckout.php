<?php
require_once(dirname(__FILE__).'/../../maincore.php');
require_once THEMES.'templates/header.php';
require_once INFUSIONS."wallet/autoloader.php";

/*
 * Prepare our items
 * I'll just prepare a simple array for a 2 stepper full purchasing experience
 */
$products[1] = array(
    'id'            => 1,
    'name'          => 'Apple Half Dozen',
    'description'   => 'Imported Fresh Apple Half a Dozen from Australia',
    'quantity'      => 6,
    'unit_price'    => \Wallet\Model::format_price(0.5),
    'unit_tax_rate' => 6,
    'tangible'      => 'Y',
);

$products[2] = array(
    'id'            => 2,
    'name'          => 'Orange Half Dozen',
    'description'   => 'Imported Fresh Orange Half a Dozen from Australia',
    'quantity'      => 6,
    'unit_price'    => \Wallet\Model::format_price(0.7),
    'unit_tax_rate' => 6,
    'tangible'      => 'Y'
);

// Item selection
$item_selections = array();
foreach ($products as $productData) {
    $item_selections[$productData['id']] = $productData['name'].' ($'.\Wallet\Model::parse_price($productData['unit_price']).' x '.$productData['quantity'].'pcs)';
}

$pay = new \Wallet\Pay();
$user_id = fusion_get_userdata('user_id');
$pay->set_title('Purchase of Fruits');
$pay->set_description('Nice Fruits Purchase');
$pay->set_payee($user_id);
$pay->set_currency('USD');
$pay->set_tax(6); // sets a general tax rate that if used for shipping, additional services during checkout
// Add the item
$pay->add_item(1, "Apple Half Dozen", "Imported Fresh Apple Half a Dozen from Australia", 6, "0.5", 6, "Y");
$pay->add_item(2, "Orange Half Dozen", "Imported Fresh Orange Half a Dozen from Australia", 6, "0.2", 6, "Y");

// This is the Security Isset.. I dont think it's necessary
$pay->set_submit_name('buy');
$pay->set_item_name('items_to_buy');

// Sets the item you wish to sell here
if ($pay->checkPostName()) {
    echo "<div class='spacer-lg'>\n";
    echo $pay->pay();
    echo "</div>\n";
} else {
    echo openform('anyname', 'post', FUSION_SELF);
    echo "<h4>Choose an Item to Buy:</h4>";
    echo "<div class='well'>\n";
    echo form_checkbox('items_to_buy[]', '', 1,
        array(
            'options' => $item_selections,
            'type'=>'radio'
        )
    );
    echo "</div>\n";
    $pay->display_payment_method_field();
    echo form_button('buy', 'Purchase the Following', 'buy');
    echo closeform();
}

// Consolidate the Code into Class Till it's all display


require_once THEMES.'templates/footer.php';