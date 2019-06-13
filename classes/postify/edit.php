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
namespace PHPFusion\Infusions\Forum\Classes\Postify;

use PHPFusion\Infusions\Forum\Classes\Forum_Postify;

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

        $post_count = get('post_count', FILTER_VALIDATE_INT);

        $post_id = get('post_id');

        $thread_id = get('thread_id');

        $forum_id = get('forum_id');

        if ($post_count) {

            // Post deleted
            add_to_title(self::$locale['global_201'].self::$locale['forum_0506']);

            add_breadcrumb(['link' => FUSION_REQUEST, 'title' => self::$locale['forum_0506']]);

            redirect(self::$default_redirect_link, 3);

            $title = self::$locale['forum_0506'];

            $description = self::$locale['forum_0546'];

            if ($post_count > 0 && $thread_id) {

                $link[] = ['url' => $settings['siteurl'].'infusions/forum/viewthread.php?thread_id='.$thread_id, 'title' => self::$locale['forum_0548']];
            }

            $link[] = ['url' => $settings['siteurl'].'infusions/forum/index.php?viewforum.php?forum_id='.$forum_id, 'title' => self::$locale['forum_0549']];

            $link[] = ['url' => $settings['siteurl'].'infusions/forum/index.php', 'title' => self::$locale['forum_0550']];

        } else {

            // Post Edited
            add_to_title(self::$locale['global_201'].self::$locale['forum_0508']);

            add_breadcrumb(['link' => FUSION_REQUEST, 'title' => self::$locale['forum_0508']]);


            if ($post_id && $thread_id) {
                redirect($settings['siteurl'].'infusions/forum/viewthread.php?thread_id='.$thread_id.'&amp;pid='.$post_id.'#post_'.$post_id, 3);
            } else {
                redirect($settings['siteurl'].'infusions/forum/index.php?viewforum&amp;forum_id='.$forum_id, 3);
            }

            $title = self::$locale['forum_0508'];

            $description = $this->get_postify_error_message() ?: self::$locale['forum_0547'];

            if ($post_id && $thread_id) {
                $link[] = ['url' => $settings['siteurl'].'infusions/forum/viewthread.php?thread_id='.$thread_id.'&amp;pid='.$post_id.'#post_'.$post_id, 'title' => self::$locale['forum_0548']];
            } else {
                $link[] = ['url' => $settings['siteurl'].'infusions/forum/viewthread.php?thread_id='.$thread_id, 'title' => self::$locale['forum_0548']];
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
