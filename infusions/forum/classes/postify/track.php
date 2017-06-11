<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: track.php
| Author: Chan (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Forums\Postify;

use PHPFusion\BreadCrumbs;

/**
 * Thread Tracking
 * Class Postify_Track
 *
 * @status  stable
 *
 * @package PHPFusion\Forums\Postify
 *
 */
class Postify_Track extends Forum_Postify {
    /*
     * Tracking on or off
     */
    public function execute() {

        BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => parent::$locale['forum_0552']]);

        $thread_data = dbarray(dbquery("SELECT thread_id, forum_id, thread_lastpostid, thread_postcount, thread_subject FROM ".DB_FORUM_THREADS." WHERE thread_id=:thread_id", [':thread_id' => $_GET['thread_id']]));

        if (!empty($thread_data)) {

            if (self::$forum_settings['thread_notify']) {

                $thread_data['thread_link'] = FORUM.'viewthread.php?thread_id='.$thread_data['thread_id'].'&pid='.$thread_data['thread_lastpostid'].'#post_'.$thread_data['thread_lastpostid'];

                $forum_index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
                if ($this->check_forum_access($forum_index, $_GET['forum_id'], $_GET['thread_id'])) {
                    $description = '';

                    switch ($_GET['post']) {
                        case 'on':
                            if (!dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY, "thread_id='".$_GET['thread_id']."' AND notify_user='".fusion_get_userdata('user_id')."'")) {
                                dbquery("INSERT INTO ".DB_FORUM_THREAD_NOTIFY." (thread_id, notify_datestamp, notify_user, notify_status) VALUES ('".$_GET['thread_id']."', '".TIME."', '".fusion_get_userdata('user_id')."', '1')");
                                $description = self::$locale['forum_0553'];
                            }
                            break;
                        case 'off':
                            dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE thread_id='".$_GET['thread_id']."' AND notify_user='".fusion_get_userdata('user_id')."'");
                            $description = self::$locale['forum_0554'];
                            break;
                    }
                    $link[] = ['url' => FORUM."viewthread.php?thread_id=".$thread_data['thread_id'], 'title' => self::$locale['forum_0548']];
                    $link[] = ['url' => FORUM."index.php?viewforum&amp;forum_id=".$thread_data['forum_id'], 'title' => self::$locale['forum_0549']];
                    $link[] = ['url' => FORUM."index.php", 'title' => self::$locale['forum_0550']];
                    render_postify([
                        'title'       => self::$locale['forum_0552'],
                        'error'       => parent::get_postify_error_message(),
                        'description' => $description,
                        'link'        => $link
                    ]);

                    redirect($thread_data['thread_link'], 3);

                } else {

                    redirect($thread_data['thread_link']);

                }
            } else {

                redirect($thread_data['thread_link'], 3);

            }
        } else {
            redirect(self::$default_redirect_link);
        }
    }
}