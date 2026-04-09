<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: wiki_submit.php
| Author: RobiNN
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

$locale = fusion_get_locale();
$wiki_settings = get_settings('wiki');

add_to_title($locale['global_200'].$locale['wiki_submit']);

opentable('<i class="fa fa-wikipedia-w fa-lg fa-fw"></i>'.$locale['wiki_submit']);

if (iMEMBER && $wiki_settings['wiki_allow_submission']) {
    $criteria_array = [
        'wiki_name'        => '',
        'wiki_cat'         => 0,
        'wiki_description' => ''
    ];

    if (isset($_POST['submit_docs'])) {
        $criteria_array = [
            'wiki_name'        => form_sanitizer($_POST['wiki_name'], '', 'wiki_name'),
            'wiki_cat'         => form_sanitizer($_POST['wiki_cat'], 0, 'wiki_cat'),
            'wiki_description' => form_sanitizer($_POST['wiki_description'], '', 'wiki_description')
        ];

        if (defender::safe()) {
            $input_array = [
                'submit_type'      => 'w',
                'submit_user'      => fusion_get_userdata('user_id'),
                'submit_datestamp' => time(),
                'submit_criteria'  => serialize($criteria_array)
            ];

            dbquery_insert(DB_SUBMISSIONS, $input_array, 'save');
            add_notice('success', $locale['wiki_221']);
            redirect(clean_request('submitted=w', ['stype'], TRUE));
        }
    }

    if (isset($_GET['submitted']) && $_GET['submitted'] == 'w') {
        echo '<div class="well text-center">';
            echo '<p><strong>'.$locale['wiki_221'].'</strong></p>';
            echo '<p><a href="'.BASEDIR.'submit.php?stype=w">'.$locale['wiki_061'].'</a></p>';
            echo '<p><a href="'.BASEDIR.'index.php">'.str_replace('[SITENAME]', fusion_get_settings('sitename'), $locale['wiki_062']).'</a></p>';
        echo '</div>';
    } else {
        if (dbcount("(wiki_cat_id)", DB_WIKI_CATS, multilang_table('WIKI') ? in_group('wiki_cat_language', LANGUAGE) : '')) {
            echo openform('submit_form', 'post', BASEDIR.'submit.php?stype=w');

            echo '<div class="alert alert-info m-b-20 submission-guidelines">'.str_replace('[SITENAME]', fusion_get_settings('sitename'), $locale['wiki_063']).'</div>';

            echo form_text('wiki_name', $locale['wiki_006'], $criteria_array['wiki_name'], [
                'inline'     => TRUE,
                'required'   => TRUE,
                'error_text' => $locale['wiki_100']
            ]);

            echo form_select_tree('wiki_cat', $locale['wiki_010'], $criteria_array['wiki_cat'], [
                'required'     => TRUE,
                'parent_value' => $locale['choose'],
                'query'        => (multilang_table('WIKI') ? "WHERE ".in_group('wiki_cat_language', LANGUAGE) : ''),
                'inline'       => TRUE,
                'error_text'   => $locale['wiki_102']
            ], DB_WIKI_CATS, 'wiki_cat_name', 'wiki_cat_id', 'wiki_cat_parent');

            echo form_textarea('wiki_description', $locale['wiki_008'], $criteria_array['wiki_description'], [
                'path'       => IMAGES_WIKI,
                'form_name'  => 'submit_form',
                'type'       => 'bbcode',
                'preview'    => TRUE,
                'height'     => '300px',
                'error_text' => $locale['wiki_101']
            ]);

            echo form_button('submit_docs', $locale['wiki_064'], $locale['wiki_064'], ['class' => 'btn-success', 'icon' => 'fa fa-hdd-o']);

            echo closeform();
        } else {
            echo '<div class="well text-center">'.$locale['wiki_039'].'</div>';
        }
    }
} else {
    echo '<div class="well text-center">'.$locale['wiki_065'].'</div>';
}

closetable();
