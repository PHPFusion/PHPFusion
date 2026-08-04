<?php

namespace PHPFusion\Core\Profile;

use PHPFusion\ProfileGlobal\ProfileCategoryRegistry;
use PHPFusion\ProfileGlobal\ProfileContext;
use PHPFusion\ProfileGlobal\ProfileModuleRegistry;
use PHPFusion\ProfileGlobal\ProfileRepository;

final class ProfileSettingsEngine
{
    private ProfileView $view;

    public function __construct()
    {
        $this->view = new ProfileView();
    }

    public function render(): void
    {
        $user = fusion_get_userdata();
        $context = new ProfileContext($user, (int)($user['user_id'] ?? 0), defined('iADMIN') && iADMIN);
        $repository = new ProfileRepository();
        $categories = new ProfileCategoryRegistry();
        $registry = new ProfileModuleRegistry($repository, $categories);
        $grouped = [];

        foreach ($registry->availableFor($context) as $module) {
            $definition = $module->definition();
            if (!$this->isUserFieldModule($definition)) {
                continue;
            }

            $category = (string)$definition['category'];
            $grouped[$category][] = [
                'definition' => $definition,
                'schema' => $module->schema($context),
                'values' => $module->values($context),
                'endpoint' => BASEDIR . 'api/?api=' . rawurlencode(ProfileModuleRegistry::endpointAlias((string)$definition['id'])),
            ];
        }

        $categoryDefinitions = array_filter(
            $categories->all(),
            static fn(array $category): bool => !empty($grouped[(string)($category['key'] ?? '')])
                && is_dir(BASEDIR . 'apps/user_fields/' . (string)($category['key'] ?? ''))
        );
        uksort($categoryDefinitions, 'strnatcasecmp');

        $this->view->render('profile-settings', [
            'user' => $user,
            'avatarUrl' => ProfileAvatar::url($user),
            'categories' => $categoryDefinitions,
            'modules' => $grouped,
            'publicEditorUrl' => BASEDIR . 'profile_edit.php',
        ]);
    }

    private function isUserFieldModule(array $definition): bool
    {
        $source = realpath((string)($definition['source'] ?? ''));
        $root = realpath(BASEDIR . 'apps/user_fields');

        return $source !== FALSE
            && $root !== FALSE
            && str_starts_with($source, $root . DIRECTORY_SEPARATOR);
    }
}
