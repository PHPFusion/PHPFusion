# Administration-theme module

Portable administrator-only Account module for
`DB_USERS.user_admin_theme`.

## Manifest

```php
return [
    'id'              => 'account.user-admin-theme',
    'category'        => 'account',
    'label'           => $locale['ato_101'],
    'description'     => $locale['ato_102'],
    'icon'            => 'panel-top',
    'order'           => 50,
    'default_enabled' => TRUE,
    'public'          => FALSE,
    'audience'        => 'administrator',
    'success_message' => $locale['ato_103'],
    'option_providers' => [
        'account.admin-theme.options' => [AdminThemeOptions::class, 'options'],
    ],
    'field' => [
        'name'             => 'admin_theme',
        'label'            => $locale['ato_101'],
        'type'             => 'select',
        'storage'          => 'user_column',
        'column'           => 'user_admin_theme',
        'options_provider' => 'account.admin-theme.options',
        'default'          => 'Default',
        'required'         => TRUE,
        'column_schema'    => [
            'type'     => 'varchar',
            'length'   => 100,
            'nullable' => FALSE,
            'default'  => 'Default',
            'after'    => 'user_theme',
        ],
    ],
];
```

## Top-level keys

| Key | Value | Purpose |
| --- | --- | --- |
| `id` | `account.user-admin-theme` | Stable module identifier used by discovery, settings, and the dedicated API endpoint. |
| `category` | `account` | Places the module on the Account settings page. |
| `label` | `$locale['ato_101']` | Visible module title loaded from this package's locale file. |
| `description` | `$locale['ato_102']` | Visible explanation on the settings overview and editor. |
| `icon` | `panel-top` | Icon name rendered by the active theme icon system. |
| `order` | `50` | Default position within Account. Lower values appear first. |
| `default_enabled` | `TRUE` | Enables the module until an administrator stores an override. |
| `public` | `FALSE` | Prevents this setting from appearing on the public profile. |
| `audience` | `administrator` | Makes the module available only to administrators. |
| `success_message` | `$locale['ato_103']` | API message returned after a successful save. |
| `option_providers` | Provider map | Registers option callbacks owned by this folder. |
| `field` | Field definition | Declares rendering, validation, persistence, and schema behavior. |

## Option provider

| Key | Value | Purpose |
| --- | --- | --- |
| `account.admin-theme.options` | `[AdminThemeOptions::class, 'options']` | Calls `AdminThemeOptions::options()` to discover installed admin themes. The same values are used for rendering and validation; `ato_100` localizes the “Site default” option. |

## Field keys

| Key | Value | Purpose |
| --- | --- | --- |
| `name` | `admin_theme` | Submitted form and API payload key. |
| `label` | `$locale['ato_101']` | Visible select label. |
| `type` | `select` | Selects the shared select renderer and option validation. |
| `storage` | `user_column` | Persists directly to `DB_USERS`. |
| `column` | `user_admin_theme` | Destination database column. |
| `options_provider` | `account.admin-theme.options` | Resolves the module-owned provider above. |
| `default` | `Default` | Delegates to the global admin theme when no personal theme is selected. |
| `required` | `TRUE` | Rejects an empty submitted value. |
| `column_schema` | Schema map | Creates the destination column when missing. |

## Column schema

| Key | Value | Purpose |
| --- | --- | --- |
| `type` | `varchar` | SQL column type. |
| `length` | `100` | Maximum database length. |
| `nullable` | `FALSE` | Generates `NOT NULL`. |
| `default` | `Default` | Database default value. |
| `after` | `user_theme` | Places a newly created column after `user_theme` when that column exists. |

## Localization

`module.php` loads `locale/{LOCALESET}.php` before registering the provider.
`AdminThemeOptions.php` then reads the merged locale with
`fusion_get_locale()`.

| Key | English value | Used by |
| --- | --- | --- |
| `ato_100` | Site default | Default option label in the theme select. |
| `ato_101` | Administration theme | Module title and field label. |
| `ato_102` | Choose the administration interface theme for your account. | Module description. |
| `ato_103` | Administration theme updated. It will be used on your next admin page. | Successful update response. |

`AdminThemeOptions.php` and `locale/English.php` belong to this package.
Copying the complete folder is enough; no core locale/provider registration or
writable-column allowlist is needed.
