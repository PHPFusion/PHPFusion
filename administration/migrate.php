<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: migrate.php
| Author: Frederick Chan MC (Chan)
| Co-Author: Joakim Falk (Falk)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../maincore.php";
pageAccess('MI');

require_once THEMES."templates/admin_header.php";

if (isset($_POST['user_primary']) && !isnum($_POST['user_primary'])) {
    die("Access Denied");
}
if (isset($_POST['user_migrate']) && !isnum($_POST['user_migrate'])) {
    die("Access Denied");
}

include LOCALE.LOCALESET."admin/migrate.php";

\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'migrate.php'.fusion_get_aidlink(), 'title' => $locale['100']]);

$settings = fusion_get_settings();

if (isset($_POST['migrate'])) {
    $user_primary_id = stripinput($_POST['user_primary']);
    $user_temp_id = stripinput($_POST['user_migrate']);
    if ($user_primary_id == $user_temp_id) {
        echo "<div class='well text-center'>".$locale['101']."</div>\n";
    } else {
        $result = dbquery("SELECT user_id, user_name FROM ".DB_USERS." WHERE user_id='$user_primary_id'");
        if (dbrows($result) > 0) {
            $result2 = dbquery("SELECT user_id, user_name FROM ".DB_USERS." WHERE user_id='$user_temp_id'");
            if (dbrows($result2) > 0) {
                if (isset($_POST['forum']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_FORUM_THREAD_NOTIFY, 'notify_user', $locale['102']);
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_FORUM_THREADS, 'thread_author', $locale['103']);
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_FORUM_THREADS, 'thread_lastuser', $locale['104']);
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_FORUM_POSTS, 'post_author', $locale['105']);
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_FORUMS, 'forum_lastuser', $locale['106']);
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_FORUM_POLL_VOTERS, 'forum_vote_user_id', $locale['107']);

                    $result = dbquery("SELECT post_author, COUNT(post_id) as num_posts FROM ".DB_FORUM_POSTS." GROUP BY post_author");
                    if (dbrows($result)) {
                        while ($data = dbarray($result)) {
                            $result2 = dbquery("UPDATE ".DB_USERS." SET user_posts='".$data['num_posts']."' WHERE user_id='".$data['post_author']."'");
                        }
                    }
                }
                if (isset($_POST['comments']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_COMMENTS, 'comment_name', $locale['108']);
                }
                if (isset($_POST['ratings']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_RATINGS, 'rating_user', $locale['109']);
                }
                if (isset($_POST['polls']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_POLL_VOTES, 'vote_user', $locale['110']);
                }
                if (isset($_POST['shoutbox']) == '1') {
                    $result = dbcount("(inf_id)", DB_INFUSIONS, "inf_folder='shoutbox_panel'");
                    if ($result > 0) {
                        require_once INFUSIONS."shoutbox_panel/infusion_db.php";
                        user_posts_migrate($user_primary_id, $user_temp_id, DB_SHOUTBOX, 'shout_name', $locale['111']);
                    }
                }
                if (isset($_POST['messages']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_MESSAGES, 'message_to', $locale['112']);
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_MESSAGES, 'message_from', $locale['113']);
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_MESSAGES, 'message_user', $locale['114']);
                    $result = dbquery("DELETE FROM ".DB_MESSAGES_OPTIONS." WHERE user_id='".$user_temp_id."'");
                }
                if (isset($_POST['articles']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_ARTICLES, 'article_name', $locale['115']);
                }
                if (isset($_POST['news']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_NEWS, 'news_name', $locale['116']);
                }
                if (isset($_POST['blog']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_BLOG, 'blog_name', $locale['117']);
                }
                if (isset($_POST['downloads']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_DOWNLOADS, 'download_user', $locale['118']);
                }
                if (isset($_POST['photos']) == '1') {
                    user_posts_migrate($user_primary_id, $user_temp_id, DB_PHOTOS, 'photo_user', $locale['119']);
                }
                if (isset($_POST['user_level']) == '1') {
                    user_rights_migrate($user_primary_id, $user_temp_id);
                }
                if (isset($_POST['del_user']) == '1') {
                    $result = dbquery("DELETE FROM ".DB_USERS." WHERE user_id='$user_temp_id'");
                } else {
                    require_once INCLUDES."suspend_include.php";
                    $result = dbquery("UPDATE ".DB_USERS." SET user_status='7' WHERE user_id='$user_temp_id'");
                    suspend_log($user_temp_id, '7', $locale['121']);
                }
            } else {
                echo "<div class='well text-center'>".$locale['122']."</div>\n";
            }
        } else {
            echo "<div class='well text-center'>".$locale['123']."</div>\n";
        }
    }
}

opentable($locale['100']);
user_posts_migrate_console();
closetable();

