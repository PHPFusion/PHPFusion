<?php

namespace PHPFusion\AdminDashboard;

interface DashboardWidgetInterface
{
    public function render(DashboardContext $context): string;
}
