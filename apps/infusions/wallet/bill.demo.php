<?php
require_once __DIR__.'/../../maincore.php';
require_once THEMES.'templates/header.php';

function display_wallet_receipt() {
    $html = \PHPFusion\Template::getInstance('wallet-receipt');
    $html->set_template(__DIR__.'/templates/bills/receipt.html');
    $html->set_tag('logo', IMAGES.'icon.svg');
    $html->set_css(WALLET.'templates/css/bill.css');
    $merchant_email = fusion_get_settings('siteemail');
    $html->set_tag('name', 'Demo User');
    $html->set_tag('amount', '$3.32 USD');
    $html->set_tag('merchant_email', '<a href="mailto:'.$merchant_email.'">'.hide_email($merchant_email).'</a>');
    $html->set_tag('discord_link', '<a href="https://discord.gg/DhPANe9">Discord Server</a>');
    $html->set_tag('browser_link', '<a href="">View it in your browser.</a>');
    $html->set_tag('sitename', '<a href="https://www.php-fusion.co.uk">PHPFusion, Inc.</a>');
    $html->set_tag('address', '2nd Floor, Block 1, Unit 7 Metro Town, Jalan Bunga Ulam Raja, Off Tuaran Road, 88300, Kota Kinabalu, Sabah, Malaysia');
    $html->set_tag('source_program', 'PHPFusion Marketplace');
    $html->set_tag('bill_no', '1499-7244');
    $html->set_tag('transaction_ref', '#Reference code here');
    return (string)$html->get_output();
}

function display_wallet_invoice() {
    \ThemeFactory\Core::setParam('header', FALSE);
    \ThemeFactory\Core::setParam('footer', FALSE);
    \ThemeFactory\Core::setParam('copyright', FALSE);
    require_once __DIR__.'/wallet_include.php';
    $html = \PHPFusion\Template::getInstance('wallet-receipt');
    $html->set_template(__DIR__.'/templates/bills/invoice.html');
    $html->set_css(WALLET.'templates/css/bill.css');
    $html->set_tag('logo', IMAGES.'icon.svg');

    $merchant_email = fusion_get_settings('siteemail');
    $html->set_tag('name', 'Demo User');
    $html->set_tag('order_total', '$3.32 USD');
    $html->set_tag('order_datestamp', showdate('shortdate', TIME)   );
    $html->set_block('wallet_order', [
        'order_item' => 'Hosting Pack Bronze',
        'order_amount' => '$'.number_format(3.32, 2).' USD'
    ]);
    $html->set_tag('merchant_email', '<a href="mailto:'.$merchant_email.'">'.hide_email($merchant_email).'</a>');
    $html->set_tag('discord_link', '<a href="https://discord.gg/DhPANe9">Discord Server</a>');
    $html->set_tag('browser_link', '<a href="">View it in your browser.</a>');
    $html->set_tag('sitename', '<a href="https://www.php-fusion.co.uk">PHPFusion, Inc.</a>');
    $html->set_tag('address', '2nd Floor, Block 1, Unit 7 Metro Town, Jalan Bunga Ulam Raja, Off Tuaran Road, 88300, Kota Kinabalu, Sabah, Malaysia');
    $html->set_tag('source_program', 'PHPFusion Marketplace');
    $html->set_tag('bill_no', '1499-7244');
    $html->set_tag('transaction_ref', '#Reference code here');
    $mode = 1;
    if ($mode == 1) {
        $html->set_block('wallet_link', [
            'link' => ''
        ]);
    } else {

        $html->set_block('wallet', ['display' => display_wallet([
            'transaction_ref' => '',
            'return_url'      => '', // This is your delivery page when payment is success - e.g value : fusion_get_settings('siteurl').'infusions/roadmap/checkout.php';
            // 'display_amount_field' => FALSE,
            // 'display_amount'       => FALSE,
            // 'amount_label'         => 'Donation',
            'order_currency'  => 'USD',
            'delimiter'       => 2,
            'label'           => FALSE,
            'reverse_display' => FALSE,
            'no_credits'      => FALSE, // options for disabling credits driver from the payment form
            'credit_only'     => FALSE, // set to true if only coin payment options applicable.
            'items'           => [
                [
                    'id'          => 1,
                    'type'        => 'ADDON',
                    'title'       => "<a href=''>Some items</a>",
                    'description' => 'Single Site License Addon',
                    'price'       => '2.34',
                    'tax'         => 0,
                    'shipping'    => 0,
                    'quantity'    => 1,
                    'currency'    => 'USD',
                ],
            ]
        ])
        ]);
    }


    return (string)$html->get_output();
}

