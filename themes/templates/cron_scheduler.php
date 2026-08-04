<?php
// Add this file to cpanel crontab
use PHPFusion\Scheduler;
use PHPFusion\TaskDispatcher;
require_once __DIR__.'/../maincore.php';

// Auto-load all tasks inside infusions/xxx/tasks.php
infusions_task_register(INFUSIONS.'/');
require_once INCLUDES.'classes/PHPFusion/Social/tasks.php';
\PHPFusion\Social\SocialQueueMonitor::maintain();

// Run all due tasks via Scheduler
Scheduler::runDueTasks(10);

/**
 * Auto-load all tasks inside every module's `tasks/` folder
 *
 * @param string $infusionsFolder Full path to INFUSIONS folder
 */
function infusions_task_register(string $infusionsFolder): void
{
    if (!is_dir($infusionsFolder)) return;

    // Scan each module folder inside INFUSIONS
    $modules = glob(rtrim($infusionsFolder, '/\\').'/*', GLOB_ONLYDIR);

    foreach ($modules as $modulePath) {
        $tasksFolder = $modulePath . '/tasks';
        if (is_dir($tasksFolder)) {
            // Include all PHP files in the tasks folder
            $taskFiles = glob($tasksFolder . '/*.php');
            foreach ($taskFiles as $file) {
                include_once $file;
            }
        }
    }
}
