<?php
/** Read-only examples of form_text addons, masks and layouts. */
defined('IN_FUSION') || exit;
require_once BASEDIR.'assets/libs/iconify/iconify_include.php';

$domain_dropdown = "<div class='dynamics-showcase__domain dropdown'>
    <a href='#demo_email_domains' class='btn dropdown-toggle dynamics-showcase__domain-toggle' role='button' data-showcase-domain-toggle aria-haspopup='true' aria-expanded='false' aria-controls='demo_email_domains' aria-label='Email domain'>@<span data-domain-label>gmail.com</span></a>
    <div class='dropdown-menu dropdown-menu-end' id='demo_email_domains' hidden>";
foreach (['gmail.com', 'hotmail.com', 'meangczac.com'] as $domain) {
    $domain_dropdown .= "<a class='dropdown-item' href='#demo_email_domains' data-showcase-domain='{$domain}'".($domain === 'gmail.com' ? " aria-current='true'" : '').">{$domain}</a>";
}
$domain_dropdown .= "</div><input type='hidden' name='demo_email_domain' value='gmail.com' data-domain-value></div>";

$text_modes = [
    ['Email · Text prefix and domain dropdown', 'Email', '', ['prepend_value' => 'Email', 'stacked' => $domain_dropdown, 'placeholder' => 'username', 'class' => 'mb-0 dynamics-showcase__email-domain']],
    ['Email · Icon prefix', 'Email', '', ['prepend_value' => iconify('at', 'tabler', '', 18), 'placeholder' => 'name@example.com']],
    ['Email · Domain suffix', 'Email', '', ['append_value' => '@meanczac.com', 'placeholder' => 'username']],
    ['Telephone · Icon and mask', 'Telephone', '', ['type' => 'tel', 'prepend_value' => iconify('phone', 'tabler', '', 18), 'mask' => '000-0000 0000', 'placeholder' => '012-3456 7890', 'max_length' => 13]],
    ['NRIC · ID mask', 'NRIC', '', ['mask' => '000000-00-0000', 'placeholder' => 'YYMMDD-PP-NNNN', 'max_length' => 14, 'regex' => '^[0-9]{6}-[0-9]{2}-[0-9]{4}$']],
    ['Passport · Two letters and numbers', 'Passport', '', ['mask' => 'SS00000000', 'placeholder' => 'AB12345678', 'max_length' => 10, 'regex' => '^[A-Za-z]{2}[0-9]{8}$']],
    ['Number · Minimum and maximum', 'Quantity', '1', ['type' => 'number', 'number_min' => '0', 'number_max' => 100]],
    ['Price · Currency prefix', 'Amount', '125.00', ['type' => 'price', 'prepend_value' => 'RM', 'placeholder' => '0.00']],
    ['Time · 24-hour mask', 'Start time', '09:30', ['type' => 'time', 'placeholder' => 'HH:MM']],
    ['IP address · Mask', 'IP address', '192.168.1.10', ['type' => 'ip', 'placeholder' => '192.168.1.10']],
    ['Password', 'Password', '', ['type' => 'password', 'autocomplete_off' => TRUE, 'placeholder' => 'Enter a sample password']],
    ['Read-only', 'Reference', 'DEMO-001', ['deactivate' => TRUE]],
    ['Inline label', 'Display name', '', ['inline' => TRUE, 'placeholder' => 'Enter a name']],
    ['Custom mask · Postcode', 'Postcode', '', ['mask' => '00000', 'max_length' => 5, 'placeholder' => '50000']],
    ['Small input', 'Short title', '', ['input_size' => 1, 'placeholder' => 'Small input']],
    ['Large input', 'Title', '', ['input_size' => 3, 'placeholder' => 'Large input']],
];
foreach ($text_modes as $index => [$variant, $label, $value, $options]) {
    $options += ['input_id' => 'demo_text_mode_'.$index, 'tip' => 'Help for '.$variant.'.', 'ext_tip' => 'Example only. Values are not saved.', 'class' => 'mb-0'];
    echo "<div class='dynamics-showcase__example'><h3 class='dynamics-showcase__variant'>{$variant}</h3>";
    echo form_text('demo_text_mode_'.$index, $label, $value, $options);
    echo '</div>';
}
foreach ([FALSE, TRUE] as $floating) {
    $suffix = $floating ? 'floating' : 'normal';
    echo "<div class='dynamics-showcase__example'><h3 class='dynamics-showcase__variant'>Credit card and CCV · ".($floating ? 'Floating' : 'Normal')."</h3><div class='dynamics-showcase__card-fields'>";
    echo form_text('demo_card_'.$suffix, 'Credit card', '', [
        'floating_label' => $floating, 'prepend_value' => iconify('credit-card', 'tabler', '', 18),
        'mask' => '0000 0000 0000 0000', 'max_length' => 19, 'placeholder' => '0000 0000 0000 0000',
        'autocomplete_off' => TRUE, 'class' => 'mb-0 dynamics-showcase__card-number',
        'tip' => 'Sixteen-digit card number mask.', 'ext_tip' => 'Example only. Values are not saved.',
    ]);
    echo form_text('demo_ccv_'.$suffix, 'CCV', '', [
        'floating_label' => $floating, 'mask' => '0009', 'max_length' => 4, 'placeholder' => '123',
        'autocomplete_off' => TRUE, 'class' => 'mb-0',
        'tip' => 'Three or four security-code digits.', 'ext_tip' => 'Three or four digits.',
    ]);
    echo '</div></div>';
}