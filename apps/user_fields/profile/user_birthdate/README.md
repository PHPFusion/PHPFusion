# Birthdate (`user_birthdate`) module

Portable Profile Global module backed by `DB_USERS.user_birthdate`.

The module renders PHPFusion's `form_datepicker()` with a canonical submitted
value in `Y-m-d` format and stores that value in a native SQL `DATE` column.
The field is optional and private. An empty control is stored as PHPFusion's
existing `1900-01-01` sentinel so strict MySQL installations never receive an
invalid empty date.

The same optional field is enabled during member registration. Server-side
validation accepts only a real calendar date in exact `Y-m-d` form; browser
JavaScript is progressive enhancement only.

## Installer coverage

Fresh installation is already covered by the core installer:

- `Installer/Lib/CoreTables.php` creates `user_birthdate` as
  `DATE NOT NULL DEFAULT '1900-01-01'`.
- `Installer/Steps/AdminSetup.php` initializes the administrator birthdate to
  `1900-01-01`.
- `Installer/Lib/CoreSettings.php` includes the legacy user-field seed used by
  compatibility surfaces.

The module manifest also declares the same `DATE` schema. Profile Global will
therefore add the column safely on an older installation where it is missing.
