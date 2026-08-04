<?php

namespace PHPFusion\AdminDashboard;

use PHPFusion\ImageRepo;
use Throwable;

final class DashboardManager
{
    private array $definitions;

    public function __construct(
        private DashboardRegistry $registry,
        private DashboardPreferences $preferences,
        private DashboardContext $context
    ) {
        $this->definitions = $this->preferences->sort($this->registry->all());
    }

    public static function create(): self
    {
        $localeFile = defined('LOCALE') && defined('LOCALESET')
            ? LOCALE . LOCALESET . 'admin/dashboard.php'
            : '';
        if ($localeFile === '' || !is_file($localeFile)) {
            $localeFile = defined('LOCALE') ? LOCALE . 'English/admin/dashboard.php' : '';
        }

        $locale = $localeFile !== '' && function_exists('fusion_get_locale')
            ? fusion_get_locale('', $localeFile)
            : [];
        $settings = function_exists('fusion_get_settings') ? fusion_get_settings() : [];
        $userdata = function_exists('fusion_get_userdata') ? fusion_get_userdata() : [];
        $context = new DashboardContext($locale, $settings, $userdata);
        $registry = (new DashboardRegistry($locale, $context->text('dashboard_source_core')))->discover();

        return new self($registry, DashboardPreferences::fromCurrentCookie(), $context);
    }

    public function render(): string
    {
        $this->loadAssets();
        $groups = [];
        foreach ($this->definitions as $definition) {
            $groups[$definition->sourceTitle()][] = $definition;
        }

        $visibleCount = 0;
        $cards = '';
        foreach ($this->definitions as $definition) {
            if ($this->preferences->isVisible($definition)) {
                $visibleCount++;
                $cards .= $this->renderDefinition($definition);
            }
        }

        $cookieName = DashboardPreferences::cookieName();
        $cookiePath = (string)($this->context->settings('site_path') ?: '/');
        $endpoint = (defined('BASEDIR') ? BASEDIR : '') . 'api/index.php?api=admin-dashboard-widget';
        $html = '<section class="admin-dashboard" data-admin-dashboard'
            . ' data-cookie-name="' . $this->context->escape($cookieName) . '"'
            . ' data-cookie-path="' . $this->context->escape($cookiePath) . '"'
            . ' data-widget-endpoint="' . $this->context->escape($endpoint) . '"'
            . ' data-load-error="' . $this->context->escape($this->context->text('dashboard_load_error')) . '"'
            . ' data-retry-label="' . $this->context->escape($this->context->text('dashboard_retry')) . '"'
            . ' data-drop-label="' . $this->context->escape($this->context->text('dashboard_drop_here')) . '"'
            . ' data-moved="' . $this->context->escape($this->context->text('dashboard_moved')) . '">';
        $html .= '<header class="admin-dashboard-header">';
        $html .= '<div class="admin-dashboard-heading"><span class="admin-dashboard-eyebrow">'
            . $this->context->escape($this->context->text('dashboard_eyebrow')) . '</span>';
        $html .= '<h1>' . $this->context->escape($this->context->text('dashboard_title')) . '</h1>';
        $html .= '<p>' . $this->context->escape($this->context->text('dashboard_description')) . '</p></div>';
        $html .= '<div class="admin-dashboard-toolbar" aria-label="'
            . $this->context->escape($this->context->text('dashboard_toolbar')) . '">';
        $html .= '<details class="admin-dashboard-picker">';
        $html .= '<summary class="admin-dashboard-button">' . $this->icon('settings')
            . '<span>' . $this->context->escape($this->context->text('dashboard_widgets')) . '</span></summary>';
        $html .= '<div class="admin-dashboard-menu">';
        $html .= '<div class="admin-dashboard-menu-header"><strong>'
            . $this->context->escape($this->context->text('dashboard_choose_widgets')) . '</strong><span>'
            . $this->context->escape($this->context->text('dashboard_choose_widgets_description')) . '</span></div>';
        foreach ($groups as $sourceTitle => $definitions) {
            $html .= '<fieldset><legend>' . $this->context->escape($sourceTitle) . '</legend>';
            foreach ($definitions as $definition) {
                $checked = $this->preferences->isVisible($definition);
                $span = $definition->span();
                $html .= '<label class="admin-dashboard-option"><span class="admin-dashboard-option-identity">'
                    . '<span class="admin-dashboard-option-icon">' . $this->icon($definition->icon()) . '</span>'
                    . '<span><strong>' . $this->context->escape($definition->title()) . '</strong>';
                if ($definition->description() !== '') {
                    $html .= '<small>' . $this->context->escape($definition->description()) . '</small>';
                }
                $html .= '</span></span><input type="checkbox" data-widget-toggle="'
                    . $this->context->escape($definition->id()) . '" data-default-visible="'
                    . ($definition->defaultVisible() ? '1' : '0') . '" data-span-sm="' . $span['sm']
                    . '" data-span-md="' . $span['md'] . '" data-span-lg="' . $span['lg']
                    . '" data-span-xl="' . $span['xl'] . '"' . ($checked ? ' checked' : '') . '></label>';
            }
            $html .= '</fieldset>';
        }
        $html .= '<div class="admin-dashboard-menu-footer"><button type="button" class="admin-dashboard-reset" data-dashboard-reset'
            . ' data-confirm="' . $this->context->escape($this->context->text('dashboard_reset_confirm')) . '">'
            . $this->icon('reset') . '<span>' . $this->context->escape($this->context->text('dashboard_reset')) . '</span></button></div>';
        $html .= '</div></details>';
        $html .= '<button type="button" class="admin-dashboard-button admin-dashboard-arrange" data-dashboard-arrange aria-pressed="false"'
            . ' data-enable-label="' . $this->context->escape($this->context->text('dashboard_arrange')) . '"'
            . ' data-disable-label="' . $this->context->escape($this->context->text('dashboard_arrange_done')) . '">'
            . $this->icon('drag') . '<span>' . $this->context->escape($this->context->text('dashboard_arrange')) . '</span></button>';
        $html .= '</div></header>';
        $html .= '<div class="admin-dashboard-live" data-dashboard-live aria-live="polite" aria-atomic="true"></div>';
        $html .= '<div class="admin-dashboard-grid" data-dashboard-grid>' . $cards . '</div>';
        $html .= '<div class="admin-dashboard-empty" data-dashboard-empty' . ($visibleCount > 0 ? ' hidden' : '') . '>'
            . $this->icon('dashboard') . '<strong>' . $this->context->escape($this->context->text('dashboard_empty_title')) . '</strong>'
            . '<span>' . $this->context->escape($this->context->text('dashboard_empty_description')) . '</span></div>';
        $html .= '</section>';

        return $html;
    }

