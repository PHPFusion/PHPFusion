# Account user-field modules

Each child folder in this directory is a portable community module. Its
manifest and its supporting files are the authoritative definitions for:

- Account settings in Profile Global.
- Registration visibility and ordering.
- Server-side validation.
- `DB_USERS` column creation.
- API and registration persistence.

Registration does not discover `DB_USER_FIELDS` records and does not load
`user_xxx_include.php` or `user_xxx_include_var.php`.

The Account package currently contains:

- `user_location` and `user_state` for regional location.
- `user_timezone` for date and time display.
- `user_theme` for the public/member interface.
- `user_admin_theme` for the administration interface.

Protection modules live in the sibling `security` category:

- `security/user_display` for public-profile visibility.
- `security/user_hide_email` for email privacy.

## Standard one-value field

Each module owns one field and everything specific to that field:

```php
<?php

defined('IN_FUSION') || exit;

$localePath = USER_FIELDS . 'account/user_location/locale/';
$localeFile = $localePath . trim(LOCALESET, '/\\') . '.php';
$locale = fusion_get_locale('', [
    is_file($localeFile) ? $localeFile : $localePath . 'English.php',
    $localePath . 'English.php',
]);

require_once __DIR__ . '/CountryOptions.php';

use PHPFusion\Apps\UserFields\Account\UserLocation\CountryOptions;

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

        'column_schema' => [
            'type'     => 'varchar',
            'length'   => 50,
            'nullable' => FALSE,
            'default'  => '',
        ],
    ],
];
```

### Manifest responsibilities

| Key | Purpose |
| --- | --- |
| `id` | Stable module and API identifier |
| `category` | Profile Global navigation category |
| `registration.enabled` | Includes the field on registration |
| `registration.order` | Registration field order |
| `field.name` | Submitted form key |
| `field.storage` | `user_column` writes to `DB_USERS` |
| `field.column` | Destination column |
| `field.column_schema` | Creates the column when it does not exist |
| `field.options_provider` | Supplies select values and labels |
| `field.required` | Shared API and registration validation |
| `field.max_length` | Shared API and registration length rule |
| `option_providers` | Module-owned option callbacks |
| `api_endpoints` | Module-owned endpoint declarations |
| `requires` | IDs of modules that must be installed and enabled |

The schema engine accepts structured column definitions instead of arbitrary
SQL from a legacy include. Supported types are `varchar`, `char`, `tinyint`,
`smallint`, `int`, `bigint`, and `text`.

## Database expansion

`ProfileRepository` discovers every module manifest. For a field with
`storage = user_column` and `column_schema`:

1. Validate the column identifier and schema.
2. Check whether the column exists in `DB_USERS`.
3. Build the SQL attributes from the structured schema.
4. Call `SqlHandler::add_column()` only when the column is missing.

`user_location` already exists on normal PHPFusion installations, so its
declaration is verified but no duplicate column is added.

`user_state` is new. Its module declares:

```php
'column_schema' => [
    'type'     => 'varchar',
    'length'   => 100,
    'nullable' => FALSE,
    'default'  => '',
    'after'    => 'user_location',
],
```

The first Profile Global or registration request after deployment creates the
missing `user_state` column.

## Portable ownership

The Profile Global engine has no country or state knowledge. It only discovers
manifests and interprets generic contracts for storage, validation, option
providers, dependencies, registration, and API endpoints.

Every category and module owns its interface text in
`locale/{LOCALESET}.php`. A manifest loads that file before returning its
definition. Packaged option providers call `fusion_get_locale()` to read the
same merged strings. Translation therefore remains part of the folder being
shared; there is no central locale registry to update. Because PHPFusion's
`LOCALESET` includes a trailing slash, manifests trim it when resolving the
flat file name and loads `English.php` second. PHPFusion keeps the selected
language's values and fills any missing keys from English.

The Account category owns these navigation strings:

| Key | English value |
| --- | --- |
| `acc_100` | Account |
| `acc_101` | Manage regional preferences and interface appearance. |
| `acc_102` | Preferences |

### Countries

`user_location` owns the namespaced `account.location.countries` provider and
its data:

```text
user_location/
├── module.php
├── CountryOptions.php
└── data/
    └── countries.php
```

