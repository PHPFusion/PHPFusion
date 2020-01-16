<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: edit.php
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
use PHPFusion\Forums\Threads;

/**
 * Forum Edit Reply
 * Class Postify_Reply
 *
 * @status  Stable
 *
 * @package PHPFusion\Forums\Postify
 */
class Postify_Edit extends Forum_Postify {

    public function execute() {

        $settings = fusion_get_settings();

        if (isset($_GET['post_count'])) {

            // Post deleted
            add_to_title(self::$locale['global_201'].self::$locale['forum_0506']);
            BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => self::$locale['forum_0506']]);
            redirect(self::$default_redirect_link, 3);

            $title = self::$locale['forum_0506'];
            $description = self::$locale['forum_0546'];
            if ($_GET['post_count'] > 0) {
                $link[] = ['url' => $settings['siteurl'].'infusions/forum/viewthread.php?thread_id='.$_GET['thread_id'], 'title' => self::$locale['forum_0548']];
            }
            $link[] = ['url' => $settings['siteurl'].'infusions/forum/index.php?viewforum.php?forum_id='.$_GET['forum_id'], 'title' => self::$locale['forum_0549']];
            $link[] = ['url' => $settings['siteurl'].'infusions/forum/index.php', 'title' => self::$locale['forum_0550']];

        } else {

            // Post Edited
            add_to_title(self::$locale['global_201'].self::$locale['forum_0508']);
            BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => self::$locale['forum_0508']]);
            $inf_settings = get_settings('forum');
            $thread_data = \PHPFusion\Forums\Threads\ForumThreads::get_thread($_GET['thread_id']);

            $thread_rowstart = '';
            if (!empty($inf_settings['posts_per_page']) && $thread_data['thread_postcount'] > $inf_settings['posts_per_page']) {
                $thread_posts = dbquery("SELECT p.post_id, p.forum_id, p.thread_id, p.post_author, p.post_datestamp
                                FROM ".DB_FORUM_POSTS." p
                                LEFT JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id
                                WHERE p.forum_id='".$thread_data['forum_id']."' AND p.thread_id='".$thread_data['thread_id']."' AND thread_hidden='0' AND post_hidden='0'
                                ORDER BY post_datestamp ASC");
                if (dbrows($thread_posts)) {
                    $counter = 1;
                    while ($thread_post_data = dbarray($thread_posts)) {
                        if ($thread_post_data['post_id'] == $_GET['post_id']) {
                            $thread_rowstart = $inf_settings['posts_per_page'] * floor(($counter - 1) / $inf_settings['posts_per_page']);
                            $thread_rowstart = "&amp;rowstart=".$thread_rowstart;
                        }
                        $counter++;
                    }
                }
            }

            if (isset($_GET['post_id'])) {
                redirect($settings['siteurl'].'infusions/forum/viewthread.php?thread_id='.$_GET['thread_id'].$thread_rowstart.'&amp;pid='.$_GET['post_id'].'#post_'.$_GET['post_id'], 3);
            } else {
                redirect($settings['siteurl'].'infusions/forum/index.php?viewforum&amp;forum_id='.$_GET['forum_id'], 3);
            }

            $title = self::$locale['forum_0508'];
            $description = $this->get_postify_error_message() ?: self::$locale['forum_0547'];

            if (isset($_GET['post_id'])) {
                $link[] = ['url' => $settings['siteurl'].'infusions/forum/viewthread.php?thread_id='.$_GET['thread_id'].$thread_rowstart.'&amp;pid='.$_GET['post_id'].'#post_'.$_GET['post_id'], 'title' => self::$locale['forum_0548']];
            } else {
                $link[] = ['url' => $settings['siteurl'].'infusions/forum/viewthread.php?thread_id='.$_GET['thread_id'], 'title' => self::$locale['forum_0548']];
            }

            $link[] = ['url' => $settings['siteurl'].'infusions/forum/index.php?viewforum&amp;forum_id='.$_GET['forum_id'], 'title' => self::$locale['forum_0549']];
            $link[] = ['url' => $settings['siteurl'].'infusions/forum/index.php', 'title' => self::$locale['forum_0550']];
        }

        render_postify([
            'title'       => $title,
            'error'       => $this->get_postify_error_message(),
            'description' => $description,
            'link'        => $link
        ]);
    }
}