    public function renderWidget(string $id): ?string
    {
        $definition = $this->registry->get($id);
        return $definition ? $this->renderDefinition($definition) : NULL;
    }

    public function text(string $key): string
    {
        return $this->context->text($key);
    }

    private function renderDefinition(DashboardDefinition $definition): string
    {
        $span = $definition->span();
        $html = '<article class="admin-dashboard-widget" data-dashboard-widget data-widget-id="'
            . $this->context->escape($definition->id()) . '" data-span-sm="' . $span['sm']
            . '" data-span-md="' . $span['md'] . '" data-span-lg="' . $span['lg']
            . '" data-span-xl="' . $span['xl'] . '">';
        $html .= '<header class="admin-dashboard-widget-header">';
        $html .= '<button type="button" class="admin-dashboard-drag-handle" data-dashboard-drag draggable="false" aria-label="'
            . $this->context->escape(sprintf($this->context->text('dashboard_move_widget'), $definition->title())) . '">'
            . $this->icon('drag') . '</button>';
        $html .= '<span class="admin-dashboard-widget-icon">' . $this->icon($definition->icon()) . '</span>';
        $html .= '<div class="admin-dashboard-widget-heading"><h2>' . $this->context->escape($definition->title()) . '</h2>';
        if ($definition->description() !== '') {
            $html .= '<p>' . $this->context->escape($definition->description()) . '</p>';
        }
        $html .= '</div><span class="admin-dashboard-source">' . $this->context->escape($definition->sourceTitle()) . '</span>';
        $html .= '<div class="admin-dashboard-move-actions">'
            . '<button type="button" data-dashboard-move="previous" aria-label="'
            . $this->context->escape($this->context->text('dashboard_move_previous')) . '">' . $this->icon('chevron-left') . '</button>'
            . '<button type="button" data-dashboard-move="next" aria-label="'
            . $this->context->escape($this->context->text('dashboard_move_next')) . '">' . $this->icon('chevron-right') . '</button></div>';
        $html .= '</header><div class="admin-dashboard-widget-body">';

        try {
            $html .= $definition->createWidget()->render($this->context);
        } catch (Throwable $exception) {
            if (function_exists('set_error')) {
                set_error(E_USER_WARNING, $exception->getMessage(), $exception->getFile(), $exception->getLine());
            }
            $html .= '<div class="admin-dashboard-error" role="alert">' . $this->icon('alert')
                . '<strong>' . $this->context->escape($this->context->text('dashboard_widget_error')) . '</strong>'
                . '<button type="button" data-widget-retry="' . $this->context->escape($definition->id()) . '">'
                . $this->context->escape($this->context->text('dashboard_retry')) . '</button></div>';
        }

        return $html . '</div></article>';
    }

    private function loadAssets(): void
    {
        if (function_exists('fusion_load_script')) {
            fusion_load_script(ADMIN . 'assets/admin-dashboard.css', 'css');
            fusion_load_script(INCLUDES . 'jscripts/js.cookie.min.js');
            fusion_load_script(ADMIN . 'assets/admin-dashboard.js');
        }
    }

    private function icon(string $name): string
    {
        $iconFile = defined('INCLUDES') ? INCLUDES . 'frameworks/bootstrap/tabler/svgs/tabler.php' : '';
        if ($iconFile !== '' && is_file($iconFile)) {
            require_once $iconFile;
        }

        $icon = ImageRepo::getSVG($name, 18, 1.8);
        if (!str_contains($icon, '<svg')) {
            return '<span class="admin-dashboard-icon-fallback" aria-hidden="true"></span>';
        }

        return preg_replace('/<svg\b/', '<svg aria-hidden="true" focusable="false"', $icon, 1) ?: '';
    }
}
