<?php

require_once __DIR__.'/../../../../maincore.php';

print_p($_POST);

print_p(defender::safe() ? "Safe" : "Null Declared");