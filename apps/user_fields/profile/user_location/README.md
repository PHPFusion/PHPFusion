# Country module

Portable Account and registration module for `DB_USERS.user_location`.

## Manifest

```php
return [
    'id'              => 'account.location',
    'category'        => 'account',
    'label'           => $locale['ulo_100'],
    'description'     => $locale['ulo_101'],
    'icon'            => 'map-pin',
    'order'           => 12,
    'default_enabled' => TRUE,
    'public'          => FALSE,
    'option_providers' => [
        'account.location.countries' => [CountryOptions::class, 'options'],
    ],
    'registration' => [
        'enabled' => TRUE,
        'order'   => 10,
    ],
    'field' => [
        'name'             => 'user_location',
        'label'            => $locale['ulo_100'],
        'type'             => 'select',
        'storage'          => 'user_column',
        'column'           => 'user_location',
        'options_provider' => 'account.location.countries',
        'required'         => FALSE,
        'max_length'       => 2,
        'column_schema'    => [
            'type'     => 'varchar',
            'length'   => 50,
            'nullable' => FALSE,
            'default'  => '',
        ],
    ],
];
```

## Top-level keys

| Key | Value | Purpose |
| --- | --- | --- |
| `id` | `account.location` | Stable module/settings/API identifier. |
| `category` | `account` | Places the module in Account. |
| `label` | `$locale['ulo_100']` | Visible title. |
| `description` | `$locale['ulo_101']` | Visible explanation. |
| `icon` | `map-pin` | Theme icon name. |
| `order` | `12` | Places Country after Timezone by default. |
| `default_enabled` | `TRUE` | Enables the module by default. |
| `public` | `FALSE` | Keeps the account country off the public profile. |
| `option_providers` | Provider map | Registers packaged country options. |
| `registration` | Registration map | Adds the same field to registration. |
| `field` | Field definition | Defines UI, validation, storage, and schema. |

## Provider and registration keys

| Key | Value | Purpose |
| --- | --- | --- |
| `account.location.countries` | `[CountryOptions::class, 'options']` | Loads `data/countries.php` and returns country code-to-label options. |
| `registration.enabled` | `TRUE` | Renders and validates Country during registration. |
| `registration.order` | `10` | Registration ordering. |

## Field keys

| Key | Value | Purpose |
| --- | --- | --- |
| `name` | `user_location` | Submitted form/API/registration key. |
| `label` | `$locale['ulo_100']` | Visible select label. |
| `type` | `select` | Select rendering and provider-based validation. |
| `storage` | `user_column` | Writes to `DB_USERS`. |
| `column` | `user_location` | Destination column. |
| `options_provider` | `account.location.countries` | Uses the packaged country list. |
| `required` | `FALSE` | Allows an empty country. |
| `max_length` | `2` | Accepts ISO-style two-character stored codes. |
| `column_schema` | Schema map | Creates the column when missing. |

## Column schema

| Key | Value | Purpose |
| --- | --- | --- |
| `type` | `varchar` | SQL string type. |
| `length` | `50` | Physical column length retained for PHPFusion compatibility. |
| `nullable` | `FALSE` | Generates `NOT NULL`. |
| `default` | Empty string | Default when no country is selected. |

Only the option key, such as `MY`, is stored.

## Localization

| Key | English value | Used by |
| --- | --- | --- |
| `ulo_100` | Country | Module title and field label. |
| `ulo_101` | Choose the country associated with your account. | Module description. |
| `ulo_102` | Select a country | Empty option from `CountryOptions.php`. |

The manifest loads `locale/{LOCALESET}.php`. The packaged option provider uses
the same merged locale, so the empty choice travels with the module.
