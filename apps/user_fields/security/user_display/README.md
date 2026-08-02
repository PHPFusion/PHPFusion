# Profile-visibility module

Portable privacy module for `DB_USERS.user_display`.

## Manifest

```php
return [
    'id'              => 'security.profile-visibility',
    'category'        => 'security',
    'label'           => $locale['usd_100'],
    'description'     => $locale['usd_101'],
    'icon'            => 'eye',
    'order'           => 10,
    'default_enabled' => TRUE,
    'public'          => FALSE,
    'policies' => [
        'public_profile_access' => [
            'field'          => 'user_display',
            'allowed_values' => ['1'],
        ],
    ],
    'field' => [
        'name'        => 'profile_visible',
        'label'       => $locale['usd_102'],
        'type'        => 'switch',
        'storage'     => 'user_column',
        'column'      => 'user_display',
        'description' => $locale['usd_103'],
        'column_schema' => [
            'type'     => 'tinyint',
            'length'   => 1,
            'unsigned' => TRUE,
            'nullable' => FALSE,
            'default'  => 1,
        ],
    ],
];
```

## Top-level and policy keys

| Key | Value | Purpose |
| --- | --- | --- |
| `id` | `security.profile-visibility` | Stable module/settings/API identifier. |
| `category` | `security` | Places the module on the Security settings page. |
| `label` | `$locale['usd_100']` | Visible title. |
| `description` | `$locale['usd_101']` | Visible explanation. |
| `icon` | `eye` | Theme icon name. |
| `order` | `10` | Places Profile visibility first in Security. |
| `default_enabled` | `TRUE` | Enables the module by default. |
| `public` | `FALSE` | Keeps the privacy control itself off the public profile. |
| `policies` | Policy map | Gives the generic policy engine module-owned access rules. |
| `public_profile_access` | Access policy | Controls whether non-owner, non-admin visitors may open the profile. |
| `field` | `user_display` | User value evaluated by the policy. |
| `allowed_values` | `['1']` | Values that permit public access. |

## Field and schema keys

| Key | Value | Purpose |
| --- | --- | --- |
| `name` | `profile_visible` | Submitted form/API key. |
| `label` | `$locale['usd_102']` | Visible switch label. |
| `type` | `switch` | Boolean switch rendering and normalization. |
| `storage` | `user_column` | Writes to `DB_USERS`. |
| `column` | `user_display` | Destination and policy source column. |
| `description` | `$locale['usd_103']` | Field-level help text. |
| `column_schema.type` | `tinyint` | Boolean-compatible SQL type. |
| `column_schema.length` | `1` | Display width. |
| `column_schema.unsigned` | `TRUE` | Prevents negative values. |
| `column_schema.nullable` | `FALSE` | Generates `NOT NULL`. |
| `column_schema.default` | `1` | New profiles are visible by default. |

The engine knows only the generic policy contract; this manifest owns the
column name and allowed value.

## Localization

| Key | English value | Used by |
| --- | --- | --- |
| `usd_100` | Profile visibility | Module title. |
| `usd_101` | Allow other members to discover and view your public profile. | Module description. |
| `usd_102` | Show my public profile | Switch label. |
| `usd_103` | Administrators may still access hidden profiles. | Field help text. |
