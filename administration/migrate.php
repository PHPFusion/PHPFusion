<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: migrate.php
| Author: PHP-Fusion Development Team
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
pageAccess('MI');

$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/migrate.php');

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'migrate.php'.fusion_get_aidlink(), 'title' => $locale['MIG_100']]);

opentable($locale['MIG_100']);

if (isset($_POST['migrate'])) {
    $user_primary_id = form_sanitizer($_POST['user_primary'], '', 'user_primary');
    $user_temp_id = form_sanitizer($_POST['user_migrate'], '', 'user_migrate');

    if ($user_primary_id == $user_temp_id || !isnum($user_primary_id) || !isnum($user_temp_id)) {
        \defender::stop();
        addNotice('danger', $locale['MIG_101']);
        redirect(FUSION_REQUEST);
    }

    if (\defender::safe()) {
        $result = dbquery("SELECT user_id, user_name FROM ".DB_USERS." WHERE user_id=:userid", [':userid' => $user_primary_id]);
        if (dbrows($result) > 0) {
            $result2 = dbquery("SELECT user_id, user_name FROM ".DB_USERS." WHERE user_id=:userid", [':userid' => $user_temp_id]);
            if (dbrows($result2) > 0) {
                if (isset($_POST['forum']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_FORUM_THREAD_NOTIFY, 'notify_user', $locale['MIG_102']);
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_FORUM_THREADS, 'thread_author', $locale['MIG_103']);
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_FORUM_THREADS, 'thread_lastuser', $locale['MIG_104']);
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_FORUM_POSTS, 'post_author', $locale['MIG_105']);
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_FORUMS, 'forum_lastuser', $locale['MIG_106']);
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_FORUM_POLL_VOTERS, 'forum_vote_user_id', $locale['MIG_107']);
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_FORUM_VOTES, 'vote_user', $locale['MIG_108']);
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_USERS, 'user_reputation', $locale['MIG_109']);

                    $posts = dbcount("(post_id)", DB_FORUM_POSTS, "post_author=:postauthor", [':postauthor' => $user_temp_id]);
                    if ($posts > 0) {
                        dbquery("UPDATE ".DB_USERS." SET user_posts=:userposts WHERE user_id=:userid", [':userposts' => $posts, ':userid' => $user_primary_id]);
                    }
                }
                if (isset($_POST['comments']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_COMMENTS, 'comment_name', $locale['MIG_115']);
                }
                if (isset($_POST['ratings']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_RATINGS, 'rating_user', $locale['MIG_116']);
                }
                if (isset($_POST['messages']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_MESSAGES, 'message_to', $locale['MIG_117']);
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_MESSAGES, 'message_from', $locale['MIG_118']);
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_MESSAGES, 'message_user', $locale['MIG_119']);
                    $result = dbquery("DELETE FROM ".DB_MESSAGES."_options WHERE user_id=:userid", [':userid' => $user_temp_id]);
                }
                if (isset($_POST['polls']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_POLL_VOTES, 'vote_user', $locale['MIG_120']);
                }
                if (isset($_POST['shoutbox']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_SHOUTBOX, 'shout_name', $locale['MIG_121']);
                }
                if (isset($_POST['articles']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_ARTICLES, 'article_name', $locale['MIG_122']);
                }
                if (isset($_POST['faq']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_FAQS, 'faq_name', $locale['MIG_123']);
                }
                if (isset($_POST['news']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_NEWS, 'news_name', $locale['MIG_124']);
                }
                if (isset($_POST['blog']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_BLOG, 'blog_name', $locale['MIG_125']);
                }
                if (isset($_POST['downloads']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_DOWNLOADS, 'download_user', $locale['MIG_126']);
                }
                if (isset($_POST['photos']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_PHOTOS, 'photo_user', $locale['MIG_127']);
                }
                if (isset($_POST['user_level']) == '1') {
                    user_rights_migrate($user_primary_id, $user_temp_id);
                }
                if (isset($_POST['del_user']) == '1') {
                    $result = dbquery("DELETE FROM ".DB_USERS." WHERE user_id=:userid", [':userid' => $user_temp_id]);
                } else {
                    require_once INCLUDES."suspend_include.php";
                    $result = dbquery("UPDATE ".DB_USERS." SET user_status=:status WHERE user_id=:userid", [':status' => '7', ':userid' => $user_temp_id]);
                    suspend_log($user_temp_id, '7', $locale['MIG_130']);
                }
            } else {
                addNotice('danger', $locale['MIG_131']);
            }
        } else {
            addNotice('danger', $locale['MIG_132']);
        }
    }
} else {
    user_posts_migrate_console();
}
closetable();

