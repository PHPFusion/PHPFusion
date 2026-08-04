<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: SocialBuddy.php
| Author: Core Development Team
+--------------------------------------------------------*/

namespace PHPFusion\Social;

/**
 * Small relationship service for friends, followers and blocked users.
 */
class SocialBuddy {

    public const TYPE_FRIEND = 'friend';
    public const TYPE_FOLLOW = 'follow';
    public const TYPE_BLOCK = 'block';

    public const STATUS_PENDING = 0;
    public const STATUS_ACTIVE = 1;

    private int $user_id;

    public function __construct(int $user_id) {
        $this->user_id = $user_id;
    }

    public function addFriend(int $target_id): bool {
        if (!$this->isValidTarget($target_id)
            || $this->isBlocked($target_id)
            || !SocialPrivacy::allowsFriendRequest($target_id, $this->user_id)) {
            return FALSE;
        }

        if ($this->hasRelation($target_id, self::TYPE_FRIEND, self::STATUS_ACTIVE, TRUE)) {
            return TRUE;
        }

        if ($this->hasRelation($target_id, self::TYPE_FRIEND, self::STATUS_PENDING)) {
            return TRUE;
        }

        if ($this->hasRelation($target_id, self::TYPE_FRIEND, self::STATUS_PENDING, FALSE, FALSE)) {
            return $this->acceptFriend($target_id);
        }

        $result = $this->insert($target_id, self::TYPE_FRIEND, self::STATUS_PENDING);
        if ($result) {
            $this->scheduleNotification('friend_request', $target_id);
        }

        return $result;
    }

    public function acceptFriend(int $requester_id): bool {
        if (!$this->isValidTarget($requester_id) || $this->isBlocked($requester_id)) {
            return FALSE;
        }

        if (!$this->hasRelation($requester_id, self::TYPE_FRIEND, self::STATUS_PENDING, FALSE, FALSE)) {
            return FALSE;
        }

        $result = (bool) dbquery(
            "UPDATE ".DB_SOCIAL."
             SET social_status=:active, social_datestamp=:datestamp
             WHERE social_user_id=:requester
               AND social_target_id=:user
               AND social_type=:type
               AND social_status=:pending",
            [
                ':active'    => self::STATUS_ACTIVE,
                ':datestamp' => time(),
                ':requester' => $requester_id,
                ':user'      => $this->user_id,
                ':type'      => self::TYPE_FRIEND,
                ':pending'   => self::STATUS_PENDING,
            ]
        );

        if ($result) {
            $this->scheduleNotification('friend_accepted', $requester_id);
        }

        return $result;
    }

    public function rejectFriend(int $requester_id): bool {
        return $this->delete($requester_id, self::TYPE_FRIEND, self::STATUS_PENDING, FALSE, TRUE, FALSE);
    }

    public function removeFriend(int $target_id): bool {
        return $this->delete($target_id, self::TYPE_FRIEND, NULL, TRUE);
    }

    public function follow(int $target_id): bool {
        if (!$this->isValidTarget($target_id)
            || $this->isBlocked($target_id)
            || !SocialPrivacy::allowsFollow($target_id, $this->user_id)) {
            return FALSE;
        }

        if ($this->hasRelation($target_id, self::TYPE_FOLLOW)) {
            return TRUE;
        }

        $result = $this->insert($target_id, self::TYPE_FOLLOW, self::STATUS_ACTIVE);
        if ($result) {
            $this->scheduleNotification('follow', $target_id);
        }

        return $result;
    }

    public function unfollow(int $target_id): bool {
        return $this->delete($target_id, self::TYPE_FOLLOW);
    }

    public function block(int $target_id): bool {
        if (!$this->isValidTarget($target_id)) {
            return FALSE;
        }

        $this->delete($target_id, self::TYPE_FRIEND, NULL, TRUE);
        $this->delete($target_id, self::TYPE_FOLLOW, NULL, TRUE);

        return $this->hasRelation($target_id, self::TYPE_BLOCK)
            || $this->insert($target_id, self::TYPE_BLOCK, self::STATUS_ACTIVE);
    }

    public function unblock(int $target_id): bool {
        return $this->delete($target_id, self::TYPE_BLOCK);
    }

    public function isFriend(int $target_id): bool {
        return $this->hasRelation($target_id, self::TYPE_FRIEND, self::STATUS_ACTIVE, TRUE);
    }

    public function isFollowing(int $target_id): bool {
        return $this->hasRelation($target_id, self::TYPE_FOLLOW, self::STATUS_ACTIVE);
    }

    /**
     * Checks blocks in both directions.
     */
    public function isBlocked(int $target_id): bool {
        return $this->hasRelation($target_id, self::TYPE_BLOCK, self::STATUS_ACTIVE, TRUE);
    }

    public function getFriends(): array {
        return $this->getRelatedIds(self::TYPE_FRIEND, self::STATUS_ACTIVE, TRUE, TRUE);
    }

    public function getFriendRequests(): array {
        return $this->getRelatedIds(self::TYPE_FRIEND, self::STATUS_PENDING, FALSE);
    }

    public function getSentFriendRequests(): array {
        return $this->getRelatedIds(self::TYPE_FRIEND, self::STATUS_PENDING, TRUE);
    }

    public function getFollowers(): array {
        return $this->getRelatedIds(self::TYPE_FOLLOW, self::STATUS_ACTIVE, FALSE);
    }

