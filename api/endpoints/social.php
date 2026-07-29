<?php

defined('IN_FUSION') || exit;

use PHPFusion\Social\SocialBuddy;
use PHPFusion\Social\SocialLocale;
use PHPFusion\Social\SocialModel;
use PHPFusion\Social\SocialPrivacy;
use PHPFusion\Social\SocialThrottle;

/**
 * Handle Social Buddy API requests.
 *
 * All access must pass through:
 * api/?api=social
 *
 * GET supports people search. POST supports relationship mutations and privacy
 * preferences. Authentication, CSRF verification and per-action throttling are
 * applied before any write.
 */
function social_api_endpoint(): void {
    header('Content-Type: application/json; charset=UTF-8');
    $locale = SocialLocale::all();

    $respond = static function (string $status, string $message, array $data = [], int $code = 200): void {
        http_response_code($code);
        echo json_encode(['status' => $status, 'message' => $message] + $data);
        die();
    };

    if (!iMEMBER) {
        $respond('error', $locale['SOCIAL_059'], [], 401);
    }

    $user_id = (int) fusion_get_userdata('user_id');

    if (server('REQUEST_METHOD') === 'GET') {
        if (!SocialThrottle::allow($user_id, 'search', 60)) {
            header('Retry-After: 60');
            $respond('error', $locale['SOCIAL_060'], [], 429);
        }
        $query = trim((string) get('q'));
        $model = new SocialModel($user_id);
        $respond('success', $locale['SOCIAL_061'], [
            'people' => $model->getPage('search', $query)['people'],
        ]);
    }

    if (server('REQUEST_METHOD') !== 'POST' || !fusion_safe()) {
        $respond('error', $locale['SOCIAL_062'], [], 403);
    }

    $action = (string) post('action');
    $limits = [
        'save_privacy' => 10,
        'add_friend' => 15,
        'accept_friend' => 20,
        'reject_friend' => 30,
        'remove_friend' => 30,
        'follow' => 20,
        'unfollow' => 30,
        'block' => 15,
        'unblock' => 20,
    ];
    if (!isset($limits[$action])) {
        $respond('error', $locale['SOCIAL_063'], [], 422);
    }
    if (!SocialThrottle::allow($user_id, $action, $limits[$action])) {
        header('Retry-After: 60');
        $respond('error', $locale['SOCIAL_064'], [], 429);
    }

    if ($action === 'save_privacy') {
        $result = SocialPrivacy::save($user_id, [
            'friend_privacy' => post('friend_privacy'),
            'follow_privacy' => post('follow_privacy'),
            'profile_visibility' => post('profile_visibility'),
            'discoverable' => post('discoverable', FILTER_VALIDATE_INT),
            'notify_friend_request' => post('notify_friend_request', FILTER_VALIDATE_INT),
            'notify_friend_accept' => post('notify_friend_accept', FILTER_VALIDATE_INT),
            'notify_follow' => post('notify_follow', FILTER_VALIDATE_INT),
        ]);
        $result
            ? $respond('success', $locale['SOCIAL_065'])
            : $respond('error', $locale['SOCIAL_066'], [], 409);
    }

    $target_id = (int) post('target_id', FILTER_VALIDATE_INT);
    $actions = [
        'add_friend'    => 'addFriend',
        'accept_friend' => 'acceptFriend',
        'reject_friend' => 'rejectFriend',
        'remove_friend' => 'removeFriend',
        'follow'        => 'follow',
        'unfollow'      => 'unfollow',
        'block'         => 'block',
        'unblock'       => 'unblock',
    ];

    if (!$target_id || !isset($actions[$action])
        || !dbcount('(user_id)', DB_USERS, 'user_id=:target', [':target' => $target_id])) {
        $respond('error', $locale['SOCIAL_063'], [], 422);
    }

    $buddy = new SocialBuddy($user_id);
    $result = $buddy->{$actions[$action]}($target_id);

    $result
        ? $respond('success', $locale['SOCIAL_067'], ['action' => $action])
        : $respond('error', $locale['SOCIAL_068'], [], 409);
}

fusion_add_hook('fusion_filters', 'social_api_endpoint');
