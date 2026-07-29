<?php

namespace PHPFusion\Api;

final class ApiRequest
{
    private string $method;
    private array $query;
    private array $input;
    private array $headers;
    private string $channel;
    private string $scope;

    public function __construct(
        string $method,
        array $query = [],
        array $input = [],
        array $headers = [],
        string $channel = 'http',
        string $scope = ''
    ) {
        $this->method = strtoupper($method ?: 'GET');
        $this->query = $query;
        $this->input = $input;
        $this->headers = $headers;
        $this->channel = $channel;
        $this->scope = $scope;
    }

    public static function fromGlobals(string $scope = ''): self
    {
        $headers = function_exists('getallheaders') ? (array)getallheaders() : [];
        $input = $_POST;
        $contentType = strtolower((string)($headers['Content-Type'] ?? $headers['content-type'] ?? ''));

        if (str_contains($contentType, 'application/json')) {
            $decoded = json_decode((string)file_get_contents('php://input'), TRUE);
            if (is_array($decoded)) {
                $input = $decoded;
            }
        }

        return new self(
            (string)($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            $_GET,
            $input,
            $headers,
            'http',
            $scope
        );
    }

    public static function direct(array $payload = [], array $context = []): self
    {
        $method = (string)($context['method'] ?? 'POST');
        $query = (array)($context['query'] ?? []);
        $headers = (array)($context['headers'] ?? []);
        $scope = (string)($context['scope'] ?? '');

        return new self($method, $query, $payload, $headers, 'direct', $scope);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function query(string $key = '', mixed $default = NULL): mixed
    {
        if ($key === '') {
            return $this->query;
        }

        return $this->query[$key] ?? $default;
    }

    public function input(string $key = '', mixed $default = NULL): mixed
    {
        if ($key === '') {
            return $this->input;
        }

        return $this->input[$key] ?? $default;
    }

    public function all(): array
    {
        return array_replace($this->query, $this->input);
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function channel(): string
    {
        return $this->channel;
    }

    public function scope(): string
    {
        return $this->scope;
    }
}