It returns country code to visible label:

```php
[
    ''   => 'Select a country',
    'MY' => 'Malaysia',
    'SG' => 'Singapore',
]
```

Only the key, such as `MY`, is stored.

### States

`user_state` owns the namespaced `account.state.options` provider, API handler,
and state data:

```text
user_state/
├── module.php
├── StateOptions.php
├── StateOptionsEndpoint.php
└── data/
    └── states.php
```

State data is grouped by the stored country code. The current source contains
state names rather than subdivision codes, so the stored value and label are
the same:

```php
[
    'Selangor' => 'Selangor',
    'Sarawak'  => 'Sarawak',
]
```

The state field declares its dependency:

```php
'requires'         => ['account.location'],
'options_provider' => 'account.state.options',
'depends_on'       => 'user_location',
'options_endpoint' => 'account-state-options',
```

On registration, changing Country requests:

```text
GET api/?api=account-state-options&id=MY
```

The State control is disabled while loading, replaced with the returned
options, and enabled when the request succeeds. Server-side validation
independently verifies that the submitted state belongs to the submitted
country.

## Account-settings save flow

1. `ProfileModuleRegistry` discovers the manifest.
2. `FieldModule::schema()` resolves its options.
3. The Profile Global form posts to the module endpoint.
4. `ProfileApiEndpoint::module()` verifies authentication, availability,
   method, and Fusion token.
5. `ProfileFieldValidator` sanitizes and validates the value.
6. `ProfileRepository::updateUserColumn()` permits columns declared by valid
   manifests and verifies that the physical column exists.
7. A bound `UPDATE DB_USERS` query persists the value.

There is no central extension allowlist to edit.

## Registration flow

1. `UserFields` renders PHPFusion's core identity controls: username, email,
   password, captcha, and terms.
2. `ProfileRegistrationFields` discovers enabled modules whose
   `registration.enabled` value is true.
3. It renders their fields from the same schema used by Profile Global.
4. `UserFieldsInput::saveInsert()` passes the submitted values to
   `ProfileRegistrationFields::collect()`.
5. `ProfileFieldValidator` applies the same option, required, length, email,
   and URL rules used by the Account settings API.
6. Valid `user_column` values are merged into the new-user array.
7. Immediate registration inserts the array into `DB_USERS`.
8. Email-verified registration serializes the same array into
   `DB_NEW_USERS.user_info`; activation later inserts it into `DB_USERS`.

`DB_USER_FIELDS`, Quantum field discovery, and legacy user-field include files
do not participate in this flow.

## Sharing and installation

To distribute a module, archive its complete folder. Installing it is a folder
copy into the matching category:

```text
apps/user_fields/account/user_location/
apps/user_fields/account/user_state/
```

No Profile Global core file, provider registry, API manifest, registration
class, or repository allowlist needs to be edited.

`user_state` declares `account.location` as a dependency. When that dependency
is missing or disabled, the engine keeps `user_state` disabled. A community
release can therefore publish `user_state` with `user_location` as a required
package, or bundle both folders together.

## Infusion modules

An infusion can register a module through
`fusion_register_profile_modules`. Its definition may use the same
`registration` and `column_schema` contract.

An infusion may still register a provider through:

```php
fusion_add_hook(
    'fusion_register_profile_option_providers',
    'my_infusion_profile_options'
);
```

Provider output must always use:

```php
[
    'stored-key' => 'Visible label',
]
```

Folder modules should normally declare `option_providers` and `api_endpoints`
inside their own manifest so that copying the folder is sufficient. The same
provider is used for rendering and server-side validation.

## Checklist

- The module ID is stable and namespaced.
- The field name and user column are valid identifiers.
- A new user column has a structured `column_schema`.
- Registration visibility is declared in the manifest.
- Select providers return stored keys mapped to visible labels.
- Provider keys and endpoint IDs are namespaced to the module.
- Every module-specific class and data file is inside the module folder.
- Dependencies are declared with `requires`.
- Dependent options are revalidated on the server.
- Required and maximum-length rules match the database.
- Every user-facing string has a module-owned locale key.
- Account API update, immediate registration, and verified registration are
  tested independently.
