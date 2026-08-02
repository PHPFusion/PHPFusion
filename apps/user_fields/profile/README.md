# Public-profile modules

The complete Profile category is a portable community package:

```text
profile/
|-- category.php
|-- locale/
|-- user_avatar/
|-- user_sig/
`-- user_website/
```

`category.php` owns the category navigation metadata. Each module owns its
manifest, persistence declaration, validation rules, and any specialized
implementation class.

The Profile category now has three focused modules:

- `user_avatar` controls the profile photo.
- `user_sig` supplies the public About content.
- `user_website` supplies the public website URL.

Country and state belong only to the Account category. Profile no longer owns
a second location value. The profile-photo template reads the generic
`avatar` header slot rather than a module ID.

Install the complete category by copying `profile` into:

```text
apps/user_fields/profile/
```

No category registry, writable-column allowlist, template module ID, or core
provider registration needs to be modified.

## Localization

`category.php` loads `profile/locale/{LOCALESET}.php` for the Public profile
title, description, and Profile details group heading. Each child module loads
its own `locale/{LOCALESET}.php`, including the specialized avatar class's
upload and validation messages. Copy the locale folder with the module; no
core translation file needs to be changed.

| Category key | English value |
| --- | --- |
| `pro_100` | Public profile |
| `pro_101` | Control the information people see on your profile. |
| `pro_102` | Profile details |