    public function getFollowing(): array {
        return $this->getRelatedIds(self::TYPE_FOLLOW, self::STATUS_ACTIVE, TRUE);
    }

    public function getBlacklist(): array {
        return $this->getRelatedIds(self::TYPE_BLOCK, self::STATUS_ACTIVE, TRUE);
    }

    private function insert(int $target_id, string $type, int $status): bool {
        return (bool) dbquery(
            "INSERT INTO ".DB_SOCIAL."
                (social_user_id, social_target_id, social_type, social_status, social_datestamp)
             VALUES (:user, :target, :type, :status, :datestamp)",
            [
                ':user'      => $this->user_id,
                ':target'    => $target_id,
                ':type'      => $type,
                ':status'    => $status,
                ':datestamp' => time(),
            ]
        );
    }

    private function delete(
        int $target_id,
        string $type,
        ?int $status = NULL,
        bool $both_directions = FALSE,
        bool $require_target = TRUE,
        bool $outgoing = TRUE
    ): bool {
        if ($require_target && !$this->isValidTarget($target_id)) {
            return FALSE;
        }

        if ($both_directions) {
            $direction = "((social_user_id=:user_from AND social_target_id=:target_to)
                           OR (social_user_id=:target_from AND social_target_id=:user_to))";
            $parameters = [
                ':user_from'   => $this->user_id,
                ':target_to'   => $target_id,
                ':target_from' => $target_id,
                ':user_to'     => $this->user_id,
                ':type'        => $type,
            ];
        } else {
            $direction = $outgoing
                ? "(social_user_id=:user AND social_target_id=:target)"
                : "(social_user_id=:target AND social_target_id=:user)";
            $parameters = [
                ':user'   => $this->user_id,
                ':target' => $target_id,
                ':type'   => $type,
            ];
        }
        $status_sql = $status === NULL ? '' : ' AND social_status=:status';

        if ($status !== NULL) {
            $parameters[':status'] = $status;
        }

        return (bool) dbquery(
            "DELETE FROM ".DB_SOCIAL." WHERE ".$direction." AND social_type=:type".$status_sql,
            $parameters
        );
    }

    private function hasRelation(
        int $target_id,
        string $type,
        ?int $status = NULL,
        bool $both_directions = FALSE,
        bool $outgoing = TRUE
    ): bool {
        if (!$this->isValidTarget($target_id)) {
            return FALSE;
        }

        if ($both_directions) {
            $direction = "((social_user_id=:user_from AND social_target_id=:target_to)
                           OR (social_user_id=:target_from AND social_target_id=:user_to))";
            $parameters = [
                ':user_from'   => $this->user_id,
                ':target_to'   => $target_id,
                ':target_from' => $target_id,
                ':user_to'     => $this->user_id,
                ':type'        => $type,
            ];
        } else {
            $direction = $outgoing
                ? "(social_user_id=:user AND social_target_id=:target)"
                : "(social_user_id=:target AND social_target_id=:user)";
            $parameters = [
                ':user'   => $this->user_id,
                ':target' => $target_id,
                ':type'   => $type,
            ];
        }
        $status_sql = $status === NULL ? '' : ' AND social_status=:status';

        if ($status !== NULL) {
            $parameters[':status'] = $status;
        }

        return (bool) dbcount(
            '(social_id)',
            DB_SOCIAL,
            $direction.' AND social_type=:type'.$status_sql,
            $parameters
        );
    }

    private function getRelatedIds(
        string $type,
        int $status,
        bool $outgoing,
        bool $both_directions = FALSE
    ): array {
        if ($both_directions) {
            $result = dbquery(
                "SELECT IF(social_user_id=:selected_user, social_target_id, social_user_id) AS user_id
                 FROM ".DB_SOCIAL."
                 WHERE (social_user_id=:source_user OR social_target_id=:target_user)
                   AND social_type=:type
                   AND social_status=:status
                 ORDER BY social_datestamp DESC",
                [
                    ':selected_user' => $this->user_id,
                    ':source_user'   => $this->user_id,
                    ':target_user'   => $this->user_id,
                    ':type'          => $type,
                    ':status'        => $status,
                ]
            );
        } else {
            $owner_column = $outgoing ? 'social_user_id' : 'social_target_id';
            $user_column = $outgoing ? 'social_target_id' : 'social_user_id';
            $result = dbquery(
                "SELECT ".$user_column." AS user_id
                 FROM ".DB_SOCIAL."
                 WHERE ".$owner_column."=:user
                   AND social_type=:type
                   AND social_status=:status
                 ORDER BY social_datestamp DESC",
                [
                    ':user'   => $this->user_id,
                    ':type'   => $type,
                    ':status' => $status,
                ]
            );
        }

        return array_map('intval', array_column(dbarray_list($result), 'user_id'));
    }

    private function isValidTarget(int $target_id): bool {
        return $this->user_id > 0 && $target_id > 0 && $target_id !== $this->user_id;
    }

    private function scheduleNotification(string $event, int $recipient_id): void {
        if (function_exists('schedule_task')) {
            schedule_task(
                'social_notification',
                'now',
                [
                    'event'        => $event,
                    'recipient_id' => $recipient_id,
                    'sender_id'    => $this->user_id,
                ],
                3,
                'social:'.$event.':'.$recipient_id.':'.$this->user_id
            );
        }
    }
}