function user_posts_migrate_console() {
    $locale = fusion_get_locale();

    $chkbox = [
        'user_level' => [
            'value'  => !empty($_POST['user_level']) ? $_POST['user_level'] : 0,
            'text'   => $locale['MIG_150'],
            'active' => TRUE
        ],
        'messages'   => [
            'value'  => !empty($_POST['messages']) ? $_POST['messages'] : 0,
            'text'   => $locale['MIG_151'],
            'active' => TRUE
        ],
        'comments'   => [
            'value'  => !empty($_POST['comments']) ? $_POST['comments'] : 0,
            'text'   => $locale['MIG_152'],
            'active' => TRUE
        ],
        'ratings'    => [
            'value'  => !empty($_POST['ratings']) ? $_POST['ratings'] : 0,
            'text'   => $locale['MIG_153'],
            'active' => TRUE
        ],
        'forum'      => [
            'value'  => !empty($_POST['forum']) ? $_POST['forum'] : 0,
            'text'   => $locale['MIG_154'],
            'active' => defined('FORUM_EXIST') ? TRUE : FALSE
        ],
        'articles'   => [
            'value'  => !empty($_POST['articles']) ? $_POST['articles'] : 0,
            'text'   => $locale['MIG_155'],
            'active' => defined('ARTICLES_EXIST') ? TRUE : FALSE
        ],
        'faq'        => [
            'value'  => !empty($_POST['faq']) ? $_POST['faq'] : 0,
            'text'   => $locale['MIG_156'],
            'active' => defined('FAQ_EXIST') ? TRUE : FALSE
        ],
        'polls'      => [
            'value'  => !empty($_POST['polls']) ? $_POST['polls'] : 0,
            'text'   => $locale['MIG_157'],
            'active' => defined('MEMBER_POLL_PANEL_EXIST') ? TRUE : FALSE
        ],
        'news'       => [
            'value'  => !empty($_POST['news']) ? $_POST['news'] : 0,
            'text'   => $locale['MIG_158'],
            'active' => defined('NEWS_EXIST') ? TRUE : FALSE
        ],
        'blog'       => [
            'value'  => !empty($_POST['blog']) ? $_POST['blog'] : 0,
            'text'   => $locale['MIG_159'],
            'active' => defined('BLOG_EXIST') ? TRUE : FALSE
        ],
        'downloads'  => [
            'value'  => !empty($_POST['downloads']) ? $_POST['downloads'] : 0,
            'text'   => $locale['MIG_160'],
            'active' => defined('DOWNLOADS_EXIST') ? TRUE : FALSE
        ],
        'photos'     => [
            'value'  => !empty($_POST['photos']) ? $_POST['photos'] : 0,
            'text'   => $locale['MIG_161'],
            'active' => defined('GALLERY_EXIST') ? TRUE : FALSE
        ],
        'shoutbox'   => [
            'value'  => !empty($_POST['shoutbox']) ? $_POST['shoutbox'] : 0,
            'text'   => $locale['MIG_162'],
            'active' => defined('SHOUTBOX_PANEL_EXIST') ? TRUE : FALSE
        ],
    ];

    echo openform('inputform', 'post', FUSION_REQUEST);
    echo "<div class='m-t-20'>";
    echo "<div class='col-xs-12 col-sm-12'>\n";
    echo "<div class='row'>\n";
    echo "<div class='col-xs-12 col-sm-4'>\n";
    echo form_user_select('user_primary', $locale['MIG_135'], (isset($_POST['user_primary']) && isnum($_POST['user_primary']) ? $_POST['user_primary'] : ''), [
        'placeholder' => $locale['MIG_136']
    ]);
    echo "</div>";
    echo "<div class='col-xs-12 col-sm-8'>\n";
    echo form_user_select('user_migrate', $locale['MIG_137'], (isset($_POST['user_migrate']) && isnum($_POST['user_migrate']) ? $_POST['user_migrate'] : ''), [
        'placeholder' => $locale['MIG_138']
    ]);
    echo "</div>";

    echo "</div>\n";
    echo "<div class='row'>\n";
    echo "<div class='col-xs-12 col-sm-4'><h4 class='m-0'>".$locale['MIG_139']."</h4><i>".$locale['MIG_140']."</i></div>\n";
    echo "<div class='col-xs-12 col-sm-8'>\n";
    foreach ($chkbox as $key => $chkboxinfo) {
        if (!empty($chkboxinfo['active'])) {
            echo "<div class='display-block overflow-hide'>";
            echo form_checkbox($key, $chkboxinfo['text'], $chkboxinfo['value'], [
                'type'          => 'checkbox',
                'reverse_label' => TRUE,
                'class'         => 'm-b-0'
            ]);
            echo "</div>\n";
        }
    }
    echo "</div>\n</div>\n";
    echo "<div class='row m-t-20'>\n";
    echo "<div class='col-xs-12 col-sm-4'><h4 class='m-0'>".$locale['MIG_141']."</h4></div>\n";
    echo "<div class='col-xs-12 col-sm-8'>\n";
    echo "<div class='display-block overflow-hide'>";
    echo form_checkbox('del_user', $locale['MIG_170'], '', [
        'type'          => 'checkbox',
        'reverse_label' => TRUE,
        'ext_tip'       => $locale['MIG_171'],
        'class'         => 'm-b-0'
    ]);
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo form_button('migrate', $locale['MIG_175'], $locale['MIG_175'], ['inline' => TRUE, 'class' => 'btn-primary m-t-20']);
    echo "</div>\n";
    echo closeform();
}

