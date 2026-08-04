<?php

use PHPFusion\Social\SocialCleanup;
use PHPFusion\Social\SocialProfile;

function social_cleanup_deleted_user($user_id): void {
    SocialCleanup::deleteUserData((int) $user_id);
}

function social_render_profile_friends(array $user_data): void {
    SocialProfile::render((int) $user_data['user_id']);
}

fusion_add_hook('fusion_user_deleted', 'social_cleanup_deleted_user');
fusion_add_hook('fusion_profile_social', 'social_render_profile_friends');
