<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: wiki.php
| Author: RobiNN
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

$locale = fusion_get_locale();

$data = [
    'wiki_id'               => 0,
    'wiki_type'             => 'page',
    'wiki_name'             => '',
    'wiki_cat'              => 0,
    'wiki_parent'           => 0,
    'wiki_description'      => '',
    'wiki_datestamp'        => time(),
    'wiki_order'            => 0,
    'wiki_user'             => fusion_get_userdata('user_id'),
    'wiki_status'           => 1,
    'wiki_access'           => 0,
    'wiki_edited'           => '',
    'wiki_edited_datestamp' => '',
    'wiki_versions'         => '',
    'wiki_allow_markdown'   => 0,
    'wiki_language'         => LANGUAGE,
    'wiki_hidden'           => []
];

if (isset($_POST['cancel'])) {
    redirect(FUSION_SELF.fusion_get_aidlink());
}

if ((isset($_GET['action']) && $_GET['action'] == 'delete') && (isset($_GET['wiki_id']) && isnum($_GET['wiki_id']))) {
    if (dbcount("(wiki_id)", DB_WIKI, "wiki_parent='".intval($_GET['wiki_id'])."'")) {
        add_notice('danger', $locale['wiki_200']);
        redirect(clean_request('', ['ref', 'section', 'aid'], TRUE));
    } else {
        dbquery("DELETE FROM ".DB_WIKI." WHERE wiki_id='".intval($_GET['wiki_id'])."'");
        add_notice('success', $locale['wiki_201']);
        redirect(WIKI.'admin.php'.fusion_get_aidlink());
    }
}

if (isset($_POST['save_page']) || isset($_POST['save_and_close'])) {
    $data = [
        'wiki_id'             => form_sanitizer($_POST['wiki_id'], 0, 'wiki_id'),
        'wiki_type'           => form_sanitizer($_POST['wiki_type'], '', 'wiki_type'),
        'wiki_name'           => form_sanitizer($_POST['wiki_name'], '', 'wiki_name'),
        'wiki_cat'            => form_sanitizer($_POST['wiki_cat'], 0, 'wiki_cat'),
        'wiki_parent'         => form_sanitizer($_POST['wiki_parent'], 0, 'wiki_parent'),
        'wiki_description'    => form_sanitizer($_POST['wiki_description'], '', 'wiki_description'),
        'wiki_datestamp'      => form_sanitizer($_POST['wiki_datestamp'], '', 'wiki_datestamp'),
        'wiki_order'          => form_sanitizer($_POST['wiki_order'], '', 'wiki_order'),
        'wiki_user'           => form_sanitizer($_POST['wiki_user'], 0, 'wiki_user'),
        'wiki_status'         => form_sanitizer($_POST['wiki_status'], 0, 'wiki_status'),
        'wiki_access'         => form_sanitizer($_POST['wiki_access'], '', 'wiki_access'),
        'wiki_versions'       => isset($_POST['wiki_versions']) ? form_sanitizer($_POST['wiki_versions'], '', 'wiki_versions') : '',
        'wiki_allow_markdown' => isset($_POST['wiki_allow_markdown']) ? 1 : 0,
        'wiki_language'       => form_sanitizer($_POST['wiki_language'], '', 'wiki_language'),
        'wiki_hidden'         => []
    ];

    if (isset($_GET['action']) && $_GET['action'] == 'edit') {
        $data['wiki_edited'] = fusion_get_userdata('user_id');
        $data['wiki_edited_datestamp'] = time();
    }

    if (empty($data['wiki_order'])) {
        $data['wiki_order'] = dbresult(dbquery("SELECT MAX(wiki_order) FROM ".DB_WIKI." ".(multilang_table('WIKI') ? "WHERE ".in_group('wiki_language', LANGUAGE) : '')), 0) + 1;
    }

    if (dbcount("(wiki_id)", DB_WIKI, "wiki_id='".$data['wiki_id']."'")) {
        if (\defender::safe()) {
            dbquery_order(DB_WIKI, $data['wiki_order'], 'wiki_order', $data['wiki_id'], 'wiki_id', $data['wiki_parent'], 'wiki_parent', TRUE, 'wiki_language', 'update');
            dbquery_insert(DB_WIKI, $data, 'update');
            add_notice('success', $locale['wiki_202']);
        }
    } else {
        if (\defender::safe()) {
            dbquery_order(DB_WIKI, $data['wiki_order'], 'wiki_order', 0, 'wiki_id', $data['wiki_parent'], 'wiki_parent', TRUE, 'wiki_language', 'save');
            dbquery_insert(DB_WIKI, $data, 'save');
            add_notice('success', $locale['wiki_204']);
        }
    }

    if (isset($_POST['save_and_close'])) {
        redirect(clean_request('', ['ref', 'action', 'wiki_id'], FALSE));
    } else {
        redirect(FUSION_REQUEST);
    }
}

