<?php
require_once __DIR__.'/../../../maincore.php';
require_once THEMES.'templates/admin_header.php';
if ($path = get('inspired')) {
    if (file_exists(INSPIRE.'tests/'.$path)) {
        include INSPIRE.'tests/'.$path;
    } else {
        test_unavailable();
    }
} else {
    test_unavailable();
}

function test_unavailable() {
    echo fusion_render(INSPIRE_TEMPLATES, '404.twig', []);
}

require_once THEMES.'templates/footer.php';
