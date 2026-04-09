<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: wiki_cats.php
| Author: RobiNN
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

$locale = fusion_get_locale();

$data = [
    'wiki_cat_id'          => 0,
    'wiki_cat_name'        => '',
    'wiki_cat_parent'      => 0,
    'wiki_cat_description' => '',
    'wiki_cat_status'      => 1,
    'wiki_cat_access'      => 0,
    'wiki_cat_order'       => dbcount("(wiki_cat_id)", DB_WIKI_CATS, multilang_table('WIKI') ? in_group('wiki_cat_language', LANGUAGE) : '') + 1,
    'wiki_cat_language'    => LANGUAGE,
    'wiki_cat_hidden'      => []
];

if (isset($_POST['cancel'])) {
    redirect(FUSION_SELF.fusion_get_aidlink().'&section=categories');
}

if ((isset($_GET['action']) && $_GET['action'] == 'delete') && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
    if (dbcount("(wiki_cat)", DB_WIKI, "wiki_cat='".intval($_GET['cat_id'])."'")
        || dbcount("(wiki_cat_id)", DB_WIKI_CATS, "wiki_cat_parent='".intval($_GET['cat_id'])."'")
    ) {
        add_notice('danger', $locale['wiki_206']);
        redirect(clean_request('', ['ref', 'action', 'cat_id'], TRUE));
    } else {
        dbquery("DELETE FROM ".DB_WIKI_CATS." WHERE wiki_cat_id='".intval($_GET['cat_id'])."'");
        add_notice('success', $locale['wiki_207']);
        redirect(clean_request('', ['ref', 'action', 'cat_id'], TRUE));
    }
}

if (isset($_POST['save_cat']) || isset($_POST['save_and_close'])) {
    $data = [
        'wiki_cat_id'          => form_sanitizer($_POST['wiki_cat_id'], 0, 'wiki_cat_id'),
        'wiki_cat_parent'      => form_sanitizer($_POST['wiki_cat_parent'], 0, 'wiki_cat_parent'),
        'wiki_cat_name'        => form_sanitizer($_POST['wiki_cat_name'], '', 'wiki_cat_name'),
        'wiki_cat_description' => form_sanitizer($_POST['wiki_cat_description'], '', 'wiki_cat_description'),
        'wiki_cat_status'      => form_sanitizer($_POST['wiki_cat_status'], 0, 'wiki_cat_status'),
        'wiki_cat_access'      => form_sanitizer($_POST['wiki_cat_access'], '', 'wiki_cat_access'),
        'wiki_cat_language'    => form_sanitizer($_POST['wiki_cat_language'], '', 'wiki_cat_language'),
        'wiki_cat_order'       => form_sanitizer($_POST['wiki_cat_order'], 0, 'wiki_cat_order'),
        'wiki_cat_hidden'      => []
    ];

    if (empty($data['wiki_cat_order'])) {
        $data['wiki_cat_order'] = dbresult(dbquery("SELECT MAX(wiki_cat_order) FROM ".DB_WIKI_CATS." ".(multilang_table('WIKI') ? "WHERE ".in_group('wiki_cat_language', LANGUAGE) : '')), 0) + 1;
    }

    $category_name_check = [
        'when_updating' => "wiki_cat_name='".$data['wiki_cat_name']."' AND wiki_cat_id !='".$data['wiki_cat_id']."'",
        'when_saving'   => "wiki_cat_name='".$data['wiki_cat_name']."'",
    ];

    if (dbcount("(wiki_cat_id)", DB_WIKI_CATS, "wiki_cat_id='".$data['wiki_cat_id']."'")) {
        if (!dbcount("(wiki_cat_id)", DB_WIKI_CATS, $category_name_check['when_updating'])) {
            if (\defender::safe()) {
                dbquery_order(DB_WIKI_CATS, $data['wiki_cat_order'], 'wiki_cat_order', $data['wiki_cat_id'], 'wiki_cat_id', $data['wiki_cat_parent'], 'wiki_cat_parent', TRUE, 'wiki_cat_language', 'update');
                dbquery_insert(DB_WIKI_CATS, $data, 'update');
                add_notice('success', $locale['wiki_208']);
            }
        } else {
            \defender::stop();
            add_notice('danger', $locale['wiki_209']);
        }
    } else {
        if (!dbcount("(wiki_cat_id)", DB_WIKI_CATS, $category_name_check['when_saving'])) {
            if (\defender::safe()) {
                dbquery_order(DB_WIKI_CATS, $data['wiki_cat_order'], 'wiki_cat_order', 0, 'wiki_cat_id', $data['wiki_cat_parent'], 'wiki_cat_parent', TRUE, 'wiki_cat_language', 'save');
                dbquery_insert(DB_WIKI_CATS, $data, 'save');
                add_notice('success', $locale['wiki_210']);
            }
        } else {
            \defender::stop();
            add_notice('danger', $locale['wiki_209']);
        }
    }

    if (isset($_POST['save_and_close'])) {
        redirect(clean_request('', ['ref', 'action', 'cat_id'], FALSE));
    } else {
        redirect(FUSION_REQUEST);
    }
}