if ((isset($_GET['action']) && $_GET['action'] == 'edit') && (isset($_GET['wiki_id']) && isnum($_GET['wiki_id']))) {
    $result = dbquery("SELECT * FROM ".DB_WIKI." ".(multilang_table('WIKI') ? "WHERE ".in_group('wiki_language', LANGUAGE)." AND" : "WHERE")." wiki_id='".$_GET['wiki_id']."'");

    if (dbrows($result)) {
        $data = dbarray($result);
        $data['wiki_hidden'] = [$data['wiki_id']];
    } else {
        redirect(clean_request('', ['section', 'aid'], TRUE));
    }
}

if (isset($_GET['ref']) && $_GET['ref'] == 'form') {
    echo openform('wikiform', 'post', FUSION_REQUEST, ['enctype' => TRUE]);
    echo form_hidden('wiki_id', '', $data['wiki_id']);
    echo form_hidden('wiki_user', '', $data['wiki_user']);

    echo '<div class="row">';

    echo '<div class="col-xs-12 col-sm-8">';
    echo form_text('wiki_name', $locale['wiki_006'], $data['wiki_name'], [
        'inline'     => TRUE,
        'required'   => TRUE,
        'error_text' => $locale['wiki_100']
    ]);

    echo form_checkbox('wiki_allow_markdown', $locale['wiki_019a'], $data['wiki_allow_markdown'], ['reverse_label' => TRUE]);

    if (file_exists(WIKI.'includes/markdown-editor/js/locales/'.$locale['short_lang_name'].'.js')) {
        $lang = $locale['short_lang_name'];
    } else {
        $lang = 'en';
    }

    add_to_head('
        <link href="'.WIKI.'includes/markdown-editor/css/markdown-editor.min.css" rel="stylesheet">
        <link href="'.WIKI.'includes/markdown-editor/plugins/highlight/highlight.min.css" rel="stylesheet">
        <script src="'.WIKI.'includes/markdown-editor/plugins/purify/purify.min.js"></script>
        <script src="'.WIKI.'includes/markdown-editor/plugins/markdown-it/markdown-it.min.js"></script>
        <script src="'.WIKI.'includes/markdown-editor/plugins/markdown-it/markdown-it-deflist.min.js"></script>
        <script src="'.WIKI.'includes/markdown-editor/plugins/markdown-it/markdown-it-footnote.min.js"></script>
        <script src="'.WIKI.'includes/markdown-editor/plugins/markdown-it/markdown-it-abbr.min.js"></script>
        <script src="'.WIKI.'includes/markdown-editor/plugins/markdown-it/markdown-it-sub.min.js"></script>
        <script src="'.WIKI.'includes/markdown-editor/plugins/markdown-it/markdown-it-sup.min.js"></script>
        <script src="'.WIKI.'includes/markdown-editor/plugins/markdown-it/markdown-it-ins.min.js"></script>
        <script src="'.WIKI.'includes/markdown-editor/plugins/markdown-it/markdown-it-mark.min.js"></script>
        <script src="'.WIKI.'includes/markdown-editor/plugins/markdown-it/markdown-it-smartarrows.min.js"></script>
        <script src="'.WIKI.'includes/markdown-editor/plugins/markdown-it/markdown-it-checkbox.min.js"></script>
        <script src="'.WIKI.'includes/markdown-editor/plugins/markdown-it/markdown-it-cjk-breaks.min.js"></script>
        <script src="'.WIKI.'includes/markdown-editor/plugins/markdown-it/markdown-it-emoji.min.js"></script>
        <script src="'.WIKI.'includes/markdown-editor/plugins/highlight/highlight.min.js"></script>
        <script src="'.WIKI.'includes/markdown-editor/js/markdown-editor.min.js"></script>
        <script src="'.WIKI.'includes/markdown-editor/js/locales/'.$lang.'.js"></script>
    ');

    add_to_jquery('
        var textarea = $(".docs_textarea");
        textarea.keyup(function() {textarea.val($(this).val());});

        $("#wiki_type").change(function() {
            var textarea_title = $("#textarea_title");

            if ($(textarea_title).text() === "'.$locale['wiki_008'].'") {
                $(textarea_title).text("'.$locale['wiki_009'].'");
            } else {
                $(textarea_title).text("'.$locale['wiki_008'].'");
            }
        });

        $("#wiki_description_md").markdownEditor({
            bsVersion: "3.3.7",
            hiddenActions: ["emoji", "export"]
        });

        $("#markdown_toggler").click(function (e) {
            e.preventDefault();
            $("#classic").toggle();
            $("#markdown").toggle();
        });

        if($("#wiki_allow_markdown").is(":checked")) {
            $("#classic").hide();
            $("#markdown").show();
        } else {
            $("#classic").show();
            $("#markdown").hide();
        }

        $("#wiki_allow_markdown:checkbox").change(function() {
            if($(this).is(":checked")) {
                $("#classic").hide();
                $("#markdown").show();
            } else {
                $("#classic").show();
                $("#markdown").hide();
            }
        });
    ');

    add_to_css('
        .md-editor .md-footer, .md-editor .md-header,
        .panel-txtarea > .panel-heading {padding: 5px;}
        .editor-wrapper > div {margin: 0 !important;}
        .md-btn-group {margin: 0;}
        .md-container {margin-bottom: 20px;}
    ');

    echo '<div class="m-b-5">';
        echo '<a href="#" id="markdown_toggler" class="btn btn-xs btn-info pull-right">'.$locale['wiki_007'].'</a>';
        echo '<label for="wiki_description"><span id="textarea_title">'.$locale['wiki_008'].'</span><span class="required">&nbsp;*</span></label>';
    echo '</div>';


    echo '<div id="classic">';
    echo form_textarea('wiki_description', '', $data['wiki_description'], [
        'input_id'    => 'wiki_description_text',
        'path'        => IMAGES_WIKI,
        'form_name'   => 'wikiform',
        'type'        => 'html',
        'preview'     => TRUE,
        'inner_class' => 'docs_textarea',
        'height'      => '300px',
        'error_text'  => $locale['wiki_101'],
        'descript'    => FALSE
    ]);
    echo '</div>';

    echo '<div id="markdown" style="display: none;">';
        echo '<textarea class="docs_textarea" data-use-twemoji="false" id="wiki_description_md" style="height: 300px;">'.$data['wiki_description'].'</textarea>';
    echo '</div>';

    echo '</div>';

    echo '<div class="col-xs-12 col-sm-4">';
    openside();
    echo form_select_tree('wiki_cat', $locale['wiki_010'], $data['wiki_cat'], [
        'required'     => TRUE,
        'parent_value' => $locale['choose'],
        'query'        => (multilang_table('WIKI') ? "WHERE ".in_group('wiki_cat_language', LANGUAGE) : ''),
        'inline'       => TRUE,
        'error_text'   => $locale['wiki_102']
    ], DB_WIKI_CATS, 'wiki_cat_name', 'wiki_cat_id', 'wiki_cat_parent');

    echo form_select_tree('wiki_parent', $locale['wiki_011'], $data['wiki_parent'], [
        'inline'  => TRUE
    ], DB_WIKI, 'wiki_name', 'wiki_id', 'wiki_parent');

    function pf_versions() {
        $locale = fusion_get_locale();

        $versions = [0 => $locale['wiki_012']];

        $result = dbquery("SELECT wiki_version_id, wiki_version FROM ".DB_WIKI_VERSIONS." ORDER BY wiki_version ASC");

        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $versions[$data['wiki_version_id']] = $data['wiki_version'];
            }
        }

       return $versions;
    }

    echo form_select('wiki_versions[]', $locale['wiki_013'], $data['wiki_versions'], [
        'options'  => pf_versions(),
        'inline'   => TRUE,
        'multiple' => TRUE
    ]);

    echo form_select('wiki_type', $locale['wiki_014'], $data['wiki_type'], [
        'options' => [
            'index' => $locale['wiki_015'],
            'page'  => $locale['wiki_016']
        ],
        'inline'  => TRUE
    ]);

    echo form_select('wiki_status', $locale['wiki_017'], $data['wiki_status'], [
        'options'     => [0 => $locale['unpublish'], 1 => $locale['publish']],
        'placeholder' => $locale['choose'],
        'inline'      => TRUE
    ]);

    echo form_select('wiki_access', $locale['wiki_018'], $data['wiki_access'], ['options' => fusion_get_groups(), 'inline' => TRUE]);

    echo form_datepicker('wiki_datestamp', $locale['wiki_019'], $data['wiki_datestamp'], ['inline' => TRUE]);

    echo form_text('wiki_order', $locale['wiki_020'], $data['wiki_order'], ['type' => 'number', 'inline' => TRUE]);

    if (multilang_table('WIKI')) {
        echo form_select('wiki_language[]', $locale['global_ML100'], $data['wiki_language'], [
            'options'     => fusion_get_enabled_languages(),
            'placeholder' => $locale['choose'],
            'inline'      => TRUE,
            'multiple'    => TRUE
        ]);
    } else {
        echo form_hidden('wiki_language', '', $data['wiki_language']);
    }

    closeside();
    echo '</div>';

    echo '</div>'; // .row

    echo form_button('cancel', $locale['cancel'], $locale['cancel'], ['class' => 'btn-sm btn-default', 'icon' => 'fa fa-fw fa-times']);
    echo form_button('save_page', $locale['save'], $locale['save'], ['class' => 'btn-sm btn-success m-l-5', 'icon' => 'fa fa-fw fa-hdd-o']);
    echo form_button('save_and_close', $locale['save_and_close'], $locale['save_and_close'], ['class' => 'btn-sm btn-primary m-l-5', 'icon' => 'fa fa-floppy-o']);
    echo closeform();
} else {
    $allowed_actions = array_flip(['publish', 'unpublish', 'delete', 'display']);

    if (isset($_POST['table_action']) && isset($allowed_actions[$_POST['table_action']])) {
        $input = (isset($_POST['wiki_id'])) ? explode(',', form_sanitizer($_POST['wiki_id'], '', 'wiki_id')) : '';

        if (!empty($input)) {
            foreach ($input as $wiki_id) {
                if (dbcount("('wiki_id')", DB_WIKI, "wiki_id=:wiki_id", [':wiki_id' => intval($wiki_id)]) && \defender::safe()) {
                    switch ($_POST['table_action']) {
                        case 'publish':
                            dbquery("UPDATE ".DB_WIKI." SET wiki_status=:status WHERE wiki_id=:wiki_id", [':status' => '0', ':wiki_id' => intval($wiki_id)]);
                            break;
                        case 'unpublish':
                            dbquery("UPDATE ".DB_WIKI." SET wiki_status=:status WHERE wiki_id=:wiki_id", [':status' => '1', ':wiki_id' => intval($wiki_id)]);
                            break;
                        case 'delete':
                            dbquery("DELETE FROM ".DB_WIKI." WHERE wiki_id=:wiki_id", [':wiki_id' => intval($wiki_id)]);
                            break;
                        default:
                            redirect(FUSION_REQUEST);
                    }
                }
            }

            add_notice('success', $locale['wiki_202']);
            redirect(FUSION_REQUEST);
        }

        add_notice('warning', $locale['wiki_205']);
        redirect(FUSION_REQUEST);
    }

    if (isset($_POST['wiki_clear'])) {
        redirect(FUSION_SELF.fusion_get_aidlink());
    }

    $sql_condition = multilang_table('WIKI') ? in_group('wiki_language', LANGUAGE) : '';
    $search_string = [];
    if (isset($_POST['p-submit-wiki_text'])) {
        $search_string['wiki_name'] = [
            'input'    => form_sanitizer($_POST['wiki_text'], '', 'wiki_text'),
            'operator' => 'LIKE'
        ];
    }

    if (!empty($_POST['wiki_status'])) {
        $search_string['wiki_status'] = [
            'input'    => form_sanitizer($_POST['wiki_status'], '', 'wiki_status'),
            'operator' => '='
        ];
    }

    if (!empty($_POST['wiki_access'])) {
        $search_string['wiki_access'] = [
            'input'    => form_sanitizer($_POST['wiki_access'], '', 'wiki_access'),
            'operator' => '='
        ];
    }

    if (!empty($_POST['wiki_user'])) {
        $search_string['wiki_user'] = [
            'input'    => form_sanitizer($_POST['wiki_user'], '', 'wiki_user'),
            'operator' => '='
        ];
    }

    if (!empty($_POST['wiki_category'])) {
        $search_string['wiki_cat'] = [
            'input'    => form_sanitizer($_POST['wiki_category'], '', 'wiki_category'),
            'operator' => '='
        ];
    }

    if (!empty($_POST['wiki_language'])) {
        $search_string['wiki_language'] = [
            'input'    => form_sanitizer($_POST['wiki_language'], '', 'wiki_language'),
            'operator' => '='
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

    $default_display = 16;
    $limit = $default_display;
    if ((!empty($_POST['wiki_display']) && isnum($_POST['wiki_display'])) || (!empty($_GET['wiki_display']) && isnum($_GET['wiki_display']))) {
        $limit = (!empty($_POST['wiki_display']) ? $_POST['wiki_display'] : $_GET['wiki_display']);
    }

    $subpages = isset($_GET['parent_id']) && isnum($_GET['parent_id']) ? $_GET['parent_id'] : 0;
    $max_rows = dbcount("(wiki_id)", DB_WIKI, "wiki_parent = ".$subpages."");
    $rowstart = 0;
    if (!isset($_POST['wiki_display'])) {
        $rowstart = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $max_rows ? $_GET['rowstart'] : 0);
    }

    $result = dbquery("SELECT w.*, wc.*
        FROM ".DB_WIKI." w
        LEFT JOIN ".DB_WIKI_CATS." AS wc ON w.wiki_cat=wc.wiki_cat_id
        ".($sql_condition ? " WHERE ".$sql_condition : "")."
        AND w.wiki_parent = ".$subpages."
        GROUP BY w.wiki_id
        ORDER BY w.wiki_order DESC, w.wiki_datestamp DESC
        LIMIT $rowstart, $limit
    ");

    $wiki_rows = dbrows($result);

    $filter_values = [
        'wiki_text'     => !empty($_POST['wiki_text']) ? form_sanitizer($_POST['wiki_text'], '', 'wiki_text') : '',
        'wiki_status'   => !empty($_POST['wiki_status']) ? form_sanitizer($_POST['wiki_status'], '', 'wiki_status') : '',
        'wiki_access'   => !empty($_POST['wiki_access']) ? form_sanitizer($_POST['wiki_access'], '', 'wiki_access') : '',
        'wiki_user'     => !empty($_POST['wiki_user']) ? form_sanitizer($_POST['wiki_user'], '', 'wiki_user') : '',
        'wiki_category' => !empty($_POST['wiki_category']) ? form_sanitizer($_POST['wiki_category'], '', 'wiki_category') : '',
        'wiki_language' => !empty($_POST['wiki_language']) ? form_sanitizer($_POST['wiki_language'], '', 'wiki_language') : ''
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
            echo '<a class="btn btn-success btn-sm" href="'.clean_request('ref=form', ['ref'], FALSE).'"><i class="fa fa-fw fa-plus"></i> '.$locale['wiki_021'].'</a>';
            echo '<button type="button" class="hidden-xs btn btn-default btn-sm m-l-5" onclick="run_admin(\'publish\', \'#table_action\', \'#wiki_table\');"><i class="fa fa-fw fa-check"></i> '.$locale['publish'].'</button>';
            echo '<button type="button" class="hidden-xs btn btn-default btn-sm m-l-5" onclick="run_admin(\'unpublish\', \'#table_action\', \'#wiki_table\');"><i class="fa fa-fw fa-ban"></i> '.$locale['unpublish'].'</button>';
            echo '<button type="button" class="hidden-xs btn btn-danger btn-sm m-l-5" onclick="run_admin(\'delete\', \'#table_action\', \'#wiki_table\');"><i class="fa fa-fw fa-trash-o"></i> '.$locale['delete'].'</button>';
        echo '</div>';

        echo '<div class="display-inline-block pull-left m-r-10">';
            echo form_text('wiki_text', '', $filter_values['wiki_text'], [
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
            echo form_select('wiki_status', '', $filter_values['wiki_status'], [
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
            echo form_select('wiki_access', '', $filter_values['wiki_access'], [
                'allowclear'  => TRUE,
                'placeholder' => '- '.$locale['wiki_025'].' -',
                'options'     => fusion_get_groups()
            ]);
        echo '</div>';

        echo '<div class="display-inline-block">';
            $author_opts = [0 => $locale['wiki_026']];
            $result_autors = dbquery("SELECT w.wiki_user, u.user_id, u.user_name, u.user_status
                FROM ".DB_WIKI." w
                LEFT JOIN ".DB_USERS." u ON w.wiki_user = u.user_id
                GROUP BY u.user_id
                ORDER BY u.user_name ASC
            ");

            if (dbrows($result_autors) > 0) {
                while ($data = dbarray($result_autors)) {
                    $author_opts[$data['user_id']] = !empty($data['user_name']) ? $data['user_name'] : 'N/A';
                }
            }
            echo form_select('wiki_user', '', $filter_values['wiki_user'], [
                'allowclear'  => TRUE,
                'placeholder' => '- '.$locale['wiki_027'].' -',
                'options'     => $author_opts
            ]);
        echo '</div>';

        echo '<div class="display-inline-block">';
            echo form_select_tree('wiki_category', '', $filter_values['wiki_category'], [
                'parent_value' => $locale['wiki_028'],
                'placeholder'  => '- '.$locale['wiki_029'].' -',
                'allowclear'   => TRUE,
                'query'        => (multilang_table('WIKI') ? "WHERE ".in_group('wiki_cat_language', LANGUAGE) : '')
            ], DB_WIKI_CATS, 'wiki_cat_name', 'wiki_cat_id', 'wiki_cat_parent');
        echo '</div>';

        echo '<div class="display-inline-block">';
            $language_opts = [0 => $locale['wiki_030']];
            $language_opts += fusion_get_enabled_languages();
            echo form_select('wiki_language', '', $filter_values['wiki_language'], [
                'allowclear'  => TRUE,
                'placeholder' => '- '.$locale['wiki_030'].' -',
                'options'     => $language_opts
            ]);
        echo '</div>';
    echo '</div>';
    echo closeform();
    echo '</div>';

    echo openform('wiki_table', 'post', FUSION_REQUEST);
    echo form_hidden('table_action', '', '');

    echo '<div class="table-responsive"><table class="table table-hover">';
        echo '<thead><tr>';
            echo '<th class="hidden-xs"></th>';
            echo '<th>'.$locale['wiki_006'].'</th>';
            echo '<th>'.$locale['wiki_010'].'</th>';
            echo '<th>'.$locale['wiki_014'].'</th>';
            echo '<th>'.$locale['wiki_017'].'</th>';
            echo '<th>'.$locale['wiki_018'].'</th>';
            echo '<th>'.$locale['wiki_033'].'</th>';
        echo '</tr></thead>';
        echo '<tbody>';

    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            $page_link = clean_request('&section=list&parent_id='.$data['wiki_id'], ['section', 'parent_id'], FALSE);
            $edit_link = clean_request('&ref=form&action=edit&wiki_id='.$data['wiki_id'], ['ref', 'action', 'wiki_id'], FALSE);
            $delete_link = clean_request('&ref=form&action=delete&wiki_id='.$data['wiki_id'], ['ref', 'action', 'wiki_id'], FALSE);

            $sub_pages = dbcount('(wiki_id)', DB_WIKI, 'wiki_parent ='.$data['wiki_id']);
            $badge = $sub_pages > 0 ? ' <span class="badge">'.format_word($sub_pages, $locale['wiki_034']).'</span>' : '';

            echo '<tr data-id="'.$data['wiki_id'].'" id="page'.$data['wiki_id'].'">';
                echo '<td class="hidden-xs">';
                echo form_checkbox('wiki_id[]', '', '', ['value' => $data['wiki_id'], 'input_id' => 'checkbox'.$data['wiki_id'], 'class' => 'm-b-0']);
                add_to_jquery('$("#checkbox'.$data['wiki_id'].'").click(function() {
                    if ($(this).prop("checked")) {
                        $("#page'.$data['wiki_id'].'").addClass("active");
                    } else {
                        $("#page'.$data['wiki_id'].'").removeClass("active");
                    }
                });');
                echo '</td>';

                echo '<td>'.($sub_pages > 0 ? '<a href="'.$page_link.'">'.$data['wiki_name'].'</a>' : $data['wiki_name']).$badge.'</td>';
                echo '<td>'.$data['wiki_cat_name'].'</td>';
                echo '<td>'.($data['wiki_type'] == 'page' ? $locale['wiki_016'] : ($data['wiki_type'] == 'index' ? $locale['wiki_015'] : '')).'</td>';
                echo '<td><span class="badge">'.($data['wiki_status'] == 1 ? $locale['published'] : $locale['unpublished']).'</span></td>';
                echo '<td><span class="badge">'.getgroupname($data['wiki_access']).'</span></td>';
                echo '<td>';
                    echo '<a href="'.$edit_link.'" title="'.$locale['edit'].'">'.$locale['edit'].'</a> | ';
                    echo '<a href="'.$delete_link.'" title="'.$locale['delete'].'">'.$locale['delete'].'</a>';
                echo '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="7" class="text-center">'.$locale['wiki_035'].'</td></tr>';
    }
    echo '</tbody>';
    echo '</table></div>';

    echo '<div class="display-block">';
        echo '<label class="control-label display-inline-block m-r-10" for="wiki_display">'.$locale['wiki_036'].'</label>';
        echo '<div class="display-inline-block">';
            echo form_select('wiki_display', '', $limit, ['options' => [5 => 5, 10 => 10, 16 => 16, 25 => 25, 50 => 50, 100 => 100]]);
        echo '</div>';

    if ($max_rows > $wiki_rows) {
        echo '<div class="display-inline-block pull-right">';
            echo makepagenav($rowstart, $limit, $max_rows, 3, FUSION_SELF.fusion_get_aidlink()."&wiki_display=$limit&");
        echo '</div>';
    }
    echo '</div>';

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
        $('#wiki_status, #wiki_access, #wiki_category, #wiki_language, #wiki_user, #wiki_display').bind('change', function(e){
            $(this).closest('form').submit();
        });
    ");
}
