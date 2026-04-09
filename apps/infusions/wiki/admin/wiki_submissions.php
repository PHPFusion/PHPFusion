<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: wiki_submissions.php
| Author: RobiNN
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

$locale = fusion_get_locale();

if (isset($_GET['submit_id']) && isnum($_GET['submit_id'])) {
    if (isset($_POST['publish']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
        $result = dbquery("SELECT s.*, u.user_id, u.user_name
            FROM ".DB_SUBMISSIONS." s
            LEFT JOIN ".DB_USERS." u ON s.submit_user=u.user_id
            WHERE submit_id='".$_GET['submit_id']."'
        ");

        if (dbrows($result)) {
            $callback_data = dbarray($result);

            $callback_data = [
                'wiki_id'          => 0,
                'wiki_type'        => form_sanitizer($_POST['wiki_type'], '', 'wiki_type'),
                'wiki_name'        => form_sanitizer($_POST['wiki_name'], '', 'wiki_name'),
                'wiki_cat'         => form_sanitizer($_POST['wiki_cat'], 0, 'wiki_cat'),
                'wiki_parent'      => form_sanitizer($_POST['wiki_parent'], 0, 'wiki_parent'),
                'wiki_description' => form_sanitizer($_POST['wiki_description'], '', 'wiki_description'),
                'wiki_datestamp'   => $callback_data['submit_datestamp'],
                'wiki_order'       => form_sanitizer($_POST['wiki_order'], '', 'wiki_order'),
                'wiki_user'        => $callback_data['submit_user'],
                'wiki_status'      => form_sanitizer($_POST['wiki_status'], 0, 'wiki_status'),
                'wiki_access'      => form_sanitizer($_POST['wiki_access'], '', 'wiki_access'),
                'wiki_language'    => form_sanitizer($_POST['wiki_language'], '', 'wiki_language'),
                'wiki_hidden'      => []
            ];

            if (\defender::safe()) {
                dbquery_insert(DB_WIKI, $callback_data, 'save');
                dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".intval($_GET['submit_id'])."'");
                add_notice('success', $locale['wiki_219']);
                redirect(clean_request('', ['submit_id'], FALSE));
            }
        } else  {
            redirect(clean_request('', ['submit_id'], FALSE));
        }
    } else {
        if (isset($_POST['delete']) && (isset($_GET['submit_id']) && isnum($_GET['submit_id']))) {
            $result = dbquery("SELECT s.submit_id, s.submit_datestamp, s.submit_criteria
                FROM ".DB_SUBMISSIONS." s
                WHERE submit_type='w' and submit_id='".intval($_GET['submit_id'])."'
            ");

            if (dbrows($result) > 0) {
                $callback_data = dbarray($result);
                $delete_criteria = unserialize($callback_data['submit_criteria']);

                dbquery("DELETE FROM ".DB_SUBMISSIONS." WHERE submit_id='".intval($callback_data['submit_id'])."'");
                add_notice('success', $locale['wiki_220']);
            }
            redirect(clean_request('', ['submit_id'], FALSE));
        } else {
            $result = dbquery("SELECT s.submit_id, s.submit_datestamp, s.submit_criteria, u.user_id, u.user_name, u.user_avatar, u.user_status
                FROM ".DB_SUBMISSIONS." s
                LEFT JOIN ".DB_USERS." u ON s.submit_user=u.user_id
                WHERE submit_type='w' AND submit_id='".$_GET['submit_id']."'
            ");

            if (dbrows($result) > 0) {
                $data = dbarray($result);
                $submit_criteria = unserialize($data['submit_criteria']);

                $callback_data = [
                    'wiki_id'          => 0,
                    'wiki_type'        => '',
                    'wiki_name'        => $submit_criteria['wiki_name'],
                    'wiki_cat'         => $submit_criteria['wiki_cat'],
                    'wiki_parent'      => 0,
                    'wiki_description' => $submit_criteria['wiki_description'],
                    'wiki_datestamp'   => $data['submit_datestamp'],
                    'wiki_order'       => 0,
                    'wiki_status'      => 1,
                    'wiki_access'      => 0,
                    'wiki_language'    => LANGUAGE,
                    'wiki_hidden'      => []
                ];

                echo openform('publish_wiki', 'post', FUSION_REQUEST);
                echo '<div class="well clearfix">';
                    echo '<div class="pull-left">';
                        echo display_avatar($data, '30px', '', FALSE, 'img-rounded m-t-5 m-r-5');
                    echo '</div>';

                    echo '<div class="overflow-hide">';
                        echo $locale['wiki_052'].profile_link($data['user_id'], $data['user_name'], $data['user_status']).'<br/>';
                        echo $locale['wiki_053'].timer($data['submit_datestamp']).' - '.showdate('shortdate', $data['submit_datestamp']);
                    echo '</div>';
                echo '</div>';

                echo '<div class="row">';
                echo '<div class="col-xs-12 col-sm-8">';
                    echo form_hidden('submit_id', '', $data['submit_id']);
                    echo form_hidden('wiki_datestamp', '', $callback_data['wiki_datestamp']);

                    echo form_text('wiki_name', $locale['wiki_006'], $callback_data['wiki_name'], [
                        'inline'     => TRUE,
                        'required'   => TRUE,
                        'error_text' => $locale['wiki_100']
                    ]);

                    echo form_textarea('wiki_description', 'Article', $callback_data['wiki_description'], [
                        'path'       => IMAGES_WIKI,
                        'form_name'  => 'submit_form',
                        'type'       => 'bbcode',
                        'preview'    => TRUE,
                        'height'     => '300px',
                        'error_text' => $locale['wiki_101']
                    ]);
                echo '</div>';

                echo '<div class="col-xs-12 col-sm-4">';
                    openside();
                    echo form_select_tree('wiki_cat', $locale['wiki_010'], $callback_data['wiki_cat'], [
                        'required'     => TRUE,
                        'parent_value' => $locale['choose'],
                        'query'        => (multilang_table('WIKI') ? "WHERE ".in_group('wiki_cat_language', LANGUAGE) : ''),
                        'inline'       => TRUE,
                        'error_text'   => $locale['wiki_102']
                    ], DB_WIKI_CATS, 'wiki_cat_name', 'wiki_cat_id', 'wiki_cat_parent');

                    echo form_select_tree('wiki_parent', $locale['wiki_011'], $callback_data['wiki_parent'], [
                        'inline'  => TRUE
                    ], DB_WIKI, 'wiki_name', 'wiki_id', 'wiki_parent');

                    echo form_select('wiki_type', $locale['wiki_014'], 'page', [
                        'options' => [
                            'index' => $locale['wiki_015'],
                            'page'  => $locale['wiki_016']
                        ],
                        'inline'  => TRUE
                    ]);

                    echo form_select('wiki_status', $locale['wiki_017'], $callback_data['wiki_status'], [
                        'options'     => [0 => $locale['unpublish'], 1 => $locale['publish']],
                        'placeholder' => $locale['choose'],
                        'inline'      => TRUE
                    ]);

                    echo form_select('wiki_access', $locale['wiki_018'], $callback_data['wiki_access'], ['options' => fusion_get_groups(), 'inline' => TRUE]);

                    echo form_datepicker('wiki_datestamp', $locale['wiki_019'], $callback_data['wiki_datestamp'], ['inline' => TRUE]);

                    echo form_text('wiki_order', $locale['wiki_020'], $callback_data['wiki_order'], ['type' => 'number', 'inline' => TRUE]);

                    if (multilang_table('WIKI')) {
                        echo form_select('wiki_language', $locale['global_ML100'], $callback_data['wiki_language'], [
                            'options'     => fusion_get_enabled_languages(),
                            'placeholder' => $locale['choose'],
                            'inline'      => TRUE
                        ]);
                    } else {
                        echo form_hidden('wiki_language', '', $callback_data['wiki_language']);
                    }

                    closeside();
                echo '</div>';

                echo '</div>';

                echo form_button('publish', $locale['wiki_054'], $locale['wiki_054'], ['class' => 'btn-success m-r-10', 'icon' => 'fa fa-hdd-o']);
                echo form_button('delete', $locale['delete'], $locale['delete'], ['class' => 'btn-danger', 'icon' => 'fa fa-trash']);
                echo closeform();
            }
        }
    }
} else {
    $result = dbquery("SELECT s.submit_id, s.submit_datestamp, s.submit_criteria, u.user_id, u.user_name, u.user_avatar, u.user_status
        FROM ".DB_SUBMISSIONS." s
        LEFT JOIN ".DB_USERS." u ON s.submit_user=u.user_id
        WHERE submit_type='w'
        ORDER BY submit_datestamp DESC
    ");

    $rows = dbrows($result);

    if ($rows > 0) {
        echo '<div class="well">'.sprintf($locale['wiki_055'], format_word($rows, $locale['fmt_submission'])).'</div>';

        echo '<div class="table-responsive"><table class="table table-striped">';
            echo '<thead><tr>';
                echo '<th>'.$locale['wiki_056'].'</th>';
                echo '<th>'.$locale['wiki_057'].'</th>';
                echo '<th>'.$locale['wiki_058'].'</th>';
                echo '<th>'.$locale['wiki_059'].'</th>';
            echo '</tr></thead>';
            echo '<tbody>';
            while ($callback_data = dbarray($result)) {
                $submit_criteria = unserialize($callback_data['submit_criteria']);
                echo '<tr>';
                    echo '<td>'.$callback_data['submit_id'].'</td>';
                    echo '<td>'.display_avatar($callback_data, '20px', '', TRUE, 'img-rounded m-r-5').profile_link($callback_data['user_id'], $callback_data['user_name'], $callback_data['user_status']).'</td>';
                    echo '<td>'.timer($callback_data['submit_datestamp']).'</td>';
                    echo '<td><a href="'.clean_request('submit_id='.$callback_data['submit_id'], ['section', 'aid'], TRUE).'">'.$submit_criteria['wiki_name'].'</a></td>';
                echo '</tr>';
            }
            echo '</tbody>';
        echo '</table></div>';
    } else {
        echo '<div class="well text-center m-t-20">'.$locale['wiki_060'].'</div>';
    }
}
