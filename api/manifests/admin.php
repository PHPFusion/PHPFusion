<?php

defined('IN_FUSION') || exit;

require_once dirname(__DIR__) . '/endpoints/AdminDashboardEndpoint.php';

/**
 * Canonical registry for core administration page endpoints.
 *
 * Each page owns a small endpoint manifest and may expose as many routes as it
 * needs. Add future page manifests here instead of adding dispatchers to page
 * entry files.
 */
$manifests = [
    dirname(__DIR__, 2).'/administration/settings/main/endpoints.php',
    dirname(__DIR__, 2).'/administration/dashboard/endpoints.php',
];

$endpoints = [];
foreach ($manifests as $manifest) {
    if (is_file($manifest)) {
        $pageEndpoints = (array)require $manifest;
        $duplicates = array_intersect_key($endpoints, $pageEndpoints);
        if ($duplicates !== []) {
            throw new RuntimeException('Duplicate administration API endpoint: '.array_key_first($duplicates));
        }
        $endpoints += $pageEndpoints;
    }
}

return $endpoints;
