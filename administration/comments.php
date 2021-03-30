<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: comments.php
| Author: Core Development Team (coredevs@phpfusion.com)
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
pageAccess('C');

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/comments.php');

add_breadcrumb(['link' => ADMIN.'comments.php'.fusion_get_aidlink(), 'title' => $locale['401']]);

$tabs['title'][] = $locale['401'];
$tabs['id'][] = 'comments_listing';
$tabs['icon'][] = 'fa fa-comment';

if (check_get('comments_edit') && check_get('comment_id')) {
    $tabs['title'][] = $locale['400'];
    $tabs['id'][] = 'comments_edit';
    $tabs['icon'][] = 'fa fa-edit';
}

$tabs['title'][] = $locale['settings'];
$tabs['id'][] = 'comments_settings';
$tabs['icon'][] = 'fa fa-cogs';

$allowed_sections = ['comments_listing', 'comments_edit', 'comments_settings'];
$sections = in_array(get('section'), $allowed_sections) ? get('section') : 'comments_listing';

opentable($locale['401']);
echo opentab($tabs, $sections, 'comments_listing', TRUE, 'nav-tabs');

switch ($sections) {
    case 'comments_edit':
        comments_edit();
        break;
    case 'comments_settings':
        comments_settings();
        break;
    case 'comments_listing':
        comments_listing();
        break;
}

echo closetab();
closetable();

function comments_edit() {
    $locale = fusion_get_locale();

    if (check_post('save_comment') && check_get('comment_id')) {
        $comment_message = sanitizer('comment_message', '', 'comment_message');
        dbquery("UPDATE ".DB_COMMENTS." SET comment_message=:comment_message WHERE comment_id=:comment_id", [
            ':comment_message' => $comment_message,
            ':comment_id'      => get('comment_id')
        ]);
        addNotice('success', $locale['410']);
        redirect(clean_request('', ['section', 'comment_item_id', 'comment_id'], FALSE));
    }

    if (check_get('comment_id') && get('comment_id', FILTER_SANITIZE_NUMBER_INT)) {
        $result = dbquery("SELECT * FROM ".DB_COMMENTS." WHERE comment_id=:comment_id", [':comment_id' => get('comment_id')]);
        $data = dbarray($result);

        echo openform('comment_edit_form', 'post', FUSION_REQUEST);
        echo form_textarea('comment_message', '', $data['comment_message'], [
            'autosize' => TRUE, 'bbcode' => TRUE, 'preview' => TRUE, 'form_name' => 'comment_edit_form'
        ]);
        echo form_button('save_comment', $locale['421'], $locale['421'], ['class' => 'btn-primary']);
        echo closeform();
    }
}

function comments_settings() {
    $locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/settings.php');
    $settings = fusion_get_settings();

    if (check_post('savesettings')) {
        $inputData = [
            'comments_enabled'  => post('comments_enabled') ? 1 : 0,
            'guestposts'        => post('guestposts') ? 1 : 0,
            'comments_per_page' => sanitizer('comments_per_page', '10', 'comments_per_page'),
            'comments_avatar'   => post('comments_avatar') ? 1 : 0,
            'comments_sorting'  => sanitizer('comments_sorting', 'DESC', 'comments_sorting')
        ];

        if (\defender::safe()) {
            foreach ($inputData as $settings_name => $settings_value) {
                dbquery("UPDATE ".DB_SETTINGS." SET settings_value=:settings_value WHERE settings_name=:settings_name", [
                    ':settings_value' => $settings_value,
                    ':settings_name'  => $settings_name
                ]);
            }

            addNotice('success', $locale['900']);
            redirect(FUSION_REQUEST);
        }
    }

    openside('');
    echo openform('settingsform', 'post', FUSION_REQUEST);
    echo form_checkbox('comments_enabled', $locale['671'], $settings['comments_enabled'], [
        'toggle' => TRUE
    ]);
    echo form_checkbox('guestposts', $locale['655'], $settings['guestposts'], [
        'toggle' => TRUE
    ]);
    echo form_checkbox('comments_avatar', $locale['656'], $settings['comments_avatar'], [
        'toggle' => TRUE
    ]);
    echo form_text('comments_per_page', $locale['913'], $settings['comments_per_page'], [
        'error_text'  => $locale['error_value'],
        'type'        => 'number',
        'inner_width' => '150px',
        'inline'      => TRUE
    ]);

    echo form_checkbox('comments_sorting', $locale['684'], $settings['comments_sorting'], [
        'options' => ['ASC' => $locale['685'], 'DESC' => $locale['686']],
        'type'    => 'radio',
        'inline'  => TRUE
    ]);

    echo form_button('savesettings', $locale['750'], $locale['750'], ['class' => 'btn-success']);
    echo closeform();
    closeside();
}

