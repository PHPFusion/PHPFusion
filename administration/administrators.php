<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: administrators.php
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
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/admin_header.php';
pageaccess('AD');

$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/admins.php");

add_breadcrumb(['link' => ADMIN.'administrators.php'.fusion_get_aidlink(), 'title' => $locale['ADM_420']]);

$edit = check_get('edit') && check_get('section') && get('section') == 'form';

$tabs['title'][] = $locale['ADM_420'];
$tabs['id'][] = 'list';
$tabs['icon'][] = 'fa fa-user-md fa-fw';
$tabs['title'][] = $edit ? $locale['ADM_440'] : $locale['ADM_410'];
$tabs['id'][] = 'form';
$tabs['icon'][] = 'fa fa-'.($edit ? 'pen' : 'plus').' fa-fw';
$tab_active = tab_active($tabs, 0);

$allowed_sections = ['list', 'form'];
$sections = in_array(get('section'), $allowed_sections) ? get('section') : 'list';

opentable($locale['ADM_420']);
echo opentab($tabs, $sections, 'adminstabs', TRUE, 'nav-tabs', "section", ['edit', 'user_id']);
switch ($sections) {
    case 'form':
        admins_form();
        break;
    default:
        admins_list();
        break;
}
echo closetab();
closetable();

function admins_form() {
    $locale = fusion_get_locale();

    if (check_post('cancel')) {
        redirect(clean_request('', ['section', 'edit', 'user_id'], FALSE));
    }

    if (check_post('add_admin') && post('user_id', FILTER_SANITIZE_NUMBER_INT)) {
        if (check_post('all_rights') || check_post('make_super')) {
            $admin_rights_array = [];

            $result = dbquery("SELECT DISTINCT admin_rights AS admin_right, admin_language FROM ".DB_ADMIN." WHERE admin_language='".LANGUAGE."' ORDER BY admin_right");
            while ($data = dbarray($result)) {
                $admin_rights_array[] = $data['admin_right'];
            }
            $admin_rights = implode('.', $admin_rights_array);

            dbquery("UPDATE ".DB_USERS." SET user_level=:userLevel, user_rights=:userRights WHERE user_id=:userId", [
                ':userLevel'  => check_post('make_super') ? USER_LEVEL_SUPER_ADMIN : USER_LEVEL_ADMIN,
                ':userRights' => $admin_rights, ':userId' => post('user_id'),
            ]);
        } else {
            addnotice('warning', $locale['ADM_463']);
            redirect(FUSION_REQUEST);
        }
        addnotice('success', $locale['ADM_400']);
        redirect(FUSION_REQUEST);
    }

    if (check_post('update_admin') && get('user_id', FILTER_SANITIZE_NUMBER_INT) != 1) {
        if (check_post('rights')) {
            $user_rights = implode('.', post(['rights']));
            dbquery("UPDATE ".DB_USERS." SET user_rights=:userRight WHERE user_id=:userId AND user_level<=:userLevel", [
                ':userRight' => $user_rights,
                ':userId'    => get('user_id'),
                ':userLevel' => USER_LEVEL_ADMIN,
            ]);
        } else {
            dbquery("UPDATE ".DB_USERS." SET user_rights='' WHERE user_id=:userId AND user_level<=:userLevel", [
                ':userId'    => get('user_id'),
                ':userLevel' => USER_LEVEL_ADMIN,
            ]);
        }

        addnotice('success', $locale['ADM_401']);
        redirect(FUSION_REQUEST);
    }

    if (check_get('edit') && get('user_id', FILTER_SANITIZE_NUMBER_INT) != 1) {
        $user = fusion_get_user(get('user_id'));
        if (fusion_get_userdata('user_level') <= $user['user_level'] && fusion_get_userdata('user_id') != $user['user_id']) {
            $result = dbquery("
                SELECT user_name, user_rights
                FROM ".DB_USERS."
                WHERE user_id=:userId AND user_level<=:userLevel
                ORDER BY user_id ASC", [
                ':userId'    => get('user_id'),
                ':userLevel' => USER_LEVEL_ADMIN,
            ]);

            if (dbrows($result)) {
                $data = dbarray($result);
                $user_rights = explode(".", $data['user_rights']);
                $rights_result = dbquery("SELECT admin_rights, admin_title, admin_page, admin_language FROM ".DB_ADMIN." WHERE admin_link != 'reserved' AND admin_language='".LANGUAGE."' ORDER BY admin_page ASC, admin_title ASC");

                echo '<h3 class="m-t-0">'.$data['user_name'].'</h3>';
                $columns = 2;
                $percent = 100 / $columns;
                $admin_page_titles = [1 => $locale['ADM_441'], $locale['ADM_442'], $locale['ADM_443'], $locale['ADM_449'], $locale['ADM_444']];
                $admin_pages = array_fill(1, count($admin_page_titles), []);
                $risky_rights = ['CP', 'AD', 'SB', 'DB', 'IP', 'P', 'S3', 'ERRO'];

                while ($row = dbarray($rights_result)) {
                    $admin_pages[$row['admin_page']][] = $row;
                }

                echo openform('rightsform', 'post', FUSION_REQUEST);
                echo "<div class='alert alert-warning'><strong>".$locale['ADM_462']."</strong></div>\n";
                echo "<div class='table-responsive'>\n";
                echo "<table id='links-table' class='table table-striped'>\n";
                echo "<tbody>\n";
                foreach ($admin_pages as $page => $admin_page) {
                    echo "<tr>\n<td colspan='$columns' class='info'><strong>".$admin_page_titles[$page]."</strong></td>\n</tr>\n";
                    $mod = count($admin_page) % $columns;
                    if ($mod !== 0) {
                        $admin_page = array_merge($admin_page, array_fill(0, $columns - (count($admin_page) % $columns), ''));
                    }
                    $admin_page_rows = array_chunk($admin_page, $columns, TRUE);
                    foreach ($admin_page_rows as $row) {
                        echo "<tr>\n";
                        foreach ($row as $cell_num => $cell) {
                            if ($cell) {
                                $insecure = in_array($cell['admin_rights'], $risky_rights);
                                echo "<td class='".($insecure ? 'insecure danger' : 'secure')."' style='width: $percent%'>\n";
                                echo form_checkbox('rights[]', $cell['admin_title'], in_array($cell['admin_rights'], $user_rights), [
                                    'reverse_label' => TRUE,
                                    'value'         => $cell['admin_rights'],
                                    'required'      => $insecure,
                                    'input_id'      => 'rights-'.$page.'-'.$cell_num,
                                    'class'         => 'm-b-0',
                                    'toggle'        => TRUE
                                ]);
                                echo "</td>\n";
                            }
                        }
                        echo "</tr>\n";
                    }
                }
                echo "</tbody>\n";
                echo "</table>\n";
                echo "</div>\n";
                echo "<div class='text-center row'>\n";
                echo " <div class='col-xs-6 col-md-2'>\n";
                echo form_checkbox('check_all', $locale['ADM_445'], '', ['reverse_label' => TRUE]);
                echo "</div>\n";
                echo "<div class='col-xs-6 col-md-2'>\n";
                echo form_checkbox('check_secure', $locale['ADM_450'], '', ['reverse_label' => TRUE]);
                echo "</div>\n";
                echo "<div class='col-xs-6 col-md-8 text-left'>\n";
                echo form_button('cancel', $locale['cancel'], $locale['cancel']);
                echo form_button('update_admin', $locale['ADM_448'], $locale['ADM_448'], ['class' => 'btn-primary']);
                echo "</div>\n";
                echo "</div>\n";

                add_to_jquery("
                    var linksTable = $('#links-table');
                    var checkboxes = linksTable.find(':checkbox');
                    var secureBoxes = linksTable.find('.secure :checkbox');
                    var insecureBoxes = linksTable.find('.insecure :checkbox');
                    var checkAll = $('#check_all');
                    var checkSecure = $('#check_secure');

                    var updateCheckAll = function () {
                        checkAll.prop('checked', checkboxes.filter(':not(:checked)').length === 0);
                    };
                    var updateCheckSecure = function () {
                       var secureNotChecked = secureBoxes.filter(':not(:checked)').length;
                       var insecureChecked = insecureBoxes.filter(':checked').length;
                       var checked = (secureNotChecked === 0 && insecureChecked === 0);
                       checkSecure.prop('checked', checked);
                    };
                    var updateStatus = function () {
                        var field = $(this).closest('[id$=\"-field\"]');
                        var td = field.closest('td');
                        td.toggleClass('active', $(this).is(':checked'));
                    };
                    updateCheckAll();
                    updateCheckSecure();
                    checkboxes.each(updateStatus);
                    checkboxes.on('change', updateCheckAll);
                    checkboxes.on('change', updateCheckSecure);
                    checkboxes.on('change', updateStatus);
                    checkAll.on('click', function () {
                        var checked = $(this).is(':checked');
                        checkboxes.prop('checked', checked).change();
                    });
                    checkSecure.on('click', function () {
                        var checked = $(this).is(':checked');
                        insecureBoxes.prop('checked', !checked).change();
                        secureBoxes.prop('checked', checked).change();
                    });
                ");

                echo closeform();
            }
        } else {
            redirect(clean_request('', ['edit'], FALSE));
        }
    } else {
        if (!check_post('search_users') || !check_post('search_criteria')) {
            echo openform('searchform', 'post', FUSION_REQUEST);

            echo form_user_select('search_criteria', $locale['ADM_411'], '', [
                'required'   => TRUE,
                'max_select' => 1,
                'allow_self' => TRUE,
            ]);
            echo form_button('search_users', $locale['search'], $locale['search']);
            echo closeform();
        } else if (check_post('search_users') && check_post('search_criteria')) {
            $search_criteria = sanitizer('search_criteria', '', 'search_criteria');
            $result = dbquery("
                SELECT user_id, user_name
                FROM ".DB_USERS."
                WHERE user_id=:userId AND user_level=:userLevel
                ORDER BY user_name", [
                    ':userId'    => $search_criteria,
                    ':userLevel' => USER_LEVEL_MEMBER,
                ]
            );

            if (dbrows($result)) {
                echo openform('add_users_form', 'post', FUSION_REQUEST);
                echo "<strong>".$locale['ADM_413']."</strong>\n";
                while ($data = dbarray($result)) {
                    echo form_checkbox('user_id', $data['user_name'], '', [
                        'type'          => 'radio',
                        'inline'        => TRUE,
                        'reverse_label' => TRUE,
                        'value'         => $data['user_id'],
                    ]);
                }

                echo '<hr>';

                echo "<div class='alert alert-warning'><strong>".$locale['ADM_462']."</strong></div>\n";
                echo form_checkbox('all_rights', $locale['ADM_415'], '', [
                    'required'      => TRUE,
                    'reverse_label' => TRUE,
                ]);
                if (fusion_get_userdata('user_level') == USER_LEVEL_SUPER_ADMIN) {
                    echo form_checkbox('make_super', $locale['ADM_416'], '', [
                        'required'      => TRUE,
                        'reverse_label' => TRUE,
                    ]);
                }

                echo form_button('cancel', $locale['cancel'], $locale['cancel']);
                echo form_button('add_admin', $locale['ADM_461'], $locale['ADM_461'], ['class' => 'btn-primary']);
                add_to_jquery("$('#add_admin').bind('click', function() { return confirm('".$locale['ADM_461']."'); });");
                echo closeform();
            } else {
                echo "<div class='well text-center'>".$locale['ADM_418']."<br /><a href='".FUSION_REQUEST."'>".$locale['ADM_419']."</a>\n</div>\n";
            }
        }
    }
}

