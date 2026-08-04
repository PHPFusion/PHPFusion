<?php
namespace PHPFusion;

/**
 * Class TaskDispatcher
 *
 * Dynamic, SDK-friendly task dispatcher.
 * Tasks must be registered via TaskDispatcher::register()
 */
class TaskDispatcher
{
    /**
     * Registered tasks
     * Format:
     *  'task_key' => [
     *      'handler'   => callable,
     *      'priority'  => int,
     *      'recurring' => int|null
     *  ]
     */
    protected static array $tasks = [];

    /**
     * Register a task dynamically
     *
     * @param string $taskKey
     * @param callable $handler
     * @param int $priority Higher value = higher priority
     * @param int|null $recurring Recurring interval in seconds
     */
    public static function register(string $taskKey, callable $handler, int $priority = 0, ?int $recurring = null): void
    {
        self::$tasks[$taskKey] = [
            'handler'   => $handler,
            'priority'  => $priority,
            'recurring' => $recurring
        ];
    }

    /**
     * Handle a single task
     *
     * @param string $taskKey
     * @param array $payload
     * @throws \RuntimeException
     */
    public static function handle(string $taskKey, array $payload = []): void
    {
        if (!isset(self::$tasks[$taskKey])) {
            throw new \RuntimeException("Task not registered: $taskKey");
        }

        $task = self::$tasks[$taskKey];

        try {
            call_user_func($task['handler'], $payload);
            self::log($taskKey, 'success', 'Executed successfully');
        } catch (\Throwable $e) {
            self::log($taskKey, 'error', $e->getMessage());
            throw $e;
        }
    }

    /**
     * Execute multiple tasks sorted by priority
     *
     * @param array $tasksPayload Format: ['task_key' => payloadArray]
     */
    public static function executeAll(array $tasksPayload = []): void
    {
        // Sort tasks by priority descending
        uasort(self::$tasks, fn($a, $b) => $b['priority'] <=> $a['priority']);

        foreach (self::$tasks as $taskKey => $task) {
            $payload = $tasksPayload[$taskKey] ?? [];
            try {
                self::handle($taskKey, $payload);
            } catch (\Throwable $e) {
                // Already logged in handle()
            }
        }
    }

    /**
     * Log task execution
     *
     * @param string $taskKey
     * @param string $level success|error
     * @param string $message
     */
    protected static function log(string $taskKey, string $level, string $message): void
    {
        $time = date('Y-m-d H:i:s');
        $logLine = "[$time] [$level] Task: $taskKey - $message\n";
        file_put_contents(__DIR__.'/task_dispatcher.log', $logLine, FILE_APPEND);
    }

    /**
     * Auto-load tasks from SDKs/modules
     *
     * @param string[] $paths Full paths to tasks.php files
     */
    public static function autoloadTasks(array $paths): void
    {
        foreach ($paths as $path) {
            if (is_file($path)) {
                include_once $path;
            }
        }
    }

    /**
     * Get all registered tasks with metadata
     *
     * @return array Format: ['task_key' => ['handler'=>..., 'priority'=>..., 'recurring'=>...]]
     */
    public static function all(): array
    {
        return self::$tasks;
    }

    /**
     * Get only task keys
     *
     * @return string[]
     */
    public static function keys(): array
    {
        return array_keys(self::$tasks);
    }
}
