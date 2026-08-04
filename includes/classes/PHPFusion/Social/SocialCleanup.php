<?php

namespace PHPFusion\Social;

class SocialCleanup {

    public static function deleteUserData(int $user_id): void {
        dbquery(
            "DELETE FROM ".DB_SOCIAL." WHERE social_user_id=:source OR social_target_id=:target",
            [':source' => $user_id, ':target' => $user_id]
        );
        dbquery(
            "DELETE FROM ".DB_SOCIAL_SETTINGS." WHERE social_settings_user_id=:user",
            [':user' => $user_id]
        );
        dbquery(
            "DELETE FROM ".DB_SOCIAL_RATE_LIMITS." WHERE social_rate_user_id=:user",
            [':user' => $user_id]
        );
        dbquery(
            "DELETE FROM ".DB_NOTIFICATIONS."
             WHERE notification_infusion='social'
               AND (notification_user_id=:recipient OR notification_sender_id=:sender)",
            [':recipient' => $user_id, ':sender' => $user_id]
        );
        dbquery(
            "DELETE FROM ".DB_SCHEDULED_TASKS."
             WHERE task_key='social_notification'
               AND (
                   JSON_UNQUOTE(JSON_EXTRACT(payload, '$.recipient_id'))=:recipient
                   OR JSON_UNQUOTE(JSON_EXTRACT(payload, '$.sender_id'))=:sender
               )",
            [':recipient' => (string) $user_id, ':sender' => (string) $user_id]
        );
    }
}
