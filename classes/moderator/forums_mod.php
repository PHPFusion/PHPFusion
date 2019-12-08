<?php
namespace PHPFusion\Infusions\Forum\Classes\Moderator;

use PHPFusion\Infusions\Forum\Classes\Forum_Moderator;

class Forums_Mod {

    private $class = NULL;

    public function __construct(Forum_Moderator $obj) {
        $this->class = $obj;
    }

    /**
     * Refresh forum stats
     * Refactor, checked
     */
    public function refreshForums() {

        $forum_id = $this->class->getForumID();

        if ($this->class->verifyForumID($forum_id)) {

            $thread_count = dbcount("(forum_id)", DB_FORUM_THREADS, "forum_id=:fid", [':fid' => (int)$forum_id]);

            if ($thread_count) {

                list($forum_id, $post_id, $user_id, $lastpost, $post_count) = dbarraynum(dbquery("
                SELECT p.forum_id, p.post_id, p.post_author, p.post_datestamp, COUNT(p.post_id) 'post_count'            
                FROM ".DB_FORUM_POSTS." p  
                INNER JOIN ".DB_FORUMS." fo ON p.forum_id=fo.forum_id
                WHERE p.forum_id=:fid AND p.post_hidden=0 
                GROUP BY p.post_id
                ORDER BY p.post_datestamp DESC LIMIT 1", [':fid' => (int)$forum_id]));

                dbquery("UPDATE ".DB_FORUMS." SET forum_lastpostid=:pid, forum_lastpost=:time, forum_postcount=:pcount, forum_threadcount=:tcount, forum_lastuser=:aid WHERE forum_id=:fid", [
                    ':pid'    => (int)$post_id,
                    ':time'   => (int)$lastpost,
                    ':pcount' => (int)$post_count,
                    ':tcount' => (int)$thread_count,
                    ':aid'    => (int)$user_id,
                    ':fid'    => (int)$forum_id
                ]);
            } else {
                dbquery("UPDATE ".DB_FORUMS." SET forum_lastpostid=0, forum_lastpost=0, forum_postcount=0, forum_threadcount=0, forum_lastuser=0 WHERE forum_id=:fid", [':fid' => $forum_id]);
            }
        }
    }
}
