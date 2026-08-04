<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: SocialModel.php
| Author: Core Development Team
+--------------------------------------------------------*/

namespace PHPFusion\Social;

/**
 * Read model used by the social pages and API.
 */
class SocialModel {

    private const VIEWS = ['friends', 'requests', 'sent', 'followers', 'following', 'blacklist', 'search', 'settings'];

    private int $user_id;

    public function __construct(int $user_id) {
        $this->user_id = $user_id;
    }

    public function getPage(string $view, string $search = ''): array {
        $view = in_array($view, self::VIEWS, TRUE) ? $view : 'friends';
        $search = trim($search);
        $people = match ($view) {
            'search' => $this->searchPeople($search),
            'settings' => [],
            default => $this->getRelationshipPeople($view),
        };

        return [
            'view'   => $view,
            'search' => $search,
            'people' => $this->decorate($people),
            'counts' => $this->getCounts(),
            'privacy' => SocialPrivacy::get($this->user_id),
        ];
    }

    public function searchPeople(string $search, int $limit = 40): array {
        $search = trim($search);
        if ($search === '') {
            return [];
        }

        $result = dbquery(
            "SELECT u.user_id, u.user_name, u.user_avatar, u.user_status, u.user_level, u.user_location
             FROM ".DB_USERS." u
             LEFT JOIN ".DB_SOCIAL_SETTINGS." settings
                ON settings.social_settings_user_id=u.user_id
             WHERE u.user_id<>:user
               AND u.user_status='0'
               AND u.user_name LIKE :search
               AND COALESCE(settings.social_discoverable, 1)='1'
               AND NOT EXISTS (
                   SELECT 1
                   FROM ".DB_SOCIAL." blocked
                   WHERE blocked.social_type='block'
                     AND blocked.social_status='1'
                     AND (
                         (blocked.social_user_id=:block_source AND blocked.social_target_id=u.user_id)
                         OR
                         (blocked.social_user_id=u.user_id AND blocked.social_target_id=:block_target)
                     )
               )
             ORDER BY user_name ASC
             LIMIT ".max(1, min($limit, 100)),
            [
                ':user'   => $this->user_id,
                ':search' => '%'.$search.'%',
                ':block_source' => $this->user_id,
                ':block_target' => $this->user_id,
            ]
        );

        return dbarray_list($result);
    }

    private function getRelationshipPeople(string $view): array {
        $config = [
            'friends'   => ['friend', SocialBuddy::STATUS_ACTIVE, TRUE],
            'requests'  => ['friend', SocialBuddy::STATUS_PENDING, FALSE],
            'sent'      => ['friend', SocialBuddy::STATUS_PENDING, TRUE],
            'followers' => ['follow', SocialBuddy::STATUS_ACTIVE, FALSE],
            'following' => ['follow', SocialBuddy::STATUS_ACTIVE, TRUE],
            'blacklist' => ['block', SocialBuddy::STATUS_ACTIVE, TRUE],
        ][$view];

        [$type, $status, $outgoing] = $config;

        if ($view === 'friends') {
            $join_id = "IF(s.social_user_id=:selected_user, s.social_target_id, s.social_user_id)";
            $direction = "(s.social_user_id=:source_user OR s.social_target_id=:target_user)";
            $parameters = [
                ':selected_user' => $this->user_id,
                ':source_user'   => $this->user_id,
                ':target_user'   => $this->user_id,
            ];
        } else {
            $owner = $outgoing ? 's.social_user_id' : 's.social_target_id';
            $join_id = $outgoing ? 's.social_target_id' : 's.social_user_id';
            $direction = $owner.'=:user';
            $parameters = [':user' => $this->user_id];
        }

        $parameters[':type'] = $type;
        $parameters[':status'] = $status;

        $result = dbquery(
            "SELECT u.user_id, u.user_name, u.user_avatar, u.user_status, u.user_level, u.user_location
             FROM ".DB_SOCIAL." s
             INNER JOIN ".DB_USERS." u ON u.user_id=".$join_id."
             WHERE ".$direction."
               AND s.social_type=:type
               AND s.social_status=:status
             ORDER BY s.social_datestamp DESC",
            $parameters
        );

        return dbarray_list($result);
    }

    private function decorate(array $people): array {
        $relations = $this->getRelationMap();

        foreach ($people as &$person) {
            $target_id = (int) $person['user_id'];
            $person['is_friend'] = !empty($relations[$target_id]['friend']);
            $person['friend_request_sent'] = !empty($relations[$target_id]['friend_sent']);
            $person['friend_request_received'] = !empty($relations[$target_id]['friend_received']);
            $person['is_following'] = !empty($relations[$target_id]['following']);
            $person['follows_you'] = !empty($relations[$target_id]['follower']);
            $person['blocked_by_me'] = !empty($relations[$target_id]['blocked_by_me']);
            $person['has_blocked_me'] = !empty($relations[$target_id]['has_blocked_me']);
        }
        unset($person);

        return $people;
    }

    private function getRelationMap(): array {
        $result = dbquery(
            "SELECT social_user_id, social_target_id, social_type, social_status
             FROM ".DB_SOCIAL."
             WHERE social_user_id=:source_user OR social_target_id=:target_user",
            [
                ':source_user' => $this->user_id,
                ':target_user' => $this->user_id,
            ]
        );
        $map = [];

        while ($row = dbarray($result)) {
            $outgoing = (int) $row['social_user_id'] === $this->user_id;
            $target_id = $outgoing ? (int) $row['social_target_id'] : (int) $row['social_user_id'];
            $active = (int) $row['social_status'] === SocialBuddy::STATUS_ACTIVE;

            if ($row['social_type'] === SocialBuddy::TYPE_FRIEND) {
                if ($active) {
                    $map[$target_id]['friend'] = TRUE;
                } else {
                    $map[$target_id][$outgoing ? 'friend_sent' : 'friend_received'] = TRUE;
                }
            } elseif ($row['social_type'] === SocialBuddy::TYPE_FOLLOW && $active) {
                $map[$target_id][$outgoing ? 'following' : 'follower'] = TRUE;
            } elseif ($row['social_type'] === SocialBuddy::TYPE_BLOCK && $active) {
                $map[$target_id][$outgoing ? 'blocked_by_me' : 'has_blocked_me'] = TRUE;
            }
        }

        return $map;
    }

    private function getCounts(): array {
        $buddy = new SocialBuddy($this->user_id);

        return [
            'friends'   => count($buddy->getFriends()),
            'requests'  => count($buddy->getFriendRequests()),
            'sent'      => count($buddy->getSentFriendRequests()),
            'followers' => count($buddy->getFollowers()),
            'following' => count($buddy->getFollowing()),
            'blacklist' => count($buddy->getBlacklist()),
        ];
    }
}