if ((isset($_GET['action']) && $_GET['action'] == 'edit') && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
    $result = dbquery("SELECT * FROM ".DB_WIKI_CATS." ".(multilang_table('WIKI') ? "WHERE ".in_group('wiki_cat_language', LANGUAGE)." AND" : "WHERE")." wiki_cat_id='".$_GET['cat_id']."'");

    if (dbrows($result)) {
        $data = dbarray($result);
        $data['wiki_cat_hidden'] = [$data['wiki_cat_id']];
    } else {
        redirect(clean_request('', ['ref', 'action', 'cat_id'], TRUE));
    }
}

if (isset($_GET['ref']) && $_GET['ref'] == 'wiki_cat_form') {
    echo openform('wiki_cats', 'post', FUSION_REQUEST);
    echo form_hidden('wiki_cat_id', '', $data['wiki_cat_id']);
    echo form_hidden('wiki_cat_order', '', $data['wiki_cat_order']);

    echo '<div class="row">';
    echo '<div class="col-xs-12 col-sm-8">';
    echo form_text('wiki_cat_name', $locale['wiki_006'], $data['wiki_cat_name'], [
        'required'   => TRUE,
        'inline'     => TRUE,
        'error_text' => $locale['wiki_103']
    ]);
    echo form_select_tree('wiki_cat_parent', $locale['wiki_011'], $data['wiki_cat_parent'], [
        'disable_opts'  => $data['wiki_cat_hidden'],
        'hide_disabled' => TRUE,
        'width'         => '100%',
        'inline'        => TRUE
    ], DB_WIKI_CATS, 'wiki_cat_name', 'wiki_cat_id', 'wiki_cat_parent');
    echo form_textarea('wiki_cat_description', $locale['wiki_009'], $data['wiki_cat_description'], [
        'required' => TRUE,
        'resize'   => 0,
        'autosize' => TRUE,
        'type'     => 'bbcode'
    ]);
    echo '</div>';

    echo '<div class="col-xs-12 col-sm-4">';
    openside();
    echo form_select('wiki_cat_status', $locale['wiki_017'], $data['wiki_cat_status'], [
        'options'     => [0 => $locale['unpublish'], 1 => $locale['publish']],
        'placeholder' => $locale['choose'],
        'inline'      => TRUE,
        'width'       => '100%'
    ]);

    echo form_select('wiki_cat_access', $locale['wiki_018'], $data['wiki_cat_access'], [
        'options' => fusion_get_groups(),
        'inline'  => TRUE,
        'width'   => '100%'
    ]);

    if (multilang_table('WIKI')) {
        echo form_select('wiki_cat_language[]', $locale['global_ML100'], $data['wiki_cat_language'], [
            'options'     => fusion_get_enabled_languages(),
            'placeholder' => $locale['choose'],
            'inline'      => TRUE,
            'width'       => '100%',
            'multiple'    => TRUE
        ]);
    } else {
        echo form_hidden('wiki_cat_language', '', $data['wiki_cat_language']);
    }

    echo form_text('wiki_cat_order', $locale['wiki_020'], $data['wiki_cat_order'], [
        'inline' => TRUE,
        'type'   => 'number'
    ]);
    closeside();
    echo '</div>';

    echo '</div>';

    echo form_button('cancel', $locale['cancel'], $locale['cancel'], ['class' => 'btn-sm btn-default', 'icon' => 'fa fa-fw fa-times']);
    echo form_button('save_cat', $locale['save'], $locale['save'], ['class' => 'btn-sm btn-success m-l-5', 'icon' => 'fa fa-fw fa-hdd-o']);
    echo form_button('save_and_close', $locale['save_and_close'], $locale['save_and_close'], ['class' => 'btn-sm btn-primary m-l-5', 'icon' => 'fa fa-floppy-o']);
    echo closeform();
} else {
    $allowed_actions = array_flip(['publish', 'unpublish', 'delete']);

    if (isset($_POST['table_action']) && isset($allowed_actions[$_POST['table_action']])) {
        $input = !empty($_POST['wiki_cat_id']) ? form_sanitizer($_POST['wiki_cat_id'], '', 'wiki_cat_id') : '';

        if (!empty($input)) {
            $input = ($input ? explode(',', $input) : []);
            foreach ($input as $wiki_cat_id) {
                if (dbcount("('wiki_cat_id')", DB_WIKI_CATS, "wiki_cat_id=:wiki_cat", [':wiki_cat' => intval($wiki_cat_id)]) && \defender::safe()) {
                    switch ($_POST['table_action']) {
                        case 'publish':
                            dbquery("UPDATE ".DB_WIKI_CATS." SET wiki_cat_status=:cat_status WHERE wiki_cat_id=:cat_id", [':cat_status' => '1', ':cat_id' => intval($wiki_cat_id)]);
                            break;
                        case 'unpublish':
                            dbquery("UPDATE ".DB_WIKI_CATS." SET wiki_cat_status=:cat_status WHERE wiki_cat_id=:cat_id", [':cat_status' => '0', ':cat_id' => intval($wiki_cat_id)]);
                            break;
                        case 'delete':
                            if (!dbcount("(wiki_id)", DB_WIKI, "wiki_cat=:wiki_cat", [':wiki_cat' => $wiki_cat_id]) && !dbcount("(wiki_cat_id)", DB_WIKI_CATS, "wiki_cat_parent=:catparent", [':catparent' => $wiki_cat_id])) {
                                dbquery("DELETE FROM  ".DB_WIKI_CATS." WHERE wiki_cat_id=:wiki_cat_id", [':wiki_cat_id' => intval($wiki_cat_id)]);
                            } else {
                                add_notice('warning', $locale['wiki_211']);
                                add_notice('warning', $locale['wiki_212']);
                            }
                            break;
                        default:
                            redirect(clean_request('', ['action', 'ref'], FALSE));
                    }
                }
            }

            add_notice('success', $locale['wiki_208']);
            redirect(FUSION_REQUEST);
        } else {
            add_notice('warning', $locale['wiki_213']);
            redirect(FUSION_REQUEST);
        }
    }

    if (isset($_POST['wiki_clear'])) {
        redirect(FUSION_SELF.fusion_get_aidlink()."&section=categories");
    }

    $sql_condition = multilang_table('WIKI') ? in_group('wc.wiki_cat_language', LANGUAGE) : '';
    $search_string = [];
    if (isset($_POST['p-submit-wiki_cat_name'])) {
        $search_string['wiki_cat_name'] = [
            'input'    => form_sanitizer($_POST['wiki_cat_name'], '', 'wiki_cat_name'),
            'operator' => 'LIKE'
        ];
    }

    if (!empty($_POST['wiki_cat_status']) && isnum($_POST['wiki_cat_status'])) {
        switch ($_POST['wiki_cat_status']) {
            case 1: // published
                $search_string['wiki_cat_status'] = [
                    'input'    => 1,
                    'operator' => '='
                ];
                break;
            case 2: // unpublished
                $search_string['wiki_cat_status'] = [
                    'input'    => 0,
                    'operator' => '='
                ];
                break;
        }
    }

    if (!empty($_POST['wiki_cat_access'])) {
        $search_string['wiki_cat_access'] = [
            'input'    => form_sanitizer($_POST['wiki_cat_access'], '', 'wiki_cat_access'),
            'operator' => "="
        ];
    }

    if (!empty($_POST['wiki_cat_language'])) {
        $search_string['wiki_cat_language'] = [
            'input'    => form_sanitizer($_POST['wiki_cat_language'], '', 'wiki_cat_language'),
            'operator' => "="
        ];
    }

    if (!empty($search_string)) {
        foreach ($search_string as $key => $values) {
            if ($sql_condition) {
                $sql_condition .= " AND ";
            }
            $sql_condition .= "`$key` ".$values['operator'].($values['operator'] == "LIKE" ? "'%" : "'").$values['input'].($values['operator'] == "LIKE" ? "%'" : "'");
        }
    }

    $result = dbquery_tree_full(DB_WIKI_CATS, 'wiki_cat_id', 'wiki_cat_parent', '', "
        SELECT wc.*, COUNT(w.wiki_id) AS wiki_count
        FROM ".DB_WIKI_CATS." wc
        LEFT JOIN ".DB_WIKI." AS w ON w.wiki_cat=wc.wiki_cat_id
        ".($sql_condition ? " WHERE ".$sql_condition : "")."
        GROUP BY wc.wiki_cat_id
        ORDER BY wc.wiki_cat_order ASC
    ");

    $filter_values = [
        'wiki_cat_name'     => !empty($_POST['wiki_cat_name']) ? form_sanitizer($_POST['wiki_cat_name'], '', 'wiki_cat_name') : '',
        'wiki_cat_status'   => !empty($_POST['wiki_cat_status']) ? form_sanitizer($_POST['wiki_cat_status'], '', 'wiki_cat_status') : '',
        'wiki_cat_access'   => !empty($_POST['wiki_cat_access']) ? form_sanitizer($_POST['wiki_cat_access'], '', 'wiki_cat_access') : '',
        'wiki_cat_language' => !empty($_POST['wiki_cat_language']) ? form_sanitizer($_POST['wiki_cat_language'], '', 'wiki_cat_language') : ''
    ];

    $filter_empty = TRUE;
    foreach ($filter_values as $val) {
        if ($val) {
            $filter_empty = FALSE;
        }
    }

    echo '<div class="m-t-15">';
        echo openform('wiki_filter', 'post', FUSION_REQUEST);
        echo '<div class="clearfix">';
            echo '<div class="pull-right">';
                echo '<a class="btn btn-success btn-sm" href="'.clean_request('ref=wiki_cat_form', ['ref'], FALSE).'"><i class="fa fa-fw fa-plus"></i> '.$locale['wiki_037'].'</a>';
                echo '<button type="button" class="hidden-xs btn btn-default btn-sm m-l-5" onclick="run_admin(\'publish\', \'#table_action\', \'#wiki_table\');"><i class="fa fa-fw fa-check"></i> '.$locale['publish'].'</button>';
                echo '<button type="button" class="hidden-xs btn btn-default btn-sm m-l-5" onclick="run_admin(\'unpublish\', \'#table_action\', \'#wiki_table\');"><i class="fa fa-fw fa-ban"></i> '.$locale['unpublish'].'</button>';
                echo '<button type="button" class="hidden-xs btn btn-danger btn-sm m-l-5" onclick="run_admin(\'delete\', \'#table_action\', \'#wiki_table\');"><i class="fa fa-fw fa-trash-o"></i> '.$locale['delete'].'</button>';
            echo '</div>';

            echo '<div class="display-inline-block pull-left m-r-10">';
                echo form_text('wiki_cat_name', '', $filter_values['wiki_cat_name'], [
                    'placeholder'       => $locale['search'],
                    'append_button'     => TRUE,
                    'append_value'      => "<i class='fa fa-fw fa-search'></i>",
                    'append_form_value' => 'search_wiki',
                    'width'             => '160px',
                    'group_size'        => 'sm'
                ]);
            echo '</div>';

            echo '<div class="display-inline-block hidden-xs">';
                echo '<a class="btn btn-sm m-r-5 '.(!$filter_empty ? 'btn-info' : 'btn-default').'" id="toggle_options" href="#">'.$locale['search'].' <span id="filter_caret" class="fa '.(!$filter_empty ? 'fa-caret-up' : 'fa-caret-down').'"></span></a>';
                echo form_button('wiki_clear', $locale['wiki_022'], 'clear', ['class' => 'btn-default btn-sm']);
            echo '</div>';
        echo '</div>';

        echo '<div id="wiki_filter_options"'.($filter_empty ? ' style="display: none;"' : '').'>';
            echo '<div class="display-inline-block">';
                echo form_select('wiki_cat_status', '', $filter_values['wiki_cat_status'], [
                    'allowclear'  => TRUE,
                    'placeholder' => '- '.$locale['wiki_023'].' -',
                    'options'     => [
                        0 => $locale['wiki_024'],
                        2 => $locale['unpublish'],
                        1 => $locale['publish']
                    ]
                ]);
            echo '</div>';

            echo '<div class="display-inline-block">';
                echo form_select('wiki_cat_access', '', $filter_values['wiki_cat_access'], [
                    'allowclear'  => TRUE,
                    'placeholder' => '- '.$locale['wiki_025'].' -',
                    'options'     => fusion_get_groups()
                ]);
            echo '</div>';

            echo '<div class="display-inline-block">';
                $language_opts = [0 => $locale['wiki_030']];
                $language_opts += fusion_get_enabled_languages();
                echo form_select('wiki_cat_language', '', $filter_values['wiki_cat_language'], [
                    'allowclear'  => TRUE,
                    'placeholder' => '- '.$locale['wiki_031'].' -',
                    'options'     => $language_opts
                ]);
            echo '</div>';
        echo '</div>';
        echo closeform();
    echo '</div>';

    echo openform('wiki_table', 'post', FUSION_REQUEST);
    echo form_hidden('table_action', '', '');
    display_wiki_category($result);
    echo closeform();

    add_to_jquery("
        $('#toggle_options').bind('click', function(e) {
            e.preventDefault();
            $('#wiki_filter_options').slideToggle();
            var caret_status = $('#filter_caret').hasClass('fa-caret-down');
            if (caret_status == 1) {
                $('#filter_caret').removeClass('fa-caret-down').addClass('fa-caret-up');
                $(this).removeClass('btn-default').addClass('btn-info');
            } else {
                $('#filter_caret').removeClass('fa-caret-up').addClass('fa-caret-down');
                $(this).removeClass('btn-info').addClass('btn-default');
            }
        });
        $('#wiki_cat_status, #wiki_cat_access, #wiki_cat_language').bind('change', function(e) {
            $(this).closest('form').submit();
        });
    ");
}

function display_wiki_category($data, $id = 0, $level = 0) {
    $locale = fusion_get_locale();

    if (!$id) {
        echo '<div class="table-responsive"><table class="table table-hover">';
        echo '<thead><tr>';
            echo '<th class="hidden-xs"></th>';
            echo '<th class="col-xs-4">'.$locale['wiki_006'].'</th>';
            echo '<th>'.$locale['wiki_038'].'</th>';
            echo '<th>'.$locale['wiki_017'].'</th>';
            echo '<th>'.$locale['wiki_018'].'</th>';
            //echo '<th>'.$locale['language'].'</th>';
            echo '<th>'.$locale['wiki_033'].'</th>';
        echo '</tr></thead>';
        echo '<tbody>';
    }

    if (!empty($data[$id])) {
        foreach ($data[$id] as $cat_id => $cdata) {
            $edit_link = clean_request('section=categories&ref=wiki_cat_form&action=edit&cat_id='.$cat_id, ['section', 'ref', 'action', 'cat_id'], FALSE);
            $delete_link = clean_request('section=categories&ref=wiki_cat_form&action=delete&cat_id='.$cat_id, ['section', 'ref', 'action', 'cat_id'], FALSE);

            echo '<tr data-id="'.$cat_id.'" id="cat'.$cat_id.'">';
                echo '<td class="hidden-xs">';
                    echo form_checkbox('wiki_cat_id[]', '', '', ['value' => $cat_id, 'input_id' => 'checkbox'.$cat_id, 'class' => 'm-b-0']);
                    add_to_jquery('$("#checkbox'.$cat_id.'").click(function() {
                        if ($(this).prop("checked")) {
                            $("#cat'.$cat_id.'").addClass("active");
                        } else {
                            $("#cat'.$cat_id.'").removeClass("active");
                        }
                    });');
                echo '</td>';

                echo '<td>'.str_repeat('|-', $level).' '.$cdata['wiki_cat_name'].'</td>';
                echo '<td><span class="badge">'.format_word($cdata['wiki_count'], $locale['fmt_doc']).'</span></td>';
                echo '<td><span class="badge">'.($cdata['wiki_cat_status'] == 1 ? $locale['published'] : $locale['unpublished']).'</span></td>';
                echo '<td><span class="badge">'.getgroupname($cdata['wiki_cat_access']).'</span></td>';
                //echo '<td>'.translate_lang_names($cdata['wiki_cat_language']).'</td>';
                echo '<td>';
                    echo '<a href="'.$edit_link.'" title="'.$locale['edit'].'">'.$locale['edit'].'</a> | ';
                    echo '<a href="'.$delete_link.'" title="'.$locale['delete'].'">'.$locale['delete'].'</a>';
                echo '</td>';
            echo '</tr>';

            if (isset($data[$cdata['wiki_cat_id']])) {
                display_wiki_category($data, $cdata['wiki_cat_id'], $level + 1);
            }
        }
    } else {
        echo '<tr><td colspan="7" class="text-center">'.$locale['wiki_039'].'</td></tr>';
    }

    if (!$id) {
        echo '</tbody>';
        echo '</table></div>';
    }
}
