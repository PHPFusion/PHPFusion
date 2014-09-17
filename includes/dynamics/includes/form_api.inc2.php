<?php

/*-------------------------------------------------------+
| PHP-Fusion Content Management System Version 8
| Copyright (C) 2002 - 2013 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Project File: Responsive Form Family IO
| Filename: form_api.inc.php
| Author: PHP-Fusion 8 Development Team
| Coded by : Frederick MC Chan (Hien)
| Version : 8.10.0 (please update every commit)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
/** PHP-Fusion v8 Theme Core Form API Template
 *  REV.2.1.1 (As requested by Tyler)
 *  This will enable flexibility in adding any kinds of config without adding additional parameters to form_ api.
 *  Implementation Code (Sample):
 *  ------------------------------
 *  $key = construct_array("required,placeholder,deactivate,labeloff,width");
 *  $array = construct_array("1,Placeholder,1,1,200px",$key);
 *  echo form_text("title", "input_name", "input_id", "input_value", $array);
 *  -------------------------------
 *  KEY                             VALUES              DESCRIPTION
 *  'required'                      ON = 1              If turned on, has state validation
 *  'placeholder'                   text                Place any text in the field on load.
 *  'deactivate'                    ON = 1              If turned on, will not allow user to key, but will still pass information to server as blank.
 *  'labeloff'                      ON = 1              If turned on, remove the bootstrap form-inline labelling.
 *  'width'                         number              If available, will override default width
 *  Any further suggestions to add into the Form API is appreciated! - from Hien
 */
require_once INCLUDES."output_handling_include.php"; // need to set jquery
function addHelper($id, $title, $content, $opts = FALSE) {
    /* Jquery Popover Helper Injector */
    $title   = ($title && (!empty($title))) ? "title: '$title'," : "";
    $content = ($content && (!empty($content))) ? "content: '$content'," : "";
    if (!is_array($opts)) {
        $placement = "";
    } else {
        $placement = (array_key_exists("placement", $opts) && (!empty($opts['placement']))) ? "placement: '".$opts['placement']."'" : "";
    }
    add_to_jquery("
    $('#$id').popover({ $title $content $placement }).blur(function () { $(this).popover('hide'); });
    ");
}

function close_form() {
    $html = "</div>";
    return $html;
}

function open_form_title($title = FALSE, $input_id = FALSE, $helper_text = FALSE, $required = FALSE) {
    $html = "<div class='panel panel-default'>\n";
    $html .= "<div class='panel-heading' id='$input_id-group'><label for='$input_id'>$title ".($required == 1 ? "<span class='required'>*</span>" : "")."</label></div>\n";
    $html .= "<div class='panel-body'>\n";
    return $html;
}

function open_form_title_2($title = FALSE, $input_id = FALSE, $helper_text = FALSE, $required = FALSE) {
    $label_class = (!$title) ? 'sr-only' : '';
    $title = ($title) ? $title : $input_id;
    $col_left_class = (!$title) ? '' : 'col-sm-3 col-md-3 col-lg-3 text-left';
    $col_right_class = (!$title) ? '' : 'col-sm-9 col-md-9 col-lg-9';
    $html = "<div id='$input_id-group' class='form-group'>";
    $html .= "<label for='$input_id' class='$col_left_class $label_class'>$title ".($required == 1 ? "<span class='required'>*</span>" : "")."</label>";
    $html .= "<div class='$col_right_class'>";
    return $html;
}

function close_form_title() {
    return "</div>\n</div>\n";
}

/*
function generate_token() {
    print_p('token generated');
    return "<input type='hidden' name='fusion_token' value='".generateFormToken()."' />"; // form token
}
function validate_form_token()
{
    global $token_debug;
    $token_debug = 1;
    if (!defined('FORM_TOKEN')) {
        define('FORM_TOKEN',true);
        /* Coded By Dan, JoiNNN - php-fusion.co.uk
        // Generate a unique token for forms
        // check if a form token is posted
        $token_debug = ($token_debug == '1') ? 1 : 0;
        if ($token_debug == '1') {
            echo token_debug();
        }
        if(!isset($_POST['fusion_token'])) {
            define("FUSION_NULL", true);
            echo ($token_debug == '1') ? print_p('Fusion Token Not posted. Failed!') : '';
            return FALSE;
        }
        // check if a session is started
        if(!isset($_SESSION['csrf_tokens'])) {
            define("FUSION_NULL", true);
            echo ($token_debug == '1') ? print_p('Session Token Not generated. Failed!') : '';
            return FALSE;
        }
        // check to see if the token isn't in the stored array of generated tokens

        if (!in_array($_POST['fusion_token'], $_SESSION['csrf_tokens'])) {
            define("FUSION_NULL", true);
            echo ($token_debug == '1') ? print_p('Form Token Check Failed') : '';
            return FALSE;
        } else {
            // remove the token from the array as it has been used
            foreach ($_SESSION['csrf_tokens'] as $key => $val) {
                if ($val == $_POST['fusion_token']) {
                    unset($_SESSION['csrf_tokens'][$key]);
                }
            }
        }
        echo ($token_debug == '1') ? print_p('Form Token passed') : '';
        return TRUE;
    }
}
function generateFormToken()
{
    /* Coded By Dan, JoiNNN - php-fusion.co.uk
    // Generate a unique token for forms
    global $userdata;
    // generate a new token
    $token = $userdata['user_id'].".".$userdata['user_lastvisit'].".".hash_hmac('sha1', $userdata['user_password'], uniqid($userdata['user_salt'], true));
    $_SESSION['csrf_tokens'][] = $token; // add the token to the array
    // maximum number of tokens to be stored
    if(isset($_SESSION['csrf_tokens']) && count($_SESSION['csrf_tokens']) > 10) {
        array_shift($_SESSION['csrf_tokens']); // remove element from beginning
    }
    return $token;
}
function token_debug() {
    echo '<h4>Test Results</h4>';
    echo '<p>This is the validate token, currently the $_SESSION["csrf_tokens"] posted as:</p>';
    print_p($_SESSION['csrf_tokens']);
    echo '<p>Now we have $_POST["fusion_token"] as:</p>';
    print_p($_POST['fusion_token']);
    echo '<p>So, our validate method is... <p>';
    echo '<p><code> if (!in_array($_POST["fusion_token"], $_SESSION["csrf_tokens"])) { </code></p>';
    echo '<p>Therefore...Running the above code results -- </p>';
}
*/
/*
function loadmore($db, $id_col, $rowstart, $limit, $order_col=false) {

    $order = ($order && (!empty($order))) ?   "ORDER BY $order_col ASC" : "";
    $result = dbquery("SELECT $id_col FROM ".$db." $order LIMIT $limit,$rowstart");


    $html .= "</div>";
    $html .= add_to_jquery("

    // append your jquery here directly - like $('button') or $(\"button\")


    ");
    return $html;
}

*/
?>