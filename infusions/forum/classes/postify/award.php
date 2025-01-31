<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: award.php
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

/**
 * Class Postify_Award
 *
 * @status  Stable
 *
 * @package PHPFusion\Forums\Postify
 */
class Postify_Award extends Forum_Postify {

    public function execute() {
        add_breadcrumb(['link' => FUSION_REQUEST, 'title' => self::$locale['forum_4107']]);
        $thread_data = dbarray(dbquery("SELECT thread_id, forum_id, thread_lastpostid, thread_postcount, thread_subject FROM
        ".DB_FORUM_THREADS." WHERE thread_id=:thread_id", [':thread_id' => $_GET['thread_id']]));
        if (!empty($thread_data)) {
            $thread_data['thread_link'] = fusion_get_settings('siteurl')."infusions/forum/viewthread.php?forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']."&pid=".$thread_data['thread_lastpostid']."#post_".$thread_data['thread_lastpostid'];
            $forum_index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
            if ($this->checkForumAccess($forum_index, $_GET['forum_id'], $_GET['thread_id'])) {
                $title = ($_GET['error'] == 7 ? self::$locale['forum_4109'] : self::$locale['forum_4107']);
                $description = ($_GET['error'] == 7 ? self::$locale['forum_4110'] : self::$locale['forum_4108']);
                render_postify([
                    'title'       => $title,
                    'error'       => parent::getPostifyErrorMessage(),
                    'description' => $description,
                    'link'        => $this->getPostifyUri()
                ]);
                redirect($thread_data['thread_link'], 3);
            }
        }
    }

}
