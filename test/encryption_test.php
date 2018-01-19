<?php
require_once dirname(__FILE__).'/../maincore.php';
require_once THEMES.'templates/header.php';

// lets encrypt an array
$raw_password = 'Secret Password';
// Set to true to test with the Unprintable Password
$use_more_secured_password = false;

if ($use_more_secured_password) {
    $raw_password = \defender::get_encrypt_key($raw_password);
    echo "<h4>Your password</h4>";
    print_p($raw_password);
    var_dump($raw_password);
}

$enc = \defender::encrypt_string('This is secret information', $raw_password);
echo "<h4>This is your encrypted value</h4>";
print_p($enc);

$enc = \defender::decrypt_string($enc, $raw_password);
echo "<h4>This is your original unencrypted value</h4>";
print_p($enc);

require_once THEMES.'templates/footer.php';