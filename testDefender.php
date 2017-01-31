<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: testDefender.php
| Author: 
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

// ADD MORE INPUTS FOR TESTS

// Email validation is not working
// Password validation is not working

require_once "maincore.php";
require_once THEMES."templates/header.php";

echo form_textarea("figure_accessories", 'Accessories', $data['figure_accessories'], [
    "type"      => 'tinymce',
    "tinymce"   => fusion_get_settings("tinymce_enabled") && iADMIN ? "advanced" : "simple",
    "autosize"  => true,
    "required"  => false,
    "form_name" => "inputform"
]);

// Formfield "Description"
echo form_textarea("figure_description", 'Description', $data['figure_description'], [
    "type"      => 'tinymce',
    "tinymce"   => fusion_get_settings("tinymce_enabled") && iADMIN ? "advanced" : "simple",
    "autosize"  => true,
    "required"  => false,
    "form_name" => "inputform"
]);


$country = \PHPFusion\Geomap::getCountryResource();
//print_p($country);
foreach ($country as $object) {
    print_p($object);
    $currency = \PHPFusion\Geomap::get_Currency($object->currency[0]);
    print_p($currency);
}

// Currency List
$currency = \PHPFusion\Geomap::get_Currency();
print_p($currency);


opentable('MimeCheck');
echo openform('post', 'post', BASEDIR.'testDefender.php', array('enctype'=>TRUE));
if (isset($_POST['upload_file'])) {
    $files = form_sanitizer($_FILES['files_input'], '', 'files_input');
}
echo form_fileinput('files_input[]', 'Insert Illegal Files', '', array('type'=>'files', 'max_count'=>3,         "max_byte" => 100000000000000000000500000,
    'multiple'=>TRUE, 'template'=>'modern', 'upload_path'=>IMAGES.'test/'));
echo form_button('upload_file', 'Start Upload', 'upload');
echo closeform();
closetable();


opentable("Cross Site Request Forgery Test");
$token = '';
if (isset($_POST['refresh'])) {
    // initiate hard reset
    redirect(FUSION_SELF);
}
if (isset($_POST['test_token'])) {
    $token = $_POST['fusion_token'];
    if (\defender::safe()) {
        addNotice("success", "Great, token is valid, and we saved your input and enter into our records");
    }
} else {
    if ($token) {
        addNotice("danger", "Token authentication failed");
    }
}
echo openform('token_form', 'post', FUSION_SELF, ['class' => 'well']);

if (\defender::safe()) {
    echo(!$token ? "<h4>Step 1: A new token generated.</h4>" : "<h4>Step 2: Logged Token Test (Hacker copying your token)</h4>\n");
    echo "<hr/>\n";
    if ($token && \defender::safe()) {
        echo form_text('fusion_token', 'Last Posted Fusion Token Value', $token);
        echo form_text('spam_title', 'Title', 'Nike Air with Good Adidas Cushion');
        echo form_textarea('spam', 'Spam Message', lorem_ipsum(400));
    }
    if (!$token) {
        echo form_text('spam_title', 'Title', '');
        echo form_textarea('spam_message', 'Description', '', ['bbcode' => TRUE, 'autosize' => TRUE]);
    }

    echo form_button('test_token', $token ? 'Hackathon it!' : 'Launch Test', '');
    if ($token && \defender::safe()) {
        echo "<div class='display-inline-block alert alert-warning m-l-15'>Or maybe even try F5 and see if it repost.</div>\n";
    }
} else {
    echo "<div class='alert alert-danger'>Post Fails... well try F5 reload as well, just in case.</div>\n";
    echo form_button('refresh', 'Reset Test', '');
}
echo closeform();
closetable();

