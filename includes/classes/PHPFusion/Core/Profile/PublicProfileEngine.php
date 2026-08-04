<?php

namespace PHPFusion\Core\Profile;

use PHPFusion\ProfileGlobal\ProfileContext;
use PHPFusion\ProfileGlobal\ProfileModuleRegistry;
use PHPFusion\ProfileGlobal\ProfilePolicyRegistry;
use PHPFusion\ProfileGlobal\ProfileRepository;

final class PublicProfileEngine
{
    private ProfileModel $model;
    private ProfileView $view;
    private PublicProfileBlockRegistry $blocks;

    public function __construct()
    {
        $this->model = new ProfileModel();
        $this->view = new ProfileView();
        $this->blocks = new PublicProfileBlockRegistry();
    }

    public function renderEditor(): void
    {
        $user = fusion_get_userdata();
        $this->view->render('public-profile-edit', [
            'user' => $user,
            'blocks' => $this->buildBlocks($user),
            'avatarUrl' => ProfileAvatar::url($user),
            'endpoint' => BASEDIR . 'api/?api=core-profile-public-block',
            'publicUrl' => BASEDIR . 'profile_view.php?lookup=' . (int)($user['user_id'] ?? 0),
        ]);
    }

    public function renderPublic(int $userId): bool
    {
        $user = $this->model->findUser($userId);
        if (!$user) {
            return FALSE;
        }

        $viewerId = defined('iMEMBER') && iMEMBER ? (int)fusion_get_userdata('user_id') : 0;
        $context = new ProfileContext($user, $viewerId, defined('iADMIN') && iADMIN);
        $policy = new ProfilePolicyRegistry(new ProfileModuleRegistry(new ProfileRepository()));
        if (!$policy->canViewPublicProfile($context)) {
            return FALSE;
        }

        $this->view->render('public-profile-display', [
            'user' => $user,
            'blocks' => $this->buildBlocks($user),
            'avatarUrl' => ProfileAvatar::url($user),
            'isOwner' => $context->isOwner(),
        ]);

        return TRUE;
    }

    private function buildBlocks(array $user): array
    {
        $result = [];
        foreach ($this->blocks->all() as $block) {
            $result[] = ['definition' => $block, 'data' => $this->blocks->data($block, $user)];
        }

        return $result;
    }
}
