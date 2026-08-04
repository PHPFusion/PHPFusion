<?php

namespace PHPFusion\Social;

class SocialQueueMonitor {

    public static function maintain(): void {
        $locale = SocialLocale::all();
        dbquery(
            "UPDATE ".DB_SCHEDULED_TASKS."
             SET status=IF(attempts>=max_attempts, 'failed', 'pending'),
                 last_error=CONCAT(COALESCE(last_error, ''), :recovered, NOW())
             WHERE task_key='social_notification'
               AND status='running'
               AND updated_at<:stale",
            [
                ':recovered' => "\n".$locale['SOCIAL_101'],
                ':stale'     => date('Y-m-d H:i:s', time() - 900),
            ]
        );
        dbquery(
            "DELETE FROM ".DB_SOCIAL_RATE_LIMITS." WHERE social_rate_window<:expired",
            [':expired' => time() - 86400]
        );
    }

    public static function getHealth(): array {
        $result = dbquery(
            "SELECT status, COUNT(*) AS total
             FROM ".DB_SCHEDULED_TASKS."
             WHERE task_key='social_notification'
             GROUP BY status"
        );
        $health = ['pending' => 0, 'running' => 0, 'success' => 0, 'failed' => 0, 'stale' => 0];
        while ($row = dbarray($result)) {
            $health[$row['status']] = (int) $row['total'];
        }
        $health['stale'] = (int) dbcount(
            '(id)',
            DB_SCHEDULED_TASKS,
            "task_key='social_notification' AND status IN ('pending','running')
             AND run_at<:stale",
            [':stale' => date('Y-m-d H:i:s', time() - 900)]
        );

        return $health;
    }

    public static function retryFailed(): bool {
        return dbquery(
            "UPDATE ".DB_SCHEDULED_TASKS."
             SET status='pending', attempts='0', last_error=NULL, run_at=NOW()
             WHERE task_key='social_notification' AND status='failed'"
        ) !== FALSE;
    }
}
