<?php

namespace PHPFusion\Administration\Api;

use PHPFusion\Cache\Cache;
use Throwable;

final class AdminApiCache
{
    public static function remember(string $key, int $seconds, callable $loader): mixed
    {
        $cacheKey = 'admin-api:'.preg_replace('/[^a-z0-9:._-]+/i', '-', $key);
        try {
            $cache = Cache::getInstance();
            if ($cache->isConnected()) {
                $cached = $cache->get($cacheKey);
                if ($cached !== NULL) {
                    return $cached;
                }
                $value = call_user_func($loader);
                $cache->set($cacheKey, $value, max(1, $seconds));

                return $value;
            }
        } catch (Throwable) {
            // A cache outage must not make an administration endpoint fail.
        }

        return call_user_func($loader);
    }

    public static function forget(string $key): void
    {
        try {
            Cache::getInstance()->delete('admin-api:'.preg_replace('/[^a-z0-9:._-]+/i', '-', $key));
        } catch (Throwable) {
        }
    }
}