function comments_listing() {
    $locale = fusion_get_locale();

    $comment_types = \PHPFusion\Admins::getInstance()->getCommentType();
    $ctype = in_array(get('ctype', FILTER_SANITIZE_STRING), array_keys($comment_types)) ? get('ctype', FILTER_SANITIZE_STRING) : key($comment_types);

    if (check_get('action') && get('action') == 'delete' && get('comment_id', FILTER_SANITIZE_NUMBER_INT)) {
        dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_id=:comment_id", [':comment_id' => get('comment_id')]);
        addNotice('success', $locale['411']);
        redirect(clean_request('', ['section', 'action', 'comment_id'], FALSE));
    }

    if (check_get('action') && get('action') == 'delban' && get('comment_id', FILTER_SANITIZE_NUMBER_INT)) {
        $resultquery = dbquery("SELECT * FROM ".DB_COMMENTS." WHERE comment_id=:comment_id", [':comment_id' => get('comment_id')]);

        $data = dbarray($resultquery);

        $info = [
            'blacklist_id'        => '',
            'blacklist_user_id'   => fusion_get_userdata('user_id'),
            'blacklist_ip'        => $data['comment_ip'],
            'blacklist_ip_type'   => $data['comment_ip_type'],
            'blacklist_email'     => '',
            'blacklist_reason'    => $locale['436'],
            'blacklist_datestamp' => time()
        ];

        dbquery_insert(DB_BLACKLIST, $info, 'save');
        dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_id=:comment_id", [':comment_id' => get('comment_id')]);

        addNotice('success', $locale['412']);
        redirect(clean_request('', ['section', 'action', 'comment_id'], FALSE));
    }

    echo "<div class='text-center well'><div class='btn-group'>";
    foreach ($comment_types as $key => $value) {
        echo "<a class='btn btn-default".($ctype == $key ? ' active' : '')."' href='".FUSION_SELF.fusion_get_aidlink()."&ctype=$key'>".$value."</a>\n";
    }
    echo "</div></div>";

    $limit = 20;
    $total_rows = dbcount("(comment_id)", DB_COMMENTS, "comment_type='".$ctype."'".((check_get('comment_item_id') && get('comment_item_id', FILTER_SANITIZE_NUMBER_INT) ? "AND comment_item_id=".get('comment_item_id') : '')));
    $rowstart = check_get('rowstart') && get('rowstart', FILTER_SANITIZE_NUMBER_INT) <= $total_rows ? get('rowstart') : 0;

    $result = dbquery("SELECT
        c.comment_id, c.comment_item_id, c.comment_name, c.comment_subject, c.comment_message, c.comment_datestamp, c.comment_ip, c.comment_ip_type, c.comment_type,
        u.user_id, u.user_name, u.user_status
        FROM ".DB_COMMENTS." AS c
        LEFT JOIN ".DB_USERS." AS u ON c.comment_name=u.user_id
        WHERE c.comment_type=:ctype
        ".(check_get('comment_item_id') && get('comment_item_id', FILTER_SANITIZE_NUMBER_INT) ? "AND c.comment_item_id=".get('comment_item_id')." " : '')."
        ORDER BY c.comment_datestamp ASC LIMIT $rowstart, $limit
    ", [
        ':ctype' => $ctype
    ]);

    $rows = dbrows($result);

    if ($rows > 0) {
        echo '<div class="list-group">';
        while ($data = dbarray($result)) {
            $edit = FUSION_SELF.fusion_get_aidlink()."&section=comments_edit&comment_id=".$data['comment_id'];
            $delete = FUSION_SELF.fusion_get_aidlink()."&section=comments_listing&action=delete&comment_id=".$data['comment_id']."' onclick=\"return confirm('".$locale['433']."');\"";
            $delete_ban = FUSION_SELF.fusion_get_aidlink()."&section=comments_listing&action=delban&comment_id=".$data['comment_id']."' onclick=\"return confirm('".$locale['435']."');\"";

            echo "<div class='list-group-item'>\n";
            echo "<div class='btn-group pull-right'>\n";
            echo "<a class='btn btn-xs btn-default' href='".$edit."'>".$locale['edit']."</a>\n";
            echo "<a class='btn btn-xs btn-default' href='".$delete."'>".$locale['delete']."</a>\n";

            if (!empty($data['user_id']) && $data['user_id'] != 1) {
                echo "<a class='btn btn-xs btn-default' href='".$delete_ban."'>".$locale['431']."</a>\n";
            }

            echo "</div>\n";
            echo $data['user_name'] ? profile_link($data['comment_name'], $data['user_name'], $data['user_status']) : $data['comment_name'];
            echo ' '.$locale['global_071'].showdate('longdate', $data['comment_datestamp']);
            echo "<span class='label label-default m-l-10'>".$locale['432']." ".$data['comment_ip']."</span>";
            echo !empty($data['comment_subject']) ? "<div class='m-t-10'>".$data['comment_subject']."</div>\n" : "";
            echo "<div class='m-t-10'>".nl2br(parse_textarea($data['comment_message'], TRUE, TRUE, FALSE))."</div>\n";
            echo "</div>\n";
        }
        echo '</div>';

        if ($total_rows > $rows) {
            echo '<div class="m-t-5 m-b-5">';
            echo makepagenav($rowstart, $limit, $total_rows, 3, FUSION_SELF.fusion_get_aidlink()."&ctype=".$ctype."&");
            echo '</div>';
        }
    } else {
        echo "<div class='alert alert-info text-center'>".$locale['434']."</div>";
    }
}

require_once THEMES.'templates/footer.php';
