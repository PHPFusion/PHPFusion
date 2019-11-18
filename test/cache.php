<?php
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/header.php';

opentable('Cache');

use PHPFusion\Cache\Cache;

$cache = new Cache();
//print_p($cache->getStorageType()); // Return current storage type

$key = 'item';

$cache->set($key, 'itemvalue');

print_p($cache->get($key));

//$cache->delete($key); // Delete by key
//$cache->flush(); // Purge whole cache

closetable();

require_once THEMES.'templates/footer.php';