function display_wallet_pre_order_invoice() {
    \ThemeFactory\Core::setParam('header', FALSE);
    \ThemeFactory\Core::setParam('footer', FALSE);
    \ThemeFactory\Core::setParam('copyright', FALSE);
    require_once __DIR__.'/wallet_include.php';
    $html = \PHPFusion\Template::getInstance('wallet-receipt');
    $html->set_template(__DIR__.'/templates/bills/invoice-email.html');
    //$html->set_template(__DIR__.'/templates/bills/invoice.html');
    // $html->set_css(WALLET.'templates/css/bill.css');
    $html->set_tag('logo', IMAGES.'icon.svg');

    $merchant_email = fusion_get_settings('siteemail');
    $html->set_tag('name', 'Demo User');
    $html->set_tag('order_total', '$3.32 USD');
    $html->set_tag('order_datestamp', showdate('shortdate', TIME)   );
    $html->set_block('message', [
        'name' => 'Frederick Czac',
        'title' => 'Thank you for your recent order!',
    ]);
    $html->set_block('footer', [
        'text' => 'We will activate your order as soon as possible after accounting payment (if applies) for this order',
    ]);
    $html->set_block('wallet_order', [
        'order_item' => 'Hosting Pack Bronze',
        'order_amount' => '$'.number_format(3.32, 2).' USD'
    ]);
    $html->set_tag('merchant_email', '<a href="mailto:'.$merchant_email.'">'.hide_email($merchant_email).'</a>');
    $html->set_tag('discord_link', '<a href="https://discord.gg/DhPANe9">Discord Server</a>');
    $html->set_tag('browser_link', '<a href="">View it in your browser.</a>');
    $html->set_tag('sitename', '<a href="https://www.php-fusion.co.uk">PHPFusion, Inc.</a>');
    $html->set_tag('address', '2nd Floor, Block 1, Unit 7 Metro Town, Jalan Bunga Ulam Raja, Off Tuaran Road, 88300, Kota Kinabalu, Sabah, Malaysia');
    $html->set_tag('source_program', 'PHPFusion Marketplace');
    $html->set_tag('bill_no', '1499-7244');
    $html->set_tag('transaction_ref', '#Reference code here');
    $mode = 1;
    if ($mode == 1) {
        $html->set_block('wallet_link', [
            'link' => ''
        ]);
    } else {

        $html->set_block('wallet', ['display' => display_wallet([
            'transaction_ref' => '',
            'return_url'      => '', // This is your delivery page when payment is success - e.g value : fusion_get_settings('siteurl').'infusions/roadmap/checkout.php';
            // 'display_amount_field' => FALSE,
            // 'display_amount'       => FALSE,
            // 'amount_label'         => 'Donation',
            'order_currency'  => 'USD',
            'delimiter'       => 2,
            'label'           => FALSE,
            'reverse_display' => FALSE,
            'no_credits'      => FALSE, // options for disabling credits driver from the payment form
            'credit_only'     => FALSE, // set to true if only coin payment options applicable.
            'items'           => [
                [
                    'id'          => 1,
                    'type'        => 'ADDON',
                    'title'       => "<a href=''>Some items</a>",
                    'description' => 'Single Site License Addon',
                    'price'       => '2.34',
                    'tax'         => 0,
                    'shipping'    => 0,
                    'quantity'    => 1,
                    'currency'    => 'USD',
                ],
            ]
        ])
        ]);
    }


    return (string)$html->get_output();
}

echo display_wallet_pre_order_invoice();

require_once THEMES.'templates/footer.php';