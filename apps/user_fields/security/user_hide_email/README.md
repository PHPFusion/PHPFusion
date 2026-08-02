# Email-privacy module

Portable privacy module for `DB_USERS.user_hide_email`.

## Manifest

```php
return [
    'id'              => 'security.email-privacy',
    'category'        => 'security',
    'label'           => $locale['uhe_100'],
    'description'     => $locale['uhe_101'],
    'icon'            => 'mail',
    'order'           => 20,
    'default_enabled' => TRUE,
    'public'          => FALSE,
    'policies' => [
        'public_field_visibility' => [
            'target'               => 'email',
            'field'                => 'user_hide_email',
            'allowed_values'       => ['0'],
            'administrator_bypass' => TRUE,
        ],
    ],
    'field' => [
        'name'        => 'hide_email',
        'label'       => $locale['uhe_102'],
        'type'        => 'switch',
        'storage'     => 'user_column',
        'column'      => 'user_hide_email',
        'description' => $locale['uhe_103'],
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
| `id` | `security.email-privacy` | Stable module/settings/API identifier. |
| `category` | `security` | Places the module on the Security settings page. |
| `label` | `$locale['uhe_100']` | Visible title. |
| `description` | `$locale['uhe_101']` | Visible explanation. |
| `icon` | `mail` | Theme icon name. |
| `order` | `20` | Places Email privacy after Profile visibility. |
| `default_enabled` | `TRUE` | Enables the module by default. |
| `public` | `FALSE` | Keeps the control itself off the public profile. |
| `policies` | Policy map | Declares visibility behavior without core column knowledge. |
| `public_field_visibility` | Field policy | Controls exposure of one named public output. |
| `target` | `email` | Public output protected by the policy. |
| `field` | `user_hide_email` | User value evaluated by the policy. |
| `allowed_values` | `['0']` | Stored values that permit public email display. |
| `administrator_bypass` | `TRUE` | Always permits authorized administrators. |

## Field and schema keys

| Key | Value | Purpose |
| --- | --- | --- |
| `name` | `hide_email` | Submitted form/API key. |
| `label` | `$locale['uhe_102']` | Visible switch label. |
| `type` | `switch` | Boolean switch rendering and normalization. |
| `storage` | `user_column` | Writes to `DB_USERS`. |
| `column` | `user_hide_email` | Destination and policy source column. |
| `description` | `$locale['uhe_103']` | Field-level help text. |
| `column_schema.type` | `tinyint` | Boolean-compatible SQL type. |
| `column_schema.length` | `1` | Display width. |
| `column_schema.unsigned` | `TRUE` | Prevents negative values. |
| `column_schema.nullable` | `FALSE` | Generates `NOT NULL`. |
| `column_schema.default` | `1` | Hides email by default. |

## Localization

| Key | English value | Used by |
| --- | --- | --- |
| `uhe_100` | Email privacy | Module title. |
| `uhe_101` | Choose whether your email address appears to other members. | Module description. |
| `uhe_102` | Hide my email address | Switch label. |
| `uhe_103` | Your email remains available to authorized administrators. | Field help text. |
