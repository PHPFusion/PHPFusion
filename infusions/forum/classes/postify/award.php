<?php
namespace PHPFusion\Forums\Postify;

use PHPFusion\BreadCrumbs;

/**
 * Class Postify_Award
 *
 * @status  Stable
 *
 * @package PHPFusion\Forums\Postify
 */
class Postify_Award extends Forum_Postify {

    public function execute() {
        BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => self::$locale['forum_4107']]);
        $thread_data = dbarray(dbquery("SELECT thread_id, forum_id, thread_lastpostid, thread_postcount, thread_subject FROM 
        ".DB_FORUM_THREADS." WHERE thread_id=:thread_id", [':thread_id' => $_GET['thread_id']]));
        if (!empty($thread_data)) {
            $thread_data['thread_link'] = fusion_get_settings('siteurl')."infusions/forum/viewthread.php?forum_id=".$thread_data['forum_id']."&thread_id=".$thread_data['thread_id']."&pid=".$thread_data['thread_lastpostid']."#post_".$thread_data['thread_lastpostid'];
            $forum_index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
            if ($this->check_forum_access($forum_index, $_GET['forum_id'], $_GET['thread_id'])) {
                render_postify([
                    'title'       => self::$locale['forum_4107'],
                    'error'       => parent::get_postify_error_message(),
                    'description' => self::$locale['forum_4108'],
                    'link'        => $this->get_postify_uri()
                ]);
                redirect($thread_data['thread_link'], 3);
            }
        }
    }

}