opentable('Using the multilocale Quantum Fields');
$weblink_value = '';
if (isset($_POST['submit_translations'])) {
    $weblink_value = form_sanitizer($_POST['weblink_description'], '', 'weblink_description', TRUE);
    if (\defender::safe()) {
        echo 'Your Value to be saved into SQL is...';
        print_p($weblink_value);
        echo 'so in order to display your text... automatically it is';
        $value = \PHPFusion\QuantumFields::parse_label($weblink_value);

        print_p('Current language to display is '.LANGUAGE);
        echo $value;

    }
}
echo openform('testQuantum', 'post', FUSION_REQUEST);
echo \PHPFusion\QuantumFields::quantum_multilocale_fields(
    'weblink_description', 'Weblink Description', $weblink_value,
    [
        'textarea' => 1, // text
        'required' => true,
        'class' => 'm-t-10',
    ]
);
echo form_button('submit_translations', 'Submit', 'test');
echo closeform();
closetable();




opentable("Testing Inputs with Defender");
add_to_head('<style>.bootstrap-switch-container span, .bootstrap-switch-label {height:auto !important}</style>');

// Test new Send PM to a user - uncomment to test
//send_pm(1, 2, "Test PM", "This is a body message", "y");
// Test new Send PM to the entire user group
//send_pm(-101, 1, "Test PM", "This is a group message", "y", true);

// These are the defaults, they are values
// pulled from DB most of the times, and we
// assume these values are already valid.
// If they aren't, the user will be prompted
// to enter valid values upon form submission.
$settings_test = array(
    'test_error_text' => '',
    "error_text" => "",
    'text_input_required' => '',
    'text_input_safe' => '',
    'password_input' => '',
    'text_input' => '',
    'number_input' => '321',
    'checkbox_input' => 0,
    'checkbox_input2' => 0,
    'checkbox_input_bs' => 0,
    'undefined_input' => 'foo', // this input is expected but not defined in code
    //'checkbox_input3'		=> 1,
    //'checkbox_input4'		=> 1,
    //'name_input'			=> '',
    //'address_input'		=> 'Some|Address',
    'email_input' => '',
    'email_input_required' => 'valid@email.com',
    'url_input' => '',
    'regex_input' => '',
    'regex_input_required' => 'abc',
    'textarea' => '',
    'file_input' => ''
);

if (isset($_POST['submit'])) {
    // Upon sumbission we check the posted inputs against
    // default inputs and override their values.
    // If an input was not posted the default input's value
    // will be returned and also checked if valid.
    // If other inputs are posted they will simply be ingored.
    foreach ($settings_test as $key => $value) {
        // We process the inputs posted here
        if (isset($_POST[$key])) {
            // aditional input processing
            if ($key == 'some_input') {
                //$settings_test[$key] = 1;
            } else {
                $settings_test[$key] = form_sanitizer($_POST[$key], $settings_test[$key], $key);
            }
            //addNotice('info', $key." was posted, the user's input was used");
            // Here go the inputs that we expected but didn't make it
            // There can be more reasons and situations for this:
            // - INPUT NOT POSTED: if the input was defined in source code but not posted
            // the $value will checked and returned, but only if is not a checkbox in which
            // case we assume it was unchecked and int 0 is returned
            // - INPUT NOT DEFINED: if the input was not defined in source code then the $default
            // will be returned, this can be a valid value previously saved in the DB
        } else {
            $settings_test[$key] = form_sanitizer($settings_test[$key], $settings_test[$key], $key);
            //addNotice('info', $key." was NOT posted, the default value was used");
        }
    }

    if (!defined('FUSION_NULL')) {
        // Everything went as expected
        addNotice("success", "Posted successfully");

        //redirect(FUSION_SELF);
    }
}

echo openform('form', 'post', FUSION_SELF, array('max_tokens' => 5));

echo form_select('test_error_text', "Test Error Text", $settings_test['test_error_text'], array(
    'options' => array(0 => 'Produce Error', 1 => 'No Error'),
    'required' => TRUE,
    'error_text' => 'The Error Text to Show',
    'inline' => TRUE,
));

echo form_select("error_text", "Test Error", $settings_test['error_text'], array("required" => TRUE, "reverse_label" => FALSE));

echo form_text('text_input_required', 'Required text input', $settings_test['text_input_required'],
               array('required' => 1, 'tip' => 'Information', 'error_text' => 'CUSTOM ERROR: This field cannot be left empty', 'inline' => 1));

