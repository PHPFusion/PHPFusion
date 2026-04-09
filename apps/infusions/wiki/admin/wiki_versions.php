<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: wiki_versions.php
| Author: RobiNN
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

$locale = fusion_get_locale();

$data = [
    'wiki_version_id' => 0,
    'wiki_version'    => ''
];

if (isset($_POST['cancel'])) {
    redirect(FUSION_SELF.fusion_get_aidlink());
}

if ((isset($_GET['action']) && $_GET['action'] == 'delete') && (isset($_GET['version_id']) && isnum($_GET['version_id']))) {
    dbquery("DELETE FROM ".DB_WIKI_VERSIONS." WHERE wiki_version_id='".intval($_GET['version_id'])."'");
    add_notice('success', $locale['wiki_215']);
    redirect(clean_request('', ['ref', 'action', 'version_id'], TRUE));
}

if (isset($_POST['save_page']) || isset($_POST['save_and_close'])) {
    $data = [
        'wiki_version_id' => form_sanitizer($_POST['wiki_version_id'], 0, 'wiki_version_id'),
        'wiki_version'    => form_sanitizer($_POST['wiki_version'], '', 'wiki_version')
    ];

    if (dbcount("(wiki_version_id)", DB_WIKI_VERSIONS, "wiki_version_id='".$data['wiki_version_id']."'")) {
        if (\defender::safe()) {
            dbquery_insert(DB_WIKI_VERSIONS, $data, 'update');
            add_notice('success', $locale['wiki_216']);
        }
    } else {
        if (\defender::safe()) {
            dbquery_insert(DB_WIKI_VERSIONS, $data, 'save');
            add_notice('success', $locale['wiki_217']);
        }
    }

    if (isset($_POST['save_and_close'])) {
        redirect(clean_request('', ['ref', 'action', 'wiki_id'], FALSE));
    } else {
        redirect(FUSION_REQUEST);
    }
}

if ((isset($_GET['action']) && $_GET['action'] == 'edit') && (isset($_GET['version_id']) && isnum($_GET['version_id']))) {
    $result = dbquery("SELECT * FROM ".DB_WIKI_VERSIONS." WHERE wiki_version_id='".$_GET['version_id']."'");

    if (dbrows($result)) {
        $data = dbarray($result);
    } else {
        redirect(clean_request('', ['section', 'aid'], TRUE));
    }
}

if (isset($_GET['ref']) && $_GET['ref'] == 'form') {
    echo openform('wikiform', 'post', FUSION_REQUEST);
    echo form_hidden('wiki_version_id', '', $data['wiki_version_id']);

    echo form_text('wiki_version', $locale['wiki_040'], $data['wiki_version'], [
        'inline'      => TRUE,
        'required'    => TRUE,
        'placeholder' => '9.0'
    ]);

    echo form_button('cancel', $locale['cancel'], $locale['cancel'], ['class' => 'btn-sm btn-default', 'icon' => 'fa fa-fw fa-times']);
    echo form_button('save_page', $locale['save'], $locale['save'], ['class' => 'btn-sm btn-success m-l-5', 'icon' => 'fa fa-fw fa-hdd-o']);
    echo form_button('save_and_close', $locale['save_and_close'], $locale['save_and_close'], ['class' => 'btn-sm btn-primary m-l-5', 'icon' => 'fa fa-floppy-o']);
    echo closeform();
} else {
    echo '<div class="m-t-15 m-b-20">';
    echo '<div class="clearfix">';
        echo '<div class="pull-right">';
            echo '<a class="btn btn-success btn-sm" href="'.clean_request('ref=form', ['ref'], FALSE).'"><i class="fa fa-fw fa-plus"></i> '.$locale['add'].'</a>';
        echo '</div>';
    echo '</div>';
    echo '</div>';

    $result = dbquery("SELECT * FROM ".DB_WIKI_VERSIONS." ORDER BY wiki_version DESC");

    echo '<div class="table-responsive"><table class="table table-hover">';
        echo '<thead><tr>';
            echo '<th>'.$locale['wiki_040'].'</th>';
            echo '<th>'.$locale['wiki_033'].'</th>';
        echo '</tr></thead>';
        echo '<tbody>';
            if (dbrows($result) > 0) {
                while ($data = dbarray($result)) {
                    $edit_link = clean_request('&ref=form&action=edit&version_id='.$data['wiki_version_id'], ['ref', 'action', 'version_id'], FALSE);
                    $delete_link = clean_request('&ref=form&action=delete&version_id='.$data['wiki_version_id'], ['ref', 'action', 'version_id'], FALSE);

                    echo '<tr>';
                        echo '<td>'.$data['wiki_version'].'</td>';
                        echo '<td>';
                            echo '<a href="'.$edit_link.'" title="'.$locale['edit'].'">'.$locale['edit'].'</a> | ';
                            echo '<a href="'.$delete_link.'" title="'.$locale['delete'].'">'.$locale['delete'].'</a>';
                        echo '</td>';
                    echo '</tr>';
                }
            }
        echo '</tbody>';
    echo '</table></div>';
}
