<?php
namespace PHPFusion;

class Scheduler
{
    /**
     * Schedule a task
     */
    public static function schedule(
        string $taskKey,
        string $runAt, // Y-m-d H:i:s
        array $payload = [],
        int $maxAttempts = 3,
        ?string $dedupeKey = NULL
    ): bool {
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $result = dbquery(
            "INSERT INTO ".DB_SCHEDULED_TASKS."
                (task_key, payload, dedupe_key, run_at, max_attempts, status)
             VALUES (:task, :payload, :dedupe, :run_at, :max_attempts, 'pending')
             ON DUPLICATE KEY UPDATE
                payload=VALUES(payload),
                run_at=IF(status IN ('success','failed'), VALUES(run_at), run_at),
                attempts=IF(status IN ('success','failed'), 0, attempts),
                last_error=IF(status IN ('success','failed'), NULL, last_error),
                status=IF(status IN ('success','failed'), 'pending', status)",
            [
                ':task'         => $taskKey,
                ':payload'      => $json,
                ':dedupe'       => $dedupeKey,
                ':run_at'       => $runAt,
                ':max_attempts' => $maxAttempts,
            ]
        );

        return $result !== FALSE;
    }

    /**
     * Fetch due tasks
     */
    public static function getDueTasks(int $limit = 5): array
    {
        $result = dbquery("
            SELECT *
            FROM ".DB_SCHEDULED_TASKS."
            WHERE status = 'pending'
              AND run_at <= NOW()
            ORDER BY run_at ASC
            LIMIT ".intval($limit)."
        ");

        $tasks = [];
        while ($row = dbarray($result)) {
            $tasks[] = $row;
        }

        return $tasks;
    }

    /**
     * Mark task as running
     */
    public static function markRunning(int $id): bool
    {
        $result = dbquery(
            "UPDATE ".DB_SCHEDULED_TASKS."
             SET status='running', attempts=attempts+1
             WHERE id=:id AND status='pending'",
            [':id' => $id]
        );

        return $result !== FALSE && dbrows($result) === 1;
    }

    /**
     * Mark task success
     */
    public static function markSuccess(int $id): void
    {
        dbquery(
            "UPDATE ".DB_SCHEDULED_TASKS."
             SET status='success', executed_at=:executed
             WHERE id=:id",
            [
                ':executed' => date('Y-m-d H:i:s'),
                ':id'       => $id,
            ]
        );
    }

    /**
     * Mark task failure
     */
    public static function markFailed(int $id, string $error): void
    {
        $task = dbarray(dbquery("
            SELECT attempts, max_attempts
            FROM ".DB_SCHEDULED_TASKS."
            WHERE id = ".intval($id)."
        "));

        $status = ($task['attempts'] >= $task['max_attempts'])
            ? 'failed'
            : 'pending';

        dbquery(
            "UPDATE ".DB_SCHEDULED_TASKS."
             SET status=:status, last_error=:error
             WHERE id=:id",
            [
                ':status' => $status,
                ':error'  => $error,
                ':id'     => $id,
            ]
        );
    }

    /**
     * Run all due tasks via TaskDispatcher
     */
    public static function runDueTasks(int $limit = 5): void
    {
        $tasks = self::getDueTasks($limit);

        foreach ($tasks as $task) {
            try {
                // Only the worker that atomically claims the task may execute it.
                if (!self::markRunning($task['id'])) {
                    continue;
                }

                // Execute dynamically via TaskDispatcher
                \PHPFusion\TaskDispatcher::handle(
                    $task['task_key'],
                    json_decode($task['payload'], true) ?? []
                );

                // Mark success
                self::markSuccess($task['id']);

                // Handle recurring tasks
                $taskInfo = \PHPFusion\TaskDispatcher::all()[$task['task_key']] ?? [];
                $recurring = $taskInfo['recurring'] ?? null;
                if ($recurring) {
                    $nextRun = (new \DateTime($task['run_at']))
                        ->modify("+{$recurring} seconds");
                    self::schedule(
                        $task['task_key'],
                        $nextRun->format('Y-m-d H:i:s'),
                        json_decode($task['payload'], true),
                        (int) $task['max_attempts'],
                        $task['dedupe_key'] ?: NULL
                    );
                }

            } catch (\Throwable $e) {
                // Mark failed or pending retry
                self::markFailed($task['id'], $e->getMessage());
            }
        }
    }
}
