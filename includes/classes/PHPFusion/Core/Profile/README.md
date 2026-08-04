# Core profile MVCT

The core profile feature has two independent engines in this folder:

- `ProfileSettingsEngine` renders `profile_settings.php`. Every `apps/user_fields/{category}` folder becomes a tab, and every enabled `{box}/module.php` inside that category becomes an independent card. A module may expose a legacy `field` or a `fields` array. Administrators enable, disable, and order module cards through Profile fields configuration.
- `PublicProfileEngine` renders `profile_edit.php` (the public-profile editor) and `profile_view.php` (the public view). Public cards are code-owned whole blocks. They are not enabled, disabled, reordered, or assembled in the CMS.

The folder follows a small MVCT split:

- Model: `ProfileModel`, `ProfileAvatar`, and `PublicProfileBlockRegistry` own data and extension rules.
- View model/controller: the two engines prepare page state and enforce visibility.
- Controller: `CoreProfileApiEndpoint` owns the AJAX boundary.
- Templates: `templates/` contains server-rendered Bootstrap/Tabler markup.

Profile Settings module endpoints are co-located as `endpoint.php` within each
`apps/user_fields/{category}/{module}` folder. These are handler adapters; the
canonical `api/index.php` dispatcher and shared authorization/update policy remain central.

## Public profile block contract

Community code registers complete cards on the canonical hook `fusion_register_public_profile_blocks`. Every block has a unique PHP namespace, editor and public render callbacks, and a declared list of `DB_USERS` columns.

```php
use Community\Profiles\Cards\QualificationsCard;

function community_public_profile_blocks(): array
{
    return [[
        'namespace' => QualificationsCard::class,
        'title' => 'Qualifications',
        'order' => 30,
        'fields' => [[
            'name' => 'qualifications',
            'column' => 'user_qualifications',
            'type' => 'textarea',
            'max_length' => 1000,
        ]],
        'userdata' => [QualificationsCard::class, 'userdata'],
        'editor' => [QualificationsCard::class, 'editor'],
        'public' => [QualificationsCard::class, 'publicCard'],
    ]];
}

fusion_add_hook('fusion_register_public_profile_blocks', 'community_public_profile_blocks');
```

The AJAX endpoint accepts and persists only declared columns that physically exist on `DB_USERS`. A `userdata` callback receives the already-loaded `DB_USERS` row and the block's declared values; it may derive display values but must not introduce another persistence source. There is no public-profile EAV table and no external database value provider.

`GET api/?api=core-profile-public-block&namespace=Community%5CProfiles%5CCards%5CQualificationsCard` returns the resolved userdata for one canonical block namespace. A GET without a namespace returns every block. POST/PATCH updates the current member's declared `DB_USERS` fields.

## Avatar rule

The canonical avatar URL is always:

```php
$userdata['user_avatar'] !== ''
    ? IMAGES . 'avatars/' . $userdata['user_avatar']
    : IMAGES . 'avatars/default-avatar.png';
```

`ProfileAvatar::url()` applies this rule across both engines and the public view.
