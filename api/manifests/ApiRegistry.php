<?php

namespace PHPFusion\Api;

use InvalidArgumentException;
use RuntimeException;

final class ApiRegistry
{
    private static ?self $instance = NULL;

    private array $endpoints = [];
    private array $aliases = [];
    private array $routes = [];

    public static function instance(): self
    {
        if (self::$instance === NULL) {
            self::$instance = new self();
            self::$instance->build();
        }

        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = NULL;
    }

    public function resolve(string $name, string $scope = ''): ?array
    {
        if (isset($this->endpoints[$name])) {
            return $this->endpoints[$name];
        }

        if ($scope !== '' && isset($this->aliases[$scope . ':' . $name])) {
            return $this->endpoints[$this->aliases[$scope . ':' . $name]];
        }

        if (isset($this->aliases['*:' . $name])) {
            return $this->endpoints[$this->aliases['*:' . $name]];
        }

        return NULL;
    }

    public function resolveRoute(string $route, string $method): ?array
    {
        $route = '/' . ltrim(parse_url($route, PHP_URL_PATH) ?: '', '/');
        $method = strtoupper($method);
        $key = $method . ' ' . rtrim($route, '/');
        $wildcard = '* ' . rtrim($route, '/');
        $id = $this->routes[$key] ?? $this->routes[$wildcard] ?? NULL;

        if ($id === NULL) {
            $routeSuffix = ' ' . rtrim($route, '/');
            foreach ($this->routes as $registeredRoute => $registeredId) {
                if (str_ends_with($registeredRoute, $routeSuffix)) {
                    $id = $registeredId;
                    break;
                }
            }
        }

        return $id !== NULL ? $this->endpoints[$id] : NULL;
    }

    public function all(): array
    {
        return $this->endpoints;
    }

    private function build(): void
    {
        $providers = [];
        $coreManifest = __DIR__ . '/core.php';

        if (is_file($coreManifest)) {
            $providers[] = require $coreManifest;
        }

        if (function_exists('fusion_filter_hook')) {
            $providers = array_merge($providers, fusion_filter_hook('fusion_register_api_endpoints'));

            // Keep third-party infusions using the previous registration hook working.
            foreach (fusion_filter_hook('fusion_register_hook_paths') as $legacyProvider) {
                $providers[] = $this->convertLegacyProvider((array)$legacyProvider);
            }
        }

        foreach ($providers as $provider) {
            if (!is_array($provider)) {
                continue;
            }

            foreach ($provider as $id => $definition) {
                if (!is_array($definition)) {
                    continue;
                }

                $this->register((string)$id, $definition);
            }
        }
    }

    private function register(string $id, array $definition): void
    {
        if ($id === '' || isset($this->endpoints[$id])) {
            throw new RuntimeException("Duplicate or empty API endpoint id: {$id}");
        }

        $definition['id'] = $id;
        $definition['scope'] = (string)($definition['scope'] ?? '');
        $definition['aliases'] = array_values(array_unique((array)($definition['aliases'] ?? [])));
        $definition['methods'] = array_values(array_unique(array_map(
            'strtoupper',
            (array)($definition['methods'] ?? ['GET', 'POST'])
        )));
        $definition['channels'] = array_values(array_unique((array)($definition['channels'] ?? ['http', 'direct'])));
        $definition['bootstrap'] = array_values(array_unique((array)($definition['bootstrap'] ?? [])));

        if (empty($definition['handler']) && empty($definition['path'])) {
            throw new InvalidArgumentException("API endpoint {$id} has no handler or path");
        }

        if (!empty($definition['path'])) {
            try {
                $definition['path'] = $this->validatePath((string)$definition['path'], $id);
            } catch (RuntimeException $exception) {
                if (!empty($definition['optional'])) {
                    return;
                }

                throw $exception;
            }
        }

        $this->endpoints[$id] = $definition;

        foreach ($definition['aliases'] as $alias) {
            $alias = trim((string)$alias);
            if ($alias === '') {
                continue;
            }

            $scopeKey = ($definition['scope'] ?: '*') . ':' . $alias;
            if (isset($this->aliases[$scopeKey]) && $this->aliases[$scopeKey] !== $id) {
                throw new RuntimeException("Duplicate API alias {$scopeKey}");
            }
            $this->aliases[$scopeKey] = $id;

            $globalKey = '*:' . $alias;
            if (!isset($this->aliases[$globalKey])) {
                $this->aliases[$globalKey] = $id;
            } elseif ($this->aliases[$globalKey] !== $id) {
                unset($this->aliases[$globalKey]);
            }
        }

        if (!empty($definition['route'])) {
            $route = '/' . trim((string)$definition['route'], '/');
            foreach ($definition['methods'] as $method) {
                $routeKey = $method . ' ' . rtrim($route, '/');
                if (isset($this->routes[$routeKey])) {
                    throw new RuntimeException("Duplicate API route {$routeKey}");
                }
                $this->routes[$routeKey] = $id;
            }
        }
    }

    private function validatePath(string $path, string $id): string
    {
        $resolved = realpath($path);
        if ($resolved === FALSE) {
            $relativePath = preg_replace('#^(?:\.\.[/\\\\])+#', '', $path);
            $resolved = realpath(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $relativePath);
        }

        if ($resolved === FALSE || !is_file($resolved)) {
            throw new RuntimeException("API endpoint file for {$id} does not exist: {$path}");
        }

        $allowedRoots = [
            realpath(dirname(__DIR__)),
            defined('INFUSIONS') ? realpath(INFUSIONS) : FALSE,
            defined('ADMIN') ? realpath(ADMIN) : FALSE,
        ];

        foreach (array_filter($allowedRoots) as $root) {
            if (str_starts_with(strtolower($resolved), strtolower($root . DIRECTORY_SEPARATOR))) {
                return $resolved;
            }
        }

        throw new RuntimeException("API endpoint file for {$id} is outside an allowed API root");
    }

    private function convertLegacyProvider(array $provider): array
    {
        $converted = [];
        foreach ($provider as $alias => $path) {
            if (!is_string($path)) {
                continue;
            }
            $id = 'legacy.' . preg_replace('/[^a-z0-9._-]+/i', '-', (string)$alias);
            $converted[$id] = [
                'aliases' => [(string)$alias],
                'path'    => $path,
                'hook'    => 'fusion_filters',
                'route'   => '/v1/legacy/' . rawurlencode((string)$alias),
                'optional' => TRUE,
            ];
        }

        return $converted;
    }
}
