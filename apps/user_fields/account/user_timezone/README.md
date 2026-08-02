# Timezone module

Portable Account module for `DB_USERS.user_timezone`.

## Manifest

```php
return [
    'id'              => 'account.timezone',
    'category'        => 'account',
    'label'           => $locale['utz_100'],
    'description'     => $locale['utz_101'],
    'icon'            => 'clock-3',
    'order'           => 10,
    'default_enabled' => TRUE,
    'public'          => FALSE,
    'option_providers' => [
        'account.timezone.options' => [TimezoneOptions::class, 'options'],
    ],
    'field' => [
        'name'             => 'timezone',
        'label'            => $locale['utz_100'],
        'type'             => 'select',
        'storage'          => 'user_column',
        'column'           => 'user_timezone',
        'options_provider' => 'account.timezone.options',
        'required'         => TRUE,
        'column_schema'    => [
            'type'     => 'varchar',
            'length'   => 50,
            'nullable' => FALSE,
            'default'  => 'Europe/London',
        ],
    ],
];
```

## Top-level keys

| Key | Value | Purpose |
| --- | --- | --- |
| `id` | `account.timezone` | Stable discovery, configuration, and API identifier. |
| `category` | `account` | Places the setting in Account. |
| `label` | `$locale['utz_100']` | Visible module title. |
| `description` | `$locale['utz_101']` | Explains the setting to the member. |
| `icon` | `clock-3` | Theme icon name. |
| `order` | `10` | Default Account ordering. |
| `default_enabled` | `TRUE` | Enables the module by default. |
| `public` | `FALSE` | Keeps the preference off the public profile. |
| `option_providers` | Provider map | Registers the folder-owned timezone callback. |
| `field` | Field definition | Defines the select, validation, storage, and schema. |

## Provider and field keys

| Key | Value | Purpose |
| --- | --- | --- |
| `account.timezone.options` | `[TimezoneOptions::class, 'options']` | Uses PHPFusion's timezone list through the module-owned adapter. |
| `name` | `timezone` | Submitted form/API key. |
| `label` | `$locale['utz_100']` | Visible select label. |
| `type` | `select` | Enables select rendering and allowed-option validation. |
| `storage` | `user_column` | Writes to `DB_USERS`. |
| `column` | `user_timezone` | Destination column. |
| `options_provider` | `account.timezone.options` | Connects the field to its provider. |
| `required` | `TRUE` | Rejects an empty timezone. |
| `column_schema` | Schema map | Creates `user_timezone` when missing. |

## Column schema

| Key | Value | Purpose |
| --- | --- | --- |
| `type` | `varchar` | SQL string type. |
| `length` | `50` | Database length. |
| `nullable` | `FALSE` | Generates `NOT NULL`. |
| `default` | `Europe/London` | Default for newly created rows/columns. |

Rendering and server validation use the same provider output.

## Localization

| Key | English value | Used by |
| --- | --- | --- |
| `utz_100` | Timezone | Module title and field label. |
| `utz_101` | Choose how dates and times are displayed throughout the site. | Module description. |

Timezone option labels continue to come from PHPFusion's localized
`get_timezones()` list.
