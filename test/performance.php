<?php
require_once dirname(__FILE__).'/../maincore.php';
require_once THEMES.'templates/header.php';

echo showBenchmark(FALSE);

// At Body, we log about -- Render time: 0.09856 seconds | Average: 0.19604 (-0.17887) seconds | Queries: 11
require_once THEMES.'templates/footer.php';
// But at Footer, we log about -- Render time: 0.33552 seconds | Average: 0.23553 (0.23696) seconds | Queries: 36