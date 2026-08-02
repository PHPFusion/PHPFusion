<?php
namespace PHPFusion\Jupiter\Classes;

use PHPFusion\Admins;
use PHPFusion\BreadCrumbs;

/**
 * Admin helper class
 */
class AdminHelper extends Admins {

    private array $active_sections = [];

    private const ICON_ALIASES = [
        'accounting' => 'calculator',
        'centre' => 'building-community',
        'classroom' => 'school',
        'group' => 'users-group',
        'groups' => 'users-group',
        'tool' => 'tools',
        'web' => 'world-www',
    ];

    public function __construct() {
        parent::__construct();
    }

    /* Admin pages */
    public function viewThemeAdminPages(): array {
        $admin_pages = [];

        foreach ($this->getAdminPageTree() ?? [] as $section) {
            if (($section['admin_link'] ?? '') !== 'reserved') {
                continue;
            }

            $section_id = (int)$section['admin_id'];
            $admin_pages[$section_id] = [];
            $section_active = FALSE;
            foreach ($section['children'] ?? [] as $page) {
                $this->appendAdminPage($admin_pages[$section_id], $page, $section_active);
            }
            $this->active_sections[$section_id] = $section_active;
        }

        return $admin_pages;
    }

    /* Admin sections */
    public function viewThemeAdminSections(): array {
        $admin_sections = [];

        foreach ($this->getAdminPageTree() ?? [] as $section) {
            if (($section['admin_link'] ?? '') === 'reserved') {
                $section_id = (int)$section['admin_id'];
                $section_icon = $section['admin_icon'] ?: $this->getAdminSectionIcons($section_id);
                $admin_sections[(int)$section['admin_id']] = [
                    'title' => $section['admin_title'],
                    'icon'  => $this->renderAdminIcon($section_icon, 'layout-dashboard'),
                    'is_active' => $this->active_sections[$section_id]
                        ?? $this->hasActivePage($section['children'] ?? [])
                ];
            }
        }

        return $admin_sections;
    }

    public function getSettingsURI(): string {
        return ADMIN.'settings_main.php'.fusion_get_aidlink();
    }

    public function getDashboardURI(): string {
        return ADMIN.'index.php'.fusion_get_aidlink();
    }

    /**
     * Format breadcrumbs
     *
     * @return array
     */
    public function getAdminBreadcrumbs(): array {
        $breadcrumbs = BreadCrumbs::getInstance();
        $arr = $breadcrumbs->toArray();
        unset($arr[0]);
        unset($arr[1]);

        return $arr;
    }

    /* Notices */
    public function getAdminNotices(): string {
        return rendernotices(getnotices(), ['container' => FALSE]);
    }

    private function appendAdminPage(array &$admin_pages, array $page, bool &$section_active): void {
        if (($page['admin_link'] ?? '') !== 'reserved') {
            $page['is_active'] = $this->isLinkActive($page['admin_link']);
            $section_active = $section_active || $page['is_active'];
            $page['admin_link'] = $this->getAdminURI($page['admin_link']);
            $page_icon = $page['admin_icon'] ?: $this->getAdminIcons($page['admin_rights']);
            $page['admin_icon'] = $this->renderAdminIcon($page_icon, 'file');
            $admin_pages[] = $page;
        }

        foreach ($page['children'] ?? [] as $child) {
            $this->appendAdminPage($admin_pages, $child, $section_active);
        }
    }

    private function hasActivePage(array $pages): bool {
        foreach ($pages as $page) {
            if (($page['admin_link'] ?? '') !== 'reserved'
                && $this->isLinkActive($page['admin_link'])) {
                return TRUE;
            }

            if ($this->hasActivePage($page['children'] ?? [])) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Convert unresolved database icon keys into the standalone Iconify
     * component used by Jupiter. Inline SVG and image markup remains intact.
     */
    private function renderAdminIcon(mixed $icon, string $fallback): string {
        $icon = trim((string)$icon);

        if ($icon !== '' && str_contains($icon, '<')) {
            return $icon;
        }

        $icon = strtolower(str_replace('_', '-', $icon));
        $icon = self::ICON_ALIASES[$icon] ?? $icon;

        if (!preg_match('/^[a-z0-9][a-z0-9-]*$/', $icon)) {
            $icon = $fallback;
        }

        return \iconify($icon, 'tabler', 'admin-ico');
    }

    private function getAdminURI(string $link): string {
        $link = preg_replace('#/(?=\?)#', '/index.php', $link);
        $aidlink = fusion_get_aidlink();

        if (str_contains($link, '?')) {
            $aidlink = preg_replace('/^\?/', '&', $aidlink);
        }

        return ADMIN.$link.$aidlink;
    }

}
