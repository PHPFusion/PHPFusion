<?php

namespace PHPFusion\Api;

use JsonSerializable;

final class ApiResponse implements JsonSerializable
{
    private int $status;
    private string $body;
    private array $headers;
    private mixed $data;

    public function __construct(int $status, string $body = '', array $headers = [], mixed $data = NULL)
    {
        $this->status = $status;
        $this->body = $body;
        $this->headers = $headers;
        $this->data = $data;
    }

    public static function json(array $data, int $status = 200, array $headers = []): self
    {
        $headers = ['Content-Type' => 'application/json; charset=utf-8'] + $headers;

        return new self(
            $status,
            (string)json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $headers,
            $data
        );
    }

    public static function success(
        array $data = [],
        string $message = '',
        string $redirect = '',
        int $status = 200
    ): self {
        return self::json([
            'success'  => TRUE,
            'message'  => $message,
            'redirect' => $redirect,
            'data'     => $data,
        ], $status);
    }

    public static function error(
        string $message,
        int $status = 422,
        string $field = '',
        array $data = []
    ): self {
        return self::json([
            'success' => FALSE,
            'message' => $message,
            'field'   => $field,
            'data'    => $data,
        ], $status);
    }

    public static function raw(string $body, int $status = 200, array $headers = []): self
    {
        return new self($status, $body, $headers);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function data(): mixed
    {
        return $this->data;
    }

    public function send(): never
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
        header('X-Content-Type-Options: nosniff');
        echo $this->body;
        exit;
    }

    public function jsonSerialize(): mixed
    {
        if ($this->data !== NULL) {
            return $this->data;
        }

        $decoded = json_decode($this->body, TRUE);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $this->body;
    }
}