function user_posts_migrate_console() {
    global $aidlink, $locale;

    $result = dbquery("SELECT user_id, user_name FROM ".DB_USERS."");
    if (dbrows($result) > 0) {
        while ($user_data = dbarray($result)) {
            $data[$user_data['user_id']] = "".$user_data['user_name']."";
        }
    } else {
        $data['0'] = $locale['124'];
    }

    echo openform('inputform', 'post', FUSION_SELF.$aidlink);
    echo "<div class='table-responsive'><table class='table table-striped'>\n";
    echo "<thead>\n";
    echo "<tr style='height:30px;'><th style='width:33%; text-align:left'>".$locale['125']."</th><th style='width:33%; text-align:left;'>".$locale['126']."</th><th class='text-left'>&nbsp;</th>\n</tr>\n";
    echo "</thead>\n";
    echo "<tbody>\n";
    echo "<tr>\n";
    echo "<td>\n";
    echo form_user_select('user_primary', '', isset($_POST['user_primary']) && isnum($_POST['user_primary'] ?: ''),
                          array('placeholder' => $locale['127']));
    echo "</td>\n";
    echo "<td>\n";
    echo form_user_select('user_migrate', '', isset($_POST['user_migrate']) && isnum($_POST['user_migrate'] ?: ''),
                          array('placeholder' => $locale['128']));
    echo "</td>\n";
    echo "<td>\n";
    echo form_button('migrate', $locale['129'], $locale['129'], array('inline' => '1', 'class' => 'btn btn-sm btn-primary'));
    echo "</td>\n";
    echo "</tr>\n";
    echo "<tr>\n";
    echo "<td>".$locale['130']."</td>";
    echo "<td colspan='2'>\n";
    echo "<input type='checkbox' name='comments' value='1' ".(isset($_POST['comments']) == '1' ? 'checked' : '')."> ".$locale['132']."<br />";
    echo "<input type='checkbox' name='ratings' value='1' ".(isset($_POST['ratings']) == '1' ? 'checked' : '')."> ".$locale['133']."<br />";
    echo "<input type='checkbox' name='polls' value='1' ".(isset($_POST['polls']) == '1' ? 'checked' : '')."> ".$locale['134']."<br />";
    echo "<input type='checkbox' name='messages' value='1' ".(isset($_POST['messages']) == '1' ? 'checked' : '')."> ".$locale['136']."<br />";
    echo "<input type='checkbox' name='user_level' value='1' ".(isset($_POST['user_level']) == '1' ? 'checked' : '')."> ".$locale['142']."<br />";
    if (db_exists(DB_FORUMS)) {
        echo "<input type='checkbox' name='forum' value='1' ".(isset($_POST['forum']) == '1' ? 'checked' : '')."> ".$locale['131']."<br />\n";
    }
    if (db_exists(DB_ARTICLES)) {
        echo "<input type='checkbox' name='articles' value='1' ".(isset($_POST['articles']) == '1' ? 'checked' : '')."> ".$locale['137']."<br />";
    }
    if (db_exists(DB_NEWS)) {
        echo "<input type='checkbox' name='news' value='1' ".(isset($_POST['news']) == '1' ? 'checked' : '')."> ".$locale['138']."<br />";
    }
    if (db_exists(DB_BLOG)) {
        echo "<input type='checkbox' name='blog' value='1' ".(isset($_POST['blog']) == '1' ? 'checked' : '')."> ".$locale['139']."<br />";
    }
    if (db_exists(DB_DOWNLOADS)) {
        echo "<input type='checkbox' name='downloads' value='1' ".(isset($_POST['downloads']) == '1' ? 'checked' : '')."> ".$locale['140']."<br />";
    }
    if (db_exists(DB_PHOTOS)) {
        echo "<input type='checkbox' name='photos' value='1' ".(isset($_POST['photos']) == '1' ? 'checked' : '')."> ".$locale['141']."<br />";
    }
    $shoutbox = dbcount("(inf_id)", DB_INFUSIONS, "inf_folder='shoutbox_panel'");
    if ($shoutbox > 0) {
        echo "<input type='checkbox' name='shoutbox' value='1' ".(isset($_POST['shoutbox']) == '1' ? 'checked' : '')."> ".$locale['135']."<br />";
    }
    echo "</td>\n";
    echo "</tr>\n";
    echo "<tr>\n";
    echo "<td>".$locale['143']."</td>";
    echo "<td colspan='3'>\n";
    echo "<input type='checkbox' name='del_user' value='1'> ".$locale['144']."<br /> ".$locale['145']."\n";
    echo "</td>\n";
    echo "</tr>\n";
    echo "</tbody>\n";
    echo "</table>\n</div>";
    echo closeform();
}

function user_posts_migrate($user_primary_id, $user_temp_id, $db, $user_column, $name) {
    global $locale;

    $users = dbarray(dbquery("SELECT user_name FROM ".DB_USERS." WHERE user_id='$user_temp_id'"));
    $p_user = dbarray(dbquery("SELECT user_name FROM ".DB_USERS." WHERE user_id='$user_primary_id'"));
    $rows = dbcount("($user_column)", $db, "$user_column='$user_temp_id'");

    if (($rows) > 0) {
        $result = dbquery("UPDATE ".$db." SET $user_column='$user_primary_id' WHERE $user_column='$user_temp_id'");
        if (!$result) {
            echo "<div class='well text-center'>".$locale['146']."</div>";
        } else {
            echo "<div class='well text-center'>$rows ".($rows > 1 ? $locale['147'] : $locale['148'])." ".$locale['149']." <strong>$name</strong> ".$locale['150']." ".$users['user_name']." ".$locale['151']." ".$p_user['user_name'].".</div>";
        }
    } else {
        echo "<div class='well text-center'>".$locale['152']." <strong>$name</strong></div>\n";
    }
}

