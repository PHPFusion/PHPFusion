<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_blacklist_include.php
| Author: Chan (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

if (!function_exists('show_blacklist')) {
    function show_blacklist($data) {
        global $locale;
        echo "<div class='alert alert-info display-none' id='ignore-message'></div>\n";
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $id) {
                $result = dbquery("SELECT user_id, user_name, user_status, user_avatar FROM ".DB_USERS." WHERE user_id='$id' ORDER BY user_id ASC");
                if (dbrows($result) > 0) {
                    while ($data = dbarray($result)) {
                        echo "<div id='".$data['user_id']."-user-list' class='panel panel-default'>\n<div class='panel-body'>\n";
                        echo "<button type='button' value='".$data['user_id']."' class='unblock pull-right m-t-5 btn btn-sm btn-primary'>".$locale['uf_blacklist_001']."</button>\n";
                        echo "<div class='pull-left m-r-10'>".display_avatar($data, '50px')."</div>\n";
                        echo "<div class='clearfix'>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."<br/>\n";
                        echo "<span class='text-lighter'>".$locale['uf_blacklist_002']."</span>\n";
                        echo "</div>\n";
                        echo "</div>\n</div>\n";
                    }
                }
            }
        }

        add_to_jquery("
            $('.unblock').bind('click', function(e) {
            var user_id = $(this).val();
            $.ajax({
                type: 'POST',
                url: '".INCLUDES."user_fields/user_blacklist.ajax.php',
                data: { user_id : user_id },
                dataType: 'html',
                success: function(data) {
                    alert(data);
                    $('#'+user_id+'-user-list').addClass('display-none');
                    $('#ignore-message').html(data).removeClass('display-none');
                },
                error: function() {
                    alert('".$locale['uf_blacklist_desc']."');
                }
                });
            });
        ");
    }
}

// Display user field input
if ($profile_method == "input") {
    if (isset($user_data['user_blacklist']) && $user_data['user_blacklist']) {
        $user_blacklist = $user_data['user_blacklist'];
    } else {
        $user_blacklist = "";
    }

    // Display in profile
} else if ($profile_method == "display") {
    //Are we not missing something here?
} else if ($profile_method == "validate_insert" || $profile_method == "validate_update") {
    $user_blacklist = '';
    $userdata = $this->userData;
    $userdata_blacklist = isset($userdata) && array_key_exists('user_blacklist', $userdata) ? explode('.', $userdata['user_blacklist']) : [];
    if (count($userdata_blacklist) && isset($_POST['user_blacklist']) && isnum($_POST['user_blacklist']) && !in_array($_POST['user_blacklist'],
            $userdata_blacklist)
    ) {
        $userdata_blacklist[] = $_POST['user_blacklist'];
        $userdata_blacklist = implode('.', $userdata_blacklist);
    } else if (isset($_POST['user_blacklist']) && isnum($_POST['user_blacklist'])) {
        $userdata_blacklist = $userdata['user_blacklist'];
    }
    if ($userdata_blacklist != 0 || $this->_isNotRequired("user_blacklist")) {
        $this->_setDBValue("user_blacklist", $userdata_blacklist);
    } else {
        $this->_setError("user_blacklist", $locale['uf_blacklist_error'], TRUE);
    }
}
