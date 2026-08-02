<?php

namespace PHPFusion\Api;

use PHPFusion\AdminDashboard\DashboardManager;
use PHPFusion\Administration\Api\AdminApiGuard;
use PHPFusion\Administration\Api\AdminApiResponse;

final class AdminDashboardEndpoint
{
    public static function widget(ApiRequest $request): ApiResponse
    {
        if ($denied = AdminApiGuard::authorize($request, '')) {
            return $denied;
        }

        if ($request->channel() === 'http' && (!defined('iAUTH') || !iAUTH)) {
            $manager = DashboardManager::create();
            return AdminApiResponse::error($request, $manager->text('dashboard_admin_auth_required'), 403);
        }

        $manager = DashboardManager::create();
        $id = trim((string)$request->query('widget'));
        $html = $id !== '' ? $manager->renderWidget($id) : NULL;
        if ($html === NULL) {
            return AdminApiResponse::error($request, $manager->text('dashboard_widget_not_found'), 404);
        }

        return AdminApiResponse::success(
            $request,
            ['html' => $html, 'widget' => $id],
            $manager->text('dashboard_widget_loaded')
        );
    }
}