function user_rights_migrate($user_primary_id, $user_temp_id) {
    global $locale;

    $result = dbquery("SELECT * FROM ".DB_USERS." WHERE user_id='$user_temp_id'");
    if (dbrows($result) > 0) {
        $data = dbarray($result);
        $result2 = dbquery("SELECT * FROM ".DB_USERS." WHERE user_id='$user_primary_id'");
        if (dbrows($result2) > 0) {
            $cdata = dbarray($result2);
            $old_user_rights = explode(".", $data['user_rights']);
            $new_user_rights = explode(".", $cdata['user_rights']);
            if (is_array($old_user_rights)) {
                if (empty($new_user_rights['0'])) {
                    $result = dbquery("UPDATE ".DB_USERS." SET user_rights='".$data['user_rights']."' WHERE user_id='$user_primary_id'");
                    if (!$result) {
                        echo "<div class='well text-center'>".$locale['153']."</div>\n";
                    } else {
                        echo "<div class='well text-center'>".count($old_user_rights)." ".$locale['154']." <strong>".$locale['155']."</strong> ".$locale['156']." ".$data['user_name']." ".$locale['151']." ".$cdata['user_name'].".</div>\n";
                    }
                } else {
                    $rights_dump = array();
                    foreach ($old_user_rights as $arr => $value) {
                        if (!in_array($value, $new_user_rights)) {
                            $rights_dump[] = $value;
                        }
                    }
                    $new_rights = array_merge($rights_dump, $new_user_rights);
                    $rights = implode($new_rights, '.');
                    $result = dbquery("UPDATE ".DB_USERS." SET user_rights='$rights' WHERE user_id='$user_primary_id'");
                    if (!$result) {
                        echo "<div class='well text-center'>".$locale['153']."</div>\n";
                    } else {
                        echo "<div class='well text-center'>".count($rights_dump)." ".$locale['154']." <strong>".$locale['155']."</strong> ".$locale['156']." ".$data['user_name']." ".$locale['151']." ".$cdata['user_name'].".</div>\n";
                    }
                }
            }

            $old_user_groups = explode(".", $data['user_groups']);
            $new_user_groups = explode(".", $data['user_groups']);
            if (is_array($old_user_groups)) {
                if (empty($new_user_groups['0'])) {
                    $result = dbquery("UPDATE ".DB_USERS." SET user_groups='".$data['user_groups']."' WHERE user_id='$user_primary_id'");
                    if (!$result) {
                        echo "<div class='well text-center'>".$locale['157']."</div>\n";
                    } else {
                        echo "<div class='well text-center'>".count($old_user_groups)." ".$locale['154']." <strong>".$locale['158']."</strong> ".$locale['156']." ".$data['user_name']." ".$locale['151']." ".$cdata['user_name'].".</div>\n";
                    }
                } else {
                    $group_dump = array();
                    foreach ($old_user_groups as $arr => $value) {
                        if (!in_array($value, $new_user_groups)) {
                            $group_dump[] = $value;
                        }
                    }
                    $new_group = array_merge($group_dump, $new_user_groups);
                    $groups = implode($new_group, '.');
                    $result = dbquery("UPDATE ".DB_USERS." SET user_groups='$groups' WHERE user_id='$user_primary_id'");
                    if (!$result) {
                        echo "<div class='well text-center'>".$locale['157']."</div>\n";
                    } else {
                        echo "<div class='well text-center'>".count($group_dump)." ".$locale['154']." <strong>".$locale['158']."</strong> ".$locale['156']." ".$data['user_name']." ".$locale['151']." ".$cdata['user_name'].".</div>\n";
                    }
                }
            }

            if ($data['user_level'] > $cdata['user_level']) {
                $result = dbquery("UPDATE ".DB_USERS." SET user_level='".$data['user_level']."' WHERE user_id='$user_primary_id'");
                if (!$result) {
                    echo "<div class='well text-center'>".$locale['159']."</div>\n";
                } else {
                    echo "<div class='well text-center'><strong>".$locale['160']." ".$data['user_level']."</strong> ".$locale['156']." ".$data['user_name']." ".$locale['151']." ".$cdata['user_name'].".</div>\n";
                }
            } else {
                echo "<div class='well text-center'>".$locale['161']."</div>\n";
            }
        } else {
            echo "<div class='well text-center'>".$locale['162']." $user_primary_id.</div>\n";
        }
    } else {
        echo "<div class='well text-center'>".$locale['162']." $user_temp_id.</div>\n";
    }
}

require_once THEMES."templates/footer.php";
