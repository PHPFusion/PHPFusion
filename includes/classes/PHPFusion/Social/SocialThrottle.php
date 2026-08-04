<?php

namespace PHPFusion\Social;

class SocialThrottle {

    public static function allow(int $user_id, string $action, int $limit = 20, int $seconds = 60): bool {
        $now = time();
        $cutoff = $now - $seconds;
        dbquery(
            "INSERT INTO ".DB_SOCIAL_RATE_LIMITS."
                (social_rate_user_id, social_rate_action, social_rate_window, social_rate_attempts)
             VALUES (:user, :action, :window, 1)
             ON DUPLICATE KEY UPDATE
                social_rate_attempts=IF(social_rate_window<:cutoff_attempts, 1, social_rate_attempts+1),
                social_rate_window=IF(social_rate_window<:cutoff_window, VALUES(social_rate_window), social_rate_window)",
            [
                ':user'            => $user_id,
                ':action'          => $action,
                ':window'          => $now,
                ':cutoff_attempts' => $cutoff,
                ':cutoff_window'   => $cutoff,
            ]
        );

        $row = dbarray(dbquery(
            "SELECT social_rate_attempts
             FROM ".DB_SOCIAL_RATE_LIMITS."
             WHERE social_rate_user_id=:user AND social_rate_action=:action",
            [':user' => $user_id, ':action' => $action]
        ));

        return (int) ($row['social_rate_attempts'] ?? $limit + 1) <= $limit;
    }
}
