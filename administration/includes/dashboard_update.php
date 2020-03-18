<?php
require_once __DIR__.'/../../maincore.php';
require_once INCLUDES.'ajax_include.php';

if (fusion_safe()) {
    if ($column_setup = post(['dashboard_setup'])) {
        session_add('dashboard_setup', $column_setup);
        echo 'OK';
    }
}
