<?php
require_once __DIR__.'/../../maincore.php';
require_once THEMES.'templates/header.php';

/**
 * Hooks test
 * The implementation is that with the functions declared, you can execute the functions somewhat remotely using hooks API.
 * The following is the test for hooks implementation.
 * We will divide the procedure with 3 files.
 * a. Index.php     -   Current View Page
 * b. Resource.php  -   The logic controller
 * c. Hooks.php     -   The model for source of data
 */
// The bigger implementation is that with functions declared, you can execute it using hooks API.
// This is a test scenario for the hooks, let's assume we have a controller code.
// In this test case, our hook can be grouped under hook_test ID.
$hook_instance_name = 'hook_test';
// Here we declare the functions the hooks resource will use.
require_once __DIR__.'/includes/hooks.php';

// Now we need to add hook, that is to ask the system to run these functions prior to running the hook output calculations.
require_once __DIR__.'/includes/resource.php';

$output = fusion_filter_hook($hook_instance_name);
echo "<h1>Hook API Test</h1>";
//print_p($output);
if (!empty($output)) {
    foreach($output as $value) {
        echo $value;
    }
}

/**
 * Advanced Test Demo
 * -------------------
 * In practical development of PHP-Fusion CMS, it's plugin, it's applications.
 * You can run plugin using hooks, with codes such as this.
 *
 * This is the code i use to 'populate' all infusions notices on Fusion Theme on phpfusion.com
 * I do not want to keep the infusions's files under the theme, and had it coded a notices.php in each directory that contain hook functions.
 * The below is how I do it.
 * /

 /*
 * Run the notification hooks
 * aggregate hooks from all hooks
 */
function fusion_add_user_notices() {
    $multiple_hooks = [
        INFUSIONS.'some_other_app/notices.php',
        INFUSIONS.'some_other_app_2/notices.php',
    ];
    foreach($multiple_hooks as $files) {
        if (is_file($files)) require_once $files; // trigger more fusion_add_hook within these files.
    }

    // Sample code of one of the files.
    // Lets assume this is the third file.
    function add_marketplace_notice() {
        // some marketplace code.
        $array['mp_notices'] = [
            'link_id' => 'mp_notices',
            'link_name' => "<div class='list'>You have notices</div>",
        ];
        return $array;
    }
    fusion_add_hook('user_notices', 'add_marketplace_notice');
    // end of third file.
}

fusion_add_hook('user_notices', 'fusion_add_user_notices'); // Note that when adding a hook, it does not mean the function is being called. You can add any amount of hook into the system, valid or invalid.
fusion_add_hook('user_notices', 'some_invalid_function'); // Don't worry, if the hook can't find the function, it will just omit it, advantage?
// Now the system could have more than 20 registered hooks.

// Either do this to run the hook
fusion_apply_hook('user_notices');
// or if you need an output, or array. Note: uncomment fusion_apply_hook above to get the $output.
//$output = fusion_filter_hook('user_notices');
//print_p($output);

// End of Hook development.
// Speed affected? Depending on your hook implementations.

require_once THEMES.'templates/footer.php';