function admins_list() {
    $locale = fusion_get_locale();

    if (check_get('remove') && get('remove', FILTER_SANITIZE_NUMBER_INT) != 1) {
        dbquery("UPDATE ".DB_USERS." SET user_admin_password='', user_admin_salt='', user_level=".USER_LEVEL_MEMBER.", user_rights='' WHERE user_id='".get('remove')."' AND user_level<=".USER_LEVEL_ADMIN);

        addnotice('success', $locale['ADM_402']);
        redirect(clean_request('', ['remove'], FALSE));
    }

    $result = dbquery("SELECT user_id, user_name, user_rights, user_level
        FROM ".DB_USERS."
        WHERE user_level<=:level
        ORDER BY user_level DESC, user_name
    ", [
        ':level' => USER_LEVEL_ADMIN
    ]);
    echo "<div class='table-responsive'>\n";
    echo "<table class='table table-hover table-striped'>\n";
    echo "<thead>\n";
    echo "<tr>\n";
    echo "<th>".$locale['ADM_421']."</th>\n";
    echo "<th class='text-center'>".$locale['ADM_422']."</th>\n";
    echo "<th class='text-center'>".$locale['ADM_423']."</th>\n";
    echo "</tr>\n";
    echo "</thead>\n";
    echo "<tbody>\n";
    while ($data = dbarray($result)) {
        $can_edit = (fusion_get_userdata('user_level') <= $data['user_level'] && fusion_get_userdata('user_id') != $data['user_id']);

        echo "<tr>\n";
        echo "<td><span title='".($data['user_rights'] ? str_replace(".", " ", $data['user_rights']) : $locale['ADM_425'])."' style='cursor:hand;'>".$data['user_name']."</span></td>\n";
        echo "<td class='text-center'>".getuserlevel($data['user_level'])."</td>\n";
        echo "<td class='text-center'>\n";
        if ($can_edit && $data['user_id'] != "1") {
            echo "<a href='".FUSION_SELF.fusion_get_aidlink()."&section=form&edit&user_id=".$data['user_id']."'>".$locale['edit']."</a> |\n";
            echo "<a href='".FUSION_SELF.fusion_get_aidlink()."&remove=".$data['user_id']."' onclick=\"return confirm('".$locale['ADM_460']."');\">".$locale['delete']."</a>\n";
        }
        echo "</td>\n";
        echo "</tr>\n";
    }
    echo "</tbody>\n";
    echo "</table>\n";
    echo "</div>";
}

require_once THEMES.'templates/footer.php';
