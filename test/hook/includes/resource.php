<?php
require_once __DIR__.'/../../../maincore.php';

/**
 * Controller Class
 * Note that this file doesn't contain any functions - orange, strawberry or apple.
 * This is just a controller file for the data output that we will be able to use on the test.php page.
 */
$hook_instance_name = 'hook_test';
if (isset($_GET['test_view'])) {
    // Here we use the orange function
    fusion_add_hook($hook_instance_name, 'orange');
    // We also use a strawberry
    fusion_add_hook($hook_instance_name, 'strawberry');
} else {
    // Here we use the apple function
    fusion_add_hook($hook_instance_name, 'apple');
    fusion_add_hook($hook_instance_name, 'apple_link');
}

