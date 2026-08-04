<?php

namespace PHPFusion\Core\Profile;

use PHPFusion\Core\Profile\Blocks\BasicInformationBlock;
use PHPFusion\Core\Profile\Blocks\LinksBlock;

final class PublicProfileBlockRegistry
{
    private ?array $blocks = NULL;

    public function all(): array
    {
        if ($this->blocks !== NULL) {
            return $this->blocks;
        }

        $blocks = [
            BasicInformationBlock::definition(),
            LinksBlock::definition(),
        ];

        if (function_exists('fusion_filter_hook')) {
            foreach (fusion_filter_hook('fusion_register_public_profile_blocks') as $provider) {
                foreach ((array)$provider as $block) {
                    if (is_array($block)) {
                        $blocks[] = $block;
                    }
                }
            }
        }

        $valid = [];
        foreach ($blocks as $block) {
            $namespace = trim((string)($block['namespace'] ?? ''));
            if (
                $namespace === ''
                || !preg_match('/^(?:[A-Z][A-Za-z0-9_]*\\\\)+[A-Z][A-Za-z0-9_]*$/', $namespace)
                || isset($valid[$namespace])
                || !is_callable($block['editor'] ?? NULL)
                || !is_callable($block['public'] ?? NULL)
            ) {
                continue;
            }

            $fields = [];
            foreach ((array)($block['fields'] ?? []) as $field) {
                $column = trim((string)($field['column'] ?? ''));
                if ($column === '' || !preg_match('/^[a-z][a-z0-9_]{1,63}$/i', $column)) {
                    continue;
                }
                $field['name'] = (string)($field['name'] ?? $column);
                $field['column'] = $column;
                $fields[] = $field;
            }

            $block['fields'] = $fields;
            $block['order'] = (int)($block['order'] ?? 100);
            $valid[$namespace] = $block;
        }

        uasort($valid, static fn(array $a, array $b): int => [$a['order'], $a['namespace']] <=> [$b['order'], $b['namespace']]);
        $this->blocks = $valid;

        return $this->blocks;
    }

    public function find(string $namespace): ?array
    {
        return $this->all()[$namespace] ?? NULL;
    }

    public function allowedColumns(): array
    {
        $columns = [];
        foreach ($this->all() as $block) {
            foreach ($block['fields'] as $field) {
                $column = (string)$field['column'];
                if (column_exists(DB_USERS, $column, FALSE)) {
                    $columns[$column] = TRUE;
                }
            }
        }

        return array_keys($columns);
    }

    public function data(array $block, array $user): array
    {
        $data = [];
        foreach ((array)$block['fields'] as $field) {
            $column = (string)$field['column'];
            if (column_exists(DB_USERS, $column, FALSE)) {
                $data[(string)$field['name']] = $user[$column] ?? ($field['default'] ?? '');
            }
        }

        if (is_callable($block['userdata'] ?? NULL)) {
            $derived = ($block['userdata'])($user, $data);
            if (is_array($derived)) {
                $data = array_replace($data, $derived);
            }
        }

        return $data;
    }
}
