# Security category

Portable Account-settings category for protection and privacy controls.

## Category manifest

```php
return [
    'key'         => 'security',
    'label'       => $locale['sec_100'],
    'description' => $locale['sec_101'],
    'icon'        => 'shield-check',
    'order'       => 25,
    'policy'      => 'trusted',
    'group_label' => $locale['sec_102'],
];
```

| Key | Value | Purpose |
| --- | --- | --- |
| `key` | `security` | Category identifier matched by module `category` values. |
| `label` | `$locale['sec_100']` | Navigation and page title. |
| `description` | `$locale['sec_101']` | Category overview text. |
| `icon` | `shield-check` | Navigation and empty-state icon. |
| `order` | `25` | Places Security after Account and before Notifications. |
| `policy` | `trusted` | Category-level policy metadata available to renderers/extensions. |
| `group_label` | `$locale['sec_102']` | Heading above the category's module list. |

## Modules

| Folder | Module ID | Responsibility |
| --- | --- | --- |
| `user_display` | `security.profile-visibility` | Controls whether the public profile can be opened. |
| `user_hide_email` | `security.email-privacy` | Controls whether email can be displayed publicly. |

Each child README contains the complete module manifest and key reference.

## Localization

The category owns `locale/{LOCALESET}.php` separately from its child modules.

| Key | English value | Used by |
| --- | --- | --- |
| `sec_100` | Security | Navigation and page title. |
| `sec_101` | Control profile access and personal information privacy. | Category description. |
| `sec_102` | Protection and privacy | Module-list heading. |
