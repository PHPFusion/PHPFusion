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

        if (isset($_GET['post_count'])) {

            // Post deleted
            add_to_title(self::$locale['global_201'].self::$locale['forum_0506']);
            BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => self::$locale['forum_0506']]);
            redirect(self::$default_redirect_link, 3);

            $title = self::$locale['forum_0506'];
            $description = self::$locale['forum_0546'];
            if ($_GET['post_count'] > 0) {
                $link[] = ['url' => FORUM.'viewthread.php?thread_id='.$_GET['thread_id'], 'title' => self::$locale['forum_0548']];
            }
            $link[] = ['url' => FORUM.'index.php?viewforum.php?forum_id='.$_GET['forum_id'], 'title' => self::$locale['forum_0549']];
            $link[] = ['url' => FORUM.'index.php', 'title' => self::$locale['forum_0550']];

        } else {

            // Post Edited
            add_to_title(self::$locale['global_201'].self::$locale['forum_0508']);
            BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => self::$locale['forum_0508']]);
            redirect(FORUM.'viewthread.php?thread_id='.$_GET['thread_id'].'&amp;pid='.$_GET['post_id'].'#post_'.$_GET['post_id'], 3);

            $title = self::$locale['forum_0508'];
            $description = $this->get_postify_error_message() ?: self::$locale['forum_0547'];
            $link[] = ['url' => FORUM.'viewthread.php?thread_id='.$_GET['thread_id'].'&amp;pid='.$_GET['post_id'].'#post_'.$_GET['post_id'], 'title' => self::$locale['forum_0548']];
            $link[] = ['url' => FORUM.'index.php?viewforum&amp;forum_id='.$_GET['forum_id'], 'title' => self::$locale['forum_0549']];
            $link[] = ['url' => FORUM.'index.php', 'title' => self::$locale['forum_0550']];

        }

        render_postify([
            'title'       => $title,
            'error'       => $this->get_postify_error_message(),
            'description' => $description,
            'link'        => $link
        ]);
    }
}