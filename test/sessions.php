<?php
require_once __DIR__.'/maincore.php';
require_once THEMES.'templates/header.php';

session_clean();
print_p($_SESSION);

session_add('face-value', 'single deeper value');
session_add(['tree-value', 'deeper'], 'single deeper value');
session_add(['tree-value', 'deeper2'], 'single deeper value 2');
session_add(['tree-value', 'deeper3'], 'single deeper value 3');
print_P($_SESSION);

session_remove(['tree-value', 'deeper2']);

print_P($_SESSION);

$output = session_add(['tree-value', 'deeper2'], 'single deeper value 2');
print_P($_SESSION);
print_P($output);

$session_get = session_get('tree-value');
print_p($session_get);

require_once THEMES.'templates/footer.php';