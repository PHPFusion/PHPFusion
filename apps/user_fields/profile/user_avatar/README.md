# Profile-photo module

Portable specialized Profile module for `DB_USERS.user_avatar`.

## Manifest

```php
return [
    'id'              => 'profile.avatar',
    'category'        => 'profile',
    'label'           => $locale['uav_100'],
    'description'     => $locale['uav_101'],
    'icon'            => 'camera',
    'order'           => 10,
    'default_enabled' => TRUE,
    'essential'       => TRUE,
    'public'          => TRUE,
    'header'          => TRUE,
    'header_slot'     => 'avatar',
    'class'           => AvatarModule::class,
    'field' => [
        'name'          => 'avatar_file',
        'storage'       => 'user_column',
        'column'        => 'user_avatar',
        'column_schema' => [
            'type'     => 'varchar',
            'length'   => 100,
            'nullable' => FALSE,
            'default'  => '',
        ],
    ],
];
```

## Top-level keys

| Key | Value | Purpose |
| --- | --- | --- |
| `id` | `profile.avatar` | Stable module/settings/API identifier. |
| `category` | `profile` | Places the module in Public profile. |
| `label` | `$locale['uav_100']` | Visible title. |
| `description` | `$locale['uav_101']` | Visible explanation. |
| `icon` | `camera` | Theme icon name. |
| `order` | `10` | Places the photo first in Profile. |
| `default_enabled` | `TRUE` | Enables the module by default. |
| `essential` | `TRUE` | Prevents administrators from disabling this identity module. |
| `public` | `TRUE` | Allows its values to participate in public-profile rendering. |
| `header` | `TRUE` | Marks its values as profile-header data. |
| `header_slot` | `avatar` | Semantic slot consumed by templates without knowing the module ID. |
| `class` | `AvatarModule::class` | Replaces the generic one-field module with specialized upload behavior. |
| `field` | Storage declaration | Authorizes and creates the avatar database column. |

## Field and schema keys

| Key | Value | Purpose |
| --- | --- | --- |
| `name` | `avatar_file` | Multipart upload field name. |
| `storage` | `user_column` | Declares direct `DB_USERS` persistence. |
| `column` | `user_avatar` | Filename destination column. |
| `column_schema.type` | `varchar` | SQL string type. |
| `column_schema.length` | `100` | Maximum stored filename length. |
| `column_schema.nullable` | `FALSE` | Generates `NOT NULL`. |
| `column_schema.default` | Empty string | Represents no custom avatar. |

## Specialized class

| Responsibility | Behavior |
| --- | --- |
| Schema | Supplies the avatar upload UI, accepted MIME types, current image URL, and help text. |
| Validation | Requires JPG, PNG, or WebP; limits size to 5 MB and dimensions to 4096 × 4096. |
| Persistence | Writes the generated filename to `user_avatar`. |
| Replacement | Removes the previous custom file after a successful replacement. |
| Deletion | Clears `user_avatar` and removes the previous unprotected custom file. |
| API result | Returns `avatar_name` and `avatar_url` for immediate UI refresh. |

The folder owns all photo-specific behavior. Core only executes the generic
module interface and header-slot contract.

## Localization

The specialized class reads the same module locale for its generated schema,
validation errors, delete response, and upload response.

| Key | English value | Used by |
| --- | --- | --- |
| `uav_100` | Profile photo | Module title and upload-field label. |
| `uav_101` | Choose a photo that helps people recognize you. | Module description. |
| `uav_102` | JPG, PNG or WebP. Maximum 5 MB. | Upload help text. |
| `uav_103` | You do not have permission to edit this photo. | Permission error. |
| `uav_104` | Profile photo removed. | Successful delete response. |
| `uav_105` | Choose a profile photo to upload. | Missing-upload response. |
| `uav_106` | Choose a JPG, PNG or WebP image. | Accepted-file validation message. |
| `uav_107` | The selected photo is larger than 5 MB. | File-size error. |
| `uav_108` | The selected file is not a valid image. | Invalid-image error. |
| `uav_109` | The image dimensions exceed 4096 × 4096 pixels. | Dimension error. |
| `uav_110` | The image could not be saved. | Storage error. |
| `uav_111` | The upload did not complete. | Incomplete-upload error. |
| `uav_112` | The photo did not pass validation. | Unknown validation fallback. |
| `uav_113` | Profile photo updated. | Successful update response. |

Keeping these messages inside the folder makes the specialized module fully
portable.
