# Admin Dashboard SDK

The administration dashboard is owned by the PHPFusion core. Themes supply semantic color tokens and normal administration framing; themes do not register a second dashboard renderer.

For the complete architecture, runtime flow, persistence, arrangement state model, localization contract, validation history, and AI handoff rules, read `documentation/framework.md` first.

## Add an infusion widget

An installed infusion is discovered when it provides this exact manifest:

```text
infusions/{infusion-folder}/dashboard/widgets.php
```

The manifest returns an array keyed by a stable, globally unique widget ID:

```php
<?php

defined('IN_FUSION') || exit;

use PHPFusion\Infusions\Example\Dashboard\StudentSummaryWidget;

$locale = fusion_get_locale('', fusion_get_inf_locale_path('dashboard.php', __DIR__ . '/../'));

return [
    'example.student-summary' => [
        'class' => StudentSummaryWidget::class,
        'title' => $locale['example_dashboard_students'],
        'description' => $locale['example_dashboard_students_description'],
        'icon' => 'people',
        'default_visible' => TRUE,
        'order' => 200,
        'span' => ['sm' => 12, 'md' => 6, 'lg' => 4, 'xl' => 3],
        'right' => 'EXAMPLE',
    ],
];
```

Only manifests belonging to rows in `DB_INFUSIONS` are loaded. The scanner validates the folder boundary, widget ID, renderer class, duplicate IDs, permissions, and responsive spans. It does not recursively execute PHP files.

## Renderer contract

The renderer implements `PHPFusion\AdminDashboard\DashboardWidgetInterface`:

```php
<?php

namespace PHPFusion\Infusions\Example\Dashboard;

use PHPFusion\AdminDashboard\DashboardContext;
use PHPFusion\AdminDashboard\DashboardWidgetInterface;

final class StudentSummaryWidget implements DashboardWidgetInterface
{
    public function render(DashboardContext $context): string
    {
        $count = (int)dbcount('(student_id)', DB_STUDENTS);

        return '<strong>' . number_format($count) . '</strong>';
    }
}
```

Renderer output is trusted project code. Escape dynamic text with `$context->escape()`. Do not print scripts, styles, dialogs, page headings, or a nested top-level card. A widget exception is logged and isolated inside that widget.

## Definition fields

| Field | Contract |
|---|---|
| `class` | Required renderer implementing `DashboardWidgetInterface` |
| `title` | Required localized label |
| `description` | Optional localized supporting text |
| `icon` | Established Tabler SVG registry key |
| `default_visible` | Supplied visibility before a user override |
| `order` | Default order; lower values appear first |
| `span` | `sm`, `md`, `lg`, and `xl` values from `3`, `4`, `6`, `8`, `9`, or `12` |
| `right` | One required PHPFusion administrator right |
| `rights` | A list of rights; any match is accepted by default |
| `rights_mode` | Set to `all` when every listed right is required |
| `super_admin` | Require `iSUPERADMIN` when `TRUE` |

Widget metadata must not run database queries. Data queries belong in `render()` and therefore run only for visible widgets or a requested lazy load.

## Preferences and interaction

Each administrator receives a versioned `{COOKIE_PREFIX}admin_dashboard_{user_id}` cookie. It stores visibility overrides and stable widget order for 365 days using the configured site path, `SameSite=Lax`, and `Secure` on HTTPS. Cookie data never grants permission; definitions are authorized again on the page and lazy endpoint.

Arrangement mode is off on page load. It supports pointer, touch, explicit move buttons, and `Alt` plus an arrow key. Mouse and touch dragging use a non-interactive ghost card and a matching-height muted placeholder to expose the destination slot. The DOM is reordered with the visual grid so keyboard and reading order remain aligned; cancelling a drag restores the original position.

## UI requirements

- Use project semantic roles rather than raw theme colors.
- Keep all user-facing words in locale files.
- Supply useful loading, empty, error, disabled, and permission behavior.
- Validate 375px, 768px, 1024px, and 1440px.
- Keep the normal dashboard useful without JavaScript; customization is progressive enhancement.
