<?php

namespace PHPFusion\Api;

use RuntimeException;

/**
 * Stops a legacy endpoint without terminating the PHP request.
 */
final class ApiHalt extends RuntimeException
{
    private string $output;

    public function __construct(string $output = '')
    {
        parent::__construct('API endpoint halted');
        $this->output = $output;
    }

    public function getOutput(): string
    {
        return $this->output;
    }
}
