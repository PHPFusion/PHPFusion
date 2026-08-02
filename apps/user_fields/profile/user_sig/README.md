# About (`user_sig`) module

Portable Profile module stored in `DB_USERS.user_sig`.

## Manifest

```php
return [
    'id'              => 'profile.bio',
    'category'        => 'profile',
    'label'           => $locale['usi_100'],
    'description'     => $locale['usi_101'],
    'icon'            => 'align-left',
    'order'           => 20,
    'default_enabled' => TRUE,
    'public'          => TRUE,
    'header'          => FALSE,
    'field' => [
        'name'        => 'user_sig',
        'label'       => $locale['usi_102'],
        'type'        => 'textarea',
        'storage'     => 'user_column',
        'column'      => 'user_sig',
        'max_length'  => 1000,
        'rows'        => 7,
        'placeholder' => $locale['usi_103'],
        'column_schema' => [
            'type'     => 'text',
            'nullable' => FALSE,
        ],
    ],
];
```

## Top-level keys

| Key | Value | Purpose |
| --- | --- | --- |
| `id` | `profile.bio` | Stable module/settings/API identifier. |
| `category` | `profile` | Places About in Public profile. |
| `label` | `$locale['usi_100']` | Visible title. |
| `description` | `$locale['usi_101']` | Visible explanation. |
| `icon` | `align-left` | Theme icon name. |
| `order` | `20` | Places About after Profile photo. |
| `default_enabled` | `TRUE` | Enables the module by default. |
| `public` | `TRUE` | Allows the saved content on the public profile. |
| `header` | `FALSE` | Renders as a normal public profile section, not identity-header data. |
| `field` | Field definition | Defines textarea UI, validation, storage, and schema. |

## Field keys

| Key | Value | Purpose |
| --- | --- | --- |
| `name` | `user_sig` | Submitted form/API key. |
| `label` | `$locale['usi_102']` | Visible textarea label. |
| `type` | `textarea` | Selects multiline rendering. |
| `storage` | `user_column` | Writes to `DB_USERS`. |
| `column` | `user_sig` | Destination column. |
| `max_length` | `1000` | Server and control length limit. |
| `rows` | `7` | Initial textarea height. |
| `placeholder` | `$locale['usi_103']` | Empty-control writing prompt. |
| `column_schema` | Schema map | Creates the destination column when missing. |

## Column schema

| Key | Value | Purpose |
| --- | --- | --- |
| `type` | `text` | SQL type suitable for longer content. |
| `nullable` | `FALSE` | Generates `NOT NULL`. |

No central writable-column allowlist or legacy include file is required.

## Localization

| Key | English value | Used by |
| --- | --- | --- |
| `usi_100` | About | Module title. |
| `usi_101` | Tell people about your background, work and interests. | Module description. |
| `usi_102` | About you | Textarea label. |
| `usi_103` | Share your experience, interests and what you are working on. | Textarea placeholder. |