echo form_text('text_input_safe', 'Required text input in SAFEMODE', $settings_test['text_input_safe'],
               array('required' => 1, 'safemode' => 1, 'inline' => 1));

echo form_text('password_input', 'Password input', $settings_test['password_input'], array(
    'type' => 'password', 'autocomplete_off' => 1, 'required' => 1, 'error_text' => 'Ummm, please enter a valid password here', 'inline' => 1
));

echo form_text('text_input', 'Text input', $settings_test['text_input'], array('required' => 1, 'inline' => 1));

echo form_text('text_input2', 'An extra text input<br /><small>This input is not accounted for and will be ignored</small>', 'something',
               array('required' => 1, 'inline' => 1));

echo form_text('email_input', 'Email', $settings_test['email_input'], array('required' => 0, 'type' => 'email', 'inline' => 1));

echo form_text('email_input_required', 'Email required', $settings_test['email_input_required'],
               array('required' => 1, 'type' => 'email', 'inline' => 1));

echo form_text('url_input', 'URL', $settings_test['url_input'], array('type' => 'url', 'inline' => 1));

echo form_text('regex_input', 'Regex', $settings_test['regex_input'],
               array('tip' => 'Characters from A to Z only', 'regex' => '[a-z]+', 'inline' => 1));

echo form_text('regex_input_required', 'Regex required', $settings_test['regex_input_required'],
               array('required' => 1, 'tip' => 'Characters from A to Z only', 'regex' => '[a-z]+', 'inline' => 1));

echo form_text('number_input', 'Number', $settings_test['number_input'], array('required' => 1, 'type' => 'number', 'inline' => 1));

echo form_checkbox('checkbox_input', 'Checkbox', $settings_test['checkbox_input'], array('required' => 1, 'inline' => 1));

// Experimental 'child_of'
echo form_checkbox('checkbox_input2', 'Checkbox 2, child of Checkbox', $settings_test['checkbox_input2'],
                   array('child_of' => 'checkbox_input', 'inline' => 1));

echo form_checkbox('checkbox_input_bs', 'Bootstrap switch checkbox', $settings_test['checkbox_input_bs'],
                   array('toggle' => 1, 'toggle_text' => array('OFF', 'ON'), 'disabled' => 0, 'inline' => 1));
//echo form_checkbox('Checkbox 3, child of Checkbox', 'checkbox_input3', 'checkbox_input3', $settings_test['checkbox_input3'], array('child_of' => 'checkbox_input', 'inline' => 1));
//echo form_checkbox('Checkbox 4, child of Checkbox 3', 'checkbox_input4', 'checkbox_input4', $settings_test['checkbox_input4'], array('child_of' => 'checkbox_input3', 'inline' => 1));
//echo form_name('Name', 'name_input', 'name_input', $settings_test['name_input'], array('required' => 1, 'inline' => 1));
//echo form_address('Address', 'address_input', 'address_input', explode('|', $settings_test['address_input']), array('inline' => 1));

echo form_textarea('textarea', 'Text area', $settings_test['textarea'], array('autosize' => 1, 'inline' => 1));

//var_dump($_SESSION['form_fields'][$_SERVER['PHP_SELF']]);
$file_options = array(
    'upload_path' => DOWNLOADS."images/",
    'max_width' => 600,
    'max_height' => 600,
    'max_byte' => 1500000000,
    'type' => 'image',
    'required' => 0,
    'delete_original' => 0,
    'thumbnail_folder' => '',
    'thumbnail' => 1,
    'thumbnail_suffix' => '_thumb',
    'thumbnail_w' => 400,
    'thumbnail_h' => 400,
    'error_text' => 'Please select an image',
    'inline' => 1,
    'thumbnail2' => 0
);
echo form_fileinput('file_input', 'File upload', '', $file_options); // all file types.

echo form_button('submit', 'Submit', 'value', array('class' => 'btn-success'));
echo closeform();

echo "<br>These are the default and posted settings merged, which would endup being inserted in the DB:";

if (isset($settings_test)) {
    print_p($settings_test);
}

