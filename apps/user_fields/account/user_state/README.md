# State module

Portable dependent Account and registration module for
`DB_USERS.user_state`.

## Manifest

```php
return [
    'id'              => 'account.state',
    'category'        => 'account',
    'label'           => $locale['ust_100'],
    'description'     => $locale['ust_101'],
    'icon'            => 'map',
    'order'           => 14,
    'default_enabled' => TRUE,
    'public'          => FALSE,
    'requires'        => ['account.location'],
    'option_providers' => [
        'account.state.options' => [StateOptions::class, 'options'],
    ],
    'api_endpoints' => [
        'account.state.options' => [
            'handler'  => [StateOptionsEndpoint::class, 'handle'],
            'route'    => '/v1/profile-modules/account/state/options',
            'methods'  => ['GET'],
            'aliases'  => ['account-state-options'],
            'channels' => ['http', 'direct'],
        ],
    ],
    'registration' => [
        'enabled' => TRUE,
        'order'   => 20,
    ],
    'field' => [
        'name'                    => 'user_state',
        'label'                   => $locale['ust_102'],
        'type'                    => 'select',
        'storage'                 => 'user_column',
        'column'                  => 'user_state',
        'options_provider'        => 'account.state.options',
        'depends_on'              => 'user_location',
        'options_endpoint'        => 'account-state-options',
        'empty_options_label'     => $locale['ust_103'],
        'options_placeholder'     => $locale['ust_104'],
        'loading_options_label'   => $locale['ust_105'],
        'options_error_label'     => $locale['ust_106'],
        'loading_options_status'  => $locale['ust_107'],
        'empty_options_status'    => $locale['ust_108'],
        'options_error_status'    => $locale['ust_109'],
        'required'                => FALSE,
        'max_length'              => 100,
        'column_schema' => [
            'type'     => 'varchar',
            'length'   => 100,
            'nullable' => FALSE,
            'default'  => '',
            'after'    => 'user_location',
        ],
    ],
];
```

## Top-level keys

| Key | Value | Purpose |
| --- | --- | --- |
| `id` | `account.state` | Stable module/settings/API identifier. |
| `category` | `account` | Places State in Account. |
| `label` | `$locale['ust_100']` | Visible title. |
| `description` | `$locale['ust_101']` | Visible explanation. |
| `icon` | `map` | Theme icon name. |
| `order` | `14` | Places State after Country. |
| `default_enabled` | `TRUE` | Enables the module by default. |
| `public` | `FALSE` | Keeps State off the public profile. |
| `requires` | `account.location` | Disables State when the Country module is absent or disabled. |
| `option_providers` | Provider map | Registers module-owned server options. |
| `api_endpoints` | Endpoint map | Registers the dependent-select endpoint from this folder. |
| `registration` | Registration map | Adds State to registration. |
| `field` | Field definition | Defines dependency UI, validation, storage, and schema. |

## Provider, endpoint, and registration keys

| Key | Value | Purpose |
| --- | --- | --- |
| `account.state.options` provider | `[StateOptions::class, 'options']` | Loads `data/states.php` and validates the state against the selected country. |
| `handler` | `[StateOptionsEndpoint::class, 'handle']` | API callback returning `{id, text}` state rows. |
| `route` | `/v1/profile-modules/account/state/options` | HTTP route. |
| `methods` | `GET` | Restricts the endpoint to reads. |
| `aliases` | `account-state-options` | Query-style API alias used by registration. |
| `channels` | `http`, `direct` | Allows browser and server-side invocation. |
| `registration.enabled` | `TRUE` | Shows State during registration. |
| `registration.order` | `20` | Places State after Country during registration. |

## Field keys

| Key | Value | Purpose |
| --- | --- | --- |
| `name` | `user_state` | Submitted form/API/registration key. |
| `label` | `$locale['ust_102']` | Visible select label. |
| `type` | `select` | Select rendering and provider validation. |
| `storage` | `user_column` | Writes to `DB_USERS`. |
| `column` | `user_state` | Destination column. |
| `options_provider` | `account.state.options` | Resolves server-side options. |
| `depends_on` | `user_location` | Parent field whose country code controls available states. |
| `options_endpoint` | `account-state-options` | Client endpoint for refreshing options. |
| `empty_options_label` | `$locale['ust_103']` | Select text before a parent value exists. |
| `options_placeholder` | `$locale['ust_104']` | Empty option after loading succeeds. |
| `loading_options_label` | `$locale['ust_105']` | Select text while loading. |
| `options_error_label` | `$locale['ust_106']` | Select text after an endpoint failure. |
| `loading_options_status` | `$locale['ust_107']` | Accessible live-region loading text. |
| `empty_options_status` | `$locale['ust_108']` | Accessible live-region empty text. |
| `options_error_status` | `$locale['ust_109']` | Accessible live-region error text. |
| `required` | `FALSE` | Allows an empty state. |
| `max_length` | `100` | Server-side value length limit. |
| `column_schema` | Schema map | Creates `user_state` when missing. |

## Column schema

| Key | Value | Purpose |
| --- | --- | --- |
| `type` | `varchar` | SQL string type. |
| `length` | `100` | Database length. |
| `nullable` | `FALSE` | Generates `NOT NULL`. |
| `default` | Empty string | Default when no state is selected. |
| `after` | `user_location` | Places the column after Country when possible. |

## Localization

| Key | English value | Used by |
| --- | --- | --- |
| `ust_100` | State | Module title. |
| `ust_101` | Choose the state or region associated with your account. | Module description. |
| `ust_102` | State or region | Field label. |
| `ust_103` | Select a country first | Empty provider/select label. |
| `ust_104` | Select a state | Loaded-options placeholder. |
| `ust_105` | Loading states... | Loading select label. |
| `ust_106` | States could not be loaded | Failed select label. |
| `ust_107` | Loading state options. | Accessible loading status. |
| `ust_108` | Select a country first. | Accessible parent-required status. |
| `ust_109` | State options could not be loaded. Choose a country to try again. | Accessible failure status. |

State names in `data/states.php` are content data, not interface strings. A
translated package may replace that dataset or return localized labels while
keeping stable stored keys.
