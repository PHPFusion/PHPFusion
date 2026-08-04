# Real name (`user_realname`) module

This Profile Global module renders two required text controls on one responsive
row:

- `first_name`
- `last_name`

On save, the module trims and normalizes both values, joins them with one space,
and stores the result in `DB_USERS.user_realname`. The two form fields are UI-only;
no separate first-name or last-name columns are created.

The module creates `user_realname VARCHAR(100) NOT NULL DEFAULT ''` after
`user_name` when the column does not already exist. Both inputs are required for
every save or update, and their combined stored value may not exceed 100
characters.

For an existing stored value, the final space-delimited word is placed in the
last-name control and the preceding text is placed in the first-name control.

## Package contents

- `module.php` declares discovery, storage, ordering, and layout metadata.
- `RealNameModule.php` splits the stored name, validates both controls, and
  performs the single `DB_USERS` update.
- `endpoint.php` delegates requests to the canonical Profile Global API.
- `locale/English.php` contains all user-facing module text.
