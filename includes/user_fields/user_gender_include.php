<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright � 2002 - 2008 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_gender_include.php
| Author: Gr@n@dE
| Homepage: www.granade.eu
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

if ($profile_method == "input") {
    $user_fields = form_checkbox('user_gender', $locale['uf_gender_001'], $field_value, ['options' =>
                                                                                             array(
                                                                                                 0 => $locale['uf_gender_004'],
                                                                                                 1 => $locale['uf_gender_002'],
                                                                                                 2 => $locale['uf_gender_003'],
                                                                                             ),
                                                                                         'type'    => 'radio',
                                                                                         'inline'  => TRUE,
    ]);

} elseif ($profile_method == "display") {

    if ($user_data['user_gender'] && ($user_data['user_gender'] == 1 || $user_data['user_gender'] == 2)) {
        $value = ($user_data['user_gender'] == 1 ? $locale['uf_gender_002'] : ($user_data['user_gender'] == 2 ? $locale['uf_gender_003'] : $locale['uf_gender_004']));
    } else {
        $value = $locale['uf_gender_004'];
    }

    $user_fields = array(
        'title' => $locale['uf_gender_001'],
        'value' => $value ?: ''
    );

}