echo "<br>These are the tokens available for this form:";
if (isset($_SESSION['csrf_tokens']['form'])) {
    print_p($_SESSION['csrf_tokens']['form']);
}
echo "<hr/>\n";

echo "<h3>Serialize method</h3>\n";

$testArray = array(
    "a" => 1,
    "b" => 2,
    "c" => 3,
    "d" => addslashes(4),
    "e" => timer(time()),
    "f" => "string",
    "g" => array("1" => "a", "2" => "b", "3" => "c"),
);
// This is used in UserFieldsInput.php L490 during registration to DB_NEW_USERS
echo "<div class='well m-10'>This will be stored into SQL column. Using base64_encode, You won't be able to see value entirely</div>\n";
$info = base64_encode(serialize($testArray));
print_p($info);

// This is called back in register.php L50
echo "<div class='well m-10'>But when callback with base64_decode and unserialize</div>\n";
$info = unserialize(base64_decode($info));
print_p($info);
closetable();

echo "<h3>fusion_get_user("; ?>$user_id<?php echo ")</h3>\n";
$performance_test = 0;
$user = fusion_get_user(1);
$user = fusion_get_user(2);
$user = fusion_get_user(3);
$user = fusion_get_user(1);
$user = fusion_get_user(5);
$user = fusion_get_user(1);
print_p("Only $performance_test queries has been made so far. This way we do not need to +query count every same user query.");
print_p($user);

// Fetch user name of user_id 3. Already queried into cache before, so it will not query
print_p(fusion_get_user(3, "user_name"));
print_p("Only $performance_test queries has been made so far. See above query count and the fetch");

// Test the New Navbar - Create Multiple Sublinks
$nav = \PHPFusion\SiteLinks::setSubLinks(
    ['id'=>'FirstNav',
     'navbar_class'=>'navbar-default',
     'callback_data' => '',
     'container' => TRUE,
     'show_header' => "<a class='navbar-brand' href='".filter_input(INPUT_SERVER, 'REQUEST_URI')."'>First Nav</a>\n"
    ]);

// This is for Nav 1
$pages = [
    'wall' => 'Wall',
    'profile' => 'Profile',
    'notifications' => 'Notifications',
    'messages' => 'Messages',
    'friends' => 'Friends',
    'following' => 'Following',
    'followers' => 'Followers',
    'groups' => 'Groups'
];
foreach($pages as $page_key => $page_name) {
    $url = clean_request("ref=".$page_key, ['ref'], FALSE);
    $nav->addMenuLink($page_key, $page_name, 0, $url);
}

$nav2 = \PHPFusion\SiteLinks::setSubLinks(
    ['id'=>'SecondNav',
     'navbar_class'=>'navbar-inverse light',
     'callback_data' => '',
     'container' => TRUE,
     'show_header' => "<a class='navbar-brand' href='".filter_input(INPUT_SERVER, 'REQUEST_URI')."'>Second Nav</a>\n"
    ]);
$pages2 = [
    'store' => 'Store Items',
    'store_cart' => 'Store Cart',
    'my_store' => 'My Store',
    'your_store' => 'Your Store'
];
foreach($pages2 as $page_key => $page_name) {
    $url = clean_request("ref=".$page_key, ['ref'], FALSE);
    $nav2->addMenuLink($page_key, $page_name, 0, $url);
}

/*
 * Test calling out different set of menus in a single file.
 * Take note of the Instance Key
 */
echo "<h3>Navbar Test</h3>";
echo \PHPFusion\SiteLinks::getInstance('FirstNav')->showSubLinks();
echo \PHPFusion\SiteLinks::getInstance('SecondNav')->showSubLinks();

$nav2->addMenuLink('alt', 'Last Minute Addition', 0, '#');
echo $nav2->showSubLinks(); // $nav2 is equivalent to `\PHPFusion\SiteLinks::getInstance();` (Object) and so you can use arrow on it.

$result = dbquery("SELECT settings_name FROM ".DB_SETTINGS);
print_p(dbresult($result, 3));// outputs the third output

require_once THEMES."templates/footer.php";