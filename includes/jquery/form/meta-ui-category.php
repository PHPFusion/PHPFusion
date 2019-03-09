<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: meta-ui-category.php
| Author: Frederick MC Chan (Deviance)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

define('FUSION_ALLOW_REMOTE', true);
require_once __DIR__.'/../../../maincore.php';
require_once INCLUDES.'ajax_include.php';
// token already being used.

$response = [
    'status' => 'error',
    'data' => $_POST,
    'error_message' => '',
];
/*
 * cat_col: "wiki_cat_parent"
cat_parent: "0"
cat_title: "asdad"
custom_query: "SELECT wiki_cat_id, wiki_cat_parent, wiki_cat_name FROM fusion_wiki_cats WHERE wiki_cat_language='English' ORDER BY wiki_cat_name ASC"
db: "fusion_wiki_cats"
form_id: "ui-category"
fusion_token: "16331.1551782990.3e79395c8e749dbe6312850f3eddd65c4beb2856cad182d0f89bf28bed71dcaa"
id_col: "wiki_cat_id"
multiple: false
parent_cat_col: "wiki_cat"
parent_db: "fusion_wiki"
parent_id_col: "wiki_id"
parent_title_col: "wiki_name"
select2_disabled: true
title_col: "wiki_cat_name"
unroot: false
 */
if (
    isset($_POST['cat_title']) &&
    isset($_POST['cat_parent']) && isnum($_POST['cat_parent']) &&
    isset($_POST['cat_options']) && is_array($_POST['cat_options'])
) {
    $_category = \Defender::sanitize_array($_POST['cat_options']);

    $table_data = [
        stripinput($_category['title_col']) => stripinput($_POST['cat_title']),

        stripinput($_category['cat_col']) =>stripinput($_POST['cat_parent'])
    ];

    if (\Defender::safe() && $table_data[$_category['title_col']] && !empty($_category['db'])) {

        $new_id = dbquery_insert($_category['db'], $table_data, 'save', ['keep_session'=>TRUE]);

        $response['status'] = 'success';

        $response['data'] = $table_data;

        $_category['custom_query'] = strtr($_category['custom_query'], [
            '&#039;' => '\''
        ]);

        $response['custom_query'] = $_category['custom_query'];

        $cat_options = [];
        if ($_category['unroot'] === TRUE) {
            $cat_options[0] = "Uncategorized";
        }

        $id_col = [
            $_category['id_col'],
            $_category['cat_col'],
            $_category['title_col'],
        ];

        $sql = "SELECT `{ID}` FROM `{DB}` ORDER BY `{ORDER}`"; // we need a hierarchy ui in the UL checkboxes
        $sql = strtr($sql, [
            '`{ID}`' => implode(',' , array_filter( $id_col )),
            '`{DB}`' => $_category['db'],
            '`{ORDER}`' => $_category['title_col'].' ASC'
        ]);
        $sql = !empty($_category['custom_query']) ? $_category['custom_query'] : $sql;
        $result = dbquery( $sql );
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $cat_options[ $data[ $_category['id_col']] ] = $data [ $_category['title_col'] ] ;
            }
        }

        $checkbox = '';
        if (!empty($cat_options)) {
            // check all added
            if ($_category['multiple'] == 'true') {
                $checkbox = form_checkbox('category[]', '', '', [
                    'type'=> 'checkbox',
                    'options' => $cat_options,
                    'class' => 'm-0'
                ]);
            } else {
                // check latest added
                $checkbox = form_checkbox('category', '', '', [
                    'type'=> 'radio',
                    'options' => $cat_options,
                    'class' => 'm-0'
                ]);
            }
        }

        $response['checkbox'] = $checkbox;
        $_category['multiple'] = FALSE;
        $response['select'] = form_select('ui_cat_parent', '', '', $_category);

    }

} else {

    $response['error_message'] = 'Incorrect parameters was used';

}

echo json_encode($response);