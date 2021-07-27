<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: notify.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * Renders notices
 *
 * @param array $notices The array contaning notices.
 *
 * @return string The notices formatted as HTML.
 */
function rendernotices($notices) {
    $messages = "";

    foreach ($notices as $status => $notice) {
        if ($status == "success") {
            // Success messages can be auto closed
            $messages .= "<div id='close-message'>\n";
        }
        $messages .= "<div class='admin-message alert alert-".$status." alert-dismissible' role='alert'>";
        $messages .= "<button type='button' class='close' data-dismiss='alert'><span aria-hidden='true'>&times;</span></button>";
        $messages .= implode("<br/>", $notice);
        $messages .= "</div>\n";
        if ($status == "success") {
            $messages .= "</div>\n";
        }
    }

    return $messages;
}

/**
 * Checks whether a group identified by the key provided has any notices
 *
 * @param string $key The key identifying a group or more holding notices, by default the page name in which the notice was set
 *
 * @return bool Ture if the group has any notices.
 */
function hasnotice($key = FUSION_SELF) {
    if (!empty($_SESSION['notices'])) {
        if ((isset($_SESSION['notices']['once'][$key]) && !empty($_SESSION['notices']['once'][$key])) ||
            (isset($_SESSION['notices']['persist'][$key]) && !empty($_SESSION['notices']['persist'][$key]))
        ) {
            return TRUE;
        }
    }

    return FALSE;
}

/**
 * Retrievs all notices for the group identified by the key provided/
 *
 * @param string|array $key    The key(s) identifying a group or more holding notices, by default the page name in which the notice was set.
 * @param bool         $delete Whether to delete or keep a notice message after it was accessed.
 *                             This only works if the notice was set or added while having $remove_after_access set to false
 *
 * @return array The notices for the group identified by the provided key.
 */
function getnotices($key = FUSION_SELF, $delete = TRUE) {
    $key = is_array($key) ? $key : [$key]; // key can be arrays or a string
    $notices = [];
    if (!empty($_SESSION['notices'])) {
        foreach ($_SESSION['notices'] as $type => $keys) {
            foreach ($key as $thiskey) {
                if (isset($keys[$thiskey])) {
                    $notices = array_merge_recursive($notices, $keys[$thiskey]);
                    if (!fusion_get_settings('site_seo') && !defined('IN_PERMALINK')) {
                        if ($delete) {
                            $_SESSION['notices'][$type][$thiskey] = [];
                        }
                    }
                }
            }
        }
    }

    return $notices;
}

/**
 * Remove notice
 *
 * @param string|array $key The key(s) identifying a group or more holding notices.
 */
function remove_notice($key = ['all', FUSION_SELF, FUSION_REQUEST]) {
    $key = is_array($key) ? $key : [$key]; // key can be arrays or a string
    if (!empty($_SESSION['notices'])) {
        foreach ($_SESSION['notices'] as $type => $keys) {
            foreach ($key as $thiskey) {
                if (isset($keys[$thiskey]) && !empty($_SESSION['notices'][$type][$thiskey])) {
                    $_SESSION['notices'][$type][$thiskey] = [];
                }
            }
        }
    }
}

/**
 * Adds a notice message to the group identified by the key provided
 *
 * @param string  $status              The status of the message.
 * @param string  $value               The message.
 * @param string  $key                 The key identifying a group holding notices, by default the page name in which the notice was set.
 * @param boolean $remove_after_access Whether the notice should be automatically removed after it was displayed once,
 *                                     if set to false when getnotices() is called you have the option to keep the notice even after it was accesed.
 */
function addnotice($status, $value, $key = FUSION_SELF, $remove_after_access = TRUE) {
    $type = $remove_after_access ? 'once' : 'persist';
    if (is_array($value)) {
        $return = "<ol style='list-style: decimal;'>\n";
        foreach ($value as $text) {
            $return .= "<li>".$text."</li>";
        }
        $return .= "</ol>\n";
        $value = $return;
    }

    if (!isset($_SESSION['notices'][$type][$key][$status])) {
        $_SESSION['notices'][$type][$key][$status] = [];
    }
    if (array_search($value, $_SESSION['notices'][$type][$key][$status]) === FALSE) {
        $_SESSION['notices'][$type][$key][$status][] = $value;
        //die(print_p($_SESSION['notices']));
    }
}

/**
 * Sets a notice message for the whole group identified by the key provided, this will overwrite any other notices previously set
 *
 * @param string  $status              The status of the message.
 * @param string  $value               The message.
 * @param string  $key                 The key identifying a group holding notices, by default the page name in which the notice was set.
 * @param boolean $remove_after_access Whether the notice should be automatically removed after it was displayed once.
 *                                     If set to false when getnotices() is called you have the option to keep the notice even after it was accesed.
 */
function setnotice($status, $value, $key = FUSION_SELF, $remove_after_access = TRUE) {
    $type = $remove_after_access ? 'once' : 'persist';
    $_SESSION['notices'][$type][$key] = [$status => [$value]];
}
