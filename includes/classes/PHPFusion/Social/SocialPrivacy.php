<?php

namespace PHPFusion\Social;

class SocialPrivacy {

    private const DEFAULTS = [
        'social_friend_privacy' => 'everyone',
        'social_follow_privacy' => 'everyone',
        'social_profile_visibility' => 'members',
        'social_discoverable' => 1,
        'social_notify_friend_request' => 1,
        'social_notify_friend_accept' => 1,
        'social_notify_follow' => 1,
    ];

    public static function get(int $user_id): array {
        $result = dbquery(
            "SELECT * FROM ".DB_SOCIAL_SETTINGS." WHERE social_settings_user_id=:user",
            [':user' => $user_id]
        );
        $data = dbarray($result) ?: [];

        return $data + self::DEFAULTS;
    }

    public static function save(int $user_id, array $input): bool {
        $friend = in_array($input['friend_privacy'] ?? '', ['everyone', 'following', 'nobody'], TRUE)
            ? $input['friend_privacy']
            : 'everyone';
        $follow = in_array($input['follow_privacy'] ?? '', ['everyone', 'friends', 'nobody'], TRUE)
            ? $input['follow_privacy']
            : 'everyone';
        $profile = in_array($input['profile_visibility'] ?? '', ['everyone', 'members', 'friends', 'nobody'], TRUE)
            ? $input['profile_visibility']
            : 'members';

        return dbquery(
            "INSERT INTO ".DB_SOCIAL_SETTINGS."
                (social_settings_user_id, social_friend_privacy, social_follow_privacy,
                 social_profile_visibility, social_discoverable,
                 social_notify_friend_request, social_notify_friend_accept,
                 social_notify_follow, social_settings_updated)
             VALUES (:user, :friend, :follow, :profile, :discoverable,
                     :notify_request, :notify_accept, :notify_follow, :updated)
             ON DUPLICATE KEY UPDATE
                social_friend_privacy=VALUES(social_friend_privacy),
                social_follow_privacy=VALUES(social_follow_privacy),
                social_profile_visibility=VALUES(social_profile_visibility),
                social_discoverable=VALUES(social_discoverable),
                social_notify_friend_request=VALUES(social_notify_friend_request),
                social_notify_friend_accept=VALUES(social_notify_friend_accept),
                social_notify_follow=VALUES(social_notify_follow),
                social_settings_updated=VALUES(social_settings_updated)",
            [
                ':user'           => $user_id,
                ':friend'         => $friend,
                ':follow'         => $follow,
                ':profile'        => $profile,
                ':discoverable'   => !empty($input['discoverable']) ? 1 : 0,
                ':notify_request' => !empty($input['notify_friend_request']) ? 1 : 0,
                ':notify_accept'  => !empty($input['notify_friend_accept']) ? 1 : 0,
                ':notify_follow'  => !empty($input['notify_follow']) ? 1 : 0,
                ':updated'        => time(),
            ]
        ) !== FALSE;
    }

    public static function allowsFriendRequest(int $target_id, int $requester_id): bool {
        $privacy = self::get($target_id)['social_friend_privacy'];
        if ($privacy === 'nobody') {
            return FALSE;
        }
        if ($privacy === 'following') {
            return (bool) dbcount(
                '(social_id)',
                DB_SOCIAL,
                "social_user_id=:target AND social_target_id=:requester
                 AND social_type='follow' AND social_status='1'",
                [':target' => $target_id, ':requester' => $requester_id]
            );
        }
        return TRUE;
    }

    public static function allowsFollow(int $target_id, int $requester_id): bool {
        $privacy = self::get($target_id)['social_follow_privacy'];
        if ($privacy === 'nobody') {
            return FALSE;
        }
        if ($privacy === 'friends') {
            return (bool) dbcount(
                '(social_id)',
                DB_SOCIAL,
                "((social_user_id=:requester_a AND social_target_id=:target_a)
                   OR (social_user_id=:target_b AND social_target_id=:requester_b))
                 AND social_type='friend' AND social_status='1'",
                [
                    ':requester_a' => $requester_id,
                    ':target_a'    => $target_id,
                    ':target_b'    => $target_id,
                    ':requester_b' => $requester_id,
                ]
            );
        }
        return TRUE;
    }

    public static function canViewFriends(int $profile_id, int $viewer_id): bool {
        $visibility = self::get($profile_id)['social_profile_visibility'];
        if ($profile_id === $viewer_id || iADMIN) {
            return TRUE;
        }
        if ($visibility === 'nobody' || ($visibility === 'members' && !$viewer_id)) {
            return FALSE;
        }
        if ($visibility === 'friends') {
            return (new SocialBuddy($profile_id))->isFriend($viewer_id);
        }
        return TRUE;
    }

    public static function wantsNotification(int $user_id, string $event): bool {
        $map = [
            'friend_request' => 'social_notify_friend_request',
            'friend_accepted' => 'social_notify_friend_accept',
            'follow' => 'social_notify_follow',
        ];
        return !isset($map[$event]) || !empty(self::get($user_id)[$map[$event]]);
    }
}
