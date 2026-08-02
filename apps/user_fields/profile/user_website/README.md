# Website (`user_website`) module

Portable Profile module stored in `DB_USERS.user_website`.

## Manifest

```php
return [
    'id'              => 'profile.website',
    'category'        => 'profile',
    'label'           => $locale['uweb_100'],
    'description'     => $locale['uweb_101'],
    'icon'            => 'globe',
    'order'           => 30,
    'default_enabled' => TRUE,
    'public'          => TRUE,
    'header'          => FALSE,
    'field' => [
        'name'        => 'user_website',
        'label'       => $locale['uweb_102'],
        'type'        => 'url',
        'storage'     => 'user_column',
        'column'      => 'user_website',
        'max_length'  => 255,
        'placeholder' => $locale['uweb_103'],
        'column_schema' => [
            'type'     => 'varchar',
            'length'   => 255,
            'nullable' => FALSE,
            'default'  => '',
            'after'    => 'user_web',
        ],
    ],
];
```

## Top-level keys

| Key | Value | Purpose |
| --- | --- | --- |
| `id` | `profile.website` | Stable module/settings/API identifier. |
| `category` | `profile` | Places Website in Public profile. |
| `label` | `$locale['uweb_100']` | Visible title. |
| `description` | `$locale['uweb_101']` | Visible explanation. |
| `icon` | `globe` | Theme icon name. |
| `order` | `30` | Places Website after About. |
| `default_enabled` | `TRUE` | Enables the module by default. |
| `public` | `TRUE` | Allows the saved URL on the public profile. |
| `header` | `FALSE` | Renders as a normal public section rather than header metadata. |
| `field` | Field definition | Defines URL UI, validation, storage, and schema. |

## Field keys

| Key | Value | Purpose |
| --- | --- | --- |
| `name` | `user_website` | Submitted form/API key. |
| `label` | `$locale['uweb_102']` | Visible input label. |
| `type` | `url` | Enables URL input rendering and `FILTER_VALIDATE_URL` validation. |
| `storage` | `user_column` | Writes to `DB_USERS`. |
| `column` | `user_website` | Destination column. |
| `max_length` | `255` | Server and input length limit. |
| `placeholder` | `$locale['uweb_103']` | Demonstrates the required complete URL format. |
| `column_schema` | Schema map | Creates `user_website` when missing. |

## Column schema

| Key | Value | Purpose |
| --- | --- | --- |
| `type` | `varchar` | SQL string type. |
| `length` | `255` | Maximum database length. |
| `nullable` | `FALSE` | Generates `NOT NULL`. |
| `default` | Empty string | Represents no website. |
| `after` | `user_web` | Places the new column after the legacy PHPFusion column when present. |

`user_website` is intentionally separate from legacy `user_web`.

## Localization

| Key | English value | Used by |
| --- | --- | --- |
| `uweb_100` | Website | Module title. |
| `uweb_101` | Add a professional website, portfolio or public page. | Module description. |
| `uweb_102` | Website URL | Input label. |
| `uweb_103` | https://example.com | Input placeholder. |
