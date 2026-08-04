<?php
namespace PHPFusion;

use PHPFusion\AdminDashboard\DashboardManager;

interface DashboardCardInterface {
	public function getOrder(): int;    // Sort order (1, 2, 3...)
	public function getWidth(): int;    // Bootstrap cols (1-12)
	public function getHeight(): string; // e.g., '300px'
	public function render(): string;
}

class Dashboard {

    /**
     * @deprecated Dashboard widgets now register through AdminDashboard manifests.
     */
    public function __construct($directory = '') {
    }

    /** @deprecated Manifest discovery is automatic. */
    public function loadModules(): void {
    }

    /** @deprecated Use DashboardManager::create()->render(). */
    public function generateHtml(): string {
        return DashboardManager::create()->render();
    }
}
