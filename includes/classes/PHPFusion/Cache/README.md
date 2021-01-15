# Cache for PHPFusion

__Usage__

```php
use PHPFusion\Cache\Cache;

$cache = new Cache();
print_p($cache->getStorageType()); // Return current storage type

$key = 'item';

// Cache::getInstance()->set($key, 'testvalue');

$cache->set($key, 'itemvalue');

print_p($cache->get($key));

$cache->delete($key); // Delete by key
$cache->flush(); // Purge whole cache
```

Add this to the config.php

```php
$cache_config = [
    'storage'        => 'memcache', // file|redis|memcache
    'memcache_hosts' => ['localhost:11211'], // e.g. ['localhost:11211', '192.168.1.100:11211', 'unix:///var/tmp/memcached.sock']
    'redis_hosts'    => ['localhost:6379'], // e.g. ['localhost:6379', '192.168.1.100:6379:1:passwd']
    'path'           => BASEDIR.'cache/' // for FileCache
];
```