function user_posts_migrate($user_primary_id, $user_temp_id, $db, $user_column, $name) {
    $locale = fusion_get_locale();

    $users = dbarray(dbquery("SELECT user_name FROM ".DB_USERS." WHERE user_id=:userid", [':userid' => $user_temp_id]));
    $p_user = dbarray(dbquery("SELECT user_name FROM ".DB_USERS." WHERE user_id=:userid", [':userid' => $user_primary_id]));
    $rows = dbcount("($user_column)", $db, "$user_column=:usercolumn", [':usercolumn' => $user_temp_id]);

    if (($rows) > 0) {
        $result = dbquery("UPDATE ".$db." SET $user_column=:primaryid WHERE $user_column=:tempid", [':primaryid' => $user_primary_id, ':tempid' => $user_temp_id]);
        if (!$result) {
            addNotice('danger', $locale['MIG_200']);
        } else {
            echo "<div class='well text-center'>".(sprintf($locale['MIG_201'], $rows, $name, $users['user_name'], $p_user['user_name']))."</div>";
        }
    } else {
        echo "<div class='well text-center'>".(sprintf($locale['MIG_202'], $name))."</div>\n";
    }
}

function user_rights_migrate($user_primary_id, $user_temp_id) {
    $locale = fusion_get_locale();

    $result = dbquery("SELECT * FROM ".DB_USERS." WHERE user_id=:userid", [':userid' => $user_temp_id]);
    if (dbrows($result) > 0) {
        $data = dbarray($result);
        $result2 = dbquery("SELECT * FROM ".DB_USERS." WHERE user_id=:userid", [':userid' => $user_primary_id]);
        if (dbrows($result2) > 0) {
            $cdata = dbarray($result2);
            $old_user_rights = explode(".", $data['user_rights']);
            $new_user_rights = explode(".", $cdata['user_rights']);
            if (is_array($old_user_rights)) {
                if (empty($new_user_rights['0'])) {
                    $result = dbquery("UPDATE ".DB_USERS." SET user_rights=:rights WHERE user_id=:userid", [':rights' => $data['user_rights'], ':userid' => $user_primary_id]);
                    if (!$result) {
                        addNotice('danger', $locale['MIG_203']);
                    } else {
                        echo "<div class='well text-center'>".(sprintf($locale['MIG_204'], count($old_user_rights), $data['user_name'], $cdata['user_name']))."</div>\n";
                    }
                } else {
                    $rights_dump = [];
                    foreach ($old_user_rights as $arr => $value) {
                        if (!in_array($value, $new_user_rights)) {
                            $rights_dump[] = $value;
                        }
                    }
                    $new_rights = array_merge($rights_dump, $new_user_rights);
                    $rights = implode($new_rights, '.');
                    $result = dbquery("UPDATE ".DB_USERS." SET user_rights=:rights WHERE user_id=:userid", [':rights' => $rights, ':userid' => $user_primary_id]);
                    if (!$result) {
                        addNotice('danger', $locale['MIG_203']);
                    } else {
                        echo "<div class='well text-center'>".(sprintf($locale['MIG_204'], count($rights_dump), $data['user_name'], $cdata['user_name']))."</div>\n";
                    }
                }
            }

            $old_user_groups = explode(".", $data['user_groups']);
            $new_user_groups = explode(".", $cdata['user_groups']);
            if (is_array($old_user_groups)) {
                if (empty($new_user_groups['0'])) {
                    $result = dbquery("UPDATE ".DB_USERS." SET user_groups=:groups WHERE user_id=:userid", [':groups' => $data['user_groups'], ':userid' => $user_primary_id]);
                    if (!$result) {
                        addNotice('danger', $locale['MIG_205']);
                    } else {
                        echo "<div class='well text-center'>".(sprintf($locale['MIG_206'], count($old_user_groups), $data['user_name'], $cdata['user_name']))."</div>\n";
                    }
                } else {
                    $group_dump = [];
                    foreach ($old_user_groups as $arr => $value) {
                        if (!in_array($value, $new_user_groups)) {
                            $group_dump[] = $value;
                        }
                    }
                    $new_group = array_merge($group_dump, $new_user_groups);
                    $groups = implode($new_group, '.');
                    $result = dbquery("UPDATE ".DB_USERS." SET user_groups=:groups WHERE user_id=:userid", [':groups' => $groups, ':userid' => $user_primary_id]);
                    if (!$result) {
                        addNotice('danger', $locale['MIG_205']);
                    } else {
                        echo "<div class='well text-center'>".(sprintf($locale['MIG_206'], count($group_dump), $data['user_name'], $cdata['user_name']))."</div>\n";
                    }
                }
            }

            if ($data['user_level'] > $cdata['user_level']) {
                $result = dbquery("UPDATE ".DB_USERS." SET user_level=:level WHERE user_id=:userid", [':level' => $data['user_level'], ':userid' => $user_primary_id]);
                if (!$result) {
                    addNotice('danger', $locale['MIG_207']);
                } else {
                    echo "<div class='well text-center'>".(sprintf($locale['MIG_208'], $data['user_level'], $data['user_name'], $cdata['user_name']))."</div>\n";
                }
            } else {
                addNotice('danger', $locale['MIG_209']);
            }
        } else {
            addNotice('danger', $locale['MIG_207'].$user_primary_id);
        }
    } else {
        addNotice('danger', $locale['MIG_207'].$user_temp_id);
    }
}

require_once THEMES.'templates/footer.php';
