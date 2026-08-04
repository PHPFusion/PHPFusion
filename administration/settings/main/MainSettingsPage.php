<?php

namespace PHPFusion\Administration\Settings\Main;

use PHPFusion\Administration\Page\AdminPage;

final class MainSettingsPage
{
    private const ENDPOINTS = [
        'site_identity' => 'admin.settings.main.site-identity',
        'site_content' => 'admin.settings.main.site-content',
        'search' => 'admin.settings.main.search',
        'url' => 'admin.settings.main.url',
        'domains' => 'admin.settings.main.domains',
    ];

    public function render(): void
    {
        $this->handleFallbackPost();

        $locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');
        $module = __DIR__;
        $page = new AdminPage([
            'id' => 'main-settings',
            'access' => 'S1',
            'title' => $locale['admins_main_settings'],
            'description' => $locale['admins_main_description'],
            'breadcrumb' => [
                'link' => ADMIN.'settings_main.php'.fusion_get_aidlink(),
                'title' => $locale['admins_main_settings'],
            ],
            'assets' => [
                ['path' => ADMIN.'settings/main/assets/settings-main.js', 'type' => 'js'],
            ],
            'endpoints' => self::ENDPOINTS,
            'default_view' => 'index',
            'views' => [
                'index' => [
                    'controller' => static function () use ($locale): array {
                        $service = new SettingsMainService();

                        return [
                            'model' => MainSettingsSchema::page($locale, $service->all(), SettingsMainService::searchOptions()),
                        ];
                    },
                    'template' => $module.'/templates/page.php',
                ],
                // Canonical AdminPage also resolves `new` and `edit` views when
                // a page declares them, without changing the page entry file.
            ],
        ]);
        $page->render();
    }

    private function handleFallbackPost(): void
    {
        foreach (self::ENDPOINTS as $section => $endpoint) {
            if (!check_post('save_'.$section)) {
                continue;
            }

            require_once BASEDIR.'api/manifests/api.php';
            $response = fusion_api_invoke($endpoint, $_POST, ['method' => 'POST']);
            $payload = (array)$response->data();
            $success = !empty($payload['success']);
            addnotice($success ? 'success' : 'danger', (string)($payload['message'] ?? 'The settings could not be updated.'));
            if ($success) {
                redirect(FUSION_REQUEST);
            }
            break;
        }
    }
}
