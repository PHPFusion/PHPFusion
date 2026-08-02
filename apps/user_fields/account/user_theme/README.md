# Site-theme module

Portable Account module for `DB_USERS.user_theme`.

## Manifest

```php
return [
    'id'              => 'account.user-theme',
    'category'        => 'account',
    'label'           => $locale['uth_101'],
    'description'     => $locale['uth_102'],
    'icon'            => 'palette',
    'order'           => 40,
    'default_enabled' => TRUE,
    'public'          => FALSE,
    'success_message' => $locale['uth_103'],
    'option_providers' => [
        'account.site-theme.options' => [ThemeOptions::class, 'options'],
    ],
    'field' => [
        'name'             => 'theme',
        'label'            => $locale['uth_101'],
        'type'             => 'select',
        'storage'          => 'user_column',
        'column'           => 'user_theme',
        'options_provider' => 'account.site-theme.options',
        'default'          => 'Default',
        'required'         => TRUE,
        'column_schema'    => [
            'type'     => 'varchar',
            'length'   => 100,
            'nullable' => FALSE,
            'default'  => 'Default',
        ],
    ],
];
```

## Top-level keys

| Key | Value | Purpose |
| --- | --- | --- |
| `id` | `account.user-theme` | Stable module/settings/API identifier. |
| `category` | `account` | Places the module in Account. |
| `label` | `$locale['uth_101']` | Visible title. |
| `description` | `$locale['uth_102']` | Visible overview/editor explanation. |
| `icon` | `palette` | Theme icon name. |
| `order` | `40` | Default Account order. |
| `default_enabled` | `TRUE` | Enables the module by default. |
| `public` | `FALSE` | Keeps the preference private. |
| `success_message` | `$locale['uth_103']` | API success response shown after saving. |
| `option_providers` | Provider map | Registers the module-owned theme discovery callback. |
| `field` | Field definition | Defines UI, validation, storage, and schema. |

## Provider and field keys

| Key | Value | Purpose |
| --- | --- | --- |
| `account.site-theme.options` | `[ThemeOptions::class, 'options']` | Discovers valid public/member themes. |
| `name` | `theme` | Submitted form/API key. |
| `label` | `$locale['uth_101']` | Visible select label. |
| `type` | `select` | Uses select rendering and option validation. |
| `storage` | `user_column` | Writes to `DB_USERS`. |
| `column` | `user_theme` | Destination column used by layout selection. |
| `options_provider` | `account.site-theme.options` | Resolves installed themes. |
| `default` | `Default` | Delegates to the global site theme. |
| `required` | `TRUE` | Rejects an empty value. |
| `column_schema` | Schema map | Creates the column when missing. |

## Column schema

| Key | Value | Purpose |
| --- | --- | --- |
| `type` | `varchar` | SQL string type. |
| `length` | `100` | Database length. |
| `nullable` | `FALSE` | Generates `NOT NULL`. |
| `default` | `Default` | Default global-theme delegation value. |

## Localization

| Key | English value | Used by |
| --- | --- | --- |
| `uth_100` | Site default | Default option from `ThemeOptions.php`. |
| `uth_101` | Site theme | Module title and field label. |
| `uth_102` | Choose the visual theme used on public and member pages. | Module description. |
| `uth_103` | Site theme updated. It will be used on the next page. | Successful update response. |

The stored value `Default` remains an internal key. Only its visible label is
translated.
