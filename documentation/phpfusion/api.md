# Central API

All core and infusion endpoints are registered and dispatched through the
central API.

## HTTP access

Existing query-string URLs remain supported:

```text
POST /api/?api=member-login-password
GET  /api/?api=salary-details
```

Versioned routes are intended for external clients:

```text
POST /api/v1/auth/member/password
GET  /api/v1/school/salary-details
GET  /api/v1/school/admin/calendar
```

Both URL forms resolve through `ApiRegistry` and execute through `ApiKernel`.

## Direct website access

Server-side PHP can avoid an HTTP round trip:

```php
require_once BASEDIR . 'api/manifests/api.php';

$response = fusion_api_invoke(
    'school.frontend.salary-details',
    ['staff_id' => 12, 'year' => 2026, 'month' => 7],
    ['method' => 'GET']
);
```

The return value is `PHPFusion\Api\ApiResponse`. Use `status()`, `body()`,
`headers()`, or `data()` as appropriate. Direct execution temporarily supplies
the endpoint request globals and restores them after the handler returns.

## Infusion registration

Infusions register endpoint metadata without creating their own dispatcher:

```php
// infusions/example/api/endpoints.php
function example_register_api_endpoints(): array
{
    return [
        'example.items.list' => [
            'path' => dirname(__DIR__) . '/classes/api/items.get.php',
            'route' => '/v1/example/items',
            'aliases' => ['example-items'],
        ],
    ];
}

fusion_add_hook('fusion_register_api_endpoints', 'example_register_api_endpoints');

// infusions/example/infusion_db.php
require_once INFUSIONS . 'example/api/endpoints.php';
```

Registry definitions support:

- stable internal ID;
- compatibility aliases;
- versioned HTTP route;
- accepted methods;
- HTTP and direct channels;
- callable handlers or lazy-loaded legacy files;
- handler-specific runtime dependency files.

Duplicate IDs, aliases, and route/method combinations are rejected.

## Endpoint implementations

Core implementations live under `api/endpoints/`. Infusion implementations
must remain owned by their infusion. Related operations should remain grouped
by domain. New handlers should accept `ApiRequest` and return `ApiResponse`;
they should not call `exit` or `die`.

The school route registry is `infusions/school/api/endpoints.php`, and its
handlers are grouped in `infusions/school/api/endpoints/`. The registry is
loaded by `infusions/school/infusion_db.php` and publishes endpoint metadata
through `fusion_register_api_endpoints`. There are no school-level HTTP
dispatchers.
