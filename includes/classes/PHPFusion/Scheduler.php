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
		int $maxAttempts = 3
	): bool {
		
		return dbquery_insert(
			DB_SCHEDULED_TASKS,
			[
				'task_key'     => $taskKey,
				'payload'      => json_encode($payload),
				'run_at'       => $runAt,
				'max_attempts' => $maxAttempts,
				'status'       => 'pending'
			],
			'save'
		);
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
	public static function markRunning(int $id): void
	{
		dbquery_update(
			DB_SCHEDULED_TASKS,
			[
				'status'   => 'running',
				'attempts' => 'attempts + 1'
			],
			"id = ".intval($id),
			false
		);
	}
	
	/**
	 * Mark task success
	 */
	public static function markSuccess(int $id): void
	{
		dbquery_update(
			DB_SCHEDULED_TASKS,
			[
				'status'      => 'success',
				'executed_at' => date('Y-m-d H:i:s')
			],
			"id = ".intval($id)
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
		
		dbquery_update(
			DB_SCHEDULED_TASKS,
			[
				'status'     => $status,
				'last_error' => $error
			],
			"id = ".intval($id)
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
				// Mark task running
				self::markRunning($task['id']);
				
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
						json_decode($task['payload'], true)
					);
				}
				
			} catch (\Throwable $e) {
				// Mark failed or pending retry
				self::markFailed($task['id'], $e->getMessage());
			}
		}
	}
}