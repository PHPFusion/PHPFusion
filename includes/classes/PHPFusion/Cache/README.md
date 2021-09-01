# Cache for PHPFusion

__Usage__

```php
$cache = new PHPFusion\Cache\Cache();

if ($cache->isConnected()) {
    $key = 'item';

    if (!empty($cache->get($key))) {
        print_p($cache->get($key));
    } else {
        $data = 'itemvalue';
        $cache->set($key, $data);
        
        print_p($data);
    }

    $cache->delete($key); // Delete by key
    $cache->flush(); // Purge whole cache
}
```
