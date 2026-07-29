<?php

namespace PHPFusion\Api;

use RuntimeException;
use Throwable;

final class ApiKernel
{
    private static array $legacyCallbacks = [];
    private static array $legacyLoaded = [];

    private ApiRegistry $registry;

    public function __construct(?ApiRegistry $registry = NULL)
    {
        $this->registry = $registry ?? ApiRegistry::instance();
    }

    public function handle(ApiRequest $request, string $endpoint = '', string $route = ''): ApiResponse
    {
        $definition = $route !== ''
            ? $this->registry->resolveRoute($route, $request->method())
            : $this->registry->resolve($endpoint, $request->scope());

        if ($definition === NULL) {
            return ApiResponse::error('API endpoint was not found.', 404);
        }

        if (!in_array($request->method(), $definition['methods'], TRUE)) {
            return ApiResponse::json([
                'success' => FALSE,
                'message' => 'HTTP method is not allowed for this endpoint.',
                'allowed' => $definition['methods'],
            ], 405, ['Allow' => implode(', ', $definition['methods'])]);
        }

        if (!in_array($request->channel(), $definition['channels'], TRUE)) {
            return ApiResponse::error('This endpoint is not available through the requested channel.', 403);
        }

        try {
            foreach ($definition['bootstrap'] as $bootstrap) {
                require_once $bootstrap;
            }

            return $this->withRequestGlobals($request, function () use ($definition, $request): ApiResponse {
                if (!empty($definition['handler'])) {
                    $response = call_user_func($definition['handler'], $request, $definition);
                    if (!$response instanceof ApiResponse) {
                        throw new RuntimeException("API handler {$definition['id']} did not return ApiResponse");
                    }

                    return $response;
                }

                return $this->runLegacy($definition, $request->channel() === 'direct');
            });
        } catch (Throwable $exception) {
            if (function_exists('set_error')) {
                set_error(E_USER_WARNING, $exception->getMessage(), $exception->getFile(), $exception->getLine());
            }

            return ApiResponse::error('The API endpoint could not complete the request.', 500);
        }
    }

    private function runLegacy(array $definition, bool $restoreTransport): ApiResponse
    {
        $previousStatus = http_response_code();
        $previousHeaders = headers_list();
        $startLevel = ob_get_level();
        ob_start();

        try {
            $endpointId = (string)$definition['id'];

            if (!empty(self::$legacyLoaded[$endpointId])) {
                if (empty(self::$legacyCallbacks[$endpointId])) {
                    throw new RuntimeException(
                        "Legacy endpoint {$endpointId} cannot be invoked twice in the same PHP request"
                    );
                }

                foreach (self::$legacyCallbacks[$endpointId] as $callback) {
                    call_user_func_array($callback['function'], $callback['default_args']);
                }
            } else {
                require $definition['path'];
                self::$legacyLoaded[$endpointId] = TRUE;

                if (!empty($definition['hook'])) {
                    $hookName = (string)$definition['hook'];
                    $registeredHooks = \PHPFusion\Hooks::get_instance($hookName)->get_hook($hookName);
                    self::$legacyCallbacks[$endpointId] = array_map(
                        static fn(array $hook): array => [
                            'function'     => $hook['function'],
                            'default_args' => (array)$hook['default_args'],
                        ],
                        $registeredHooks
                    );

                    fusion_apply_hook($hookName);
                }
            }
        } catch (ApiHalt $halt) {
            if ($halt->getOutput() !== '') {
                echo $halt->getOutput();
            }
        } finally {
            $body = '';
            while (ob_get_level() > $startLevel) {
                $body = (string)ob_get_clean() . $body;
            }
        }

        $status = http_response_code() ?: 200;
        $headers = $this->newHeaders($previousHeaders, headers_list());

        if ($restoreTransport) {
            $this->restoreHeaders($previousHeaders, headers_list());
            http_response_code($previousStatus ?: 200);
        }

        return ApiResponse::raw($body, $status, $headers);
    }

    private function withRequestGlobals(ApiRequest $request, callable $callback): ApiResponse
    {
        $previousGet = $_GET;
        $previousPost = $_POST;
        $previousRequest = $_REQUEST;
        $previousMethod = $_SERVER['REQUEST_METHOD'] ?? NULL;
        $previousServerHeaders = [];

        $_GET = $request->query();
        $_POST = $request->input();
        $_REQUEST = array_replace($_GET, $_POST);
        $_SERVER['REQUEST_METHOD'] = $request->method();

        foreach ($request->headers() as $name => $value) {
            $serverKey = strtoupper(str_replace('-', '_', (string)$name));
            if (!in_array($serverKey, ['CONTENT_TYPE', 'CONTENT_LENGTH'], TRUE)) {
                $serverKey = 'HTTP_' . $serverKey;
            }

            $previousServerHeaders[$serverKey] = $_SERVER[$serverKey] ?? NULL;
            $_SERVER[$serverKey] = (string)$value;
        }

        try {
            return $callback();
        } finally {
            $_GET = $previousGet;
            $_POST = $previousPost;
            $_REQUEST = $previousRequest;

            if ($previousMethod === NULL) {
                unset($_SERVER['REQUEST_METHOD']);
            } else {
                $_SERVER['REQUEST_METHOD'] = $previousMethod;
            }

            foreach ($previousServerHeaders as $serverKey => $value) {
                if ($value === NULL) {
                    unset($_SERVER[$serverKey]);
                } else {
                    $_SERVER[$serverKey] = $value;
                }
            }
        }
    }

    private function newHeaders(array $before, array $after): array
    {
        $headers = [];
        foreach (array_diff($after, $before) as $header) {
            if (!str_contains($header, ':')) {
                continue;
            }
            [$name, $value] = array_map('trim', explode(':', $header, 2));
            $headers[$name] = $value;
        }

        return $headers;
    }

    private function restoreHeaders(array $before, array $after): void
    {
        $names = [];
        foreach ($after as $header) {
            $names[] = trim(strtok($header, ':'));
        }

        foreach (array_unique(array_filter($names)) as $name) {
            header_remove($name);
        }

        foreach ($before as $header) {
            header($header, FALSE);
        }
    }
}
